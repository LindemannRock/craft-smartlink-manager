<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

/**
 * English Translations
 *
 * @since 1.0.0
 */

return [

    // =========================================================================
    // Plugin Meta
    // =========================================================================

    'SmartLink Manager' => 'SmartLink Manager',
    'Manage smart links, route users by device, and track engagement from one control panel workspace.' => 'Manage smart links, route users by device, and track engagement from one control panel workspace.',
    'Open SmartLink Manager' => 'Open SmartLink Manager',
    '{name} plugin loaded' => '{name} plugin loaded',
    '{displayName} caches' => '{displayName} caches',

    // =========================================================================
    // Element Names
    // =========================================================================

    'Smart Link' => 'Smart Link',
    'smart link' => 'smart link',
    'smart links' => 'smart links',
    'New smart link' => 'New smart link',

    // =========================================================================
    // Permissions
    // =========================================================================

    'Manage {plural}' => 'Manage {plural}',
    'Create {plural}' => 'Create {plural}',
    'Edit {plural}' => 'Edit {plural}',
    'Delete {plural}' => 'Delete {plural}',
    'View analytics' => 'View analytics',
    'Export analytics' => 'Export analytics',
    'Clear analytics' => 'Clear analytics',
    'Clear cache' => 'Clear cache',
    'View logs' => 'View logs',
    'View system logs' => 'View system logs',
    'Download system logs' => 'Download system logs',
    'Manage settings' => 'Manage settings',

    // =========================================================================
    // Navigation & Breadcrumbs
    // =========================================================================

    'Links' => 'Links',
    'Analytics' => 'Analytics',
    'Logs' => 'Logs',
    'Settings' => 'Settings',
    'General' => 'General',
    'QR Code' => 'QR Code',
    'Redirect' => 'Redirect',
    'Export' => 'Export',
    'Advanced' => 'Advanced',
    'Interface' => 'Interface',
    'Behavior' => 'Behavior',
    'Integrations' => 'Integrations',
    'Cache' => 'Cache',
    'Field Layout' => 'Field Layout',
    'Overview' => 'Overview',

    // =========================================================================
    // General Settings
    // =========================================================================

    'General Settings' => 'General Settings',
    'Plugin Name' => 'Plugin Name',
    'The name of the plugin as it appears in the Control Panel menu' => 'The name of the plugin as it appears in the Control Panel menu',
    'Plugin Settings' => 'Plugin Settings',
    'Log Level' => 'Log Level',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Choose what types of messages to log. Debug level requires devMode to be enabled.',
    'Error (Critical errors only)' => 'Error (Critical errors only)',
    'Warning (Errors and warnings)' => 'Warning (Errors and warnings)',
    'Info (General information)' => 'Info (General information)',
    'Debug (Detailed debugging)' => 'Debug (Detailed debugging)',
    'Logging Settings' => 'Logging Settings',

    // =========================================================================
    // Site Settings
    // =========================================================================

    'Site Settings' => 'Site Settings',
    'Enabled Sites' => 'Enabled Sites',
    'Select which sites SmartLink Manager should be enabled for. Leave empty to enable for all sites.' => 'Select which sites SmartLink Manager should be enabled for. Leave empty to enable for all sites.',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.',

    // =========================================================================
    // URL Settings
    // =========================================================================

    'URL Settings' => 'URL Settings',
    'Smart Link URL Prefix' => 'Smart Link URL Prefix',
    '{singularName} URL Prefix' => '{singularName} URL Prefix',
    'QR Code URL Prefix' => 'QR Code URL Prefix',
    'The URL prefix for smart links (e.g., \'go\' creates /go/your-link)' => 'The URL prefix for smart links (e.g., \'go\' creates /go/your-link)',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link)' => 'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link). Clear routes cache after changing (php craft clear-caches/compiled-templates).',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link/view or \'go/qr\' creates /go/qr/your-link/view)' => 'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link/view or \'go/qr\' creates /go/qr/your-link/view)',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'Clear routes cache after changing this (php craft clear-caches/compiled-templates).',
    'Smart Link Base URL' => 'Smart Link Base URL',
    '{singularName} Base URL' => '{singularName} Base URL',
    'Optional absolute URL used for generated smart links and QR URLs. Leave empty to use each site\'s base URL.' => 'Optional absolute URL used for generated smart links and QR URLs. Leave empty to use each site\'s base URL.',
    'Base URL for generated smart links and QR URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'Base URL for generated smart links and QR URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.',
    'Base URL for {singularName} and QR code URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'Base URL for {singularName} and QR code URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.',
    'Changing the URL prefix will break all existing {pluginName}. Only change this before creating your first {singularName}.' => 'Changing the URL prefix will break all existing {pluginName}. Only change this before creating your first {singularName}.',
    'Multisite detected: <code>Smart Link Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multisite detected: <code>Smart Link Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.',
    'Multisite detected: <code>{singularName} Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multisite detected: <code>{singularName} Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.',

    // =========================================================================
    // Template Settings
    // =========================================================================

    'Template Settings' => 'Template Settings',
    'Redirect Template' => 'Redirect Template',
    'Custom Redirect Template' => 'Custom Redirect Template',
    'Template path in your templates/ folder. Leave empty to use the default path.' => 'Template path in your templates/ folder. Leave empty to use the default path.',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)' => 'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)',
    'QR Code Template' => 'QR Code Template',
    'Custom QR Code Template' => 'Custom QR Code Template',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)' => 'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)',
    'These templates must exist in your site\'s <code>templates/</code> folder. Copy the reference templates from <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> to <code>templates/smartlink-manager/</code> and customize as needed.' => 'These templates must exist in your site\'s <code>templates/</code> folder. Copy the reference templates from <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> to <code>templates/smartlink-manager/</code> and customize as needed.',

    // =========================================================================
    // Asset Settings
    // =========================================================================

    'Asset Settings' => 'Asset Settings',
    'Image Volume' => 'Image Volume',
    'Smart Link Image Volume' => 'Smart Link Image Volume',
    '{singularName} Image Volume' => '{singularName} Image Volume',
    'Which asset volume should be used for SmartLink Manager images' => 'Which asset volume should be used for SmartLink Manager images',
    'Which asset volume should be used for {singularName} images' => 'Which asset volume should be used for {singularName} images',
    'All asset volumes' => 'All asset volumes',

    // =========================================================================
    // QR Code Settings — Appearance
    // =========================================================================

    'QR Code Settings' => 'QR Code Settings',
    'Appearance & Style' => 'Appearance & Style',
    'Enable QR Code' => 'Enable QR Code',
    'Default QR Code Size' => 'Default QR Code Size',
    'Default size in pixels for generated QR codes' => 'Default size in pixels for generated QR codes',
    'QR Code Color' => 'QR Code Color',
    'Default QR Code Color' => 'Default QR Code Color',
    'Default QR Background Color' => 'Default QR Background Color',
    'Background Color' => 'Background Color',
    'Default QR Code Format' => 'Default QR Code Format',
    'Default format for generated QR codes' => 'Default format for generated QR codes',
    'Override the default QR code format' => 'Override the default QR code format',
    'Format' => 'Format',
    'Use Default ({format|upper})' => 'Use Default ({format|upper})',
    'Color' => 'Color',
    'Background' => 'Background',
    'Eye Color' => 'Eye Color',
    'Color for position markers (leave empty to use main color)' => 'Color for position markers (leave empty to use main color)',
    'Size' => 'Size',

    // =========================================================================
    // QR Code Settings — Logo
    // =========================================================================

    'Logo Settings' => 'Logo Settings',
    'Enable QR Code Logo' => 'Enable QR Code Logo',
    'Enable Logo Overlay' => 'Enable Logo Overlay',
    'Add a logo in the center of QR codes' => 'Add a logo in the center of QR codes',
    'Logo Volume' => 'Logo Volume',
    'Logo Asset Volume' => 'Logo Asset Volume',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.',
    'Default Logo' => 'Default Logo',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'Default logo to use for QR codes (can be overridden per smart link)',
    'Default logo is required when logo overlay is enabled.' => 'Default logo is required when logo overlay is enabled.',
    'Logo Size (%)' => 'Logo Size (%)',
    'Logo Size' => 'Logo Size',
    'Logo size as percentage of QR code (10-30%)' => 'Logo size as percentage of QR code (10-30%)',
    'Logo' => 'Logo',
    'Override the default QR code logo' => 'Override the default QR code logo',
    'Using default logo from settings (click to override)' => 'Using default logo from settings (click to override)',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'Logo overlay only works with PNG format. SVG format does not support logos.',
    'Logo requires PNG format' => 'Logo requires PNG format',
    'Please save settings to apply the volume change to the logo selection field.' => 'Please save settings to apply the volume change to the logo selection field.',
    'Please save to apply the volume change' => 'Please save to apply the volume change',

    // =========================================================================
    // QR Code Settings — Technical
    // =========================================================================

    'Technical Options' => 'Technical Options',
    'Error Correction Level' => 'Error Correction Level',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'Higher levels work better if QR code is damaged but create denser patterns',
    'QR Code Margin' => 'QR Code Margin',
    'Margin Size' => 'Margin Size',
    'White space around QR code (0-10 modules)' => 'White space around QR code (0-10 modules)',
    'Module Style' => 'Module Style',
    'Shape of the QR code modules' => 'Shape of the QR code modules',
    'Eye Style' => 'Eye Style',
    'Shape of the position markers (corners)' => 'Shape of the position markers (corners)',

    // =========================================================================
    // QR Code Settings — Downloads
    // =========================================================================

    'Download Settings' => 'Download Settings',
    'Enable QR Code Downloads' => 'Enable QR Code Downloads',
    'Allow users to download QR codes' => 'Allow users to download QR codes',
    'Download Filename Pattern' => 'Download Filename Pattern',
    'Available variables: {slug}, {size}, {format}' => 'Available variables: {slug}, {size}, {format}',
    'Download QR Code' => 'Download QR Code',
    'Small (256px)' => 'Small (256px)',
    'Medium (512px)' => 'Medium (512px)',
    'Large (1024px)' => 'Large (1024px)',
    'Extra Large (2048px)' => 'Extra Large (2048px)',
    'Custom Size...' => 'Custom Size...',

    // =========================================================================
    // QR Code Settings — Actions & Preview
    // =========================================================================

    'QR Code Actions' => 'QR Code Actions',
    'View QR Code' => 'View QR Code',
    'QR Code Image' => 'QR Code Image',
    'QR Code Page' => 'QR Code Page',
    'Reset to Defaults' => 'Reset to Defaults',
    'Live Preview' => 'Live Preview',
    'Preview' => 'Preview',
    'Click to view QR code image' => 'Click to view QR code image',
    'Click to view QR code page' => 'Click to view QR code page',
    'Toggle preview' => 'Toggle preview',
    'QR code settings reset to defaults' => 'QR code settings reset to defaults',
    'Performance & Caching' => 'Performance & Caching',
    'Configure QR code caching to improve performance and reduce server load.' => 'Configure QR code caching to improve performance and reduce server load.',
    'Go to Cache Settings' => 'Go to Cache Settings',

    // =========================================================================
    // Behavior Settings
    // =========================================================================

    'Behavior Settings' => 'Behavior Settings',
    'Redirect Behavior' => 'Redirect Behavior',
    '404 Redirect URL' => '404 Redirect URL',
    'Where to redirect when a smart link is not found or disabled' => 'Where to redirect when a smart link is not found or disabled',
    'Where to redirect when a {singularName} is not found or disabled' => 'Where to redirect when a {singularName} is not found or disabled',
    'Can be a relative path (/) or full URL (https://example.com)' => 'Can be a relative path (/) or full URL (https://example.com)',

    // =========================================================================
    // Analytics Settings
    // =========================================================================

    'Analytics Settings' => 'Analytics Settings',
    'Enable Analytics' => 'Enable Analytics',
    'Track Analytics' => 'Track Analytics',
    'Track clicks and visitor data for smart links' => 'Track clicks and visitor data for smart links',
    'Track clicks and visitor data for {pluginName}' => 'Track clicks and visitor data for {pluginName}',
    'When enabled, SmartLink Manager will track visitor interactions, device types, geographic data, and other analytics information.' => 'When enabled, SmartLink Manager will track visitor interactions, device types, geographic data, and other analytics information.',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.',
    'Are you sure you want to disable analytics tracking for this smart link? This smart link will no longer collect visitor data and interactions.' => 'Are you sure you want to disable analytics tracking for this smart link? This smart link will no longer collect visitor data and interactions.',
    'Are you sure you want to disable analytics tracking for this {singularName}? This {singularName} will no longer collect visitor data and interactions.' => 'Are you sure you want to disable analytics tracking for this {singularName}? This {singularName} will no longer collect visitor data and interactions.',

    // =========================================================================
    // Analytics Settings — IP Privacy
    // =========================================================================

    'IP Address Privacy' => 'IP Address Privacy',
    'Anonymize IP Addresses' => 'Anonymize IP Addresses',
    'Mask IP addresses before storage for maximum privacy. <strong>IPv4</strong>: masks last octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: masks last 80 bits. <strong>Trade-off</strong>: Reduces unique visitor accuracy (users on same subnet counted as one visitor). Geo-location still works normally.' => 'Mask IP addresses before storage for maximum privacy. <strong>IPv4</strong>: masks last octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: masks last 80 bits. <strong>Trade-off</strong>: Reduces unique visitor accuracy (users on same subnet counted as one visitor). Geo-location still works normally.',
    'Privacy Levels' => 'Privacy Levels',
    'Enabled' => 'Enabled',
    'default' => 'default',
    'Full IP hashed with salt (accurate unique visitors)' => 'Full IP hashed with salt (accurate unique visitors)',
    'Subnet masked + hashed with salt (maximum privacy, less accurate)' => 'Subnet masked + hashed with salt (maximum privacy, less accurate)',

    // =========================================================================
    // Analytics Settings — Retention & Cleanup
    // =========================================================================

    'Analytics Retention (days)' => 'Analytics Retention (days)',
    'Analytics Retention' => 'Analytics Retention',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'How many days to keep analytics data (0 for unlimited, max 3650)',
    'Data Retention' => 'Data Retention',
    'Analytics Cleanup' => 'Analytics Cleanup',
    'Analytics data older than {days} days will be automatically cleaned up daily.' => 'Analytics data older than {days} days will be automatically cleaned up daily.',
    'Clean Up Now' => 'Clean Up Now',
    'Are you sure you want to clean up old analytics data now?' => 'Are you sure you want to clean up old analytics data now?',
    'Unlimited Retention Warning' => 'Unlimited Retention Warning',
    'Warning' => 'Warning',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.',

    // =========================================================================
    // Geo Provider Settings (from base _partials/geo-settings, uses |t(pluginHandle))
    // =========================================================================

    'Geographic Detection' => 'Geographic Detection',
    'Geographic Analytics' => 'Geographic Analytics',
    'Geographic Distribution' => 'Geographic Distribution',
    'Enable Geographic Detection' => 'Enable Geographic Detection',
    'Detect user location for analytics' => 'Detect user location for analytics',
    'View Geographic Details' => 'View Geographic Details',
    'Loading geographic data...' => 'Loading geographic data...',

    // Geo provider partial (lindemannrock-base/_partials/geo-settings)
    'Geo Provider' => 'Geo Provider',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Select the geo IP lookup provider. HTTPS providers recommended for privacy.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP free, HTTPS paid)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/day free)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/month free)',
    'API Key' => 'API Key',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).',

    // IP salt error banner (from base partial)
    'error' => 'error',
    'Configuration Required' => 'Configuration Required',
    'IP hash salt is missing.' => 'IP hash salt is missing.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'Analytics tracking requires a secure salt for privacy protection.',
    'Run one of these commands in your terminal:' => 'Run one of these commands in your terminal:',
    'Standard:' => 'Standard:',
    'COPY' => 'COPY',
    'DDEV:' => 'DDEV:',
    'This will automatically add' => 'This will automatically add',
    'to your' => 'to your',
    'file.' => 'file.',
    'Warning:' => 'Warning:',
    'Copy the same salt to staging and production environments.' => 'Copy the same salt to staging and production environments.',
    'COPIED!' => 'COPIED!',
    'Failed to copy to clipboard' => 'Failed to copy to clipboard',

    // =========================================================================
    // Device Detection Settings
    // =========================================================================

    'Cache Device Detection' => 'Cache Device Detection',
    'Cache device detection results for better performance' => 'Cache device detection results for better performance',
    'Device Detection Cache Duration (seconds)' => 'Device Detection Cache Duration (seconds)',

    // =========================================================================
    // Language Detection Settings
    // =========================================================================

    'Language Detection Method' => 'Language Detection Method',
    'How to detect user language preference' => 'How to detect user language preference',
    'Language Detection' => 'Language Detection',
    'Enable automatic language detection to redirect users based on their browser or location' => 'Enable automatic language detection to redirect users based on their browser or location',

    // =========================================================================
    // Cache Settings
    // =========================================================================

    'Cache Settings' => 'Cache Settings',
    'Cache Storage Settings' => 'Cache Storage Settings',
    'Cache Storage Method' => 'Cache Storage Method',
    'How to store cache data. Use Redis/Database for load-balanced or multi-server environments.' => 'How to store cache data. Use Redis/Database for load-balanced or multi-server environments.',
    'File System (default, single server)' => 'File System (default, single server)',
    'Redis/Database (load-balanced, multi-server, cloud hosting)' => 'Redis/Database (load-balanced, multi-server, cloud hosting)',
    'QR Code Caching' => 'QR Code Caching',
    'Enable QR Code Cache' => 'Enable QR Code Cache',
    'Cache generated QR codes for better performance' => 'Cache generated QR codes for better performance',
    'QR Code Cache Duration (seconds)' => 'QR Code Cache Duration (seconds)',
    'QR Code Cache Duration' => 'QR Code Cache Duration',
    'How long to cache generated QR codes (in seconds)' => 'How long to cache generated QR codes (in seconds)',
    'Cache duration in seconds' => 'Cache duration in seconds',
    'Min: 60 (1 minute), Max: 604800 (7 days)' => 'Min: 60 (1 minute), Max: 604800 (7 days)',
    'Caching' => 'Caching',
    'Device Detection Caching' => 'Device Detection Caching',
    'Device Detection Cache Duration' => 'Device Detection Cache Duration',
    'Device detection caching is only available when Analytics is enabled. Go to' => 'Device detection caching is only available when Analytics is enabled. Go to',
    'to enable analytics.' => 'to enable analytics.',

    // =========================================================================
    // Export Settings
    // =========================================================================

    'Export Settings' => 'Export Settings',
    'Analytics Export Options' => 'Analytics Export Options',
    'Include Disabled Links in Export' => 'Include Disabled Links in Export',
    'Include Disabled SmartLinks in Export' => 'Include Disabled SmartLinks in Export',
    'Include Disabled {pluginName} in Export' => 'Include Disabled {pluginName} in Export',
    'When enabled, analytics exports will include data from disabled smart links' => 'When enabled, analytics exports will include data from disabled smart links',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'When enabled, analytics exports will include data from disabled {pluginName}',
    'Include Expired Links in Export' => 'Include Expired Links in Export',
    'Include Expired SmartLinks in Export' => 'Include Expired SmartLinks in Export',
    'Include Expired {pluginName} in Export' => 'Include Expired {pluginName} in Export',
    'When enabled, analytics exports will include data from expired smart links' => 'When enabled, analytics exports will include data from expired smart links',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'When enabled, analytics exports will include data from expired {pluginName}',
    'Export as CSV' => 'Export as CSV',

    // =========================================================================
    // Interface Settings
    // =========================================================================

    'Interface Settings' => 'Interface Settings',
    'Items Per Page' => 'Items Per Page',
    'Number of smart links to show per page' => 'Number of smart links to show per page',
    'Number of {pluginName} to show per page' => 'Number of {pluginName} to show per page',
    'Allow Multiple' => 'Allow Multiple',
    'Whether to allow multiple smart links to be selected' => 'Whether to allow multiple smart links to be selected',
    'Whether to allow multiple {pluginName} to be selected' => 'Whether to allow multiple {pluginName} to be selected',
    'The maximum number of {pluginName} that can be selected.' => 'The maximum number of {pluginName} that can be selected.',
    'Which sources should be available to select {pluginName} from?' => 'Which sources should be available to select {pluginName} from?',

    // =========================================================================
    // Integration Settings
    // =========================================================================

    'Third-Party Integrations' => 'Third-Party Integrations',
    'Integrations Settings' => 'Integrations Settings',
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.',
    '{pluginName} Integration' => '{pluginName} Integration',
    'Installed & Active' => 'Installed & Active',
    'Installed but Disabled' => 'Installed but Disabled',
    'Not Installed' => 'Not Installed',
    'Install Plugin' => 'Install Plugin',
    'Push {smartLinksName} click events to Google Tag Manager and analytics platforms for tracking redirects, button clicks, and QR code scans.' => 'Push {smartLinksName} click events to Google Tag Manager and analytics platforms for tracking redirects, button clicks, and QR code scans.',
    'Active Tracking Scripts' => 'Active Tracking Scripts',
    'Scripts receiving {pluginName} events' => 'Scripts receiving {pluginName} events',
    'Note' => 'Note',
    'No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}.' => 'No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}.',
    'Configuration' => 'Configuration',
    'Tracking Events' => 'Tracking Events',
    'Select which events to send to {pluginName}' => 'Select which events to send to {pluginName}',
    'Auto-Redirects' => 'Auto-Redirects',
    'Mobile users automatically redirected' => 'Mobile users automatically redirected',
    'Button Clicks' => 'Button Clicks',
    'Manual platform selection on landing page' => 'Manual platform selection on landing page',
    'QR Code Scans' => 'QR Code Scans',
    'QR code accessed via ?src=qr parameter' => 'QR code accessed via ?src=qr parameter',
    'Event Prefix' => 'Event Prefix',
    'Prefix for event names (e.g., \'smart_links_redirect\')' => 'Prefix for event names (e.g., \'smart_links_redirect\')',
    'Event Data Structure' => 'Event Data Structure',
    'Click to view the data layer event format' => 'Click to view the data layer event format',
    'How Events Are Sent' => 'How Events Are Sent',
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} pushes events to GTM or GA4 dataLayer only',
    'Only Google Tag Manager and Google Analytics 4 support the dataLayer format in SEOmatic' => 'Only Google Tag Manager and Google Analytics 4 support the dataLayer format in SEOmatic',
    'Use GTM to forward to other platforms' => 'Use GTM to forward to other platforms',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.',
    'Events are only sent when analytics tracking is enabled both globally and per-link' => 'Events are only sent when analytics tracking is enabled both globally and per-link',
    'Architecture' => 'Architecture',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.',
    'Select which {pluginName} events to send to SEOmatic' => 'Select which {pluginName} events to send to SEOmatic',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}',
    // Redirect Manager Integration
    'Create permanent redirect records when {pluginName} slugs change. Provides centralized redirect management and analytics tracking.' => 'Create permanent redirect records when {pluginName} slugs change. Provides centralized redirect management and analytics tracking.',
    'Creates permanent redirects when {pluginName} slugs change or links are deleted' => 'Creates permanent redirects when {pluginName} slugs change or links are deleted',
    'Automatic Redirect Creation' => 'Automatic Redirect Creation',
    'Select which events should create permanent redirects in {pluginName}' => 'Select which events should create permanent redirects in {pluginName}',
    'Slug Changes' => 'Slug Changes',
    'Change slug from <code>promo-2024</code> to <code>promo-2025</code> → Creates <code>/go/promo-2024</code> → <code>/go/promo-2025</code>' => 'Change slug from <code>promo-2024</code> to <code>promo-2025</code> → Creates <code>/go/promo-2024</code> → <code>/go/promo-2025</code>',
    'Benefits of This Integration' => 'Benefits of This Integration',
    'Centralized Management' => 'Centralized Management',
    'View and manage all redirects ({pluginName} + regular pages) in one place' => 'View and manage all redirects ({pluginName} + regular pages) in one place',
    'Analytics Tracking' => 'Analytics Tracking',
    'See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries' => 'See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries',
    'Persistent Redirects' => 'Persistent Redirects',
    'Redirects persist even if {pluginName} is deleted, preventing broken links permanently' => 'Redirects persist even if {pluginName} is deleted, preventing broken links permanently',
    'Source Tracking' => 'Source Tracking',
    '{rmPluginName} shows which plugin created each redirect for better organization' => '{rmPluginName} shows which plugin created each redirect for better organization',
    'Enabled Integrations' => 'Enabled Integrations',
    // SmartLinkType (Link field integration)
    '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.' => '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.',
    'Invalid {pluginName} format.' => 'Invalid {pluginName} format.',
    '{pluginName} not found.' => '{pluginName} not found.',

    // =========================================================================
    // Smart Link Fields (edit page)
    // =========================================================================

    'Title' => 'Title',
    'The title of this smart link' => 'The title of this smart link',
    'The title of this {singularName}' => 'The title of this {singularName}',
    'Description' => 'Description',
    'A brief description of this smart link' => 'A brief description of this smart link',
    'A brief description of this {singularName}' => 'A brief description of this {singularName}',
    'Icon' => 'Icon',
    'Icon identifier or URL for this smart link' => 'Icon identifier or URL for this smart link',
    'Icon identifier or URL for this {singularName}' => 'Icon identifier or URL for this {singularName}',
    'Image' => 'Image',
    'Select an image for this smart link' => 'Select an image for this smart link',
    'Select an image for this {singularName}' => 'Select an image for this {singularName}',
    'Image Size' => 'Image Size',
    'Select the size for the smart link image' => 'Select the size for the smart link image',
    'Select the size for the {singularName} image' => 'Select the size for the {singularName} image',
    'Hide Title on Landing Pages' => 'Hide Title on Landing Pages',
    'Hide the smart link title on both redirect and QR code landing pages' => 'Hide the smart link title on both redirect and QR code landing pages',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'Hide the {singularName} title on both redirect and QR code landing pages',
    'Display Settings' => 'Display Settings',
    'Advanced Settings' => 'Advanced Settings',
    'Destination URL' => 'Destination URL',
    'Last Destination URL' => 'Last Destination URL',
    'Fallback URL' => 'Fallback URL',
    'The URL to redirect to when no platform-specific URL is available' => 'The URL to redirect to when no platform-specific URL is available',
    'iOS URL' => 'iOS URL',
    'App Store URL for iOS devices' => 'App Store URL for iOS devices',
    'Android URL' => 'Android URL',
    'Google Play Store URL for Android devices' => 'Google Play Store URL for Android devices',
    'Huawei URL' => 'Huawei URL',
    'AppGallery URL for Huawei devices' => 'AppGallery URL for Huawei devices',
    'Amazon URL' => 'Amazon URL',
    'Amazon Appstore URL' => 'Amazon Appstore URL',
    'Windows URL' => 'Windows URL',
    'Microsoft Store URL for Windows devices' => 'Microsoft Store URL for Windows devices',
    'Mac URL' => 'Mac URL',
    'Mac App Store URL' => 'Mac App Store URL',
    'App Store URLs' => 'App Store URLs',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.',
    '{pluginName} URL' => '{pluginName} URL',
    'URL copied to clipboard' => 'URL copied to clipboard',
    'New {singularName}' => 'New {singularName}',

    // =========================================================================
    // Field Layout
    // =========================================================================

    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.',
    'No field layout available.' => 'No field layout available.',

    // =========================================================================
    // Smart Link Element — Index & Actions
    // =========================================================================

    'Slug' => 'Slug',
    'Redirect Page' => 'Redirect Page',
    'All {pluginName}' => 'All {pluginName}',
    'New {name}' => 'New {name}',
    'Are you sure you want to delete the selected smart links?' => 'Are you sure you want to delete the selected smart links?',
    'Smart links deleted.' => 'Smart links deleted.',
    'Smart links restored.' => 'Smart links restored.',
    'Some smart links restored.' => 'Some smart links restored.',
    'Smart links not restored.' => 'Smart links not restored.',
    'Add a smart link' => 'Add a smart link',
    'No smart links selected' => 'No smart links selected',
    'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.' => 'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.',
    'Create a new smart link' => 'Create a new smart link',

    // =========================================================================
    // Analytics Dashboard — Overview Tab
    // =========================================================================

    'SmartLink Manager Overview' => 'SmartLink Manager Overview',
    'View Analytics' => 'View Analytics',
    'Traffic Overview' => 'Traffic Overview',
    'Traffic & Devices' => 'Traffic & Devices',
    'Geographic' => 'Geographic',
    'Total Links' => 'Total Links',
    'Active Links' => 'Active Links',
    'Total Clicks' => 'Total Clicks',
    'total clicks' => 'total clicks',
    'Clicks' => 'Clicks',
    'Unique Visitors' => 'Unique Visitors',
    'Total Interactions' => 'Total Interactions',
    'Avg. Clicks/Day' => 'Avg. Clicks/Day',
    'Avg. Interactions/Day' => 'Avg. Interactions/Day',
    'Engagement Rate' => 'Engagement Rate',
    'Top {pluginName} (Top 20)' => 'Top {pluginName} (Top 20)',
    'Top SmartLinks' => 'Top SmartLinks',
    'Top Performing Links (Last 7 Days)' => 'Top Performing Links (Last 7 Days)',
    'Latest Interactions (Top 20)' => 'Latest Interactions (Top 20)',
    'Interactions (Last 20)' => 'Interactions (Last 20)',
    'No analytics data yet' => 'No analytics data yet',
    'Analytics will appear here once your smart link starts receiving clicks.' => 'Analytics will appear here once your smart link starts receiving clicks.',
    'Analytics will appear here once your {singularName} starts receiving clicks.' => 'Analytics will appear here once your {singularName} starts receiving clicks.',
    'Failed to load analytics data' => 'Failed to load analytics data',
    'Failed to load countries data' => 'Failed to load countries data',
    'No data for selected period' => 'No data for selected period',

    // =========================================================================
    // Analytics Dashboard — Traffic & Devices Tab
    // =========================================================================

    'Device Analytics' => 'Device Analytics',
    'Device Types' => 'Device Types',
    'Device Brands' => 'Device Brands',
    'Operating Systems' => 'Operating Systems',
    'Browser Usage' => 'Browser Usage',
    'Usage Patterns' => 'Usage Patterns',
    'Peak Usage Hours' => 'Peak Usage Hours',
    'Peak usage at {hour}' => 'Peak usage at {hour}',
    'Daily Clicks' => 'Daily Clicks',

    // =========================================================================
    // Analytics Dashboard — Geographic Tab
    // =========================================================================

    'Top Countries' => 'Top Countries',
    'Top Cities' => 'Top Cities',
    'Top Cities Worldwide' => 'Top Cities Worldwide',
    'No country data available' => 'No country data available',
    'No city data available' => 'No city data available',
    'Geographic detection is disabled.' => 'Geographic detection is disabled.',
    'Enable in Settings' => 'Enable in Settings',

    // =========================================================================
    // Analytics Data — Table Columns & Labels
    // =========================================================================

    'Date' => 'Date',
    'Time' => 'Time',
    'Device' => 'Device',
    'Location' => 'Location',
    'Country' => 'Country',
    'Countries' => 'Countries',
    'City' => 'City',
    'Site' => 'Site',
    'Source' => 'Source',
    'Type' => 'Type',
    'OS' => 'OS',
    'Operating System' => 'Operating System',
    'Browser' => 'Browser',
    'Interactions' => 'Interactions',
    'Latest Interactions' => 'Latest Interactions',
    'No interactions recorded yet' => 'No interactions recorded yet',
    'Last Interaction' => 'Last Interaction',
    'Last Interaction Type' => 'Last Interaction Type',
    'Last Click' => 'Last Click',
    'Device information not available' => 'Device information not available',
    'OS information not available' => 'OS information not available',
    'Name' => 'Name',
    'Percentage' => 'Percentage',

    // =========================================================================
    // Analytics Dashboard — JS strings (passed to JavaScript)
    // =========================================================================

    'No interaction data available for the selected filters.' => 'No interaction data available for the selected filters.',
    'No device data available for the selected filters.' => 'No device data available for the selected filters.',
    'No device brand data available for the selected filters.' => 'No device brand data available for the selected filters.',
    'No OS data available for the selected filters.' => 'No OS data available for the selected filters.',
    'No browser data available for the selected filters.' => 'No browser data available for the selected filters.',
    'No hourly data available for the selected filters.' => 'No hourly data available for the selected filters.',
    'Peak usage at' => 'Peak usage at',

    // =========================================================================
    // Interaction Types
    // =========================================================================

    'Direct' => 'Direct',
    'Direct Visits' => 'Direct Visits',
    'QR' => 'QR',
    'QR Scans' => 'QR Scans',
    'Button' => 'Button',
    'Landing' => 'Landing',

    // =========================================================================
    // Analytics Export — CSV/Excel Column Headers
    // =========================================================================

    'Date/Time' => 'Date/Time',
    'Status' => 'Status',
    'Smart Link URL' => 'Smart Link URL',
    'Referrer' => 'Referrer',
    'Device Type' => 'Device Type',
    'Device Brand' => 'Device Brand',
    'Device Model' => 'Device Model',
    'OS Version' => 'OS Version',
    'Browser Version' => 'Browser Version',
    'Language' => 'Language',
    'User Agent' => 'User Agent',

    // =========================================================================
    // Time Periods
    // =========================================================================

    'Today' => 'Today',
    'Yesterday' => 'Yesterday',
    'Last 7 days' => 'Last 7 days',
    'Last 30 days' => 'Last 30 days',
    'Last 90 days' => 'Last 90 days',
    'All time' => 'All time',
    'Date Range' => 'Date Range',

    // =========================================================================
    // Utilities
    // =========================================================================

    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.',
    'Active {pluginName}' => 'Active {pluginName}',
    'Links Status' => 'Links Status',
    'Total {pluginName}' => 'Total {pluginName}',
    'Performance' => 'Performance',
    'Total interactions tracked' => 'Total interactions tracked',
    'Redirects' => 'Redirects',
    'QR Codes' => 'QR Codes',
    'Devices' => 'Devices',
    'Cache Status' => 'Cache Status',
    'Total cached entries' => 'Total cached entries',
    'Active' => 'Active',
    'Pending' => 'Pending',
    'Expired' => 'Expired',
    'Disabled' => 'Disabled',
    'Navigation' => 'Navigation',
    'Access main plugin sections' => 'Access main plugin sections',
    'Manage {pluginName}' => 'Manage {pluginName}',
    'View Settings' => 'View Settings',
    'Cache Management' => 'Cache Management',
    'Clear cached data to force regeneration. Useful after changing QR code settings or when troubleshooting.' => 'Clear cached data to force regeneration. Useful after changing QR code settings or when troubleshooting.',
    'Clear QR Cache' => 'Clear QR Cache',
    'Clear Device Cache' => 'Clear Device Cache',
    'Clear All Caches' => 'Clear All Caches',
    'Analytics Data Management' => 'Analytics Data Management',
    'Permanently delete all analytics tracking data. This action cannot be undone!' => 'Permanently delete all analytics tracking data. This action cannot be undone!',
    'Clear All Analytics' => 'Clear All Analytics',
    'Are you sure you want to permanently delete ALL analytics data? This action cannot be undone!' => 'Are you sure you want to permanently delete ALL analytics data? This action cannot be undone!',
    'This will delete all click tracking data and reset all click counts. Are you absolutely sure?' => 'This will delete all click tracking data and reset all click counts. Are you absolutely sure?',
    'Failed to clear QR cache' => 'Failed to clear QR cache',
    'Failed to clear device cache' => 'Failed to clear device cache',
    'Failed to clear caches' => 'Failed to clear caches',
    'Failed to clear analytics' => 'Failed to clear analytics',

    // =========================================================================
    // Widgets — Analytics Summary
    // =========================================================================

    '{pluginName} - Analytics' => '{pluginName} - Analytics',
    'Top Performer' => 'Top Performer',
    'interactions' => 'interactions',
    'View full analytics' => 'View full analytics',
    'You don\'t have permission to view analytics.' => 'You don\'t have permission to view analytics.',
    'Analytics are disabled in plugin settings.' => 'Analytics are disabled in plugin settings.',

    // =========================================================================
    // Widgets — Top Links
    // =========================================================================

    '{pluginName} - Top Links' => '{pluginName} - Top Links',
    'Link' => 'Link',
    'Number of Links' => 'Number of Links',
    'How many top links to display (1-20)' => 'How many top links to display (1-20)',
    'View all {pluginName}' => 'View all {pluginName}',
    'No {pluginName} yet' => 'No {pluginName} yet',
    'Create your first {singularName} to see it here.' => 'Create your first {singularName} to see it here.',

    // =========================================================================
    // Public Templates — Redirect Page (redirect.twig)
    // =========================================================================

    'App Store' => 'App Store',
    'Google Play' => 'Google Play',
    'AppGallery' => 'AppGallery',
    'Amazon' => 'Amazon',
    'Windows Store' => 'Windows Store',
    'Mac App Store' => 'Mac App Store',
    'Continue to Website' => 'Continue to Website',

    // =========================================================================
    // Public Templates — QR Code Page (qr.twig)
    // =========================================================================

    'Scan with your phone\'s camera to download' => 'Scan with your phone\'s camera to download',

    // =========================================================================
    // Controller Messages — Flash Notices & Errors
    // =========================================================================

    // SmartlinksController
    'Smart link saved.' => 'Smart link saved.',
    'Couldn\'t save smart link.' => 'Couldn\'t save smart link.',
    'Error saving smart link: {error}' => 'Error saving smart link: {error}',
    'Could not save smart link.' => 'Could not save smart link.',
    'Smart link deleted.' => 'Smart link deleted.',
    'Couldn\'t delete smart link.' => 'Couldn\'t delete smart link.',
    'Smart link restored.' => 'Smart link restored.',
    'Couldn\'t restore smart link.' => 'Couldn\'t restore smart link.',
    'Smart link permanently deleted.' => 'Smart link permanently deleted.',
    'Couldn\'t delete smart link permanently.' => 'Couldn\'t delete smart link permanently.',
    'Smart link not found' => 'Smart link not found',
    'Cannot edit trashed smart links.' => 'Cannot edit trashed smart links.',
    'Failed to generate QR code.' => 'Failed to generate QR code.',
    // SettingsController
    'Settings saved.' => 'Settings saved.',
    'Couldn\'t save settings.' => 'Couldn\'t save settings.',
    'Field layout saved.' => 'Field layout saved.',
    'Couldn\'t save field layout.' => 'Couldn\'t save field layout.',
    'Analytics cleanup job has been queued. It will run in the background.' => 'Analytics cleanup job has been queued. It will run in the background.',
    'QR code cache cleared successfully.' => 'QR code cache cleared successfully.',
    'Cleared {count} QR code caches.' => 'Cleared {count} QR code caches.',
    'Device cache cleared successfully.' => 'Device cache cleared successfully.',
    'Cleared {count} device detection caches.' => 'Cleared {count} device detection caches.',
    'All caches cleared successfully.' => 'All caches cleared successfully.',
    'Cleared {count} cache entries.' => 'Cleared {count} cache entries.',
    'Cleared {count} analytics records and reset all click counts.' => 'Cleared {count} analytics records and reset all click counts.',
    'An unexpected error occurred.' => 'An unexpected error occurred.',
    // AnalyticsController
    'No analytics data to export.' => 'No analytics data to export.',
    // JS notices
    'Enter custom size (100-4096 pixels):' => 'Enter custom size (100-4096 pixels):',
    'Please enter a valid size between 100 and 4096 pixels' => 'Please enter a valid size between 100 and 4096 pixels',
    'Reset QR code settings to plugin defaults?' => 'Reset QR code settings to plugin defaults?',

    // =========================================================================
    // Job Messages
    // =========================================================================

    '{pluginName}: Cleaning up old analytics' => '{pluginName}: Cleaning up old analytics',
    'Deleting {count} old analytics records' => 'Deleting {count} old analytics records',
    'Deleted {deleted} of {total} records' => 'Deleted {deleted} of {total} records',

    // =========================================================================
    // Validation Messages
    // =========================================================================

    'Only letters, numbers, hyphens, and underscores are allowed.' => 'Only letters, numbers, hyphens, and underscores are allowed.',
    'Only letters, numbers, hyphens, underscores, and slashes are allowed.' => 'Only letters, numbers, hyphens, underscores, and slashes are allowed.',
    'Only lowercase letters, numbers, and underscores are allowed.' => 'Only lowercase letters, numbers, and underscores are allowed.',
    '{attribute} should only contain letters, numbers, underscores, and hyphens.' => '{attribute} should only contain letters, numbers, underscores, and hyphens.',
    'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}',
    'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr' => 'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr',
    'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"' => 'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"',
    'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}',
    'Smart link base URL must start with http:// or https://' => 'Smart link base URL must start with http:// or https://',
    'Smart link base URL cannot contain spaces.' => 'Smart link base URL cannot contain spaces.',
    'Unsupported token in smart link base URL. Supported tokens: {siteHandle}, {siteId}, {siteUid}.' => 'Unsupported token in smart link base URL. Supported tokens: {siteHandle}, {siteId}, {siteUid}.',

    // =========================================================================
    // Config Override Warnings
    // =========================================================================

    'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledIntegrations</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enabledIntegrations</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticTrackingEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>seomaticTrackingEvents</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticEventPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>seomaticEventPrefix</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheStorageMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>cacheStorageMethod</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrCodeCache</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>enableQrCodeCache</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>anonymizeIpAddress</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>anonymizeIpAddress</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectManagerEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>redirectManagerEvents</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>logLevel</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>logLevel</code> setting in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>smartlinkBaseUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'This is being overridden by the <code>smartlinkBaseUrl</code> setting in <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // General Interface
    // =========================================================================

    'Save Settings' => 'Save Settings',
    'Actions' => 'Actions',
    'Manage SmartLinks' => 'Manage SmartLinks',
    'Loading...' => 'Loading...',
    'Error' => 'Error',

    // =========================================================================
    // Behavior Settings — Select Options
    // =========================================================================

    'Browser preference' => 'Browser preference',
    'IP geolocation' => 'IP geolocation',
    'Both' => 'Both',

    // =========================================================================
    // General Settings — URL Tips (Redirect Manager integration)
    // =========================================================================

    'Changing will break existing URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard)' => 'Changing will break existing URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard)',
    'Changing will break existing QR URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard). Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns.' => 'Changing will break existing QR URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard). Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns.',
    'Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns. Checked for conflicts with ShortLink Manager.' => 'Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns. Checked for conflicts with ShortLink Manager.',

    // =========================================================================
    // QR Code Settings — Select Options
    // =========================================================================

    'Square' => 'Square',
    'Rounded' => 'Rounded',
    'Dots' => 'Dots',
    'Leaf' => 'Leaf',
    'Low (~7% correction)' => 'Low (~7% correction)',
    'Medium (~15% correction)' => 'Medium (~15% correction)',
    'Quartile (~25% correction)' => 'Quartile (~25% correction)',
    'High (~30% correction)' => 'High (~30% correction)',
    'Failed to generate preview' => 'Failed to generate preview',

    // =========================================================================
    // Smart Link Fields — Image Size Options
    // =========================================================================

    'Extra Large' => 'Extra Large',
    'Large' => 'Large',
    'Medium' => 'Medium',
    'Small' => 'Small',

    // =========================================================================
    // Smart Link Field Input — Tooltip
    // =========================================================================

    'Clicks:' => 'Clicks:',

    // =========================================================================
    // Cache Settings — Info Boxes & Durations
    // =========================================================================

    'Cache Location' => 'Cache Location',
    'Using Craft\'s configured Redis cache from <code>config/app.php</code>' => 'Using Craft\'s configured Redis cache from <code>config/app.php</code>',
    'Redis Not Configured' => 'Redis Not Configured',
    'To use Redis caching, install <code>yiisoft/yii2-redis</code> and configure it in <code>config/app.php</code>.' => 'To use Redis caching, install <code>yiisoft/yii2-redis</code> and configure it in <code>config/app.php</code>.',
    'How it works' => 'How it works',
    'Device detection parses user-agent strings to identify devices, browsers, and operating systems' => 'Device detection parses user-agent strings to identify devices, browsers, and operating systems',
    'Results are cached to avoid re-parsing the same user-agent repeatedly' => 'Results are cached to avoid re-parsing the same user-agent repeatedly',
    'Recommended to keep enabled for production sites' => 'Recommended to keep enabled for production sites',
    'Cache duration in seconds. Current:' => 'Cache duration in seconds. Current:',

    // =========================================================================
    // Time Unit Strings (for JS secondsToHuman)
    // =========================================================================

    '{count} second' => '{count} second',
    '{count} seconds' => '{count} seconds',
    '{count} minute' => '{count} minute',
    '{count} minutes' => '{count} minutes',
    '{count} hour' => '{count} hour',
    '{count} hours' => '{count} hours',
    '{count} day' => '{count} day',
    '{count} days' => '{count} days',

    // =========================================================================
    // Template Settings — Copy hints
    // =========================================================================

    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> to <code>templates/smartlink-manager/redirect.twig</code>' => 'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> to <code>templates/smartlink-manager/redirect.twig</code>',
    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> to <code>templates/smartlink-manager/qr.twig</code>' => 'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> to <code>templates/smartlink-manager/qr.twig</code>',

    // =========================================================================
    // Import/Export
    // =========================================================================

    'Manage import/export' => 'Manage import/export',
    'Import links' => 'Import links',
    'Export links' => 'Export links',
    'View import history' => 'View import history',
    'Clear import history' => 'Clear import history',
    'Export Smart Links' => 'Export Smart Links',
    'Export All Smart Links as CSV' => 'Export All Smart Links as CSV',
    'Import Smart Links' => 'Import Smart Links',
    'You do not have permission to export smart links.' => 'You do not have permission to export smart links.',
    'You do not have permission to import smart links.' => 'You do not have permission to import smart links.',
    'Download all your current smart links as a CSV file for backup or migration to another site.' => 'Download all your current smart links as a CSV file for backup or migration to another site.',
    'Import smart links from CSV. You\'ll map columns and preview before importing.' => 'Import smart links from CSV. You\'ll map columns and preview before importing.',
    'Select a CSV file to import smart links' => 'Select a CSV file to import smart links',
    'No smart links to export.' => 'No smart links to export.',
    'Map your CSV columns to smart link fields. Required fields must be mapped.' => 'Map your CSV columns to smart link fields. Required fields must be mapped.',
    'Valid Smart Links to Import' => 'Valid Smart Links to Import',
    'No valid smart links found to import.' => 'No valid smart links found to import.',
    'Import {count} Smart Links' => 'Import {count} Smart Links',
    'No Valid Smart Links to Import' => 'No Valid Smart Links to Import',
    'Click the button below to import {count} valid smart link(s).' => 'Click the button below to import {count} valid smart link(s).',
    'Import completed: {imported} smart links imported.' => 'Import completed: {imported} smart links imported.',
    'Import completed: {imported} imported, {failed} failed.' => 'Import completed: {imported} imported, {failed} failed.',
    'Import completed: {imported} {pluginName} imported.' => 'Import completed: {imported} {pluginName} imported.',
    'Import completed: {imported} {pluginName} imported, {failed} failed.' => 'Import completed: {imported} {pluginName} imported, {failed} failed.',
    'Failed to clear import history.' => 'Failed to clear import history.',
    'Slug must be mapped.' => 'Slug must be mapped.',
    'Slug (required)' => 'Slug (required)',
    'Fallback URL (required)' => 'Fallback URL (required)',
    'Image Asset ID' => 'Image Asset ID',
    'Image Size (xl/lg/md/sm)' => 'Image Size (xl/lg/md/sm)',
    'QR Enabled (1/0)' => 'QR Enabled (1/0)',
    'QR Size' => 'QR Size',
    'QR Color (#RRGGBB)' => 'QR Color (#RRGGBB)',
    'QR Background (#RRGGBB)' => 'QR Background (#RRGGBB)',
    'QR Eye Color (#RRGGBB)' => 'QR Eye Color (#RRGGBB)',
    'QR Format (png/svg)' => 'QR Format (png/svg)',
    'QR Logo Asset ID' => 'QR Logo Asset ID',
    'Hide Title (1/0)' => 'Hide Title (1/0)',
    'Language Detection (1/0)' => 'Language Detection (1/0)',
    'Metadata (JSON)' => 'Metadata (JSON)',

];
