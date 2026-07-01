![SmartLink Manager](docs/images/hero.webp)

# SmartLink Manager for Craft CMS

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-smartlink-manager.svg)](https://packagist.org/packages/lindemannrock/craft-smartlink-manager)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.10%2B-orange.svg)](https://craftcms.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net/)
[![Logging Library](https://img.shields.io/badge/Logging%20Library-5.13.1%2B-green.svg)](https://github.com/LindemannRock/craft-logging-library)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-smartlink-manager.svg)](LICENSE)

Intelligent device detection and app store routing for Craft CMS. Create smart links that detect a visitor's device and redirect them to the right app store, download page, or fallback URL automatically.

## Features

- **Smart Links** — Custom element type with per-platform URLs (iOS, Android, Huawei, Amazon, Windows, Mac, Fallback)
- **Device Detection** — Matomo Device Detector identifies platform and redirects automatically
- **QR Codes** — Styled QR codes with custom colors, module/eye styles, logo overlay, and PNG/SVG export
- **Analytics** — Click tracking with device, browser, country, city, language, referrer, and source breakdown
- **Integrations** — SEOmatic (Content SEO source and GTM/GA4 events), Redirect Manager (auto-301), Craft Link Field
- **Smart Link Field** — Element picker field for entries and other elements
- **Custom Fields** — Add editor-managed fields to SmartLink elements via a configurable field layout
- **GraphQL** — Resolve smart links, list enabled links, and query SmartLink fields from headless frontends
- **Custom Domain** — Serve smart links from a branded domain like `go.myapp.com` with single-site and multisite support
- **Multi-Site** — Per-site destination URLs with a single shared slug
- **Dashboard Widgets** — Analytics Summary and Top Links widgets
- **Import & Export** — Bulk CSV export and a guided CSV import wizard with history tracking

## Requirements

- Craft CMS 5.10+
- PHP 8.2+
- [Logging Library](https://github.com/LindemannRock/craft-logging-library) 5.13.1+ — optional, install in CP for logs

## Installation

### Composer

```bash
composer require lindemannrock/craft-smartlink-manager && php craft plugin/install smartlink-manager
```

### DDEV

```bash
ddev composer require lindemannrock/craft-smartlink-manager && ddev craft plugin/install smartlink-manager
```

### Post-install

Generate the IP hash salt used by privacy-conscious analytics:

```bash
php craft smartlink-manager/security/generate-salt
```

```bash
ddev craft smartlink-manager/security/generate-salt
```

## Documentation

Full documentation is available in the [docs](docs/) folder.

## Support

- **Issues**: [GitHub Issues](https://github.com/LindemannRock/craft-smartlink-manager/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the [Craft License](https://craftcms.github.io/license/). See [LICENSE.md](LICENSE.md) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
