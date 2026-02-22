# Configuration

Configure SmartLink Manager by creating a config file at `config/smartlink-manager.php`. The plugin ships a sample template at `vendor/lindemannrock/craft-smartlink-manager/src/config.php` you can copy as a starting point.

## Environment Variables

These settings support `.env` values and are **not stored in the database** (config/env only):

| Variable | Description |
|----------|-------------|
| `SMARTLINK_MANAGER_IP_SALT` | Salt for IP address hashing (privacy protection). Generate with `php craft smartlink-manager/security/generate-salt` |
| `SMARTLINK_MANAGER_DEFAULT_COUNTRY` | Default 2-letter country code for local dev (e.g., `US`, `GB`, `AE`) |
| `SMARTLINK_MANAGER_DEFAULT_CITY` | Default city name for local dev (e.g., `Dubai`, `London`) |

```bash
# .env
SMARTLINK_MANAGER_IP_SALT="your-64-char-salt-here"
SMARTLINK_MANAGER_DEFAULT_COUNTRY="US"
SMARTLINK_MANAGER_DEFAULT_CITY="New York"
```

## General Settings

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `pluginName` | `string` | `'SmartLink Manager'` | Plugin display name |
| `slugPrefix` | `string` | `'go'` | URL prefix for smart links (e.g., `/go/your-link`) |
| `qrPrefix` | `string` | `'qr'` | URL prefix for QR code pages. Supports nested patterns like `go/qr` |
| `shortlinkBaseUrl` | `string` | `null` | Optional absolute base URL for generated smart links and QR URLs (e.g., `https://short.example.com`). Supports env vars. @since(5.22.0) |
| `shortlinkBaseUrlPattern` | `string` | `null` | Optional base URL pattern with site tokens: `{siteHandle}`, `{siteId}`, `{siteUid}`. Example: `https://short.example.com/{siteHandle}`. Supports env vars. @since(5.22.0) |
| `notFoundRedirectUrl` | `string` | `'/'` | URL to redirect to when smart link is not found (404). Supports env vars |
| `redirectTemplate` | `string` | `null` | Custom redirect template path. Supports env vars |
| `qrTemplate` | `string` | `null` | Custom QR code display template path. Supports env vars |
| `enabledSites` | `array` | `[]` | Site IDs where SmartLink Manager should be enabled (empty = all sites) |
| `logLevel` | `string` | `'error'` | Log level: `error`, `warning`, `info`, `debug`. Debug requires `devMode` |
| `itemsPerPage` | `int` | `100` | Items per page in element index (10–500) |

## QR Code Settings

### Appearance

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `defaultQrSize` | `int` | `256` | QR code size in pixels (100–1000) |
| `defaultQrFormat` | `string` | `'png'` | QR code format: `png` or `svg` |
| `defaultQrColor` | `string` | `'#000000'` | QR code foreground color |
| `defaultQrBgColor` | `string` | `'#FFFFFF'` | QR code background color |
| `defaultQrMargin` | `int` | `4` | QR code quiet zone in modules (0–10) |
| `defaultQrErrorCorrection` | `string` | `'M'` | Error correction level: `L` (7%), `M` (15%), `Q` (25%), `H` (30%) |
| `qrModuleStyle` | `string` | `'square'` | Module shape: `square`, `rounded`, `dots` |
| `qrEyeStyle` | `string` | `'square'` | Eye shape: `square`, `rounded`, `leaf` |
| `qrEyeColor` | `string` | `null` | Eye color override (`null` = same as foreground color) |

### Logo

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enableQrLogo` | `bool` | `false` | Enable logo overlay in center of QR codes |
| `qrLogoVolumeUid` | `string` | `null` | Asset volume UID for logo selection (`null` = all volumes). Supports env vars |
| `defaultQrLogoId` | `int` | `null` | Default logo asset ID. Required when `enableQrLogo` is `true` |
| `qrLogoSize` | `int` | `20` | Logo size as percentage of QR code (10–30) |

### Downloads

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enableQrDownload` | `bool` | `true` | Allow users to download QR codes |
| `qrDownloadFilename` | `string` | `'{slug}-qr-{size}'` | Download filename pattern. Tokens: `{slug}`, `{size}`, `{format}` |

