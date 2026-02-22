# Analytics

SmartLink Manager records a click event every time a smart link is followed, capturing device type, geography, language, referrer, and click type — all without requiring a third-party analytics service.

## What Gets Tracked

Each click event records:

| Data Point | Description |
|------------|-------------|
| **Device type** | Phone, tablet, desktop, smart TV, etc. |
| **OS** | iOS, Android, Windows, macOS, Linux, etc. |
| **Browser** | Chrome, Safari, Firefox, Samsung Internet, etc. |
| **Country** | Visitor's country (requires geo-detection enabled) |
| **City** | Visitor's city (requires geo-detection enabled) |
| **Language** | Detected language code (browser or IP-based) |
| **Referrer** | HTTP referrer URL |
| **Click type** | `redirect` (followed the link), `qr_scan` (scanned QR), `button_click` (SEOmatic event) |
| **Site** | Which Craft site the smart link belongs to |
| **Timestamp** | When the click occurred |

## Enabling and Disabling Analytics

Analytics can be toggled at two levels:

**Globally** — in **Settings → Analytics**, toggle **Enable Analytics**. When disabled globally, no clicks are recorded for any smart link.

**Per link** — on the smart link edit page, toggle **Track Analytics**. This lets you selectively disable tracking on specific links without affecting others.

```php
// config/smartlink-manager.php
return [
    'enableAnalytics' => true,  // global toggle
];
```

> [!IMPORTANT]
> The `SMARTLINK_MANAGER_IP_SALT` environment variable must be set before analytics will record clicks. See [Quickstart](../get-started/quickstart.md) for how to generate it.

## IP Anonymization

SmartLink Manager never stores raw IP addresses. Instead, it stores a one-way HMAC hash of the IP address using the salt from `SMARTLINK_MANAGER_IP_SALT`. This means:

- The same visitor generates the same hash → unique visitor counting works
- The hash cannot be reversed to recover the IP address → GDPR-friendly
- If you rotate the salt, all existing hashes become unresolvable (this is intentional — it provides a "right to be forgotten" mechanism)

Enable full anonymization (truncate the IP before hashing) in **Settings → Analytics → Anonymize IP Address**:

```php
// config/smartlink-manager.php
return [
    'anonymizeIpAddress' => true,
];
```

When `anonymizeIpAddress` is `true`, the last octet of IPv4 addresses (and the last 80 bits of IPv6 addresses) are zeroed before hashing, reducing precision further.

## Geo-Detection

Country and city data require an external geo-detection API. Configure it in **Settings → Analytics → Geo Detection**:

| Setting | Description |
|---------|-------------|
| `enableGeoDetection` | Toggle geo lookups on/off |
| `geoProvider` | API provider: `'ip-api.com'`, `'ipapi.co'`, or `'ipinfo.io'` |
| `geoApiKey` | API key (required by ipapi.co and ipinfo.io; free tier for ip-api.com) |

```php
// config/smartlink-manager.php
return [
    'enableGeoDetection' => true,
    'geoProvider'        => 'ip-api.com',
    'geoApiKey'          => null,  // not required for ip-api.com free tier
];
```

Default country and city when geo-detection is unavailable or disabled can be set via environment variables:

```bash
# .env
SMARTLINK_MANAGER_DEFAULT_COUNTRY=US
SMARTLINK_MANAGER_DEFAULT_CITY=New York
```

## Data Retention

By default, click data is kept indefinitely. Configure a retention period in **Settings → Analytics → Retention**:

```php
// config/smartlink-manager.php
return [
    'analyticsRetention' => 365,  // keep data for 365 days (0 = unlimited)
];
```

When a retention period is set, SmartLink Manager runs a `CleanupAnalyticsJob` queue job to purge old records. Cleanup runs automatically on a schedule — you can also trigger it manually from the **Utilities** panel.

## Analytics Dashboard

The analytics dashboard is available in the **SmartLink Manager** CP section under **Analytics**. It shows:

- **Summary stats** — total clicks, unique visitors, top devices, top countries
- **Click trend chart** — clicks over time (configurable date range)
- **Device breakdown** — pie or bar chart of device types
- **Geographic map** — country-level click distribution
- **Top links** — most-clicked smart links in the selected period
- **Referrer breakdown** — where traffic is coming from

The dashboard date range filter applies to all charts and stats simultaneously.

## Exporting Analytics Data

Export click data from the analytics dashboard using the **Export** button. Three formats are available:

| Format | Use |
|--------|-----|
| **CSV** | Spreadsheet import, raw data analysis |
| **Excel** | Formatted spreadsheet with column headers |
| **JSON** | API consumers, programmatic processing |

Exports respect the currently active date range and site filters. The `exportAnalytics` permission is required to export.

## Clearing Analytics Data

> [!CAUTION]
> Clearing analytics is permanent and cannot be undone.

Clear all click data for a smart link from its **Analytics** tab in the CP, or clear all analytics globally from **Utilities → SmartLink Manager**. The `clearAnalytics` permission is required.

## Multi-Site Analytics

Each click is associated with the Craft site the smart link belongs to. The analytics dashboard includes a site filter when running a multi-site installation, letting you view analytics per site or across all sites combined.

## Analytics in Templates

Retrieve analytics data for a specific smart link in your Twig templates using `craft.smartLinks.getAnalytics()`:

```twig
{% set stats = craft.smartLinks.getAnalytics(smartLink, {
    startDate: now|date_modify('-30 days'),
    endDate: now
}) %}

<p>{{ stats.totalClicks }} clicks in the last 30 days</p>
```

See [Template Variables](../developers/template-variables.md) for the full `getAnalytics()` API.
