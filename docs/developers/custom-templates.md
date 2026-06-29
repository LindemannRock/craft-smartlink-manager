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
| `autoRedirect` | `bool` | Whether the visitor should be forwarded automatically (vs. shown a chooser). |
| `source` | `string` | `direct` or `qr`. |
| `eventType` | `string` | `redirect`. |
| `device` | `array` | Detected device/platform details for the request. |
| `language` | `string` | Detected request language. |

The element also exposes `smartLink.renderSeomaticTracking(eventType)` for [SEOmatic](integrations.md) data-layer tracking. For the full redirect-template walkthrough (platform buttons, tracked hops), see [Device detection](../feature-tour/device-detection.md) and [Smart links](../feature-tour/smart-links.md).

```twig
{# templates/smartlink-manager/redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    {{ smartLink.renderSeomaticTracking(eventType)|raw }}
    {% if autoRedirect %}<script>window.location.replace({{ goUrl|json_encode|raw }});</script>{% endif %}
</head>
<body>
    <a href="{{ goUrls.ios }}">iOS</a>
    <a href="{{ goUrls.android }}">Android</a>
    <a href="{{ goUrls.fallback }}">Other</a>
</body>
</html>
```

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
