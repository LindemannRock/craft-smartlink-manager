# QR Codes

SmartLink Manager generates customizable QR codes for any smart link, with options for module style, eye style, colors, logo overlay, and output format.

## How It Works

Every smart link can have QR code generation enabled. When enabled, SmartLink Manager exposes two QR endpoints:

| URL | Returns |
|-----|---------|
| `/{qrPrefix}/{slug}` | Raw QR code image (PNG or SVG) |
| `/{qrPrefix}/{slug}/view` | Styled display page with title, image, and download button |

The QR code always points to the smart link's public redirect URL (`/{slugPrefix}/{slug}`), so the same device detection and analytics tracking applies when someone scans it.

## Enabling QR Codes Per Link

On the smart link edit page, check **QR Code Enabled** to activate QR endpoints for that link. You can also enable or disable QR globally in **Settings → QR Codes**.

## Customization Options

QR code appearance can be customized globally in **Settings → QR Codes** and overridden per-link in the smart link edit page. Per-link settings that are left `null` inherit from the global defaults.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `size` | `int` | `256` | Output image size in pixels (100–1000) |
| `color` | `string` | `#000000` | QR module foreground color (hex) |
| `backgroundColor` | `string` | `#FFFFFF` | Background color (hex) |
| `format` | `string` | `'png'` | Output format: `'png'` or `'svg'` |
| `margin` | `int` | `4` | Quiet zone margin in modules (0–10) |
| `errorCorrection` | `string` | `'M'` | Error correction level: `'L'`, `'M'`, `'Q'`, `'H'` |
| `moduleStyle` | `string` | `'square'` | Module shape: `'square'`, `'dots'`, `'rounded'` |
| `eyeStyle` | `string` | `'square'` | Eye (finder pattern) shape: `'square'`, `'rounded'`, `'leaf'` |
| `eyeColor` | `string\|null` | `null` | Eye color override (hex). Falls back to `color` if null. |
| `logo` | `int\|null` | `null` | Asset ID for logo overlay (PNG only) |
| `logoSize` | `int` | `20` | Logo size as percentage of QR code width (10–30) |

> [!NOTE]
> Logo overlays are only supported in PNG format. If `format` is set to `'svg'`, the `logo` option is ignored.

### Module Styles

| Value | Appearance |
|-------|-----------|
| `square` | Classic square modules (default) |
| `dots` | Circular dots |
| `rounded` | Rounded square corners |

### Eye Styles

| Value | Appearance |
|-------|-----------|
| `square` | Classic square finder pattern (default) |
| `rounded` | Rounded finder pattern |
| `leaf` | Leaf-shaped finder pattern |

## Logo Overlay

Add your brand logo to the center of the QR code by setting the `logo` option to a Craft asset ID. The logo is rendered as a centered overlay at `logoSize` percent of the total QR code width (10–30%).

> [!WARNING]
> A logo reduces the scannable area of the QR code. Use `errorCorrection: 'H'` (30% recovery) when adding a logo to ensure reliable scanning, especially at smaller sizes.

To use logo overlays, configure a Craft asset volume in **Settings → QR Codes → Logo Volume**.

## Global vs Per-Link Settings

Global QR defaults are set in **Settings → QR Codes**. Any option left blank or set to `null` on a per-link basis inherits from the global setting. This lets you define brand-consistent defaults while allowing individual links to override specific properties.

```php
// config/smartlink-manager.php
return [
    // Global QR defaults
    'defaultQrSize'             => 400,
    'defaultQrColor'            => '#1a1a2e',
    'defaultQrBgColor'          => '#FFFFFF',
    'defaultQrFormat'           => 'png',
    'qrModuleStyle'             => 'rounded',
    'qrEyeStyle'               => 'rounded',
    'qrEyeColor'               => null,
    'defaultQrMargin'           => 2,
    'defaultQrErrorCorrection'  => 'H',
];
```

## Caching

QR codes can be expensive to generate, especially with logo overlays. SmartLink Manager supports two caching strategies:

| Strategy | Setting | Best for |
|----------|---------|----------|
| File system | `cacheStorageMethod: 'file'` | Simple single-server setups |
| Redis | `cacheStorageMethod: 'redis'` | Multi-server or high-traffic sites |

Enable QR caching in **Settings → QR Codes → Enable Cache**:

```php
// config/smartlink-manager.php
return [
    'enableQrCodeCache'   => true,
    'cacheStorageMethod'  => 'file',  // or 'redis'
];
```

The cache key includes the slug and all rendering options, so changing any option automatically generates a fresh QR code.

## QR Code URLs in Templates

Use `getQrCodeUrl()` for the most efficient approach — it returns the cached QR image URL without generating anything immediately:

```twig
{# Simple: use global defaults #}
<img src="{{ smartLink.getQrCodeUrl() }}" alt="QR code for {{ smartLink.title }}">

{# With options: override specific properties #}
<img src="{{ smartLink.getQrCodeUrl({ size: 200, format: 'png', moduleStyle: 'dots' }) }}" alt="QR code">

{# Base64 data URI (for email templates or inline embedding) #}
<img src="{{ smartLink.getQrCodeDataUri({ size: 150 }) }}" alt="QR code">

{# Link to the QR display page #}
<a href="{{ smartLink.getQrCodeDisplayUrl() }}">View QR code</a>
```

### Available Template Methods

| Method | Returns | Notes |
|--------|---------|-------|
| `getQrCodeUrl(options)` | `string` | URL to cached QR image — most efficient |
| `getQrCodeDataUri(options)` | `string` | Base64 `data:image/...` URI — use for email or inline |
| `getQrCode(options)` | `string` | Raw binary image data |
| `getQrCodeDisplayUrl(options)` | `string` | URL to the `/view` display page |

All methods accept the same `options` array with any of the customization options listed above.

### QR Filename Tokens

When the QR image is downloaded from the display page, the filename is generated using configurable tokens:

| Token | Replaced with |
|-------|--------------|
| `{slug}` | The smart link's slug |
| `{size}` | The pixel size |
| `{format}` | The file format (`png` or `svg`) |

Configure the filename pattern in **Settings → QR Codes → Filename Pattern** (e.g., `qr-{slug}-{size}.{format}`).

## The QR Display Page

The `/{qrPrefix}/{slug}/view` endpoint renders a styled page containing:

- The smart link's title (unless `hideTitle` is enabled)
- The smart link's image (if one is attached)
- The QR code image
- A download button

This page is useful for print campaigns, marketing collateral, or any situation where you want to present the QR code with context rather than serving the raw image.

## Limitations

- Logo overlays require the `bacon/bacon-qr-code` package and the Imagick PHP extension
- SVG output does not support logo overlays
- QR codes always encode the smart link's public redirect URL — the destination URL cannot be encoded directly
