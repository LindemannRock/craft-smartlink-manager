# SmartLink Manager Plugin for Craft CMS

[![Latest Version](https://img.shields.io/packagist/v/lindemannrock/craft-smartlink-manager.svg)](https://packagist.org/packages/lindemannrock/craft-smartlink-manager)
[![Craft CMS](https://img.shields.io/badge/Craft%20CMS-5.0+-orange.svg)](https://craftcms.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://php.net/)
[![Logging Library](https://img.shields.io/badge/Logging%20Library-5.0+-green.svg)](https://github.com/LindemannRock/craft-logging-library)
[![License](https://img.shields.io/packagist/l/lindemannrock/craft-smartlink-manager.svg)](LICENSE)

Intelligent URL shortening and redirect management plugin for Craft CMS 5.x with device detection, QR codes, and analytics.

## ⚠️ Beta Notice

This plugin is currently in active development and provided under the MIT License for testing purposes.

**Licensing is subject to change.** We are finalizing our licensing structure and some or all features may require a paid license when officially released on the Craft Plugin Store. Some plugins may remain free, others may offer free and Pro editions, or be fully commercial.

If you are using this plugin, please be aware that future versions may have different licensing terms.

## Features

- **URL Shortening**: Create memorable short URLs that redirect to any destination
- **Device-Specific Redirects**: Different URLs for iOS, Android, Huawei, Amazon, Windows, macOS, and desktop users using accurate DeviceDetector library
- **Cache-Safe Device Detection**: Works with CDN/static page caching (Servd, Cloudflare) by fetching fresh device detection via uncached endpoint
- **Image Management**: Upload and configure images with multiple size options (xl, lg, md, sm)
- **QR Code Generation**: Automatic QR codes for each smart link with customizable colors, styles, and logo overlay
- **Landing Page Customization**: Hide titles on landing pages, custom layouts, and template override support
- **Advanced Analytics**:
  - Geographic tracking with country and city-level data
  - Peak usage hours visualization
  - Device, browser, and platform breakdown
  - Mobile usage insights by location
  - Real-time interaction tracking (auto-redirects and button clicks)
  - Last interaction type and destination URL tracking
  - Source tracking (QR code scan, landing page visit, or direct access)
  - Button click tracking by platform (App Store, Google Play, etc.)
  - Configurable analytics retention and export options
- **Smart Link Field**: Integrate smart links into your entries and elements
- **Multi-Site Support**: Different destination URLs per site/language with a single shared slug
- **User-Friendly CP**: Clean interface matching Craft's design standards

## Requirements

- Craft CMS 5.0 or greater
- PHP 8.2 or greater
- [Logging Library](https://github.com/LindemannRock/craft-logging-library) 5.0 or greater (installed automatically as dependency)

## Installation

### Via Composer

```bash
cd /path/to/project
```

```bash
composer require lindemannrock/craft-smartlink-manager
```

```bash
./craft plugin/install smartlink-manager
```

### Using DDEV

```bash
cd /path/to/project
```

```bash
ddev composer require lindemannrock/craft-smartlink-manager
```

```bash
ddev craft plugin/install smartlink-manager
```

### Via Control Panel

In the Control Panel, go to Settings → Plugins and click "Install" for SmartLink Manager.

### ⚠️ Required Post-Install Step

**IMPORTANT:** After installation, you MUST generate the IP hash salt for analytics to work:

```bash
php craft smartlink-manager/security/generate-salt
```

**What happens if you skip this:**
- ⚠️ Analytics will still track clicks (query, device, referrer, etc.) but **without IP hash or geo-location data**
- ⚠️ An error will be logged: `IP hash salt not configured`
- ✅ Smart links will still redirect normally
- ✅ You can generate the salt later — full IP/geo tracking resumes immediately

**Quick Start:**
```bash
# After plugin installation:
php craft smartlink-manager/security/generate-salt

# The command will automatically add SMARTLINK_MANAGER_IP_SALT to your .env file
# Copy this value to staging/production .env files manually
```

## Configuration

### Settings

Navigate to **Settings → SmartLink Manager** in the control panel to configure:

- **Enable Analytics**: Global toggle to enable/disable all analytics functionality (disables all tracking and hides analytics UI when off)
- **Analytics Retention**: How many days to keep analytics data (0 for unlimited)
- **Export Settings**: Include/exclude disabled smart links in analytics exports
- **QR Code Settings**: Default size, colors, and format (individual links can override)
- **Redirect Settings**: Language detection method and 404 redirect URL
- **Interface Settings**: Items per page in element index

### Config File

Create a `config/smartlink-manager.php` file to override default settings:

```bash
cp vendor/lindemannrock/craft-smartlink-manager/src/config.php config/smartlink-manager.php
```

```php
<?php
return [
    // Plugin Settings
    'pluginName' => 'SmartLink Manager',

    // Logging Settings
    'logLevel' => 'error', // error, warning, info, or debug (debug requires devMode)

    // URL Settings
    'slugPrefix' => 'go',     // URL prefix for smart links (e.g., 'go' creates /go/your-link)
    'qrPrefix' => 'go/qr',    // URL prefix for QR code pages (e.g., 'go/qr' creates /go/qr/your-link)

    // Analytics Settings
    'enableAnalytics' => true,
    'analyticsRetention' => 90, // days (0 for unlimited)
    'includeDisabledInExport' => false,
    'includeExpiredInExport' => false,
    'enableGeoDetection' => false,
    'geoProvider' => 'ip-api.com', // Options: 'ip-api.com', 'ipapi.co', 'ipinfo.io'
    'geoApiKey' => App::env('SMARTLINK_MANAGER_GEO_API_KEY'), // Required for ip-api.com HTTPS

    // QR Code Settings
    'defaultQrSize' => 256,
    'defaultQrColor' => '#000000',
    'defaultQrBgColor' => '#FFFFFF',
    'defaultQrFormat' => 'png', // or 'svg'
    'defaultQrErrorCorrection' => 'M', // L, M, Q, H
    'defaultQrMargin' => 4,
    'qrModuleStyle' => 'square', // square, rounded, dots
    'qrEyeStyle' => 'square', // square, rounded, leaf
    'qrEyeColor' => null, // null = use main color
    'enableQrLogo' => false,
    'qrLogoSize' => 20, // percentage (10-30%)
    'defaultQrLogoId' => null,
    'enableQrDownload' => true,
    'qrDownloadFilename' => '{slug}-qr-{size}',

    // Cache Settings
    'cacheStorageMethod' => 'file',  // 'file' or 'redis'
    'enableQrCodeCache' => true,
    'qrCodeCacheDuration' => 86400, // seconds
    'cacheDeviceDetection' => true,
    'deviceDetectionCacheDuration' => 3600, // seconds

    // Template Settings
    'redirectTemplate' => null, // e.g., 'smartlink-manager/redirect'
    'qrTemplate' => null, // e.g., 'smartlink-manager/qr'

    // Language & Redirect Settings
    'languageDetectionMethod' => 'browser', // 'browser', 'ip', or 'both'
    'notFoundRedirectUrl' => '/',

    // Interface Settings
    'itemsPerPage' => 100,

    // Site Selection
    'enabledSites' => [], // Array of site IDs, empty = all sites

    // Multi-environment support
    'dev' => [
        'logLevel' => 'debug', // More verbose in dev
        'enableAnalytics' => true,
        'analyticsRetention' => 30,
    ],
    'production' => [
        'logLevel' => 'error', // Only errors in production
        'enableAnalytics' => true,
        'analyticsRetention' => 365,
        'cacheStorageMethod' => 'redis',  // Use Redis in production (Servd/AWS/Platform.sh)
        'cacheDeviceDetection' => true,
        'deviceDetectionCacheDuration' => 7200,
    ],
];
```

**Important:** After changing `slugPrefix` or `qrPrefix`, clear Craft's routes cache:
```bash
php craft clear-caches/compiled-templates
```

See [Configuration Documentation](docs/CONFIGURATION.md) for all available options.

### Production Environments

SmartLink Manager treats content and operational settings differently from system configuration:

**Always Editable (regardless of `allowAdminChanges`):**
- ✅ Creating and editing smart links
- ✅ Plugin settings (QR codes, analytics, integrations, colors, etc.)
- ✅ Changing global defaults

**Respects `allowAdminChanges=false`:**
- ❌ Field layout designer only

This design allows you to manage your smart links and operational settings in production while preventing system-level changes.

## Multi-Site Management

SmartLink Manager supports restricting functionality to specific sites in multi-site installations.

### Site Selection

Configure which sites SmartLink Manager should be enabled for:

**Via Control Panel:**
- Go to **Settings → Plugins → SmartLink Manager → General**
- Check the sites where SmartLink Manager should be available
- Leave empty to enable for all sites

**Via Configuration File:**
```php
// config/smartlink-manager.php
return [
    'enabledSites' => [1, 2], // Only enable for sites 1 and 2

    // Environment-specific overrides
    'dev' => [
        'enabledSites' => [1], // Only main site in development
    ],
    'production' => [
        'enabledSites' => [1, 2, 3], // All sites in production
    ],
];
```

**Behavior:**
- **CP Navigation**: SmartLink Manager only appears in sidebar for enabled sites
- **Site Switcher**: Only enabled sites appear in the site dropdown
- **Access Control**: Direct access to disabled sites returns 403 Forbidden
- **Backwards Compatibility**: Empty selection enables all sites

**Important Notes:**
- If the primary site is not included in `enabledSites`, SmartLink Manager will not appear in the main CP navigation at all, as the navigation uses the primary site context. Ensure you include your primary site ID if you want SmartLink Manager accessible from the main menu.
- You can still access SmartLink Manager on enabled non-primary sites via direct URLs, but the main navigation will be hidden.

## Usage

### Creating SmartLinks

1. Navigate to **SmartLink Manager** in the control panel
2. Click **"New smart link"**
3. Fill in the required fields:
   - **Title**: Display name for the smart link
   - **Slug**: The short URL path (e.g., `promo-2024`)
   - **Fallback URL**: Default destination when no platform-specific URL matches
4. Optionally add platform-specific App Store URLs:
   - **iOS URL**: App Store link for iOS devices
   - **Android URL**: Google Play Store link
   - **Huawei URL**: AppGallery link for Huawei devices
   - **Amazon URL**: Amazon Appstore link
   - **Windows URL**: Microsoft Store link
   - **Mac URL**: Mac App Store link

### Smart Link URLs

Your smart links will be accessible at:
- `https://yourdomain.com/go/[slug]` - Redirect URL
- `https://yourdomain.com/qr/[slug]` - QR code image
- `https://yourdomain.com/qr/[slug]/view` - QR code display page

**Customizable URL Prefixes:**
You can customize the `/go/` and `/go/qr/` prefixes via Settings → General:
- Change `slugPrefix` from `go` to `link`, `s`, or any custom prefix
- Change `qrPrefix` from `go/qr` to `qr`, `qrcode`, or any custom prefix (supports nested patterns)
- Only letters, numbers, hyphens, and underscores are allowed
- After changing, clear routes cache: `php craft clear-caches/compiled-templates`

### Using Smart Link Field

Add a Smart Link field to any element:

1. Go to **Settings → Fields**
2. Create a new **Smart Link** field
3. Add it to your field layout

In templates:
```twig
{# Get the smart link #}
{% set smartLink = entry.mySmartLinkField.one() %}

{# Output the redirect URL #}
<a href="{{ smartLink.getRedirectUrl() }}">{{ smartLink.title }}</a>

{# Get the QR code URL #}
<img src="{{ smartLink.getQrCodeUrl() }}" alt="QR Code">

{# Check if QR is enabled #}
{% if smartLink.qrCodeEnabled %}
    <img src="{{ siteUrl('qr/' ~ smartLink.slug) }}" alt="QR Code">
{% endif %}
```

### Analytics

SmartLink Manager provides comprehensive analytics dashboard with interaction tracking:

#### Main Analytics View
Navigate to **SmartLink Manager → Analytics** to see:
- Total interactions (auto-redirects and button clicks)
- Active links and links used percentage
- Daily interaction trends chart organized by sections:
  - **Traffic Overview**: Daily interactions visualization
  - **Device & Platform Analytics**: Separate charts for device types and operating systems
  - **Geographic Distribution**: Top countries and cities side by side
  - **Usage Patterns**: Peak hours and behavioral insights
- **Top SmartLinks** table showing:
  - Total interactions count
  - Last interaction timestamp and type (redirect or button click)
  - Last destination URL (truncated to 25 characters)
  - QR code scans vs direct visits breakdown
- **Latest Interactions** table with detailed tracking:
  - Interaction type (redirect or button click)
  - Button type (App Store, Google Play, etc.) for button clicks
  - Source (QR scan, landing page, or direct access)
  - Destination URL, device info, OS, and location

#### Geographic Analytics
- **Top Countries**: See which countries your traffic comes from
- **Top Cities**: City-level breakdown with click percentages
- **View Geographic Details**: Comprehensive modal showing all countries and cities

#### Advanced Insights
- **Peak Usage Hours**: Hourly bar chart showing when users are most active
- **Mobile Usage by City**: See mobile vs desktop preferences by location
- **Browser Preferences**: Most popular browsers by country

#### Features
- Date range filtering with AJAX updates (Last 7 days, 30 days, 90 days, All time)
- Export analytics data to CSV with configurable options
- Real-time interaction tracking (auto-redirects and button clicks only)
- Privacy-focused IP hashing with secure salt (rainbow-table proof)
- Automatic geographic detection using ip-api.com
- Global analytics toggle to disable all tracking
- Per-link analytics control with confirmation prompts

#### IP Privacy Protection

SmartLink Manager uses salted SHA256 hashing for IP addresses to prevent rainbow table attacks and protect visitor privacy:

**Setup (Local/Dev Environment Only):**
1. Generate a salt in your local/dev environment:
   ```bash
   php craft smartlink-manager/security/generate-salt
   ```
   This automatically adds `SMARTLINK_MANAGER_IP_SALT` to your `.env` file.

2. **Copy the salt to staging/production:**
   - Open your local `.env` file
   - Copy the `SMARTLINK_MANAGER_IP_SALT` value
   - Manually add it to staging and production `.env` files
   - **DO NOT** regenerate the salt in staging/production

   Example:
   ```bash
   # Local .env
   SMARTLINK_MANAGER_IP_SALT="0dffabe583a420819eba489d4c54f81aca4d6d8260f8188833a619127fab2646"

   # Copy this EXACT value to:
   # - staging/.env
   # - production/.env
   ```

**How It Works:**
- Plugin automatically reads salt from `.env` (no config file needed!)
- Config file can override if needed: `'ipHashSalt' => App::env('SMARTLINK_MANAGER_IP_SALT')`
- If no salt found, error banner shown in settings

**Critical Requirements:**
- ⚠️ **Generate ONCE in local/dev environment only**
- ⚠️ **Use the SAME salt across all environments** (dev, staging, production)
- ❌ **Never regenerate in staging/production** - this will break analytics
- ❌ **Never commit the salt to version control** (add `.env` to `.gitignore`)
- ✅ **Store the salt securely** (password manager recommended)
- ⚠️ Changing the salt will reset unique visitor tracking
- ✅ Salt is required for analytics tracking to work
- ✅ Raw IP addresses are NEVER stored (only salted hash + geo-location data)

**Privacy Features:**
- ✅ Rainbow table proof - Salted SHA256 hashing
- ✅ No raw IP storage - IP removed from metadata after geo-lookup
- ✅ Geo-location preserved - Country, city, region stored separately
- ✅ GDPR compliant - No personally identifiable IP data retained
- ✅ Unique visitor tracking - Maintained via salted hashes
- ✅ Optional IP anonymization - Extra privacy layer (see below)

#### Optional: IP Address Anonymization

For **maximum privacy**, you can enable IP address anonymization. This masks IPs before hashing and geo-location:

**How it works:**
- **IPv4**: Masks last octet (`192.168.1.123` → `192.168.1.0`)
- **IPv6**: Masks last 80 bits (keeps first 48 bits for ISP/network)
- IP is anonymized BEFORE hashing and geo-lookup
- Even if salt leaks, attackers only get subnet information

**Enable via Settings:**
1. Go to **Settings → SmartLink Manager → Analytics**
2. Enable **Anonymize IP Addresses**

**Or via Config:**
```php
// config/smartlink-manager.php
return [
    'anonymizeIpAddress' => true,
];
```

**Trade-offs:**
- ✅ **Extra Privacy**: Subnet-level anonymization + salt
- ✅ **Geo-location**: Still works (city/country detection unaffected)
- ⚠️ **Accuracy**: Multiple users on same subnet = counted as one visitor
- ⚠️ **Corporate/Office Networks**: All users appear as single visitor

**When to use:**
- High-privacy requirements (government, healthcare, EU)
- GDPR/privacy law compliance requirements
- Don't need precise unique visitor counts
- Prefer privacy over analytics accuracy

**Privacy Levels Comparison:**

| Feature | Default (Salt Only) | With Anonymization |
|---------|-------------------|-------------------|
| **Privacy** | Rainbow-table proof | Maximum (subnet-level) |
| **Unique Visitors** | Very accurate (per IP) | Less accurate (per subnet) |
| **Geo-location** | ✅ Works | ✅ Works |
| **Corporate Networks** | Each user = unique | All users = one visitor |
| **Salt Leak Risk** | Reveals full IP | Only reveals subnet |

### Third-Party Integrations

SmartLink Manager can integrate with third-party plugins to enhance functionality with analytics tracking and centralized redirect management.

#### Redirect Manager Integration

When [Redirect Manager](https://github.com/LindemannRock/craft-redirect-manager) is installed, SmartLink Manager can automatically create permanent redirects when smart link slugs change.

**Setup:**
1. Install Redirect Manager plugin
2. Navigate to **Settings → SmartLink Manager → Integrations**
3. Enable **Redirect Manager Integration**
4. Slug changes will automatically create redirects (e.g., `/go/promo-2024` → `/go/promo-2025`)
5. Save settings

**Benefits:**
- **Centralized Management** - View all redirects (smart links + regular pages) in one place
- **Analytics Tracking** - Track how many people access old smart link slugs after changes
- **Smart Undo Detection** - Prevents flip-flop redirects within configurable time window (30-240 minutes)
- **Loop Prevention** - Automatically detects and prevents circular redirects
- **Persistent Redirects** - Slug change redirects remain active permanently

**Configuration:**
```php
// config/smartlink-manager.php
return [
    'enabledIntegrations' => ['redirect-manager'],
    'redirectManagerEvents' => ['slug-change'],
];
```

#### SEOmatic Integration

When [SEOmatic](https://plugins.craftcms.com/seomatic) is installed, SmartLink Manager can push click events to Google Tag Manager's data layer for tracking in GTM and Google Analytics.

**Setup:**
1. Install and configure SEOmatic plugin with GTM or Google Analytics
2. Navigate to **Settings → SmartLink Manager → Analytics**
3. Scroll to **Third-Party Integrations** section
4. Enable **SEOmatic Integration**
5. Select which events to track (redirects, button clicks, QR scans)
6. Customize the event prefix if needed (default: `smart_links`)
7. Save settings

**GTM Event Structure:**

Events are pushed to `window.dataLayer` with the following structure:

```javascript
{
  event: "smart_links_redirect",
  smart_link: {
    slug: "promo-2024",
    title: "Summer Promo",
    destination_url: "https://app.example.com/promo",
    platform: "ios",              // ios, android, windows, macos, other
    source: "qr",                 // qr, landing, direct
    device_type: "mobile",        // mobile, tablet, desktop
    os: "iOS 17",
    browser: "Safari",
    country: "United States",
    city: "New York",
    click_type: "button"          // button or redirect
  }
}
```

**Event Types:**
- `smart_links_redirect` - Auto-redirects (mobile users automatically redirected)
- `smart_links_button_click` - Button clicks (manual platform selection)
- `smart_links_qr_scan` - QR code scans (accessed via `?src=qr` parameter)

**GTM Trigger Setup:**

Create triggers in Google Tag Manager to listen for these events:

1. **Trigger Type**: Custom Event
2. **Event Name**: `smart_links_redirect` (or your custom prefix)
3. **Use regex matching** to catch all SmartLink Manager events: `smart_links_.*`

**GA4 Event Example:**

Forward SmartLink Manager events to Google Analytics 4:

```
Event Name: smart_link_click
Parameters:
  - link_slug: {{smart_link.slug}}
  - link_platform: {{smart_link.platform}}
  - link_source: {{smart_link.source}}
  - device_type: {{smart_link.device_type}}
```

**Configuration via Config File:**

```php
// config/smartlink-manager.php
return [
    'enabledIntegrations' => ['seomatic'],
    'seomaticTrackingEvents' => ['redirect', 'button_click', 'qr_scan'],
    'seomaticEventPrefix' => 'smart_links',
];
```

**Important Notes:**
- Events are only sent when analytics tracking is enabled (globally and per-link)
- Requires SEOmatic plugin to be installed and enabled
- GTM or Google Analytics must be configured in SEOmatic
- Events include all analytics data SmartLink Manager already tracks
- No additional external API calls or performance impact

**Template Usage:**

Add the tracking method to your templates to enable client-side event tracking:

```twig
{# templates/smartlink-manager/redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{{ smartLink.title }}</title>

    {# Render SEOmatic tracking script (outputs JavaScript if enabled, null if disabled) #}
    {{ smartLink.renderSeomaticTracking('redirect') }}
</head>
<body>
    {# Your template content #}
</body>
</html>
```

For QR code templates:
```twig
{# templates/smartlink-manager/qr.twig #}
{{ smartLink.renderSeomaticTracking('qr_scan') }}
```

**Event Types:**
- `'redirect'` - Use for landing pages with buttons and auto-redirects
- `'qr_scan'` - Use for QR code display pages

**How It Works:**
- The method returns client-side JavaScript that pushes events to `window.dataLayer`
- Returns `null` if SEOmatic is not installed or disabled (no output)
- No need for `|raw` filter (returns `\Twig\Markup` automatically)
- Button clicks are intercepted with 300ms delay to ensure tracking completes
- Works with debug mode: add `?debug=1` to test tracking without redirects

### QR Codes

Each smart link automatically generates a QR code:

- Access at: `https://yourdomain.com/qr/[slug]`
- Customize size: `?size=300`
- Customize colors: `?color=FF0000&bg=FFFFFF`
- Returns PNG or SVG image based on settings
- Configurable default colors and size in settings
- Per-link QR code customization with reset button
- Live preview in edit page (280px, bottom-right corner)
- Logo overlay support (PNG format only, not available with SVG)
- Advanced styling options (module style, eye style, eye color)

### Individual Smart Link Settings

Each smart link has its own settings:

#### QR Code Customization
- Custom size, colors per link
- Reset to defaults button in sidebar
- Live preview in edit page

#### Analytics Control
- Toggle analytics tracking per link
- Confirmation prompt when disabling
- Respects global analytics setting

### Trashed SmartLinks

When smart links are trashed:
- They are no longer accessible via their URLs
- They cannot be edited until restored
- They appear in the trashed status filter
- Analytics data is preserved until permanent deletion

## Templating

### Template Requirements

**SmartLink Manager requires custom templates** for the redirect landing page and QR code display. These templates must be created in your project's `templates/` directory.

#### Default Template Paths

When `redirectTemplate` and `qrTemplate` are not configured (set to `null`), the plugin looks for:
- **Redirect landing page:** `templates/smartlink-manager/redirect.twig`
- **QR code display:** `templates/smartlink-manager/qr.twig`

**Important:** If these templates don't exist, visitors will see a "Unable to find template" error when accessing your smart links.

#### Quick Start: Copy Example Templates

The plugin includes example templates you can copy to get started:

```bash
# Create templates directory
mkdir -p templates/smartlink-manager

# Copy example templates
cp vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig templates/smartlink-manager/
cp vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig templates/smartlink-manager/

# Customize the templates to match your site's design
```

#### Custom Template Paths

You can use different template paths by configuring them:

**Via Config File:**
```php
// config/smartlink-manager.php
return [
    'redirectTemplate' => 'my-custom/landing', // Path relative to templates/
    'qrTemplate' => 'my-custom/qr-display',    // Path relative to templates/
];
```

**Via Control Panel:**
Settings → Redirect Settings → Custom Redirect Template
Settings → QR Code → Custom QR Code Template

**Basic Template Example:**
```twig
{# templates/smartlink-manager/redirect.twig #}
<!DOCTYPE html>
<html>
<head>
    <title>{{ smartLink.title }}</title>

    {# SEOmatic tracking integration (if enabled) #}
    {{ smartLink.renderSeomaticTracking('redirect') }}

    <script>
        // Client-side mobile detection for auto-redirect (works with cached pages)
        (function() {
            fetch('{{ actionUrl('smartlink-manager/redirect/refresh-csrf')|raw }}', {
                credentials: 'same-origin',
                cache: 'no-store'
            })
            .then(r => r.json())
            .then(data => {
                if (data.isMobile) {
                    window.location.replace('{{ actionUrl('smartlink-manager/redirect/go', {slug: smartLink.slug, platform: 'auto'})|raw }}');
                }
            })
            .catch(err => {
                console.error('Device detection failed:', err);
            });
        })();
    </script>
</head>
<body>
    <h1>{{ smartLink.title }}</h1>

    {# Platform-specific buttons that track clicks via redirect controller #}
    {% if smartLink.iosUrl %}
        <a href="{{ actionUrl('smartlink-manager/redirect/go', {slug: smartLink.slug, platform: 'ios', site: smartLink.site.handle}) }}">Download on App Store</a>
    {% endif %}

    {% if smartLink.androidUrl %}
        <a href="{{ actionUrl('smartlink-manager/redirect/go', {slug: smartLink.slug, platform: 'android', site: smartLink.site.handle}) }}">Get it on Google Play</a>
    {% endif %}

    {# Fallback button #}
    <a href="{{ actionUrl('smartlink-manager/redirect/go', {slug: smartLink.slug, platform: 'fallback', site: smartLink.site.handle}) }}">Continue to Website</a>

    {# QR Code #}
    {% if smartLink.qrCodeEnabled %}
        <img src="{{ smartLink.getQrCodeUrl() }}" alt="QR Code">
    {% endif %}
</body>
</html>
```

**How Tracking Works:**

The tracking system uses the redirect controller to log all interactions:
- **Mobile auto-redirects**: JavaScript detects mobile and redirects via `platform: 'auto'`
- **Button clicks**: All buttons use `actionUrl('smartlink-manager/redirect/go')` which tracks before redirecting
- **QR code scans**: QR codes include `?src=qr` parameter for source tracking
- **Works with CDN caching**: Device detection happens client-side via uncached endpoint
- **Desktop page loads**: Not tracked unless a button is clicked

**Available Template Variables:**
- `smartLink` - The SmartLink element
- `device` - DeviceInfo object with detection results
- `redirectUrl` - The calculated redirect URL for current device
- `language` - Detected language code

#### Custom QR Code Template

Create a custom QR code display page:

**Template Example:**
```twig
{# templates/smartlink-manager/qr.twig #}
<!DOCTYPE html>
<html lang="{{ currentSite.language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ smartLink.title }} - QR Code</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="flex justify-center items-center max-w-lg mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-lg max-w-lg w-full p-8 text-center">
            <h1 class="text-3xl font-semibold mb-4">{{ smartLink.title }}</h1>

            {% if smartLink.description %}
                <p class="text-gray-600 mb-8">{{ smartLink.description }}</p>
            {% endif %}

            {# QR Code display #}
            <div class="my-8 mx-auto">
                <img src="{{ smartLink.getQrCodeUrl({ size: size ?? 300 }) }}"
                     alt="{{ smartLink.title }} QR Code"
                     class="mx-auto">
            </div>

            <p class="text-sm text-gray-600">Scan with your phone's camera</p>
        </div>
    </div>
</body>
</html>
```

**Available QR Template Variables:**
- `smartLink` - The SmartLink element
- `size` - QR code size from URL parameter
- `format` - QR code format from URL parameter
- `color` - QR code color from URL parameter
- `bg` - QR code background color from URL parameter

### Available Properties

```twig
smartLink.id
smartLink.slug
smartLink.title
smartLink.description
smartLink.fallbackUrl
smartLink.iosUrl
smartLink.androidUrl
smartLink.huaweiUrl
smartLink.amazonUrl
smartLink.windowsUrl
smartLink.macUrl
smartLink.enabled           {# Per-site status managed by Craft's element system #}
smartLink.trackAnalytics
smartLink.qrCodeEnabled
smartLink.hideTitle
smartLink.getImage()
smartLink.imageSize
smartLink.clicks            {# Dynamically calculated from analytics data #}
smartLink.dateCreated
smartLink.dateUpdated
```

**Note:**
- `enabled` is a per-site property managed by Craft CMS's element system (stored in `elements_sites` table)
- `clicks` is dynamically calculated by counting records in the `smartlinks_analytics` table

### Device Detection Properties

```twig
device.isMobile
device.isTablet
device.isDesktop
device.platform  {# ios, android, huawei, windows, macos, linux, other #}
device.deviceType
device.brand
device.osName
device.osVersion
device.browser
device.language
```

### Methods

```twig
{# Get the appropriate redirect URL for current device #}
smartLink.getRedirectUrl()

{# Get full URL #}
smartLink.getUrl()

{# Get image asset #}
smartLink.getImage()

{# QR Code Methods #}

{# 1. Get QR code URL - Returns URL string (most efficient for templates) #}
smartLink.getQrCodeUrl()
smartLink.getQrCodeUrl({size: 500, color: 'FF0000', bg: '00FF00'})

{# 2. Get QR code as data URI - Returns base64 data URI (for inline/email) #}
smartLink.getQrCodeDataUri()
smartLink.getQrCodeDataUri({size: 300})

{# 3. Get QR code binary data - Returns PNG/SVG bytes (for downloads/API) #}
smartLink.getQrCode()
smartLink.getQrCode({format: 'svg'})

{# Get QR code display page URL #}
smartLink.getQrCodeDisplayUrl()

{# Render SEOmatic tracking script (returns Twig\Markup or null) #}
smartLink.renderSeomaticTracking(eventType = 'qr_scan')
{# Event types: 'redirect' for landing pages, 'qr_scan' for QR code pages #}
```

**QR Code Method Usage:**
- Use **getQrCodeUrl()** for regular templates (browser fetches image via URL)
- Use **getQrCodeDataUri()** for emails or when you need inline base64 data
- Use **getQrCode()** when you need raw binary data (downloads, API responses, file saving)

**QR Code Options:**
- `size`: Image size in pixels (100-4096)
- `color`: Foreground hex color (without #)
- `bg`: Background hex color (without #)
- `format`: 'png' or 'svg'
- `margin`: Quiet zone around QR code (0-10)
- `eyeColor`: Custom color for position markers
- `logo`: Asset ID for logo overlay

All options are optional - they fall back to the smart link's settings or global defaults.

### GraphQL Support

```graphql
query {
  smartLinks {
    id
    slug
    title
    description
    fallbackUrl
    iosUrl
    androidUrl
    enabled
    clicks
  }
}
```

## Events

```php
use lindemannrock\smartlinkmanager\events\SmartLinkEvent;
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use yii\base\Event;

Event::on(
    SmartLinksService::class,
    SmartLinksService::EVENT_BEFORE_REDIRECT,
    function(SmartLinkEvent $event) {
        // Modify redirect URL based on device
        if ($event->device->isMobile) {
            $event->redirectUrl = 'https://m.example.com';
        }
    }
);
```

## Console Commands

```bash
# Generate secure salt for IP hashing (required for analytics)
./craft smartlink-manager/security/generate-salt
```

**Note:** Analytics backfill commands (`update-countries`, `update-cities`) were removed in v5.0.2+. Geo-location now happens at tracking time only.

## Logging

SmartLink Manager uses the [LindemannRock Logging Library](https://github.com/LindemannRock/craft-logging-library) for centralized logging.

### Log Levels
- **Error**: Critical errors only (default)
- **Warning**: Errors and warnings
- **Info**: General information
- **Debug**: Detailed debugging (includes performance metrics, requires devMode)

### Configuration
```php
// config/smartlink-manager.php
return [
    'logLevel' => 'error', // error, warning, info, or debug
];
```

**Note:** Debug level requires Craft's `devMode` to be enabled. If set to debug with devMode disabled, it automatically falls back to info level.

### Log Files
- **Location**: `storage/logs/smartlink-manager-YYYY-MM-DD.log`
- **Retention**: 30 days (automatic cleanup via Logging Library)
- **Format**: Structured JSON logs with context data
- **Web Interface**: View and filter logs in CP at SmartLink Manager → Logs

### Log Management
Access logs through the Control Panel:
1. Navigate to SmartLink Manager → Logs
2. Filter by date, level, or search terms
3. Download log files for external analysis
4. View file sizes and entry counts
5. Auto-cleanup after 30 days (configurable via Logging Library)

**Requires:** `lindemannrock/logginglibrary` plugin (installed automatically as dependency)

See [docs/LOGGING.md](docs/LOGGING.md) for detailed logging documentation.

## Troubleshooting

### QR Codes Not Generating
- Ensure GD or ImageMagick is installed
- Check file permissions on `storage/runtime/`
- Verify QR codes are enabled in settings

### Redirects Not Working
- Check `.htaccess` or nginx config allows `/go/` URLs
- Ensure smart link is enabled
- Verify URLs are properly formatted

### How Mobile Redirects Work
Mobile users will briefly see the landing page before being automatically redirected:
1. All users (mobile and desktop) see the landing page with JavaScript
2. JavaScript fetches fresh device detection from `/smartlink-manager/redirect/refresh-csrf` (uncached endpoint)
3. If mobile device is detected, JavaScript redirects via `actionUrl('smartlink-manager/redirect/go', {platform: 'auto'})`
4. Desktop users stay on the landing page with platform buttons

This client-side approach ensures tracking works correctly even when pages are cached by CDN (Servd, Cloudflare).

**Troubleshooting:**
- Ensure mobile detection script is in your template (fetches from `refresh-csrf` endpoint)
- Ensure `/smartlink-manager/redirect/refresh-csrf` endpoint is not being cached
- Check browser console for errors
- The brief landing page flash is normal and necessary for tracking to work with page caching

### Analytics Not Tracking
- Confirm analytics is enabled globally in Settings → General
- Verify per-link analytics is enabled for the smart link
- Ensure buttons use `actionUrl('smartlink-manager/redirect/go')` instead of direct URLs
- Check browser isn't blocking JavaScript
- Check browser console for errors
- Desktop page loads without `?src=qr` parameter are intentionally NOT tracked

### Wrong Location in Local Development
When running locally (DDEV, localhost), analytics will **default to Dubai, UAE** because local IPs can't be geolocated. To set your actual location for testing:

**Option 1: Config File** (recommended for project-wide default)
```php
// config/smartlink-manager.php
return [
    'defaultCountry' => 'US',
    'defaultCity' => 'New York',
];
```

**Option 2: Environment Variable** (recommended for per-environment control)
```bash
# .env
SMARTLINK_MANAGER_DEFAULT_COUNTRY=US
SMARTLINK_MANAGER_DEFAULT_CITY=New York
```

**Fallback Priority:**
1. Config file setting
2. .env variable
3. Hardcoded default: Dubai, UAE

**Supported locations:**
- **US**: New York, Los Angeles, Chicago, San Francisco
- **GB**: London, Manchester
- **AE**: Dubai, Abu Dhabi (default: Dubai)
- **SA**: Riyadh, Jeddah
- **DE**: Berlin, Munich
- **FR**: Paris
- **CA**: Toronto, Vancouver
- **AU**: Sydney, Melbourne
- **JP**: Tokyo
- **SG**: Singapore
- **IN**: Mumbai, Delhi

**Important:** This setting is **safe to use in all environments** (dev, staging, production). It **only affects private/local IP addresses** (127.0.0.1, 192.168.x.x, 10.x.x.x, etc.). Real visitor IPs in production will always use actual geolocation from ip-api.com. This means you can safely commit config file settings without impacting production analytics.

## Support

- **Documentation**: [https://github.com/LindemannRock/craft-smartlink-manager](https://github.com/LindemannRock/craft-smartlink-manager)
- **Issues**: [https://github.com/LindemannRock/craft-smartlink-manager/issues](https://github.com/LindemannRock/craft-smartlink-manager/issues)
- **Email**: [support@lindemannrock.com](mailto:support@lindemannrock.com)

## License

This plugin is licensed under the MIT License. See [LICENSE](LICENSE) for details.

---

Developed by [LindemannRock](https://lindemannrock.com)
