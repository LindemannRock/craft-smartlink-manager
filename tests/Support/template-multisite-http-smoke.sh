#!/usr/bin/env bash
set -Eeuo pipefail

SMOKE_ROOT="$(pwd -P)"
SMOKE_WORK="$SMOKE_ROOT/.craft-compat/template-http-smoke"
SMOKE_COOKIE="$SMOKE_WORK/cookies.txt"
SMOKE_LOGIN="$SMOKE_WORK/login.html"
SMOKE_SETUP="$SMOKE_WORK/setup.html"
SMOKE_GENERAL="$SMOKE_WORK/general.html"
SMOKE_STATE_FILE="$SMOKE_WORK/state.json"
SMOKE_ENV_BACKUP="$SMOKE_WORK/env.backup"

mkdir -p "$SMOKE_WORK"
cp .env "$SMOKE_ENV_BACKUP"

cleanup_smoke() {
  cleanup_status=$?
  trap - ERR
  set +e
  if [[ -f "$SMOKE_STATE_FILE" ]]; then
    php craft exec '
      $state = json_decode(file_get_contents(".craft-compat/template-http-smoke/state.json"), true, flags: JSON_THROW_ON_ERROR);
      \lindemannrock\smartlinkmanager\tests\Support\TemplateMultisiteHttpSmokeSetup::cleanup($state);
    '
  fi
  cp "$SMOKE_ENV_BACKUP" .env
  rm -rf "$SMOKE_WORK"
  exit "$cleanup_status"
}
trap 'printf "Authenticated multisite template smoke failed at line %s: %s\n" "$LINENO" "$BASH_COMMAND" >&2' ERR
trap cleanup_smoke EXIT INT TERM HUP

printf '\nSMARTLINK_TEMPLATE_HTTP_REDIRECT="http-smoke/redirect"\nSMARTLINK_TEMPLATE_HTTP_QR="http-smoke/qr"\n' >> .env

php craft exec '
  file_put_contents(
    ".craft-compat/template-http-smoke/state.json",
    json_encode(
      \lindemannrock\smartlinkmanager\tests\Support\TemplateMultisiteHttpSmokeSetup::seed(),
      JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    ),
  );
'

state_value() {
  php -r '
    $state = json_decode(file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);
    $key = $argv[2];
    $value = $state[$key] ?? null;
    if (!is_scalar($value)) { throw new RuntimeException("Missing scalar smoke state: {$key}"); }
    echo $value;
  ' "$SMOKE_STATE_FILE" "$1"
}

PRIMARY_HANDLE="$(state_value primaryHandle)"
SECONDARY_HANDLE="$(state_value secondaryHandle)"
PRIMARY_LANGUAGE="$(state_value primaryLanguage)"
SECONDARY_LANGUAGE="$(state_value secondaryLanguage)"
PRIMARY_SLUG="$(state_value primarySlug)"
SECONDARY_SLUG="$(state_value secondarySlug)"
GLOBAL_QR_PATH="$(state_value globalQrPath)"

[[ "$PRIMARY_HANDLE" != "$PRIMARY_LANGUAGE" ]]
[[ "$SECONDARY_HANDLE" != "$SECONDARY_LANGUAGE" ]]

