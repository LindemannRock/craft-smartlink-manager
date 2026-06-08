# SmartLink Manager — Manual CSV Fixtures

This directory contains **manual QA CSV fixtures** for exercising the CSV import flow through the CP UI (Import/Export → upload → map columns → preview → import). They are **not** loaded by PHPUnit — automated coverage lives in `../Integration/` (see `ImportUrlValidationTest`).

Unlike Translation Manager (which imports free text rendered as HTML), SmartLink imports **URLs that become redirect targets**. The primary attack surface is therefore the **URL scheme** of `fallbackUrl` and the platform fields (`iosUrl`, `androidUrl`, `huaweiUrl`, `amazonUrl`, `windowsUrl`, `macUrl`). Validation rules:

- A URL field must pass `filter_var(FILTER_VALIDATE_URL)` — so it must be an **absolute** URL (relative `/paths` are rejected here, unlike ShortLink/Redirect).
- Executable schemes (`javascript:`, `vbscript:`, `data:`, `file:`) are rejected up front by `UrlSafetyHelper::hasDangerousScheme()`, including whitespace/`//`-obfuscated variants.
- **Custom app deep links keep working** (`myapp://`, `fb://`, `intent://`) — that's the whole point of the platform fields, so the guard must not block them.

All files share the column header: `slug,title,description,iosUrl,androidUrl,fallbackUrl,enabled,trackAnalytics`.

## Test Files

### `smartlink-valid.csv` — positive control
Rows that **should import cleanly**, including the deep-link cases that prove the scheme guard does not over-block:
- Plain `https://` fallback
- `myapp://` and `fb://` custom app schemes in `iosUrl`
- `intent://…` Android intent link
- App Store / Play Store `https://` links
- Unicode title/description with an ASCII URL

### `smartlink-malicious.csv` — security
Rows that **should be blocked or sanitized**. Each row targets one field with one attack; non-target required fields are kept valid so the row reaches the attack:
- `javascript:` in `fallbackUrl` (plain, `//%0a`-obfuscated, and leading-space forms)
- `data:text/html` in `iosUrl`
- `vbscript:` in `androidUrl`
- `file:///` in `fallbackUrl`
- `<script>` / `onerror=` XSS in `title` / `description`
- CSV formula injection in slug/title (`=`, `@`, `+`, `-`, `|`)
- SQL injection string in `title`

### `smartlink-edge-cases.csv` — boundary conditions
Unusual-but-not-malicious inputs that probe graceful handling:
- Missing `fallbackUrl` (required → error)
- Empty `slug` (required → error)
- Bare domain `example.com` (no scheme → `filter_var` rejects)
- Protocol-relative `//evil.com` (not a valid absolute URL → rejected)
- `mailto:` fallback (valid URL, not a dangerous scheme → **accepted**; documents current behavior)
- `ftp://` fallback (valid URL, not a dangerous scheme → **accepted**; documents current behavior)
- Emoji slug (normalizes to empty → error)
- Very long title, blank platform URLs, non-ASCII URL path

## How to run a pass

1. **Baseline:** export current smart links to confirm the round-trip format.
2. **Valid:** import `smartlink-valid.csv`. Every row should preview as importable; confirm the `myapp://` / `fb://` / `intent://` rows are **accepted** (deep-link regression guard).
3. **Malicious:** import `smartlink-malicious.csv`. Confirm:
   - Every dangerous-scheme URL row lands in **errors** (not imported).
   - No `javascript:`/`data:`/`vbscript:`/`file:` value reaches the database.
   - Formula cells are neutralized (no leading `=`/`@`/`+`/`-`/`|` survives to a re-export).
   - No script/HTML executes anywhere in the preview UI.
4. **Edge cases:** import `smartlink-edge-cases.csv`. Confirm required-field errors are clear, format rejections are graceful, and `ftp://` behaves as documented above.

## Expected behavior summary

| Input | Expected |
|-------|----------|
| `javascript:` / `vbscript:` / `data:` / `file:` (any URL field) | Rejected — row in errors |
| Obfuscated `javascript://%0a…` / leading-space scheme | Rejected |
| `myapp://`, `fb://`, `intent://` deep links | **Accepted** |
| Relative `/path`, bare `domain.com`, `//host` in a URL field | Rejected (not an absolute URL per `filter_var`) |
| `mailto:…`, `ftp://…` | Accepted (valid URL, not dangerous) — documented, revisit if undesired |
| Leading `= + - @ \|` in any cell | Formula prefix stripped on import |
| Missing `slug` or `fallbackUrl` | Rejected with a clear error |
