# Features Overview

SmartLink Manager is an intelligent deep-linking and app-routing plugin for Craft CMS that detects a visitor's device and redirects them to the right app store, download page, or URL — automatically.

> [!TIP]
> New to SmartLink Manager? Start with the [Quickstart](../get-started/quickstart.md) to get your first smart link live in under 5 minutes.

## What It Does

When a visitor follows a smart link URL (e.g., `yoursite.com/go/my-app`), SmartLink Manager detects their device and operating system, then redirects them to the most appropriate destination — the iOS App Store, Google Play, Huawei AppGallery, Amazon Appstore, a platform-specific download page, or a universal fallback URL.

Every smart link is a native Craft element with its own slug, status, publish schedule, and analytics. You manage them in the control panel just like entries.

![SmartLink Manager element index in the Craft control panel](images/overview-element-index.webp)

## What you'll use it for

- **One link for every platform** — share a single `go/my-app` URL on social, email, or print, and send each visitor to the right app store automatically.
- **Branded download pages** — route desktop visitors to a landing page while mobile visitors go straight to the App Store or Google Play.
- **Print and packaging** — drop a styled QR code on a flyer or box; update the destination later without reprinting.
- **Campaign measurement** — see which links, devices, and countries drive the most clicks, without wiring up a third-party analytics service.

## Core Capabilities

- **[Smart Links](smart-links.md)** — Custom element type with per-platform URLs (iOS, Android, Huawei, Amazon, Windows, Mac, Fallback). Each link has a unique slug, publish schedule, expiry date, and status lifecycle.

- **[Field layout](field-layout.md)** — Add fields to SmartLink elements when the link itself needs campaign metadata, app-owner notes, launch details, or internal approval fields. Populated tabs render on the SmartLink edit screen.

- **[Custom Domain](custom-domain.md)** — Serve smart links from a dedicated domain like `go.myapp.com`. Supports single-site and multisite setups with per-site URL tokens.

- **[QR Codes](qr-codes.md)** — Generate styled QR codes for any smart link. Customize module style, eye style, colors, logo overlay, size, margin, and output format (PNG or SVG). Cacheable via file system or Redis.

- **[Analytics](analytics.md)** — Track every click with device type, OS, browser, country, city, language, referrer, and click type (redirect, QR scan, button click). Configurable retention policy. Exportable to CSV, Excel, and JSON.

- **[Device Detection](device-detection.md)** — Matomo Device Detector identifies iOS, Android, Huawei, Amazon Fire, Windows, and Mac devices and routes each visitor to the right platform URL.

- **[Integrations](integrations.md)** — Built-in integrations with SEOmatic (Content SEO source and GTM/GA4 data layer events), Redirect Manager (automatic 301 on slug change), and Craft Link Field (SmartLink as a link type).

- **[Dashboard Widgets](dashboard-widgets.md)** — Two Craft dashboard widgets: Analytics Summary and Top Links. Both are configurable by date range and respect user permissions.

- **[Import & Export](import-export.md)** — Export all smart links to CSV, or bulk-create links by uploading a CSV through a guided map → preview → import wizard. Past imports are logged in an import history.

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
| Custom SmartLink fields | Settings → Field Layout |
| Platform-specific redirect routing | Automatic on install |
| QR code generation (PNG/SVG) | Per smart link, globally configurable |
| Click analytics with geo-detection | Built-in analytics dashboard |
| IP anonymization and hashing | Settings → Privacy |
| SEOmatic GTM/GA4 events | Settings → Integrations |
| Redirect Manager auto-301 | Settings → Integrations |
| Craft Link Field support | Available in field type selection |
| SmartLinkField element picker | Available in field type selection |
| CSV import & export | CP → SmartLink Manager → Import/Export |
| `craft.smartLinks` Twig variable | Template access to all elements and analytics |
| Dashboard widgets (2) | Craft Dashboard → New Widget |
| CP Utility panel | Utilities → SmartLink Manager |
| Console command for salt generation | `smartlink-manager/security/generate-salt` |

The CP Utility panel includes a site selector for overview link and analytics stats: choose **All Sites** for the aggregate view, or select one enabled site available to the current user to narrow those counts. QR/device cache counts and Servd static-cache purge actions remain global because they operate at the plugin/cache layer, not a single site's analytics scope.

## Dashboard Widgets

SmartLink Manager provides two Craft dashboard widgets. Add them via **Dashboard → New Widget**.

| Widget | What It Shows |
|--------|---------------|
| **Analytics Summary** | Total interactions, unique visitors, active links, engagement rate, and the top-performing link over a configurable date range |
| **Top Links** | Most-clicked smart links over a configurable date range with interaction counts |

Both widgets require the `smartLinkManager:viewAnalytics` permission and can be scoped to **All Sites** or one selected site.

## Next Steps

If you're new to SmartLink Manager, start here:

1. [Quickstart](../get-started/quickstart.md) — install the plugin, generate the IP salt, create your first link
2. [Smart Links](smart-links.md) — learn about platform URLs and statuses
3. [QR Codes](qr-codes.md) — generate and customize QR codes
4. [Analytics](analytics.md) — understand what's tracked and how to export data
5. [Integrations](integrations.md) — connect SEOmatic, Redirect Manager, and Craft Link Field
