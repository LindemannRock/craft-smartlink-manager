# Smart Links

A smart link is a Craft element that holds platform-specific destination URLs and routes visitors to the right one based on their device.

## What a Smart Link Is

Smart links are first-class Craft elements — they have slugs, titles, statuses, publish dates, expiry dates, and full support for multi-site. Each smart link generates a public redirect URL at `/{slugPrefix}/{slug}` (default: `/go/{slug}`) that detects the visitor's device and immediately redirects them to the appropriate destination.

You create and manage smart links in **SmartLink Manager** in the Craft control panel, the same way you work with entries.

## Creating and Editing Smart Links

Go to **SmartLink Manager → New Smart Link** to create a link. The edit screen has these main fields:

| Field | Description |
|-------|-------------|
| **Title** | Internal name shown in the CP listing |
| **Slug** | The URL segment used in `/{prefix}/{slug}` — must be unique |
| **Description** | Optional internal note about this link |
| **Fallback URL** | Where to send visitors when no platform-specific URL matches |
| **Enabled** | Whether this link accepts redirects |
| **Track Analytics** | Whether clicks on this link are recorded |
| **QR Code Enabled** | Whether the QR code endpoint is active for this link |
| **Hide Title** | Controls whether the title is shown on the QR display page |

## Platform-Specific URLs

The core of a smart link is its platform URL table. Add a URL for any platform you want to target:

| Platform | Typical Destination |
|----------|-------------------|
| **iOS** | Apple App Store listing URL |
| **Android** | Google Play Store listing URL |
| **Huawei** | Huawei AppGallery listing URL |
| **Amazon** | Amazon Appstore listing URL |
| **Windows** | Microsoft Store or Windows download page |
| **Mac** | Mac App Store or macOS download page |
| **Fallback** | Universal fallback — any unmatched device lands here |

Leave a platform field blank and that platform falls through to the next available match. If no platform matches at all, the visitor is sent to the Fallback URL. If no Fallback URL is set, a 404 is returned.

### Fallback Chain

SmartLink Manager resolves the redirect URL in this order:

1. Exact platform match (e.g., iOS URL for iOS visitors)
2. Related platform fallback (e.g., Huawei → Android if no Huawei URL is set)
3. Fallback URL
4. 404

## URL Structure

Smart links are accessible at two URL patterns:

| Pattern | Purpose |
|---------|---------|
| `/{slugPrefix}/{slug}` | Redirect landing — detects device and redirects (default: `/go/{slug}`) |
| `/{qrPrefix}/{slug}` | Serves the raw QR code image (default: `/qr/{slug}`) |
| `/{qrPrefix}/{slug}/view` | QR code display page with title and download button |

Configure the prefixes in **Settings → General**:

```php
// config/smartlink-manager.php
return [
    'slugPrefix' => 'go',   // default
    'qrPrefix'   => 'qr',   // default
];
```

### Custom Domain @since(5.22.0)

You can serve smart links from a dedicated custom domain like `go.myapp.com`. See [Custom Domain](custom-domain.md) for single-site and multisite configuration.

## Element Statuses

Smart links support four statuses, matching the standard Craft element lifecycle:

| Status | Color | When |
|--------|-------|------|
| **Enabled** | Green | The link is live and accepts redirects |
| **Disabled** | Red | The link is inactive — redirects return 404 |
| **Pending** | Orange | `postDate` is set to a future date — not yet live |
| **Expired** | Grey | `dateExpired` has passed — redirects return 404 |

Set a `postDate` to schedule a link to go live in the future. Set a `dateExpired` to automatically retire it after a campaign ends.

## Language Detection and Localized URLs

SmartLink Manager can detect a visitor's language and route them to a language-specific URL. Configure the detection method in **Settings → General**:

| Method | How it works |
|--------|-------------|
| `browser` | Reads the `Accept-Language` HTTP header |
| `ip` | Queries a geo-detection API to determine country, then maps to language |
| `both` | Tries browser header first, falls back to IP-based detection |

Add localized URLs to a smart link using the **Localized URLs** section on the edit page. Each entry maps a language code (e.g., `de`, `fr`, `en-US`) to a destination URL.

When a localized URL matches the visitor's detected language, it takes priority over the platform URL.

## Image Attachments

Each smart link can have an associated image asset. This image is used on the QR code display page and can be rendered in templates via `smartLink.getImage()`.

Configure the `imageSize` property to control the size at which the asset transform is applied.

## Custom Redirect Templates

By default, SmartLink Manager handles redirects internally with a 302 response. If you need to customize the redirect experience — for example, to show an interstitial page or fire custom JavaScript before the redirect — you can override the redirect template.

The following variables are available in the redirect template:

| Variable | Type | Description |
|----------|------|-------------|
| `smartLink` | `SmartLink` | The smart link element |
| `device` | `DeviceInfo` | Detected device information |
| `language` | `string\|null` | Detected language code |

## Querying Smart Links in Templates

Use `craft.smartLinks` to query smart links in your Twig templates:

```twig
{# Get a single smart link by slug #}
{% set link = craft.smartLinks.getBySlug('my-app') %}

{# Get all enabled smart links #}
{% set links = craft.smartLinks.active().all() %}

{# Render the public redirect URL #}
<a href="{{ link.getUrl() }}">Download our app</a>
```

See [Template Variables](../developers/template-variables.md) for the full query API.

## Limitations

- Slugs must be unique across all sites — there is no per-site slug scoping
- Platform detection depends on the `User-Agent` header — clients that send no user agent fall through to the fallback URL
- Language detection via IP requires a configured geo-detection provider (see [Configuration](../get-started/configuration.md))
