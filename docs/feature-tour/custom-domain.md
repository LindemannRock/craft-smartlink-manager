# Custom Domain @since(5.22.0)

By default, SmartLink Manager generates smart link URLs using each site's base URL. You can override this with a dedicated custom domain — useful for branded short URLs like `go.myapp.com` — using a single setting: `smartlinkBaseUrl`.

## Single-Site URLs

Use `smartlinkBaseUrl` to serve all smart links from your custom domain:

```php
// config/smartlink-manager.php
use craft\helpers\App;

return [
    '*' => [
        'smartlinkBaseUrl' => App::env('SMARTLINK_BASE_URL'),
    ],
];
```

```bash
# .env
SMARTLINK_BASE_URL=https://go.myapp.com
```

With this configuration, a link with slug `my-app` generates URLs like:

```
https://go.myapp.com/go/my-app
https://go.myapp.com/qr/my-app
https://go.myapp.com/qr/my-app/view
```

This overrides the site's own base URL when generating smart link URLs, but does **not** require a separate Craft site. Your existing Craft site handles the routing — `smartlinkBaseUrl` only changes what URL is displayed in the CP and encoded in QR codes.

## Multisite Site-Aware URLs

For a Craft multisite where each site needs its own URL path segment, use `smartlinkBaseUrl` with a site token:

```php
// config/smartlink-manager.php
use craft\helpers\App;

return [
    '*' => [
        'smartlinkBaseUrl' => App::env('SMARTLINK_BASE_URL'),
    ],
];
```

```bash
# .env
SMARTLINK_BASE_URL=https://go.myapp.com/{siteHandle}
```

**Supported tokens:**

| Token | Replaced with |
|-------|--------------|
| `{siteHandle}` | The site's handle (e.g., `en`, `de`) |
| `{siteId}` | The site's numeric ID |
| `{siteUid}` | The site's UID |

With `https://go.myapp.com/{siteHandle}`, links generate URLs like:

- English site: `https://go.myapp.com/en/go/my-app`
- German site: `https://go.myapp.com/de/go/my-app`

## Site-Aware Routes

SmartLink Manager automatically registers site-aware routes in addition to the standard routes:

| Route | Controller |
|-------|-----------|
| `/{slugPrefix}/{slug}` | Redirect controller |
| `/{siteHandle}/{slugPrefix}/{slug}` | Redirect controller (site-aware) |
| `/{qrPrefix}/{slug}` | QR code image |
| `/{siteHandle}/{qrPrefix}/{slug}` | QR code image (site-aware) |
| `/{qrPrefix}/{slug}/view` | QR code display page |
| `/{siteHandle}/{qrPrefix}/{slug}/view` | QR code display page (site-aware) |

The site-aware routes allow the controller to resolve which Craft site to look up the smart link in, based on the `{siteHandle}` in the URL path.

## How URLs Are Built

The `Settings::buildPublicUrl()` method resolves the correct base URL in this order:

1. If `smartlinkBaseUrl` is set and non-empty — expand supported site tokens and use as base
2. Else — use `UrlHelper::siteUrl()` with the smart link's `siteId`

This method is called when generating `SmartLink::getRedirectUrl()`, `getQrCodeUrl()`, and `getQrCodeDisplayUrl()`.

## Server Configuration

Your web server must point the custom domain to your Craft installation. The plugin handles routing internally — no additional server config beyond a standard Craft vhost is needed.

If you use a true separate domain (not a subdomain of your main site), ensure:

- The domain resolves to the same server as your Craft installation
- Your server vhost serves Craft from that domain
- SSL is configured for the domain

## Validation

`smartlinkBaseUrl` must be a valid URL starting with `http://` or `https://`.

If `{...}` tokens are used in `smartlinkBaseUrl`, only `{siteHandle}`, `{siteId}`, and `{siteUid}` are supported. Using unsupported tokens like `{siteName}` triggers a validation error.

## Multi-Environment Example

```php
// config/smartlink-manager.php
use craft\helpers\App;

return [
    '*' => [
        // Use a custom domain in production
        'smartlinkBaseUrl' => App::env('SMARTLINK_BASE_URL'),
    ],

    'dev' => [
        // On local dev, use the default site URL (no override needed)
        'smartlinkBaseUrl' => null,
    ],
];
```
