<?php
/**
 * SmartLink Manager config.php
 *
 * This file exists only as a template for the SmartLink Manager settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'smartlink-manager.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 *
 * @since 1.0.0
 */

use craft\helpers\App;

return [
    // Global settings
    '*' => [
        // ========================================
        // GENERAL SETTINGS
        // ========================================
        // Configuration for basic plugin behavior, URLs, templates, and sites

        // Plugin Settings
        'pluginName' => 'SmartLink Manager',

        // IP Privacy Protection
        // Generate salt with: php craft smartlink-manager/security/generate-salt
        // Store in .env as: SMARTLINK_MANAGER_IP_SALT="your-64-char-salt"
        'ipHashSalt' => App::env('SMARTLINK_MANAGER_IP_SALT'),

        // URL Settings
        'slugPrefix' => 'go',              // URL prefix for smart links (e.g., 'go' creates /go/your-link)
        'qrPrefix' => 'go/qr',             // URL prefix for QR code pages (e.g., 'go/qr' creates /go/qr/your-link)

        // Template Settings
        'redirectTemplate' => null,        // Custom redirect landing page template path
        'qrTemplate' => null,              // Custom QR code display page template path

        // Site Settings
        'enabledSites' => [],              // Array of site IDs where SmartLink Manager should be enabled (empty = all sites)

        // Asset Settings
        // 'imageVolumeUid' => null,       // Asset volume UID for Smart Link images

        // Logging Settings
        'logLevel' => 'error',             // Log level: 'error', 'warning', 'info', 'debug' (debug requires devMode)


        // ========================================
        // QR CODE SETTINGS
        // ========================================
        // Appearance, styling, logo, and download options for QR codes

        // Appearance & Style
        // Note: Individual smart links inherit these defaults. Only custom-set values are saved.
        // If a smart link's color matches the global default, it's stored as NULL and will
        // automatically update when you change the global default.
        'defaultQrSize' => 256,            // Size in pixels (100-1000)
        'defaultQrFormat' => 'png',        // Format: 'png' or 'svg'
        'defaultQrColor' => '#000000',     // Foreground color (default: black)
        'defaultQrBgColor' => '#FFFFFF',   // Background color (default: white)
        'defaultQrMargin' => 4,            // White space around QR code (0-10 modules)
        'qrModuleStyle' => 'square',       // Module shape: 'square', 'rounded', 'dots'
        'qrEyeStyle' => 'square',          // Eye shape: 'square', 'rounded', 'leaf'
        'qrEyeColor' => null,              // Eye color (null = use main color)

        // Logo Settings
        'enableQrLogo' => false,           // Enable logo overlay in center of QR codes
        // 'qrLogoVolumeUid' => null,      // Asset volume UID for logo selection
        'defaultQrLogoId' => null,         // Default logo asset ID
        'qrLogoSize' => 20,                // Logo size as percentage (10-30%)

        // Technical Options
        'defaultQrErrorCorrection' => 'M', // Error correction level: L, M, Q, H

        // Download Settings
        'enableQrDownload' => true,        // Allow users to download QR codes
        'qrDownloadFilename' => '{slug}-qr-{size}', // Pattern with {slug}, {size}, {format}


        // ========================================
        // REDIRECT SETTINGS
        // ========================================
        // Language detection and redirect behavior

        'languageDetectionMethod' => 'browser', // Options: 'browser', 'ip', 'both'
        'notFoundRedirectUrl' => '/',      // Where to redirect for 404/disabled links


        // ========================================
        // ANALYTICS SETTINGS
        // ========================================
        // Analytics tracking, geographic detection, IP privacy, and data retention

        'enableAnalytics' => true,
        'enableGeoDetection' => false,     // Detect user location for analytics
        'anonymizeIpAddress' => false,     // Mask IP addresses for maximum privacy (IPv4: last octet, IPv6: last 80 bits)
        'analyticsRetention' => 90,        // Days to keep analytics data (0 = unlimited, max 3650)

        // Geo IP lookup provider
        // Options: 'ip-api.com', 'ipapi.co', 'ipinfo.io'
        // - ip-api.com: HTTP free (45/min), HTTPS requires paid key (default, backward compatible)
        // - ipapi.co: HTTPS, 1,000 requests/day free
        // - ipinfo.io: HTTPS, 50,000 requests/month free
        // 'geoProvider' => 'ip-api.com',

        // Geo provider API key
        // Required for ip-api.com HTTPS (Pro tier)
        // Optional for ipapi.co and ipinfo.io (increases rate limits)
        // 'geoApiKey' => App::env('SMARTLINK_MANAGER_GEO_API_KEY'),

        // Default location for local development
        // Used when IP address is private/local (127.0.0.1, 192.168.x.x, etc.)
        // 'defaultCountry' => App::env('SMARTLINK_MANAGER_DEFAULT_COUNTRY') ?: 'AE', // 2-letter country code (US, GB, AE, etc.)
        // 'defaultCity' => App::env('SMARTLINK_MANAGER_DEFAULT_CITY') ?: 'Dubai', // Must match a city in the predefined locations list


        // ========================================
        // INTERFACE SETTINGS
        // ========================================
        // Control panel interface options

        'itemsPerPage' => 100,             // Number of smart links per page (10-500)


        // ========================================
        // EXPORT SETTINGS
        // ========================================
        // Options for analytics exports

        'includeDisabledInExport' => false, // Include disabled smart links in analytics exports
        'includeExpiredInExport' => false,  // Include expired smart links in analytics exports


        // ========================================
        // CACHE SETTINGS
        // ========================================
        // Performance and caching configuration

        // Cache Storage Method
        // 'file' = File system (default, single server)
        // 'redis' = Redis/Database (load-balanced, multi-server, cloud hosting)
        'cacheStorageMethod' => 'file',

        // QR Code Caching
        'enableQrCodeCache' => true,       // Cache generated QR codes
        'qrCodeCacheDuration' => 86400,    // QR cache duration in seconds (24 hours)

        // Device Detection Caching
        'cacheDeviceDetection' => true,    // Cache device detection results
        'deviceDetectionCacheDuration' => 3600, // Device detection cache in seconds (1 hour)


        // ========================================
        // INTEGRATION SETTINGS
        // ========================================
        // Third-party integrations for enhanced functionality

        'enabledIntegrations' => [],       // Enabled integration handles (e.g., ['seomatic', 'redirect-manager'])

        // SEOmatic Integration
        'seomaticTrackingEvents' => ['redirect', 'button_click', 'qr_scan'], // Event types to track
        'seomaticEventPrefix' => 'smart_links', // Event prefix for GTM/GA events (lowercase, numbers, underscores only)

        // Redirect Manager Integration
        'redirectManagerEvents' => ['slug-change'], // Which events create redirects


        // ========================================
        // BASE PLUGIN OVERRIDES
        // ========================================
        // These settings override lindemannrock-base defaults for this plugin only.
        // Global defaults: vendor/lindemannrock/craft-plugin-base/src/config.php
        // To customize globally: copy to config/lindemannrock-base.php

        /**
         * Date/time formatting overrides
         * Override base plugin date/time display settings for this plugin
         * Defaults: from config/lindemannrock-base.php
         */
        // 'timeFormat' => '24',      // '12' (AM/PM) or '24' (military)
        // 'monthFormat' => 'short',  // 'numeric' (01), 'short' (Jan), 'long' (January)
        // 'dateOrder' => 'dmy',      // 'dmy', 'mdy', 'ymd'
        // 'dateSeparator' => '/',    // '/', '-', '.'
        // 'showSeconds' => false,    // Show seconds in time display

        /**
         * Default date range for analytics, logs, and dashboard pages
         * Options: 'today', 'yesterday', 'last7days', 'last30days', 'last90days',
         *          'thisMonth', 'lastMonth', 'thisYear', 'lastYear', 'all'
         * Default: 'last30days' (from base plugin)
         */
        // 'defaultDateRange' => 'last7days',

        /**
         * Export format overrides
         * Enable/disable specific export formats for this plugin
         * Default: all enabled (from base plugin)
         */
        // 'exports' => [
        //     'csv' => true,
        //     'json' => true,
        //     'excel' => true,
        // ],
    ],

    // Dev environment settings
    'dev' => [
        'logLevel' => 'debug',              // More verbose logging in dev
        'analyticsRetention' => 30,         // Keep less data in dev
        'cacheDeviceDetection' => false,    // No cache - testing
        'enableQrCodeCache' => false,       // No cache - see changes immediately
        'qrCodeCacheDuration' => 60,        // 1 minute - minimal cache if enabled
    ],

    // Staging environment settings
    'staging' => [
        'logLevel' => 'info',               // Moderate logging in staging
        'analyticsRetention' => 90,
        'cacheDeviceDetection' => true,
        'deviceDetectionCacheDuration' => 1800,  // 30 minutes - balance testing/performance
        'qrCodeCacheDuration' => 3600,      // 1 hour - catch issues while testing cache
    ],

    // Production environment settings
    'production' => [
        'logLevel' => 'error',              // Only errors in production
        'analyticsRetention' => 365,        // Keep more data in production
        'cacheStorageMethod' => 'redis',    // Use Redis for production (Servd/AWS/Platform.sh)
        'cacheDeviceDetection' => true,
        'deviceDetectionCacheDuration' => 7200,  // 2 hours - stable performance
        'qrCodeCacheDuration' => 604800,    // 7 days - QR codes rarely change
    ],
];
