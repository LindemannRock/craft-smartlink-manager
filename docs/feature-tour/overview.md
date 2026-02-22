# Features Overview

SmartLink Manager is an intelligent deep-linking and app-routing plugin for Craft CMS that detects a visitor's device and redirects them to the right app store, download page, or URL — automatically.

> [!TIP]
> New to SmartLink Manager? Start with the [Quickstart](../get-started/quickstart.md) to get your first smart link live in under 5 minutes.

## What It Does

When a visitor follows a smart link URL (e.g., `yoursite.com/go/my-app`), SmartLink Manager detects their device and operating system, then redirects them to the most appropriate destination — the iOS App Store, Google Play, Huawei AppGallery, Amazon Appstore, a platform-specific download page, or a universal fallback URL.

Every smart link is a native Craft element with its own slug, status, publish schedule, and analytics. You manage them in the control panel just like entries.

## Core Capabilities

- **[Smart Links](smart-links.md)** — Custom element type with per-platform URLs (iOS, Android, Huawei, Amazon, Windows, Mac, Fallback). Each link has a unique slug, publish schedule, expiry date, and status lifecycle.

- **[QR Codes](qr-codes.md)** — Generate styled QR codes for any smart link. Customize module style, eye style, colors, logo overlay, size, margin, and output format (PNG or SVG). Cacheable via file system or Redis.

- **[Analytics](analytics.md)** — Track every click with device type, OS, browser, country, city, language, referrer, and click type (redirect, QR scan, button click). Configurable retention policy. Exportable to CSV, Excel, and JSON.

- **[Device Detection](device-detection.md)** — Matomo Device Detector identifies iOS, Android, Huawei, Amazon Fire, Windows, and Mac devices. Language detection supports browser headers, IP-based geolocation, or both combined.

- **[Integrations](integrations-overview.md)** — Built-in integrations with SEOmatic (GTM/GA4 data layer events), Redirect Manager (automatic 301 on slug change), and Craft Link Field (SmartLink as a link type).

- **[Dashboard Widgets](widgets.md)** — Two Craft dashboard widgets: Analytics Summary and Top Links. Both are configurable by date range and respect user permissions.

## How It Works

1. A smart link is created in the CP with a slug (e.g., `my-app`) and platform-specific destination URLs
2. SmartLink Manager registers the route `/{slugPrefix}/{slug}` (default prefix: `go`)
3. A visitor follows the link — SmartLink Manager detects their device using Matomo Device Detector
4. The visitor is redirected to the most specific matching URL, falling back to the generic fallback URL if no platform match exists
5. The click is recorded in the analytics database (device, OS, browser, country, language, referrer)

QR codes follow the same pattern but are accessed via `/{qrPrefix}/{slug}` (default prefix: `qr`).

## What's Included

| Feature | Where |
|---------|-------|
| Smart Link element type | CP → SmartLink Manager |
| Platform-specific redirect routing | Automatic on install |
| QR code generation (PNG/SVG) | Per smart link, globally configurable |
| Click analytics with geo-detection | Built-in analytics dashboard |
| IP anonymization and hashing | Settings → Privacy |
| SEOmatic GTM/GA4 events | Settings → Integrations |
| Redirect Manager auto-301 | Settings → Integrations |
| Craft Link Field support | Available in field type selection |
| SmartLinkField element picker | Available in field type selection |
| `craft.smartLinks` Twig variable | Template access to all elements and analytics |
| Dashboard widgets (2) | Craft Dashboard → New Widget |
| CP Utility panel | Utilities → SmartLink Manager |
| Console command for salt generation | `smartlink-manager/security/generate-salt` |

## Dashboard Widgets

SmartLink Manager provides two Craft dashboard widgets. Add them via **Dashboard → New Widget**.

| Widget | What It Shows |
|--------|---------------|
| **Analytics Summary** | Total clicks, top device types, top countries, and click trends over a configurable date range |
| **Top Links** | Most-clicked smart links over a configurable date range with click counts |

Both widgets require the `smartLinkManager:viewAnalytics` permission.

## Next Steps

If you're new to SmartLink Manager, start here:

1. [Quickstart](../get-started/quickstart.md) — install the plugin, generate the IP salt, create your first link
2. [Smart Links](smart-links.md) — learn about platform URLs, statuses, and language detection
3. [QR Codes](qr-codes.md) — generate and customize QR codes
4. [Analytics](analytics.md) — understand what's tracked and how to export data
5. [Integrations](integrations-overview.md) — connect SEOmatic, Redirect Manager, and Craft Link Field
