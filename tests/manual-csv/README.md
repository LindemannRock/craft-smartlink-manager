# SmartLink Manager — Manual CSV Fixtures

This directory contains **manual QA CSV fixtures** for exercising the CSV import flow through the CP UI (Import/Export → upload → map columns → preview → import). They are **not** loaded by PHPUnit — automated coverage lives in `../Integration/` (`ImportUrlValidationTest`, `MarkupValidationTest`).

SmartLink imports two kinds of sensitive data:

**URL fields** — `fallbackUrl` plus the platform store fields (`iosUrl`, `androidUrl`, `huaweiUrl`, `amazonUrl`, `windowsUrl`, `macUrl`). These are **store/landing URLs and must be absolute `http(s)`**, matching the CP form's `UrlValidator`:

- Only `http://` / `https://` are accepted. `mailto:`, `tel:`, `ftp:`, custom app schemes (`myapp://`, `fb://`), and executable schemes (`javascript:`, `data:`, `vbscript:`, `file:`) are all **rejected** — none are valid store URLs.
- Relative paths (`/page`) and protocol-relative `//host` are also rejected (not absolute `http(s)`).

**Free-text fields** — `title` / `description`. Dangerous HTML/script markup is rejected via `ContentSafetyHelper::containsMaliciousMarkup()` (detect-and-reject; a lone `<` in `price < $5` is fine). Formula injection in CSV cells is neutralized on import (prefix stripped) and on export (formula-escaped); SQL is inert via parameterized queries.

All files share the column header: `slug,title,description,iosUrl,androidUrl,fallbackUrl,enabled,trackAnalytics`.

## Test Files

### `smartlink-valid.csv` — positive control
Rows that **should import cleanly** — all `https` store/landing URLs:
- Plain `https://` fallback
- App Store `https://` URL in `iosUrl`, Play Store `https://` URL in `androidUrl`
- A row populating both platform fields
- Unicode title/description with an ASCII URL

### `smartlink-malicious.csv` — security
Rows that **should be blocked or sanitized**. Each row targets one field with one attack; non-target required fields are kept valid so the row reaches the attack:
- `javascript:` in `fallbackUrl` (plain, `//%0a`-obfuscated, and leading-space forms)
- `data:text/html` in `iosUrl`
- `vbscript:` in `androidUrl`
- `file:///` in `fallbackUrl`
- `<script>` / `onerror=` markup in `title` / `description` (→ "Disallowed markup in title or description")
- CSV formula injection in slug/title (`=`, `@`, `+`, `-`, `|`)
- SQL injection string in `title`

### `smartlink-edge-cases.csv` — boundary conditions
Unusual-but-not-malicious inputs that probe graceful handling:
- Missing `fallbackUrl` (required → error)
- Empty `slug` (required → error)
- Bare domain `example.com` (no scheme → rejected)
- Protocol-relative `//evil.com` (not absolute `http(s)` → rejected)
- `mailto:` fallback (not `http(s)` → rejected)
- `ftp://` fallback (not `http(s)` → rejected)
- Custom app scheme `myapp://` in `iosUrl` (not a store URL → rejected)
- Emoji slug (normalizes to empty → error)
- Very long title, blank platform URLs, non-ASCII URL path

## How to run a pass

1. **Baseline:** export current smart links to confirm the round-trip format.
2. **Valid:** import `smartlink-valid.csv`. Every row should preview as importable (all `https` store URLs).
3. **Malicious:** import `smartlink-malicious.csv`. Confirm:
   - Every non-`http(s)` / dangerous-scheme URL row lands in **errors** (not imported).
   - The `<script>` / `onerror=` row is rejected as "Disallowed markup in title or description".
   - Formula cells are neutralized (no leading `=`/`@`/`+`/`-`/`|` survives to a re-export).
   - No script/HTML executes anywhere in the preview UI.
4. **Edge cases:** import `smartlink-edge-cases.csv`. Confirm required-field errors are clear and every non-`http(s)` scheme (`mailto:`, `ftp:`, `myapp://`) is rejected.

## Expected behavior summary

| Input | Expected |
|-------|----------|
| `http://…` / `https://…` (store/landing URL) | **Accepted** |
| `javascript:` / `vbscript:` / `data:` / `file:` (+ obfuscated) | Rejected |
| `mailto:`, `tel:`, `ftp:`, custom app schemes (`myapp://`, `fb://`) | Rejected (not a store URL) |
| Relative `/path`, bare `domain.com`, `//host` | Rejected (not absolute `http(s)`) |
| `<script>` / `<iframe>` / `on*=` in `title`/`description` | Rejected (disallowed markup) |
| Leading `= + - @ \|` in any cell | Formula prefix stripped on import / escaped on export |
| Missing `slug` or `fallbackUrl` | Rejected with a clear error |
