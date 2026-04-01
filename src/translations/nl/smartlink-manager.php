<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

return [

    // =========================================================================
    // Plugin Meta
    // =========================================================================

    'SmartLink Manager' => 'SmartLink Manager',
    'Manage smart links, route users by device, and track engagement from one control panel workspace.' => 'Beheer uw smart links, stuur gebruikers door op basis van hun apparaat en volg betrokkenheid vanuit één werkruimte.',
    'Open SmartLink Manager' => 'SmartLink Manager openen',
    '{name} plugin loaded' => 'Plugin {name} geladen',
    '{displayName} caches' => '{displayName} caches',

    // =========================================================================
    // Element Names
    // =========================================================================

    'Smart Link' => 'Smart Link',
    'smart link' => 'smart link',
    'smart links' => 'smart links',
    'New smart link' => 'Nieuwe smart link',

    // =========================================================================
    // Permissions
    // =========================================================================

    'Manage {plural}' => '{plural} beheren',
    'Create {plural}' => '{plural} aanmaken',
    'Edit {plural}' => '{plural} bewerken',
    'Delete {plural}' => '{plural} verwijderen',
    'View analytics' => 'Analyses bekijken',
    'Export analytics' => 'Analyses exporteren',
    'Clear analytics' => 'Analyses wissen',
    'Clear cache' => 'Cache leegmaken',
    'View logs' => 'Logboeken bekijken',
    'View system logs' => 'Systeemlogboeken bekijken',
    'Download system logs' => 'Systeemlogboeken downloaden',
    'Manage settings' => 'Instellingen beheren',

    // =========================================================================
    // Navigation & Breadcrumbs
    // =========================================================================

    'Links' => 'Links',
    'Analytics' => 'Analyses',
    'Logs' => 'Logboeken',
    'Settings' => 'Instellingen',
    'General' => 'Algemeen',
    'QR Code' => 'QR Code',
    'Redirect' => 'Doorsturen',
    'Export' => 'Exporteren',
    'Advanced' => 'Geavanceerd',
    'Interface' => 'Interface',
    'Behavior' => 'Gedrag',
    'Integrations' => 'Integraties',
    'Cache' => 'Cache',
    'Field Layout' => 'Veldindeling',
    'Overview' => 'Overzicht',
    'Import/Export' => 'Import/Export',

    // =========================================================================
    // General Settings
    // =========================================================================

    'General Settings' => 'Algemene instellingen',
    'Plugin Name' => 'Pluginnaam',
    'The name of the plugin as it appears in the Control Panel menu' => 'De naam van de plugin zoals deze verschijnt in het menu van het configuratiescherm',
    'Plugin Settings' => 'Plugininstellingen',
    'Log Level' => 'Logniveau',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Kies welke typen berichten worden gelogd. Het debugniveau vereist dat devMode is ingeschakeld.',
    'Error (Critical errors only)' => 'Fout (alleen kritieke fouten)',
    'Warning (Errors and warnings)' => 'Waarschuwing (fouten en waarschuwingen)',
    'Info (General information)' => 'Info (algemene informatie)',
    'Debug (Detailed debugging)' => 'Debug (gedetailleerde foutopsporing)',
    'Logging Settings' => 'Loginstellingen',

    // Logs viewer (logging-library)
    'All Levels' => 'Alle niveaus',
    'Info' => 'Info',
    'Debug' => 'Debug',
    'Select File' => 'Bestand selecteren',
    'Select Date' => 'Datum selecteren',
    'All Sources' => 'Alle bronnen',
    'Search messages and context...' => 'Berichten en context doorzoeken...',
    'System Logs' => 'Systeemlogboeken',
    'System' => 'Systeem',
    'Current log level' => 'Huidig logniveau',
    'No log files found. Log files are created when plugin activities occur.' => 'Geen logbestanden gevonden. Logbestanden worden aangemaakt wanneer plugin-activiteiten plaatsvinden.',
    'No log entries found for the selected filters.' => 'Geen logvermeldingen gevonden voor de geselecteerde filters.',
    'No context data available.' => 'Geen contextgegevens beschikbaar.',
    'Level' => 'Niveau',
    'User' => 'Gebruiker',
    'Message' => 'Bericht',
    'entry' => 'vermelding',
    'entries' => 'vermeldingen',
    'Available Logs' => 'Beschikbare logboeken',
    'Current File' => 'Huidig bestand',
    'Download File' => 'Bestand downloaden',
    'Log Location' => 'Loglocatie',
    'Current Level' => 'Huidig niveau',
    'Retention' => 'Retentie',
    'days' => 'dagen',
    'Context' => 'Context',
    'Entries' => 'Vermeldingen',
    'file' => 'bestand',
    'files' => 'bestanden',

    // =========================================================================
    // Site Settings
    // =========================================================================

    'Site Settings' => 'Site-instellingen',
    'Enabled Sites' => 'Ingeschakelde sites',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'Selecteer voor welke sites {pluginName} moet worden ingeschakeld. Laat leeg om voor alle sites in te schakelen.',

    // =========================================================================
    // URL Settings
    // =========================================================================

    'URL Settings' => 'URL-instellingen',
    'Smart Link URL Prefix' => 'Smart Link URL-voorvoegsel',
    '{singularName} URL Prefix' => '{singularName} URL-voorvoegsel',
    'QR Code URL Prefix' => 'QR Code URL-voorvoegsel',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link)' => 'Het URL-voorvoegsel voor {pluginName} (bijv. \'go\' maakt /go/uw-link). Maak de routecache leeg na wijziging (php craft clear-caches/compiled-templates).',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link/view or \'go/qr\' creates /go/qr/your-link/view)' => 'Het URL-voorvoegsel voor QR Code-pagina\'s (bijv. \'qr\' maakt /qr/uw-link/view of \'go/qr\' maakt /go/qr/uw-link/view)',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'Maak de routecache leeg na deze wijziging (php craft clear-caches/compiled-templates).',
    'Smart Link Base URL' => 'Smart Link basis-URL',
    '{singularName} Base URL' => '{singularName} basis-URL',
    'Optional absolute URL used for generated smart links and QR URLs. Leave empty to use each site\'s base URL.' => 'Optionele absolute URL voor gegenereerde smart links en QR-URL\'s. Laat leeg om de basis-URL van elke site te gebruiken.',
    'Base URL for generated smart links and QR URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'Basis-URL voor gegenereerde smart links en QR-URL\'s. Voor multisite kunt u tokens gebruiken: {siteHandle}, {siteId}, {siteUid} (bijv. https://go.example.com/{siteHandle}). Laat leeg om de basis-URL van elke site te gebruiken.',
    'Base URL for {singularName} and QR code URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'Basis-URL voor {singularName} en QR Code-URL\'s. Voor multisite kunt u tokens gebruiken: {siteHandle}, {siteId}, {siteUid} (bijv. https://go.example.com/{siteHandle}). Laat leeg om de basis-URL van elke site te gebruiken.',
    'Changing the URL prefix will break all existing {pluginName}. Only change this before creating your first {singularName}.' => 'Het wijzigen van het URL-voorvoegsel zal alle bestaande {pluginName} kapotmaken. Wijzig dit alleen voordat u uw eerste {singularName} aanmaakt.',
    'Multisite detected: <code>Smart Link Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multisite gedetecteerd: <code>Smart Link Base URL</code> is ingesteld zonder sitetoken. Gegenereerde URL\'s kunnen naar slechts één site verwijzen. Gebruik een getokeniseerde URL zoals <code>https://go.example.com/{siteHandle}</code> om sitespecifieke routering te behouden.',
    'Multisite detected: <code>{singularName} Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multisite gedetecteerd: <code>{singularName} Base URL</code> is ingesteld zonder sitetoken. Gegenereerde URL\'s kunnen naar slechts één site verwijzen. Gebruik een getokeniseerde URL zoals <code>https://go.example.com/{siteHandle}</code> om sitespecifieke routering te behouden.',
    'Use URL Prefix' => 'URL-voorvoegsel gebruiken',
    'Enable to generate {singularName} URLs as /{prefix}/{slug}. Disable to generate root URLs as /{slug}.' => 'Inschakelen om {singularName} URL\'s te genereren als /{prefix}/{slug}. Uitschakelen om root-URL\'s te genereren als /{slug}.',
    'Both {smartName} and {shortName} are set to root URLs (no prefix) and share at least one host. Redirect routes can collide (e.g., <code>/slug</code>), and QR routes can also collide when both plugins use the same QR prefix (e.g., <code>/qr/slug</code>).' => 'Zowel {smartName} als {shortName} zijn ingesteld op root-URL\'s (geen voorvoegsel) en delen ten minste één host. Doorstuurroutes kunnen conflicteren (bijv. <code>/slug</code>), en QR-routes kunnen ook conflicteren als beide plugins hetzelfde QR-voorvoegsel gebruiken (bijv. <code>/qr/slug</code>).',
    'Both {smartName} and {shortName} are set to root URLs (no prefix). Host overlap could not be fully resolved from current settings/config, so redirect route collisions are possible. QR routes may also collide if both plugins use the same QR prefix.' => 'Zowel {smartName} als {shortName} zijn ingesteld op root-URL\'s (geen voorvoegsel). Hostoverlap kon niet volledig worden opgelost vanuit de huidige instellingen, dus conflicten in doorstuurroutes zijn mogelijk. QR-routes kunnen ook conflicteren als beide plugins hetzelfde QR-voorvoegsel gebruiken.',
    'URL Prefix is disabled. {singularName} URLs will be generated as root paths like <code>/your-link</code>.' => 'URL-voorvoegsel is uitgeschakeld. {singularName} URL\'s worden gegenereerd als rootpaden zoals <code>/uw-link</code>.',
    'This is being overridden by the <code>usePrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>usePrefix</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>slugPrefix</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrPrefix</code> in <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // Template Settings
    // =========================================================================

    'Template Settings' => 'Template-instellingen',
    'Redirect Template' => 'Doorstuur-template',
    'Custom Redirect Template' => 'Aangepaste doorstuur-template',
    'Template path in your templates/ folder. Leave empty to use the default path.' => 'Templatepad in uw map templates/. Laat leeg om het standaardpad te gebruiken.',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)' => 'Pad naar aangepaste template in uw map templates/ (bijv. smartlink-manager/redirect)',
    'QR Code Template' => 'QR Code-template',
    'Custom QR Code Template' => 'Aangepaste QR Code-template',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)' => 'Pad naar aangepaste template in uw map templates/ (bijv. smartlink-manager/qr)',
    'These templates must exist in your site\'s <code>templates/</code> folder. Copy the reference templates from <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> to <code>templates/smartlink-manager/</code> and customize as needed.' => 'Deze templates moeten bestaan in de map <code>templates/</code> van uw site. Kopieer de referentietemplates van <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> naar <code>templates/smartlink-manager/</code> en pas ze naar wens aan.',

    // =========================================================================
    // Asset Settings
    // =========================================================================

    'Asset Settings' => 'Bestandsinstellingen',
    'Image Volume' => 'Afbeeldingsvolume',
    '{singularName} Image Volume' => '{singularName} afbeeldingsvolume',
    'Which asset volume should be used for {singularName} images' => 'Welk bestandsvolume moet worden gebruikt voor {singularName}-afbeeldingen',
    'All asset volumes' => 'Alle bestandsvolumes',

    // =========================================================================
    // QR Code Settings — Appearance
    // =========================================================================

    'QR Code Settings' => 'QR Code-instellingen',
    'Appearance & Style' => 'Uiterlijk en stijl',
    'Enable QR Code' => 'QR Code inschakelen',
    'Default QR Code Size' => 'Standaard QR Code-grootte',
    'Default size in pixels for generated QR codes' => 'Standaardgrootte in pixels voor gegenereerde QR Codes',
    'QR Code Color' => 'QR Code-kleur',
    'Default QR Code Color' => 'Standaard QR Code-kleur',
    'Default QR Background Color' => 'Standaard achtergrondkleur QR Code',
    'Background Color' => 'Achtergrondkleur',
    'Default QR Code Format' => 'Standaard QR Code-indeling',
    'Default format for generated QR codes' => 'Standaardindeling voor gegenereerde QR Codes',
    'Override the default QR code format' => 'De standaard QR Code-indeling overschrijven',
    'Format' => 'Indeling',
    'Use Default ({format|upper})' => 'Standaard gebruiken ({format|upper})',
    'Color' => 'Kleur',
    'Background' => 'Achtergrond',
    'Eye Color' => 'Kleur van positiemarkeerders',
    'Color for position markers (leave empty to use main color)' => 'Kleur voor positiemarkeerders (laat leeg om de hoofdkleur te gebruiken)',
    'Size' => 'Grootte',

    // =========================================================================
    // QR Code Settings — Logo
    // =========================================================================

    'Logo Settings' => 'Logo-instellingen',
    'Enable QR Code Logo' => 'QR Code-logo inschakelen',
    'Enable Logo Overlay' => 'Logo-overlay inschakelen',
    'Add a logo in the center of QR codes' => 'Een logo toevoegen in het midden van QR Codes',
    'Logo Volume' => 'Logovolume',
    'Logo Asset Volume' => 'Logo-bestandsvolume',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'Welk bestandsvolume QR Code-logo\'s bevat. Sla instellingen op na wijziging om de logoselectie hieronder bij te werken.',
    'Default Logo' => 'Standaardlogo',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'Standaardlogo voor QR Codes (kan per smart link worden overschreven)',
    'Default logo is required when logo overlay is enabled.' => 'Standaardlogo is vereist wanneer logo-overlay is ingeschakeld.',
    'Logo Size (%)' => 'Logogrootte (%)',
    'Logo Size' => 'Logogrootte',
    'Logo size as percentage of QR code (10-30%)' => 'Logogrootte als percentage van de QR Code (10–30%)',
    'Logo' => 'Logo',
    'Override the default QR code logo' => 'Het standaard QR Code-logo overschrijven',
    'Using default logo from settings (click to override)' => 'Standaardlogo uit instellingen gebruiken (klik om te overschrijven)',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'Logo-overlay werkt alleen met PNG-indeling. SVG-indeling ondersteunt geen logo\'s.',
    'Logo requires PNG format' => 'Logo vereist PNG-indeling',
    'Please save settings to apply the volume change to the logo selection field.' => 'Sla de instellingen op om de volumewijziging toe te passen op het logoselectieveld.',
    'Please save to apply the volume change' => 'Sla op om de volumewijziging toe te passen',

    // =========================================================================
    // QR Code Settings — Technical
    // =========================================================================

    'Technical Options' => 'Technische opties',
    'Error Correction Level' => 'Foutcorrectieniveau',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'Hogere niveaus werken beter als de QR Code beschadigd is, maar creëren dichtere patronen',
    'QR Code Margin' => 'QR Code-marge',
    'Margin Size' => 'Margegrootte',
    'White space around QR code (0-10 modules)' => 'Witruimte rondom QR Code (0–10 modules)',
    'Module Style' => 'Modulestijl',
    'Shape of the QR code modules' => 'Vorm van de QR Code-modules',
    'Eye Style' => 'Stijl van positiemarkeerders',
    'Shape of the position markers (corners)' => 'Vorm van de positiemarkeerders (hoeken)',

    // =========================================================================
    // QR Code Settings — Downloads
    // =========================================================================

    'Download Settings' => 'Download-instellingen',
    'Enable QR Code Downloads' => 'QR Code-downloads inschakelen',
    'Allow users to download QR codes' => 'Gebruikers toestaan QR Codes te downloaden',
    'Download Filename Pattern' => 'Patroon voor downloadbestandsnaam',
    'Available variables: {slug}, {size}, {format}' => 'Beschikbare variabelen: {slug}, {size}, {format}',
    'Download QR Code' => 'QR Code downloaden',
    'Small (256px)' => 'Klein (256px)',
    'Medium (512px)' => 'Middel (512px)',
    'Large (1024px)' => 'Groot (1024px)',
    'Extra Large (2048px)' => 'Extra groot (2048px)',
    'Custom Size...' => 'Aangepaste grootte...',

    // =========================================================================
    // QR Code Settings — Actions & Preview
    // =========================================================================

    'QR Code Actions' => 'QR Code-acties',
    'View QR Code' => 'QR Code bekijken',
    'QR Code Image' => 'QR Code-afbeelding',
    'QR Code Page' => 'QR Code-pagina',
    'Reset to Defaults' => 'Terugzetten naar standaard',
    'Live Preview' => 'Live voorbeeld',
    'Preview' => 'Voorbeeld',
    'Click to view QR code image' => 'Klik om de QR Code-afbeelding te bekijken',
    'Click to view QR code page' => 'Klik om de QR Code-pagina te bekijken',
    'Toggle preview' => 'Voorbeeld in-/uitschakelen',
    'QR code settings reset to defaults' => 'QR Code-instellingen teruggezet naar standaard',
    'Performance & Caching' => 'Prestaties en caching',
    'Configure QR code caching to improve performance and reduce server load.' => 'Configureer QR Code-caching om de prestaties te verbeteren en de serverbelasting te verminderen.',
    'Go to Cache Settings' => 'Naar cache-instellingen',

    // =========================================================================
    // Behavior Settings
    // =========================================================================

    'Behavior Settings' => 'Gedragsinstellingen',
    'Redirect Behavior' => 'Doorstuurgedrag',
    '404 Redirect URL' => '404-doorstuur-URL',
    'Where to redirect when a {singularName} is not found or disabled' => 'Waarheen doorsturen wanneer een {singularName} niet gevonden of uitgeschakeld is',
    'Can be a relative path (/) or full URL (https://example.com)' => 'Kan een relatief pad (/) of een volledige URL (https://example.com) zijn',

    // =========================================================================
    // Analytics Settings
    // =========================================================================

    'Analytics Settings' => 'Analyseinstellingen',
    'Enable Analytics' => 'Analyses inschakelen',
    'Track Analytics' => 'Analyses bijhouden',
    'Track clicks and visitor data for {pluginName}' => 'Klikken en bezoekersgegevens bijhouden voor {pluginName}',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'Wanneer ingeschakeld, zal {pluginName} bezoekersinacties, apparaattypen, geografische gegevens en andere analyse-informatie bijhouden.',
    'Are you sure you want to disable analytics tracking for this {singularName}? This {singularName} will no longer collect visitor data and interactions.' => 'Weet u zeker dat u het bijhouden van analyses voor deze {singularName} wilt uitschakelen? Deze {singularName} verzamelt dan geen bezoekersgegevens en -interacties meer.',

    // =========================================================================
    // Analytics Settings — IP Privacy
    // =========================================================================

    'IP Address Privacy' => 'IP-adresprivacy',
    'Anonymize IP Addresses' => 'IP-adressen anonimiseren',
    'Mask IP addresses before storage for maximum privacy. <strong>IPv4</strong>: masks last octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: masks last 80 bits. <strong>Trade-off</strong>: Reduces unique visitor accuracy (users on same subnet counted as one visitor). Geo-location still works normally.' => 'Maskeer IP-adressen voor opslag voor maximale privacy. <strong>IPv4</strong>: maskeert het laatste octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: maskeert de laatste 80 bits. <strong>Afweging</strong>: vermindert de nauwkeurigheid van unieke bezoekers (gebruikers op hetzelfde subnet tellen als één bezoeker). Geolocatie werkt nog normaal.',
    'Privacy Levels' => 'Privacyniveaus',
    'Enabled' => 'Ingeschakeld',
    'default' => 'standaard',
    'Full IP hashed with salt (accurate unique visitors)' => 'Volledig IP gehasht met salt (nauwkeurige unieke bezoekers)',
    'Subnet masked + hashed with salt (maximum privacy, less accurate)' => 'Subnet gemaskeerd + gehasht met salt (maximale privacy, minder nauwkeurig)',

    // =========================================================================
    // Analytics Settings — Retention & Cleanup
    // =========================================================================

    'Analytics Retention (days)' => 'Analysebewaring (dagen)',
    'Analytics Retention' => 'Analysebewaring',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'Hoeveel dagen analysegegevens bewaren (0 voor onbeperkt, max. 3650)',
    'Data Retention' => 'Gegevensbewaring',
    'Analytics Cleanup' => 'Analyses opruimen',
    'Analytics data older than {days} days will be automatically cleaned up daily.' => 'Analysegegevens ouder dan {days} dagen worden dagelijks automatisch opgeruimd.',
    'Clean Up Now' => 'Nu opruimen',
    'Are you sure you want to clean up old analytics data now?' => 'Weet u zeker dat u de oude analysegegevens nu wilt opruimen?',
    'Unlimited Retention Warning' => 'Waarschuwing voor onbeperkte bewaring',
    'Warning' => 'Waarschuwing',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'Analysegegevens worden voor onbepaalde tijd bewaard. Dit kan resulteren in een grote databaseomvang, tragere prestaties en hogere opslagkosten. Overweeg een bewaartermijn in te stellen (aanbevolen: 90–365 dagen) voor productiesites.',

    // =========================================================================
    // Geo Provider Settings (from base _partials/geo-settings, uses |t(pluginHandle))
    // =========================================================================

    'Geographic Detection' => 'Geografische detectie',
    'Geographic Analytics' => 'Geografische analyses',
    'Geographic Distribution' => 'Geografische verdeling',
    'Enable Geographic Detection' => 'Geografische detectie inschakelen',
    'Detect user location for analytics' => 'Gebruikerslocatie detecteren voor analyses',
    'View Geographic Details' => 'Geografische details bekijken',
    'Loading geographic data...' => 'Geografische gegevens laden...',

    // Geo provider partial (lindemannrock-base/_partials/geo-settings)
    'Geo Provider' => 'Geo-provider',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Selecteer de geo-IP-opzoekprovider. HTTPS-providers aanbevolen voor privacy.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratis, HTTPS betaald)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/dag gratis)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/maand gratis)',
    'API Key' => 'API-sleutel',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Optioneel. Vereist voor betaalde niveaus (schakelt HTTPS in voor ip-api.com Pro).',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'Het gratis niveau van ip-api.com gebruikt HTTP. IP-adressen worden onversleuteld verzonden. Voeg een API-sleutel toe voor HTTPS (Pro-niveau) of schakel over naar ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: gratis HTTP-niveau (45 verzoeken/min). Voeg API-sleutel toe voor HTTPS (Pro-niveau, $13/maand). IP-adressen worden onversleuteld verzonden zonder API-sleutel.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS met 1.000 gratis verzoeken/dag. API-sleutel optioneel (verhoogt snelheidslimieten).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS met 50.000 gratis verzoeken/maand. API-sleutel optioneel (verhoogt snelheidslimieten).',

    // IP salt error banner (from base partial)
    'error' => 'fout',
    'Configuration Required' => 'Configuratie vereist',
    'IP hash salt is missing.' => 'IP-hash-salt ontbreekt.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'Analyse-tracking vereist een veilige salt voor privacybescherming.',
    'Run one of these commands in your terminal:' => 'Voer een van deze opdrachten uit in uw terminal:',
    'Standard:' => 'Standaard:',
    'COPY' => 'KOPIËREN',
    'DDEV:' => 'DDEV:',
    'This will automatically add' => 'Dit voegt automatisch toe',
    'to your' => 'aan uw',
    'file.' => 'bestand.',
    'Warning:' => 'Waarschuwing:',
    'Copy the same salt to staging and production environments.' => 'Kopieer dezelfde salt naar staging- en productieomgevingen.',
    'COPIED!' => 'GEKOPIEERD!',
    'Failed to copy to clipboard' => 'Kopiëren naar klembord mislukt',

    // =========================================================================
    // Device Detection Settings
    // =========================================================================

    'Cache Device Detection' => 'Apparaatdetectie cachen',
    'Cache device detection results for better performance' => 'Resultaten van apparaatdetectie cachen voor betere prestaties',
    'Device Detection Cache Duration (seconds)' => 'Cacheduur apparaatdetectie (seconden)',

    // =========================================================================
    // Language Detection Settings
    // =========================================================================

    'Language Detection Method' => 'Taaldetectiemethode',
    'How to detect user language preference' => 'Hoe de taalvoorkeur van de gebruiker te detecteren',
    'Language Detection' => 'Taaldetectie',
    'Enable automatic language detection to redirect users based on their browser or location' => 'Automatische taaldetectie inschakelen om gebruikers door te sturen op basis van hun browser of locatie',

    // =========================================================================
    // Cache Settings
    // =========================================================================

    'Cache Settings' => 'Cache-instellingen',
    'Cache Storage Settings' => 'Instellingen voor cacheopslag',
    'Cache Storage Method' => 'Methode voor cacheopslag',
    'How to store cache data. Use Redis/Database for load-balanced or multi-server environments.' => 'Hoe cachegegevens op te slaan. Gebruik Redis/Database voor taakverdeling of omgevingen met meerdere servers.',
    'File System (default, single server)' => 'Bestandssysteem (standaard, één server)',
    'Redis/Database (load-balanced, multi-server, cloud hosting)' => 'Redis/Database (taakverdeling, meerdere servers, cloudhosting)',
    'QR Code Caching' => 'QR Code-caching',
    'Enable QR Code Cache' => 'QR Code-cache inschakelen',
    'Cache generated QR codes for better performance' => 'Gegenereerde QR Codes cachen voor betere prestaties',
    'QR Code Cache Duration (seconds)' => 'QR Code-cacheduur (seconden)',
    'QR Code Cache Duration' => 'QR Code-cacheduur',
    'How long to cache generated QR codes (in seconds)' => 'Hoe lang gegenereerde QR Codes te cachen (in seconden)',
    'Cache duration in seconds' => 'Cacheduur in seconden',
    'Min: 60 (1 minute), Max: 604800 (7 days)' => 'Min: 60 (1 minuut), Max: 604800 (7 dagen)',
    'Caching' => 'Caching',
    'Device Detection Caching' => 'Apparaatdetectiecaching',
    'Device Detection Cache Duration' => 'Cacheduur apparaatdetectie',
    'Device detection caching is only available when Analytics is enabled. Go to' => 'Apparaatdetectiecaching is alleen beschikbaar wanneer analyses zijn ingeschakeld. Ga naar',
    'to enable analytics.' => 'om analyses in te schakelen.',

    // =========================================================================
    // Export Settings
    // =========================================================================

    'Export Settings' => 'Exportinstellingen',
    'Analytics Export Options' => 'Opties voor analyse-export',
    'Include Disabled Links in Export' => 'Uitgeschakelde links opnemen in export',
    'Include Disabled {pluginName} in Export' => 'Uitgeschakelde {pluginName} opnemen in export',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'Wanneer ingeschakeld, bevatten analyse-exports gegevens van uitgeschakelde {pluginName}',
    'Include Expired Links in Export' => 'Verlopen links opnemen in export',
    'Include Expired {pluginName} in Export' => 'Verlopen {pluginName} opnemen in export',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'Wanneer ingeschakeld, bevatten analyse-exports gegevens van verlopen {pluginName}',
    'Export as CSV' => 'Exporteren als CSV',

    // =========================================================================
    // Interface Settings
    // =========================================================================

    'Interface Settings' => 'Interface-instellingen',
    'Items Per Page' => 'Items per pagina',
    'Number of {pluginName} to show per page' => 'Aantal {pluginName} per pagina weergeven',
    'Allow Multiple' => 'Meerdere toestaan',
    'Whether to allow multiple {pluginName} to be selected' => 'Of meerdere {pluginName} kunnen worden geselecteerd',
    'The maximum number of {pluginName} that can be selected.' => 'Het maximale aantal {pluginName} dat kan worden geselecteerd.',
    'Which sources should be available to select {pluginName} from?' => 'Welke bronnen moeten beschikbaar zijn om {pluginName} uit te selecteren?',

    // =========================================================================
    // Integration Settings
    // =========================================================================

    'Third-Party Integrations' => 'Integraties van derden',
    'Integrations Settings' => 'Integratie-instellingen',
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'Integreer {pluginName} met analyse- en trackingdiensten van derden om klikgebeurtenissen naar Google Tag Manager, Google Analytics en andere platforms te sturen.',
    '{pluginName} Integration' => '{pluginName}-integratie',
    'Installed & Active' => 'Geïnstalleerd en actief',
    'Installed but Disabled' => 'Geïnstalleerd maar uitgeschakeld',
    'Not Installed' => 'Niet geïnstalleerd',
    'Install Plugin' => 'Plugin installeren',
    'Push {smartLinksName} click events to Google Tag Manager and analytics platforms for tracking redirects, button clicks, and QR code scans.' => 'Stuur {smartLinksName} klikgebeurtenissen naar Google Tag Manager en analyseplatforms voor het bijhouden van doorstuuracties, knopclikken en QR Code-scans.',
    'Active Tracking Scripts' => 'Actieve trackingscripts',
    'Scripts receiving {pluginName} events' => 'Scripts die {pluginName}-gebeurtenissen ontvangen',
    'Note' => 'Opmerking',
    'No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}.' => 'Er zijn momenteel geen trackingscripts geconfigureerd in {pluginName}. Gebeurtenissen worden in de wachtrij gezet maar niet verzonden totdat u GTM of Google Analytics in {pluginName} configureert.',
    'Configuration' => 'Configuratie',
    'Tracking Events' => 'Trackinggebeurtenissen',
    'Select which events to send to {pluginName}' => 'Selecteer welke gebeurtenissen naar {pluginName} worden verzonden',
    'Auto-Redirects' => 'Automatische doorstuuracties',
    'Mobile users automatically redirected' => 'Mobiele gebruikers automatisch doorgestuurd',
    'Button Clicks' => 'Knopclikken',
    'Manual platform selection on landing page' => 'Handmatige platformselectie op de landingspagina',
    'QR Code Scans' => 'QR Code-scans',
    'QR code accessed via ?src=qr parameter' => 'QR Code benaderd via de parameter ?src=qr',
    'Event Prefix' => 'Gebeurtenisprefix',
    'Prefix for event names (e.g., \'smart_links_redirect\')' => 'Prefix voor gebeurtenisnamen (bijv. \'smart_links_redirect\')',
    'Event Data Structure' => 'Gegevensstructuur van de gebeurtenis',
    'Click to view the data layer event format' => 'Klik om de indeling van de datalaaggebeurtenis te bekijken',
    'How Events Are Sent' => 'Hoe gebeurtenissen worden verzonden',
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} stuurt gebeurtenissen alleen naar GTM of GA4 dataLayer',
    'Only Google Tag Manager and Google Analytics 4 support the dataLayer format in SEOmatic' => 'Alleen Google Tag Manager en Google Analytics 4 ondersteunen de dataLayer-indeling in SEOmatic',
    'Use GTM to forward to other platforms' => 'Gebruik GTM om door te sturen naar andere platforms',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'Configureer GTM-triggers en -tags om {pluginName}-gebeurtenissen door te sturen naar Facebook Pixel, LinkedIn, HubSpot, enz.',
    'Events are only sent when analytics tracking is enabled both globally and per-link' => 'Gebeurtenissen worden alleen verzonden wanneer analyse-tracking globaal en per link is ingeschakeld',
    'Architecture' => 'Architectuur',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'Stuur {pluginName}-gebeurtenissen naar de Google Tag Manager-datalaag van SEOmatic voor tracking in GTM en Google Analytics.',
    'Select which {pluginName} events to send to SEOmatic' => 'Selecteer welke {pluginName}-gebeurtenissen naar SEOmatic worden verzonden',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'Fathom, Matomo en Plausible worden hierboven weergegeven maar ontvangen geen gebeurtenissen rechtstreeks van {pluginName}',
    // Redirect Manager Integration
    'Create permanent redirect records when {pluginName} slugs change. Provides centralized redirect management and analytics tracking.' => 'Maak permanente doorstuurrecords aan wanneer {pluginName}-slugs wijzigen. Biedt gecentraliseerd doorstuurbeleid en analyse-tracking.',
    'Creates permanent redirects when {pluginName} slugs change or links are deleted' => 'Maakt permanente doorstuuracties aan wanneer {pluginName}-slugs wijzigen of links worden verwijderd',
    'Automatic Redirect Creation' => 'Automatisch doorstuuracties aanmaken',
    'Select which events should create permanent redirects in {pluginName}' => 'Selecteer welke gebeurtenissen permanente doorstuuracties moeten aanmaken in {pluginName}',
    'Slug Changes' => 'Slugwijzigingen',
    'Change slug from <code>promo-2024</code> to <code>promo-2025</code> → Creates <code>/go/promo-2024</code> → <code>/go/promo-2025</code>' => 'Wijzig slug van <code>promo-2024</code> naar <code>promo-2025</code> → Maakt <code>/go/promo-2024</code> → <code>/go/promo-2025</code> aan',
    'Benefits of This Integration' => 'Voordelen van deze integratie',
    'Centralized Management' => 'Gecentraliseerd beheer',
    'View and manage all redirects ({pluginName} + regular pages) in one place' => 'Bekijk en beheer alle doorstuuracties ({pluginName} + gewone pagina\'s) op één plek',
    'Analytics Tracking' => 'Analyse-tracking',
    'See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries' => 'Zie hoeveel mensen proberen toegang te krijgen tot verwijderde of gewijzigde {pluginName}, hun apparaten, browsers en landen',
    'Persistent Redirects' => 'Permanente doorstuuracties',
    'Redirects persist even if {pluginName} is deleted, preventing broken links permanently' => 'Doorstuuracties blijven bestaan ook als {pluginName} wordt verwijderd, waardoor verbroken links permanent worden voorkomen',
    'Source Tracking' => 'Brontracking',
    '{rmPluginName} shows which plugin created each redirect for better organization' => '{rmPluginName} toont welke plugin elke doorstuuractie heeft aangemaakt voor betere organisatie',
    'Enabled Integrations' => 'Ingeschakelde integraties',
    // SmartLinkType (Link field integration)
    '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.' => '{pluginName} is niet ingeschakeld voor site "{site}". Schakel het in via de plugininstellingen om {pluginNameLower} hier te gebruiken.',
    'Invalid {pluginName} format.' => 'Ongeldige {pluginName}-indeling.',
    '{pluginName} not found.' => '{pluginName} niet gevonden.',

    // =========================================================================
    // Smart Link Fields (edit page)
    // =========================================================================

    'Title' => 'Titel',
    'The title of this {singularName}' => 'De titel van deze {singularName}',
    'Description' => 'Beschrijving',
    'A brief description of this {singularName}' => 'Een korte beschrijving van deze {singularName}',
    'Icon' => 'Pictogram',
    'Icon identifier or URL for this {singularName}' => 'Pictogramidentificatie of URL voor deze {singularName}',
    'Image' => 'Afbeelding',
    'Select an image for this {singularName}' => 'Selecteer een afbeelding voor deze {singularName}',
    'Image Size' => 'Afbeeldingsgrootte',
    'Select the size for the {singularName} image' => 'Selecteer de grootte voor de {singularName}-afbeelding',
    'Hide Title on Landing Pages' => 'Titel verbergen op landingspagina\'s',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'De {singularName}-titel verbergen op zowel doorstuur- als QR Code-landingspagina\'s',
    'Display Settings' => 'Weergave-instellingen',
    'Advanced Settings' => 'Geavanceerde instellingen',
    'Destination URL' => 'Doel-URL',
    'Last Destination URL' => 'Laatste doel-URL',
    'Fallback URL' => 'Fallback-URL',
    'The URL to redirect to when no platform-specific URL is available' => 'De URL waarnaar wordt doorgestuurd wanneer er geen platformspecifieke URL beschikbaar is',
    'iOS URL' => 'iOS-URL',
    'App Store URL for iOS devices' => 'App Store-URL voor iOS-apparaten',
    'Android URL' => 'Android-URL',
    'Google Play Store URL for Android devices' => 'Google Play Store-URL voor Android-apparaten',
    'Huawei URL' => 'Huawei-URL',
    'AppGallery URL for Huawei devices' => 'AppGallery-URL voor Huawei-apparaten',
    'Amazon URL' => 'Amazon-URL',
    'Amazon Appstore URL' => 'Amazon Appstore-URL',
    'Windows URL' => 'Windows-URL',
    'Microsoft Store URL for Windows devices' => 'Microsoft Store-URL voor Windows-apparaten',
    'Mac URL' => 'Mac-URL',
    'Mac App Store URL' => 'Mac App Store-URL',
    'App Store URLs' => 'App Store-URL\'s',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'Voer de winkel-URL\'s in voor elk platform. Het systeem stuurt gebruikers automatisch door naar de juiste winkel op basis van hun apparaat.',
    '{pluginName} URL' => '{pluginName}-URL',
    'URL copied to clipboard' => 'URL gekopieerd naar klembord',
    'New {singularName}' => 'Nieuwe {singularName}',

    // =========================================================================
    // Field Layout
    // =========================================================================

    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'Voeg aangepaste velden toe aan {singularName}-elementen. Alle velden die u hier toevoegt, verschijnen in het bewerkingsscherm van {singularName}.',
    'No field layout available.' => 'Geen veldindeling beschikbaar.',

    // =========================================================================
    // Smart Link Element — Index & Actions
    // =========================================================================

    'Slug' => 'Slug',
    'Redirect Page' => 'Doorstuurpagina',
    'All {pluginName}' => 'Alle {pluginName}',
    'New {name}' => 'Nieuwe {name}',
    'Are you sure you want to delete the selected smart links?' => 'Weet u zeker dat u de geselecteerde smart links wilt verwijderen?',
    'Smart links deleted.' => 'Smart links verwijderd.',
    'Smart links restored.' => 'Smart links hersteld.',
    'Some smart links restored.' => 'Enkele smart links hersteld.',
    'Smart links not restored.' => 'Smart links niet hersteld.',
    'Add a smart link' => 'Een smart link toevoegen',
    'No smart links selected' => 'Geen smart links geselecteerd',
    'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.' => 'U kunt maximaal {limit} {limit, plural, =1{smart link} other{smart links}} selecteren.',
    'Create a new smart link' => 'Een nieuwe smart link aanmaken',

    // =========================================================================
    // Analytics Dashboard — Overview Tab
    // =========================================================================

    'View Analytics' => 'Analyses bekijken',
    'Traffic Overview' => 'Verkeersoverzicht',
    'Traffic & Devices' => 'Verkeer en apparaten',
    'Geographic' => 'Geografisch',
    'Total Links' => 'Totaal links',
    'Active Links' => 'Actieve links',
    'Total Clicks' => 'Totaal klikken',
    'total clicks' => 'totaal klikken',
    'Clicks' => 'Klikken',
    'Unique Visitors' => 'Unieke bezoekers',
    'Total Interactions' => 'Totaal interacties',
    'Avg. Clicks/Day' => 'Gem. klikken/dag',
    'Avg. Interactions/Day' => 'Gem. interacties/dag',
    'Engagement Rate' => 'Betrokkenheidspercentage',
    'Top {pluginName} (Top 20)' => 'Top {pluginName} (Top 20)',
    'Latest Interactions (Top 20)' => 'Laatste interacties (Top 20)',
    'Interactions (Last 20)' => 'Interacties (laatste 20)',
    'No analytics data yet' => 'Nog geen analysegegevens',
    'Analytics will appear here once your {singularName} starts receiving clicks.' => 'Analyses verschijnen hier zodra uw {singularName} klikken begint te ontvangen.',
    'Failed to load analytics data' => 'Laden van analysegegevens mislukt',
    'Failed to load countries data' => 'Laden van landengegevens mislukt',
    'No data for selected period' => 'Geen gegevens voor de geselecteerde periode',

    // =========================================================================
    // Analytics Dashboard — Traffic & Devices Tab
    // =========================================================================

    'Device Analytics' => 'Apparaatanalyses',
    'Device Types' => 'Apparaattypen',
    'Device Brands' => 'Apparaatmerken',
    'Operating Systems' => 'Besturingssystemen',
    'Browser Usage' => 'Browsergebruik',
    'Usage Patterns' => 'Gebruikspatronen',
    'Peak Usage Hours' => 'Piekuren',
    'Peak usage at {hour}' => 'Piekgebruik om {hour}',
    'Daily Clicks' => 'Dagelijkse klikken',

    // =========================================================================
    // Analytics Dashboard — Geographic Tab
    // =========================================================================

    'Top Countries' => 'Toplanden',
    'Top Cities' => 'Topsteden',
    'Top Cities Worldwide' => 'Topsteden wereldwijd',
    'No country data available' => 'Geen landengegevens beschikbaar',
    'No city data available' => 'Geen stadsgegevens beschikbaar',
    'Geographic detection is disabled.' => 'Geografische detectie is uitgeschakeld.',
    'Enable in Settings' => 'Inschakelen via instellingen',

    // =========================================================================
    // Analytics Data — Table Columns & Labels
    // =========================================================================

    'Date' => 'Datum',
    'Time' => 'Tijd',
    'Device' => 'Apparaat',
    'Location' => 'Locatie',
    'Country' => 'Land',
    'Countries' => 'Landen',
    'City' => 'Stad',
    'Site' => 'Site',
    'Source' => 'Bron',
    'Type' => 'Type',
    'OS' => 'OS',
    'Operating System' => 'Besturingssysteem',
    'Browser' => 'Browser',
    'Interactions' => 'Interacties',
    'Latest Interactions' => 'Laatste interacties',
    'No interactions recorded yet' => 'Nog geen interacties geregistreerd',
    'Last Interaction' => 'Laatste interactie',
    'Last Interaction Type' => 'Type laatste interactie',
    'Last Click' => 'Laatste klik',
    'Device information not available' => 'Apparaatinformatie niet beschikbaar',
    'OS information not available' => 'OS-informatie niet beschikbaar',
    'Name' => 'Naam',
    'Percentage' => 'Percentage',

    // =========================================================================
    // Analytics Dashboard — JS strings (passed to JavaScript)
    // =========================================================================

    'No interaction data available for the selected filters.' => 'Geen interactiegegevens beschikbaar voor de geselecteerde filters.',
    'No device data available for the selected filters.' => 'Geen apparaatgegevens beschikbaar voor de geselecteerde filters.',
    'No device brand data available for the selected filters.' => 'Geen apparaatmerkgegevens beschikbaar voor de geselecteerde filters.',
    'No OS data available for the selected filters.' => 'Geen OS-gegevens beschikbaar voor de geselecteerde filters.',
    'No browser data available for the selected filters.' => 'Geen browsergegevens beschikbaar voor de geselecteerde filters.',
    'No hourly data available for the selected filters.' => 'Geen uurgegevens beschikbaar voor de geselecteerde filters.',
    'Peak usage at' => 'Piekgebruik om',

    // =========================================================================
    // Interaction Types
    // =========================================================================

    'Direct' => 'Direct',
    'Direct Visits' => 'Directe bezoeken',
    'QR' => 'QR',
    'QR Scans' => 'QR-scans',
    'Button' => 'Knop',
    'Landing' => 'Landing',

    // =========================================================================
    // Analytics Export — CSV/Excel Column Headers
    // =========================================================================

    'Date/Time' => 'Datum/Tijd',
    'Status' => 'Status',
    'Smart Link URL' => 'Smart Link-URL',
    'Referrer' => 'Referrer',
    'Device Type' => 'Apparaattype',
    'Device Brand' => 'Apparaatmerk',
    'Device Model' => 'Apparaatmodel',
    'OS Version' => 'OS-versie',
    'Browser Version' => 'Browserversie',
    'Language' => 'Taal',
    'User Agent' => 'User Agent',

    // =========================================================================
    // Time Periods
    // =========================================================================

    'Today' => 'Vandaag',
    'Yesterday' => 'Gisteren',
    'Last 7 days' => 'Afgelopen 7 dagen',
    'Last 30 days' => 'Afgelopen 30 dagen',
    'Last 90 days' => 'Afgelopen 90 dagen',
    'All time' => 'Alle tijd',
    'Date Range' => 'Datumreeks',

    // =========================================================================
    // Utilities
    // =========================================================================

    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'Bewaak de linkprestaties, volg analyses bij en beheer de cache voor uw {singularName}-doorstuuracties en QR Codes.',
    'Active {pluginName}' => 'Actieve {pluginName}',
    'Links Status' => 'Linkstatus',
    'Total {pluginName}' => 'Totaal {pluginName}',
    'Performance' => 'Prestaties',
    'Total interactions tracked' => 'Totaal bijgehouden interacties',
    'Redirects' => 'Doorstuuracties',
    'QR Codes' => 'QR Codes',
    'Devices' => 'Apparaten',
    'Cache Status' => 'Cachestatus',
    'Total cached entries' => 'Totaal gecachede vermeldingen',
    'Active' => 'Actief',
    'Pending' => 'In behandeling',
    'Expired' => 'Verlopen',
    'Disabled' => 'Uitgeschakeld',
    'Navigation' => 'Navigatie',
    'Access main plugin sections' => 'Toegang tot de hoofdsecties van de plugin',
    'Manage {pluginName}' => '{pluginName} beheren',
    'View Settings' => 'Instellingen bekijken',
    'Cache Management' => 'Cachebeheer',
    'Clear cached data to force regeneration. Useful after changing QR code settings or when troubleshooting.' => 'Wis gecachede gegevens om regeneratie te forceren. Nuttig na het wijzigen van QR Code-instellingen of bij probleemoplossing.',
    'Clear QR Cache' => 'QR-cache wissen',
    'Clear Device Cache' => 'Apparaatcache wissen',
    'Clear All Caches' => 'Alle caches wissen',
    'Analytics Data Management' => 'Beheer van analysegegevens',
    'Permanently delete all analytics tracking data. This action cannot be undone!' => 'Verwijder alle analyse-trackinggegevens permanent. Deze actie kan niet ongedaan worden gemaakt!',
    'Clear All Analytics' => 'Alle analyses wissen',
    'Are you sure you want to permanently delete ALL analytics data? This action cannot be undone!' => 'Weet u zeker dat u ALLE analysegegevens permanent wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt!',
    'This will delete all click tracking data and reset all click counts. Are you absolutely sure?' => 'Dit verwijdert alle kliktrackinggegevens en reset alle kliktellingen. Weet u het absoluut zeker?',
    'Failed to clear QR cache' => 'QR-cache wissen mislukt',
    'Failed to clear device cache' => 'Apparaatcache wissen mislukt',
    'Failed to clear caches' => 'Caches wissen mislukt',
    'Failed to clear analytics' => 'Analyses wissen mislukt',

    // =========================================================================
    // Widgets — Analytics Summary
    // =========================================================================

    '{pluginName} - Analytics' => '{pluginName} – Analyses',
    'Top Performer' => 'Beste prestatie',
    'interactions' => 'interacties',
    'View full analytics' => 'Volledige analyses bekijken',
    'You don\'t have permission to view analytics.' => 'U heeft geen toestemming om analyses te bekijken.',
    'Analytics are disabled in plugin settings.' => 'Analyses zijn uitgeschakeld in de plugininstellingen.',

    // =========================================================================
    // Widgets — Top Links
    // =========================================================================

    '{pluginName} - Top Links' => '{pluginName} – Toplinks',
    'Link' => 'Link',
    'Number of Links' => 'Aantal links',
    'How many top links to display (1-20)' => 'Hoeveel toplinks weergeven (1–20)',
    'View all {pluginName}' => 'Alle {pluginName} bekijken',
    'No {pluginName} yet' => 'Nog geen {pluginName}',
    'Create your first {singularName} to see it here.' => 'Maak uw eerste {singularName} aan om deze hier te zien.',

    // =========================================================================
    // Public Templates — Redirect Page (redirect.twig)
    // =========================================================================

    'App Store' => 'App Store',
    'Google Play' => 'Google Play',
    'AppGallery' => 'AppGallery',
    'Amazon' => 'Amazon',
    'Windows Store' => 'Windows Store',
    'Mac App Store' => 'Mac App Store',
    'Continue to Website' => 'Doorgaan naar website',

    // =========================================================================
    // Public Templates — QR Code Page (qr.twig)
    // =========================================================================

    'Scan with your phone\'s camera to download' => 'Scan met de camera van uw telefoon om te downloaden',

    // =========================================================================
    // Controller Messages — Flash Notices & Errors
    // =========================================================================

    // SmartlinksController
    'Smart link saved.' => 'Smart link opgeslagen.',
    'Couldn\'t save smart link.' => 'Smart link kon niet worden opgeslagen.',
    'Error saving smart link: {error}' => 'Fout bij opslaan van smart link: {error}',
    'Could not save smart link.' => 'Smart link kon niet worden opgeslagen.',
    'Smart link deleted.' => 'Smart link verwijderd.',
    'Couldn\'t delete smart link.' => 'Smart link kon niet worden verwijderd.',
    'Smart link restored.' => 'Smart link hersteld.',
    'Couldn\'t restore smart link.' => 'Smart link kon niet worden hersteld.',
    'Smart link permanently deleted.' => 'Smart link permanent verwijderd.',
    'Couldn\'t delete smart link permanently.' => 'Smart link kon niet permanent worden verwijderd.',
    'Smart link not found' => 'Smart link niet gevonden',
    'Cannot edit trashed smart links.' => 'Prullenbak-smart links kunnen niet worden bewerkt.',
    'Failed to generate QR code.' => 'QR Code genereren mislukt.',
    // SettingsController
    'Settings saved.' => 'Instellingen opgeslagen.',
    'Couldn\'t save settings.' => 'Instellingen konden niet worden opgeslagen.',
    'Field layout saved.' => 'Veldindeling opgeslagen.',
    'Couldn\'t save field layout.' => 'Veldindeling kon niet worden opgeslagen.',
    'Analytics cleanup job has been queued. It will run in the background.' => 'De opruimtaak voor analyses is in de wachtrij geplaatst. Deze wordt op de achtergrond uitgevoerd.',
    'QR code cache cleared successfully.' => 'QR Code-cache succesvol gewist.',
    'Cleared {count} QR code caches.' => '{count} QR Code-caches gewist.',
    'Device cache cleared successfully.' => 'Apparaatcache succesvol gewist.',
    'Cleared {count} device detection caches.' => '{count} apparaatdetectiecaches gewist.',
    'All caches cleared successfully.' => 'Alle caches succesvol gewist.',
    'Cleared {count} cache entries.' => '{count} cache-vermeldingen gewist.',
    'Cleared {count} analytics records and reset all click counts.' => '{count} analyserecords gewist en alle kliktellingen gereset.',
    'An unexpected error occurred.' => 'Er is een onverwachte fout opgetreden.',
    // AnalyticsController
    'No analytics data to export.' => 'Geen analysegegevens om te exporteren.',
    // JS notices
    'Enter custom size (100-4096 pixels):' => 'Voer een aangepaste grootte in (100–4096 pixels):',
    'Please enter a valid size between 100 and 4096 pixels' => 'Voer een geldige grootte in tussen 100 en 4096 pixels',
    'Reset QR code settings to plugin defaults?' => 'QR Code-instellingen terugzetten naar standaardinstellingen van de plugin?',

    // =========================================================================
    // Job Messages
    // =========================================================================

    '{pluginName}: Cleaning up old analytics' => '{pluginName}: Oude analyses opruimen',
    'Deleting {count} old analytics records' => '{count} oude analyserecords verwijderen',
    'Deleted {deleted} of {total} records' => '{deleted} van {total} records verwijderd',

    // =========================================================================
    // Validation Messages
    // =========================================================================

    'Only letters, numbers, hyphens, and underscores are allowed.' => 'Alleen letters, cijfers, koppeltekens en underscores zijn toegestaan.',
    'Only letters, numbers, hyphens, underscores, and slashes are allowed.' => 'Alleen letters, cijfers, koppeltekens, underscores en slashes zijn toegestaan.',
    'Only lowercase letters, numbers, and underscores are allowed.' => 'Alleen kleine letters, cijfers en underscores zijn toegestaan.',
    '{attribute} should only contain letters, numbers, underscores, and hyphens.' => '{attribute} mag alleen letters, cijfers, underscores en koppeltekens bevatten.',
    'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'Slug-voorvoegsel "{prefix}" conflicteert met: {conflicts}. Suggesties: {suggestions}',
    'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr' => 'QR-voorvoegsel mag niet hetzelfde zijn als uw slug-voorvoegsel. Probeer: qr, code, qrc, of {slug}/qr',
    'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"' => 'Genest QR-voorvoegsel moet beginnen met uw slug-voorvoegsel "{slug}". Gebruik: {slug}/{qr} of gebruik zelfstandig zoals "qr"',
    'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'QR-voorvoegsel "{prefix}" conflicteert met: {conflicts}. Suggesties: {suggestions}',
    'Smart link base URL must start with http:// or https://' => 'Basis-URL van smart link moet beginnen met http:// of https://',
    'Smart link base URL cannot contain spaces.' => 'Basis-URL van smart link mag geen spaties bevatten.',
    'Unsupported token in smart link base URL. Supported tokens: {siteHandle}, {siteId}, {siteUid}.' => 'Niet-ondersteund token in de basis-URL van smart link. Ondersteunde tokens: {siteHandle}, {siteId}, {siteUid}.',

    // =========================================================================
    // Config Override Warnings
    // =========================================================================

    'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>pluginName</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enableAnalytics</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>analyticsRetention</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>includeDisabledInExport</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>includeExpiredInExport</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>defaultQrSize</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>defaultQrColor</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>defaultQrBgColor</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>defaultQrFormat</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrCodeCacheDuration</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>defaultQrErrorCorrection</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>defaultQrMargin</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrModuleStyle</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrEyeStyle</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrEyeColor</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enableQrLogo</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrLogoVolumeUid</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>imageVolumeUid</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrLogoSize</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enableQrDownload</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrDownloadFilename</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>redirectTemplate</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>qrTemplate</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enableGeoDetection</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>cacheDeviceDetection</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>deviceDetectionCacheDuration</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>languageDetectionMethod</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>itemsPerPage</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>notFoundRedirectUrl</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enabledSites</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledIntegrations</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enabledIntegrations</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticTrackingEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>seomaticTrackingEvents</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticEventPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>seomaticEventPrefix</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheStorageMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>cacheStorageMethod</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrCodeCache</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>enableQrCodeCache</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>anonymizeIpAddress</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>anonymizeIpAddress</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectManagerEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>redirectManagerEvents</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>logLevel</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>logLevel</code> in <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>smartlinkBaseUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dit wordt overschreven door de instelling <code>smartlinkBaseUrl</code> in <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // General Interface
    // =========================================================================

    'Save Settings' => 'Instellingen opslaan',
    'Actions' => 'Acties',
    'Loading...' => 'Laden...',
    'Error' => 'Fout',

    // =========================================================================
    // Behavior Settings — Select Options
    // =========================================================================

    'Browser preference' => 'Browservoorkeur',
    'IP geolocation' => 'IP-geolocatie',
    'Both' => 'Beide',

    // =========================================================================
    // General Settings — URL Tips (Redirect Manager integration)
    // =========================================================================

    'Changing will break existing URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard)' => 'Wijziging verbreekt bestaande URL\'s. Om te migreren, maak een wildcard-doorstuuractie aan in {redirectPluginName}: Bron \'/old/*\' → Bestemming \'/new/$1\' (Overeenkomsttype: Wildcard)',
    'Changing will break existing QR URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard). Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns.' => 'Wijziging verbreekt bestaande QR-URL\'s. Om te migreren, maak een wildcard-doorstuuractie aan in {redirectPluginName}: Bron \'/old/*\' → Bestemming \'/new/$1\' (Overeenkomsttype: Wildcard). Ondersteunt zelfstandige (bijv. \'qr\') of geneste (bijv. \'go/qr\') patronen.',
    'Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns. Checked for conflicts with ShortLink Manager.' => 'Ondersteunt zelfstandige (bijv. \'qr\') of geneste (bijv. \'go/qr\') patronen. Gecontroleerd op conflicten met ShortLink Manager.',

    // =========================================================================
    // QR Code Settings — Select Options
    // =========================================================================

    'Square' => 'Vierkant',
    'Rounded' => 'Afgerond',
    'Dots' => 'Punten',
    'Leaf' => 'Blad',
    'Low (~7% correction)' => 'Laag (~7% correctie)',
    'Medium (~15% correction)' => 'Middel (~15% correctie)',
    'Quartile (~25% correction)' => 'Kwartiel (~25% correctie)',
    'High (~30% correction)' => 'Hoog (~30% correctie)',
    'Failed to generate preview' => 'Voorbeeld genereren mislukt',

    // =========================================================================
    // Smart Link Fields — Image Size Options
    // =========================================================================

    'Extra Large' => 'Extra groot',
    'Large' => 'Groot',
    'Medium' => 'Middel',
    'Small' => 'Klein',

    // =========================================================================
    // Smart Link Field Input — Tooltip
    // =========================================================================

    'Clicks:' => 'Klikken:',

    // =========================================================================
    // Cache Settings — Info Boxes & Durations
    // =========================================================================

    'Cache Location' => 'Cachelocatie',
    'Using Craft\'s configured Redis cache from <code>config/app.php</code>' => 'Gebruik van de geconfigureerde Redis-cache van Craft uit <code>config/app.php</code>',
    'Redis Not Configured' => 'Redis niet geconfigureerd',
    'To use Redis caching, install <code>yiisoft/yii2-redis</code> and configure it in <code>config/app.php</code>.' => 'Om Redis-caching te gebruiken, installeer <code>yiisoft/yii2-redis</code> en configureer het in <code>config/app.php</code>.',
    'How it works' => 'Hoe het werkt',
    'Device detection parses user-agent strings to identify devices, browsers, and operating systems' => 'Apparaatdetectie parseert user-agent-strings om apparaten, browsers en besturingssystemen te identificeren',
    'Results are cached to avoid re-parsing the same user-agent repeatedly' => 'Resultaten worden gecacht om herhaalde parsing van dezelfde user-agent te vermijden',
    'Recommended to keep enabled for production sites' => 'Aanbevolen om ingeschakeld te laten voor productiesites',
    'Cache duration in seconds. Current:' => 'Cacheduur in seconden. Huidig:',

    // =========================================================================
    // Time Unit Strings (for JS secondsToHuman)
    // =========================================================================

    '{count} second' => '{count} seconde',
    '{count} seconds' => '{count} seconden',
    '{count} minute' => '{count} minuut',
    '{count} minutes' => '{count} minuten',
    '{count} hour' => '{count} uur',
    '{count} hours' => '{count} uur',
    '{count} day' => '{count} dag',
    '{count} days' => '{count} dagen',

    // =========================================================================
    // Template Settings — Copy hints
    // =========================================================================

    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> to <code>templates/smartlink-manager/redirect.twig</code>' => 'Vereist: kopieer <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> naar <code>templates/smartlink-manager/redirect.twig</code>',
    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> to <code>templates/smartlink-manager/qr.twig</code>' => 'Vereist: kopieer <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> naar <code>templates/smartlink-manager/qr.twig</code>',

    // =========================================================================
    // Import/Export
    // =========================================================================

    'Manage import/export' => 'Import/export beheren',
    'Import links' => 'Links importeren',
    'Export links' => 'Links exporteren',
    'Clear import history' => 'Importgeschiedenis wissen',
    'Export Smart Links' => 'Smart Links exporteren',
    'Export All Smart Links as CSV' => 'Alle Smart Links exporteren als CSV',
    'Import Smart Links' => 'Smart Links importeren',
    'You do not have permission to export smart links.' => 'U heeft geen toestemming om smart links te exporteren.',
    'You do not have permission to import smart links.' => 'U heeft geen toestemming om smart links te importeren.',
    'Download all your current smart links as a CSV file for backup or migration to another site.' => 'Download al uw huidige smart links als CSV-bestand voor back-up of migratie naar een andere site.',
    'Import smart links from CSV. You\'ll map columns and preview before importing.' => 'Importeer smart links vanuit CSV. U mapt kolommen en bekijkt een voorbeeld voor het importeren.',
    'Select a CSV file to import smart links' => 'Selecteer een CSV-bestand om smart links te importeren',
    'No smart links to export.' => 'Geen smart links om te exporteren.',
    'Map your CSV columns to smart link fields. Required fields must be mapped.' => 'Wijs uw CSV-kolommen toe aan smart link-velden. Verplichte velden moeten worden toegewezen.',
    'Valid Smart Links to Import' => 'Geldige Smart Links om te importeren',
    'No valid smart links found to import.' => 'Geen geldige smart links gevonden om te importeren.',
    'Import {count} Smart Links' => '{count} Smart Links importeren',
    'No Valid Smart Links to Import' => 'Geen geldige Smart Links om te importeren',
    'Click the button below to import {count} valid smart link(s).' => 'Klik op de knop hieronder om {count} geldige smart link(s) te importeren.',
    'Import completed: {imported} smart links imported.' => 'Import voltooid: {imported} smart links geïmporteerd.',
    'Import completed: {imported} imported, {failed} failed.' => 'Import voltooid: {imported} geïmporteerd, {failed} mislukt.',
    'Import completed: {imported} {pluginName} imported.' => 'Import voltooid: {imported} {pluginName} geïmporteerd.',
    'Import completed: {imported} {pluginName} imported, {failed} failed.' => 'Import voltooid: {imported} {pluginName} geïmporteerd, {failed} mislukt.',
    'Failed to clear import history.' => 'Importgeschiedenis wissen mislukt.',
    'Slug must be mapped.' => 'Slug moet worden toegewezen.',
    'Slug (required)' => 'Slug (vereist)',
    'Fallback URL (required)' => 'Fallback-URL (vereist)',
    'Image Asset ID' => 'Afbeeldingsbestand-ID',
    'Image Size (xl/lg/md/sm)' => 'Afbeeldingsgrootte (xl/lg/md/sm)',
    'QR Enabled (1/0)' => 'QR ingeschakeld (1/0)',
    'QR Size' => 'QR-grootte',
    'QR Color (#RRGGBB)' => 'QR-kleur (#RRGGBB)',
    'QR Background (#RRGGBB)' => 'QR-achtergrond (#RRGGBB)',
    'QR Eye Color (#RRGGBB)' => 'QR-markeerderkleur (#RRGGBB)',
    'QR Format (png/svg)' => 'QR-indeling (png/svg)',
    'QR Logo Asset ID' => 'QR-logo-bestand-ID',
    'Hide Title (1/0)' => 'Titel verbergen (1/0)',
    'Language Detection (1/0)' => 'Taaldetectie (1/0)',
    'Metadata (JSON)' => 'Metadata (JSON)',

    // Import/Export — Controller messages
    'Unknown' => 'Onbekend',
    'Please select a CSV file to upload.' => 'Selecteer een CSV-bestand om te uploaden.',
    'Failed to parse CSV: {error}' => 'CSV parseren mislukt: {error}',
    'No import data found. Please upload a CSV file.' => 'Geen importgegevens gevonden. Upload een CSV-bestand.',
    'No preview data found. Please map columns first.' => 'Geen voorbeeldgegevens gevonden. Wijs eerst kolommen toe.',
    'Import session expired. Please upload the file again.' => 'Importsessie verlopen. Upload het bestand opnieuw.',

    // Import/Export — Template UI
    'Import History' => 'Importgeschiedenis',
    'CSV Format' => 'CSV-indeling',
    'Required columns:' => 'Verplichte kolommen:',
    'Optional columns:' => 'Optionele kolommen:',
    'Import from CSV' => 'Importeren vanuit CSV',
    'CSV File' => 'CSV-bestand',
    'CSV Delimiter' => 'CSV-scheidingsteken',
    'Character used to separate values in your CSV (auto-detect is default)' => 'Teken voor het scheiden van waarden in uw CSV (automatisch detecteren is standaard)',
    'Auto (detect)' => 'Automatisch (detecteren)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Puntkomma (;)',
    'Tab' => 'Tab',
    'Pipe (|)' => 'Sluisteken (|)',
    'The maximum file size is {size} and the import is limited to {rows} rows per file.' => 'De maximale bestandsgrootte is {size} en de import is beperkt tot {rows} rijen per bestand.',
    'Upload & Map Columns' => 'Uploaden en kolommen toewijzen',
    'Clear history' => 'Geschiedenis wissen',
    'No import history yet.' => 'Nog geen importgeschiedenis.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Weet u zeker dat u alle importlogboeken wilt wissen? Deze actie kan niet ongedaan worden gemaakt.',
    'Failed to clear history.' => 'Geschiedenis wissen mislukt.',
    'Map CSV Columns' => 'CSV-kolommen toewijzen',
    'Your CSV has {count} rows. Map each CSV column to a smart link field.' => 'Uw CSV heeft {count} rijen. Wijs elke CSV-kolom toe aan een smart link-veld.',
    'Preview of CSV Data' => 'Voorbeeld van CSV-gegevens',
    'Showing first 5 rows. {total} total rows will be imported.' => 'De eerste 5 rijen worden weergegeven. {total} rijen in totaal worden geïmporteerd.',
    'Column Mapping' => 'Kolomtoewijzing',
    'Note: only columns mapped to a field will be imported.' => 'Opmerking: alleen kolommen die aan een veld zijn toegewezen, worden geïmporteerd.',
    '-- Do not import --' => '-- Niet importeren --',
    'Enabled (1/0)' => 'Ingeschakeld (1/0)',
    'Site ID' => 'Site-ID',
    'Site Handle' => 'Site-handle',
    'Track Analytics (1/0)' => 'Analyses bijhouden (1/0)',
    'Post Date (YYYY-MM-DD HH:MM:SS)' => 'Publicatiedatum (JJJJ-MM-DD UU:MM:SS)',
    'Date Expired (YYYY-MM-DD HH:MM:SS)' => 'Vervaldatum (JJJJ-MM-DD UU:MM:SS)',
    'CSV Column' => 'CSV-kolom',
    'Maps to Field' => 'Toegewezen aan veld',
    'Sample Data' => 'Voorbeeldgegevens',
    'Map Columns' => 'Kolommen toewijzen',
    'Cancel' => 'Annuleren',
    'Preview Import' => 'Import bekijken',
    'Import Preview' => 'Importvoorbeeld',
    'Total Rows' => 'Totaal rijen',
    'Valid' => 'Geldig',
    'Duplicates' => 'Duplicaten',
    'Errors' => 'Fouten',
    'Duplicates (will be skipped)' => 'Duplicaten (worden overgeslagen)',
    'Invalid Rows (will be skipped)' => 'Ongeldige rijen (worden overgeslagen)',
    'Row' => 'Rij',
    'Reason' => 'Reden',
    'Image ID' => 'Afbeeldings-ID',
    'Ready to Import' => 'Klaar om te importeren',

    // Base partial: import-history
    'Created By' => 'Aangemaakt door',
    'Filename' => 'Bestandsnaam',
    'Imported' => 'Geïmporteerd',
    'Failed' => 'Mislukt',

    // Analytics partial
    'Device Breakdown' => 'Apparaatverdeling',

];
