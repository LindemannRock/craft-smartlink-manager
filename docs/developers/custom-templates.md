# Custom templates

SmartLink Manager renders two front-end pages — the redirect page and the QR code page. Each ships with a default template you can override in your own site to control the markup, branding, and behavior. You only need to override the ones you want to change; anything left at its default keeps working.

## Overridable templates

| Template | Default path | Setting | What it renders |
|----------|--------------|---------|-----------------|
| `redirect.twig` | `smartlink-manager/redirect` | `redirectTemplate` | The smart link landing/redirect page. Detects the visitor's platform, fires analytics/SEOmatic tracking, and either auto-forwards or shows platform buttons that link to the tracked `goUrls`. |
| `qr.twig` | `smartlink-manager/qr` | `qrTemplate` | The QR code display page at `/{qrPrefix}/{slug}/view`. |

## Where to find and copy them

The reference templates ship inside the plugin. Copy the one you want to customize into your own `templates/` folder:

```bash
# Redirect / landing page
cp vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig templates/smartlink-manager/redirect.twig

# QR code page
cp vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig templates/smartlink-manager/qr.twig
```

Once a file exists at `templates/smartlink-manager/{name}.twig`, the default path resolves to it automatically — no setting change is needed. The path **settings** only matter if you want the template somewhere else:

- Set `redirectTemplate` / `qrTemplate` in **Settings → SmartLink Manager** (or `config/smartlink-manager.php`) to point at a different template path.
- Leave a setting empty to use the default path shown above.
- Each path field accepts a `$ENV_VAR` in the Control Panel, or `App::env()` in the config file.
- A value in `config/smartlink-manager.php` overrides the Control Panel field (the CP field is shown disabled with an override warning).

See [Configuration → Template settings](../get-started/configuration.md) for the settings reference.

## Available variables

Each template receives a fixed set of variables from the plugin. Use these instead of querying for the link yourself.

### `redirect.twig`

The default template renders a platform-aware landing page — app-store buttons for the visitor's device, a *Continue to Website* link, and the QR code:

![The default SmartLink redirect page rendered by redirect.twig — app-store buttons and QR code](images/frontend-template.webp)

It receives these variables:

| Variable | Type | Description |
|----------|------|-------------|
| `smartLink` | `SmartLink` | The resolved smart link element. |
| `goUrl` | `string` | The tracked forwarding URL for the detected platform (`goUrls['auto']`). Send auto-redirects here so the click is recorded — do **not** use `smartLink.getRedirectUrl()`, which bypasses tracking. |
| `goUrls` | `array` | Per-platform tracked URLs (e.g. `goUrls.ios`, `goUrls.android`, `goUrls.fallback`). Use these for platform buttons. |
| `autoRedirectUrl` | `string` | Server-side resolver URL for the **cache-safe** auto-redirect. Pass it to `smartLink.renderAutoRedirectScript()` (below) instead of redirecting from the template directly. @since(5.32.0) |
| `autoRedirect` | `bool` | Whether *this* request resolves to an automatic forward (vs. showing a chooser). Handy for conditional markup or debug; for the actual forward use the cache-safe helper, not a hard-coded `goUrl` redirect. |
| `source` | `string` | `direct` or `qr`. |
| `eventType` | `string` | `redirect`. |
| `device` | `array` | Detected device/platform details for the request. |
| `language` | `string` | Detected request language. |

The element also exposes:

- `smartLink.renderSeomaticTracking(eventType)` — [SEOmatic](integrations.md) data-layer tracking.
- `smartLink.renderAutoRedirectScript(autoRedirectUrl)` @since(5.32.0) — the **cache-safe auto-redirect** script (see [below](#cache-safe-auto-redirect)).

For the full redirect-template walkthrough (platform buttons, tracked hops), see [Device detection](../feature-tour/device-detection.md) and [Smart links](../feature-tour/smart-links.md).

```twig
{# templates/smartlink-manager/redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    {{ smartLink.renderSeomaticTracking(eventType)|raw }}
</head>
<body>
    <a href="{{ goUrls.ios }}">iOS</a>
    <a href="{{ goUrls.android }}">Android</a>
    <a href="{{ goUrls.fallback }}">Other</a>

    {# Cache-safe auto-redirect (resolves the device-specific destination at request time) #}
    {{ smartLink.renderAutoRedirectScript(autoRedirectUrl) }}
</body>
</html>
```

#### Cache-safe auto-redirect

The landing page is platform-aware, so the auto-forward **must not** be baked into the HTML — if a CDN or static cache served a page that hard-coded one platform's `goUrl`, every later visitor would be sent to the wrong store. `renderAutoRedirectScript(autoRedirectUrl)` avoids this: it outputs a small script that, on each load, fetches a **no-store** server-side resolver (`autoRedirectUrl`) which returns the correct `{ autoRedirect, goUrl }` for *that* request, then forwards. So the cached HTML stays generic and the redirect decision is always resolved fresh.

- Use `renderAutoRedirectScript(autoRedirectUrl)` for the auto-forward — don't redirect to `goUrl` directly from the template.
- The per-platform **buttons** still use the tracked `goUrls` values (those record the click via the `…/redirect/go/{slug}/{platform}` hop).
- In `devMode`, the script skips the redirect when `?debug=1` is present.

> [!TIP]
> In `devMode`, append `?debug=1` to a smart link URL to stop the auto-redirect and log the generated `goUrl` in the browser console — useful for verifying custom-domain and multisite URLs. See [Troubleshooting](../resources/troubleshooting.md).

### `qr.twig`

| Variable | Type | Description |
|----------|------|-------------|
| `smartLink` | `SmartLink` | The smart link the QR code points to. |
| `size` | `int` | Requested QR size in pixels. |
| `format` | `string` | `png` or `svg`. |
| `qrCodeData` | `string` | Base64-encoded PNG data (present when `format` is `png`). |
| `qrCodeSvg` | `string` | Raw SVG markup (present when `format` is `svg`). |

```twig
{% if format == 'svg' %}
    {{ qrCodeSvg|raw }}
{% else %}
    <img src="data:image/png;base64,{{ qrCodeData }}" width="{{ size }}" alt="QR code">
{% endif %}
```

## What to customize (and what to keep)

- **Customize freely:** layout, branding, copy, styling, the platform-button design, the redirect delay, and any extra markup or analytics you want on the page.
- **Keep on the redirect page:** the forward to `goUrl` / the `goUrls` button links (and `renderSeomaticTracking()` if you use SEOmatic) — these are what record the click. Replacing them with `smartLink.getRedirectUrl()` skips tracking.
- Redirect templates are standalone pages by default. You can `{% extends %}` your own layout if you prefer, but a minimal page generally redirects faster.

## Related

- [Configuration → Template settings](../get-started/configuration.md)
- [Device detection](../feature-tour/device-detection.md) — platform routing and the redirect flow
- [QR codes](../feature-tour/qr-codes.md)
- [SEOmatic integration](integrations.md)