HTTP_BASE="${DDEV_PRIMARY_URL:?DDEV_PRIMARY_URL is required}"
LOGIN_CODE="$(curl -ksS -c "$SMOKE_COOKIE" -o "$SMOKE_LOGIN" -w '%{http_code}' "$HTTP_BASE/admin/login")"
[[ "$LOGIN_CODE" == "200" ]]
CSRF_NAME="$(sed -n 's/.*name="\([^"]*CSRF[^"]*\)" value="[^"]*".*/\1/p' "$SMOKE_LOGIN" | head -n 1)"
CSRF_VALUE="$(sed -n 's/.*name="[^"]*CSRF[^"]*" value="\([^"]*\)".*/\1/p' "$SMOKE_LOGIN" | head -n 1)"
[[ -n "$CSRF_NAME" && -n "$CSRF_VALUE" ]]

AUTH_CODE="$(curl -ksS -L -b "$SMOKE_COOKIE" -c "$SMOKE_COOKIE" -o /dev/null -w '%{http_code}' \
  --data-urlencode "$CSRF_NAME=$CSRF_VALUE" \
  --data-urlencode 'loginName=admin' \
  --data-urlencode 'password=password' \
  "$HTTP_BASE/admin/login")"
[[ "$AUTH_CODE" == "200" ]]

SETUP_CODE="$(curl -ksS -b "$SMOKE_COOKIE" -o "$SMOKE_SETUP" -w '%{http_code}' "$HTTP_BASE/admin/smartlink-manager/setup")"
GENERAL_CODE="$(curl -ksS -b "$SMOKE_COOKIE" -o "$SMOKE_GENERAL" -w '%{http_code}' "$HTTP_BASE/admin/smartlink-manager/settings/general")"
[[ "$SETUP_CODE" == "200" && "$GENERAL_CODE" == "200" ]]
grep -q 'Setup complete.' "$SMOKE_SETUP"
grep -q 'All required frontend templates are available.' "$SMOKE_GENERAL"
grep -q '\$SMARTLINK_TEMPLATE_HTTP_REDIRECT' "$SMOKE_GENERAL"
grep -q '\$SMARTLINK_TEMPLATE_HTTP_QR' "$SMOKE_GENERAL"

assert_public_template() {
  expected="$1"
  path="$2"
  output="$SMOKE_WORK/public-$(printf '%s' "$expected" | tr '[:upper:]' '[:lower:]').html"
  code="$(curl -ksS -o "$output" -w '%{http_code}' "$HTTP_BASE/$path")"
  if [[ "$code" != "200" ]]; then
    printf 'Public template request failed: path=%s status=%s\n' "$path" "$code" >&2
    return 1
  fi
  grep -q "$expected" "$output"
  ! grep -q '\$SMARTLINK_TEMPLATE_HTTP_' "$output"
}

assert_public_template SITE_REDIRECT_TEMPLATE "$PRIMARY_HANDLE/go/$PRIMARY_SLUG"
assert_public_template SITE_QR_TEMPLATE "$PRIMARY_HANDLE/go/qr/$PRIMARY_SLUG/view"
assert_public_template GLOBAL_REDIRECT_TEMPLATE "$SECONDARY_HANDLE/go/$SECONDARY_SLUG"
assert_public_template GLOBAL_QR_TEMPLATE "$SECONDARY_HANDLE/go/qr/$SECONDARY_SLUG/view"

mv "$GLOBAL_QR_PATH" "$GLOBAL_QR_PATH.missing"
MISSING_SETUP_CODE="$(curl -ksS -b "$SMOKE_COOKIE" -o "$SMOKE_SETUP" -w '%{http_code}' "$HTTP_BASE/admin/smartlink-manager/setup")"
MISSING_GENERAL_CODE="$(curl -ksS -b "$SMOKE_COOKIE" -o "$SMOKE_GENERAL" -w '%{http_code}' "$HTTP_BASE/admin/smartlink-manager/settings/general")"
[[ "$MISSING_SETUP_CODE" == "200" && "$MISSING_GENERAL_CODE" == "200" ]]
grep -q 'Missing' "$SMOKE_SETUP"
grep -q 'Some frontend templates are missing.' "$SMOKE_GENERAL"
mv "$GLOBAL_QR_PATH.missing" "$GLOBAL_QR_PATH"

printf 'Authenticated multisite template smoke passed: setup=%s general=%s site=%s/%s global=%s/%s missing=%s/%s\n' \
  "$SETUP_CODE" "$GENERAL_CODE" SITE_REDIRECT_TEMPLATE SITE_QR_TEMPLATE \
  GLOBAL_REDIRECT_TEMPLATE GLOBAL_QR_TEMPLATE "$MISSING_SETUP_CODE" "$MISSING_GENERAL_CODE"