### Caching

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enableQrCodeCache` | `bool` | `true` | Cache generated QR code images |
| `qrCodeCacheDuration` | `int` | `86400` | QR code cache duration in seconds (default: 24 hours) |
| `cacheStorageMethod` | `string` | `'file'` | Cache storage: `file` (single server) or `redis` (multi-server/cloud) |

## Analytics Settings

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enableAnalytics` | `bool` | `true` | Enable analytics tracking |
| `analyticsRetention` | `int` | `90` | Analytics data retention in days (0 = unlimited, max 3650) |
| `anonymizeIpAddress` | `bool` | `false` | Anonymize IP addresses before storing (masks last octet for IPv4, last 80 bits for IPv6) |
| `ipHashSalt` | `string` | `null` | IP hash salt for privacy protection. Falls back to `SMARTLINK_MANAGER_IP_SALT` env var. **Config/env only — not stored in DB** |
| `includeDisabledInExport` | `bool` | `false` | Include disabled smart links in analytics exports |
| `includeExpiredInExport` | `bool` | `false` | Include expired smart links in analytics exports |

## Detection Settings

### Geographic Detection

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enableGeoDetection` | `bool` | `false` | Enable geographic detection for analytics |
| `geoProvider` | `string` | `'ip-api.com'` | Geo IP provider: `ip-api.com` (HTTP free, HTTPS paid), `ipapi.co` (1K/day free), `ipinfo.io` (50K/month free) |
| `geoApiKey` | `string` | `null` | API key for paid provider tiers. Required for ip-api.com HTTPS |
| `defaultCountry` | `string` | `null` | Default 2-letter country code for local dev. Falls back to `SMARTLINK_MANAGER_DEFAULT_COUNTRY` env var. **Config/env only — not stored in DB** |
| `defaultCity` | `string` | `null` | Default city for local dev. Falls back to `SMARTLINK_MANAGER_DEFAULT_CITY` env var. **Config/env only — not stored in DB** |

### Device & Language Detection

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `cacheDeviceDetection` | `bool` | `true` | Cache device detection results |
| `deviceDetectionCacheDuration` | `int` | `3600` | Device detection cache duration in seconds (default: 1 hour) |
| `languageDetectionMethod` | `string` | `'browser'` | Language detection method: `browser`, `ip`, or `both` |

## Asset Settings

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `imageVolumeUid` | `string` | `null` | Asset volume UID for smart link image selection (`null` = all volumes). Supports env vars |

## Integration Settings

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `enabledIntegrations` | `array` | `[]` | Enabled integration handles (e.g., `['seomatic', 'redirect-manager']`) |
| `seomaticTrackingEvents` | `array` | `['redirect', 'button_click', 'qr_scan']` | Event types to track via SEOmatic integration |
| `seomaticEventPrefix` | `string` | `'smart_links'` | Event prefix for GTM/GA events (lowercase, numbers, underscores only) |
| `redirectManagerEvents` | `array` | `['slug-change', 'delete']` | Event types that create redirects in Redirect Manager |

## Example Configuration

```php
<?php
// config/smartlink-manager.php

use craft\helpers\App;

return [
    '*' => [
        'pluginName' => 'SmartLink Manager',
        'slugPrefix' => 'go',
        'qrPrefix' => 'go/qr',
        'notFoundRedirectUrl' => '/',
        'enableAnalytics' => true,
        'analyticsRetention' => 90,
        'enableGeoDetection' => false,
        'logLevel' => 'error',

        // IP privacy
        'ipHashSalt' => App::env('SMARTLINK_MANAGER_IP_SALT'),

        // QR code defaults
        'defaultQrSize' => 256,
        'defaultQrFormat' => 'png',
        'defaultQrErrorCorrection' => 'M',
        'qrModuleStyle' => 'square',
        'qrEyeStyle' => 'square',
    ],

    'dev' => [
        'logLevel' => 'debug',
        'analyticsRetention' => 30,
        'enableQrCodeCache' => false,
        'cacheDeviceDetection' => false,

        // Local dev geo fallback
        'defaultCountry' => App::env('SMARTLINK_MANAGER_DEFAULT_COUNTRY'),
        'defaultCity' => App::env('SMARTLINK_MANAGER_DEFAULT_CITY'),
    ],

    'production' => [
        'logLevel' => 'error',
        'analyticsRetention' => 365,
        'cacheStorageMethod' => 'redis',
        'qrCodeCacheDuration' => 604800, // 7 days
    ],
];
```
