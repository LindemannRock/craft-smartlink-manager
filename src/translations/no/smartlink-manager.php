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
    'Manage smart links, route users by device, and track engagement from one control panel workspace.' => 'Administrer smart links, viderestill brukere etter enhet og spor engasjement fra ett arbeidsområde i kontrollpanelet.',
    'Open SmartLink Manager' => 'Åpne SmartLink Manager',
    '{name} plugin loaded' => '{name} plugin lastet inn',
    '{displayName} caches' => '{displayName} cache',

    // =========================================================================
    // Element Names
    // =========================================================================

    'Smart Link' => 'Smart Link',
    'smart link' => 'smart link',
    'smart links' => 'smart links',
    'New smart link' => 'Ny smart link',

    // =========================================================================
    // Permissions
    // =========================================================================

    'Manage {plural}' => 'Administrer {plural}',
    'Create {plural}' => 'Opprett {plural}',
    'Edit {plural}' => 'Rediger {plural}',
    'Delete {plural}' => 'Slett {plural}',
    'View analytics' => 'Vis analyse',
    'Export analytics' => 'Eksporter analyse',
    'Clear analytics' => 'Tøm analyse',
    'Clear cache' => 'Tøm cache',
    'View logs' => 'Vis logger',
    'View system logs' => 'Vis systemlogger',
    'Download system logs' => 'Last ned systemlogger',
    'Manage settings' => 'Administrer innstillinger',

    // =========================================================================
    // Navigation & Breadcrumbs
    // =========================================================================

    'Links' => 'Lenker',
    'Analytics' => 'Analyse',
    'Logs' => 'Logger',
    'Settings' => 'Innstillinger',
    'General' => 'Generelt',
    'QR Code' => 'QR Code',
    'Redirect' => 'Viderestilling',
    'Export' => 'Eksporter',
    'Advanced' => 'Avansert',
    'Interface' => 'Grensesnitt',
    'Behavior' => 'Atferd',
    'Integrations' => 'Integrasjoner',
    'Cache' => 'Cache',
    'Field Layout' => 'Feltoppsett',
    'Overview' => 'Oversikt',
    'Import/Export' => 'Import/Export',

    // =========================================================================
    // General Settings
    // =========================================================================

    'General Settings' => 'Generelle innstillinger',
    'Plugin Name' => 'Plugin-navn',
    'The name of the plugin as it appears in the Control Panel menu' => 'Navnet på plugin-programmet slik det vises i kontrollpanelets meny',
    'Plugin Settings' => 'Plugin-innstillinger',
    'Log Level' => 'Loggnivå',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'Velg hvilke typer meldinger som skal logges. Feilsøkingsnivå krever at devMode er aktivert.',
    'Error (Critical errors only)' => 'Feil (kun kritiske feil)',
    'Warning (Errors and warnings)' => 'Advarsel (feil og advarsler)',
    'Info (General information)' => 'Info (generell informasjon)',
    'Debug (Detailed debugging)' => 'Debug (detaljert feilsøking)',
    'Logging Settings' => 'Logginnstillinger',

    // Logs viewer (logging-library)
    'All Levels' => 'Alle nivåer',
    'Info' => 'Info',
    'Debug' => 'Debug',
    'Select File' => 'Velg fil',
    'Select Date' => 'Velg dato',
    'All Sources' => 'Alle kilder',
    'Search messages and context...' => 'Søk i meldinger og kontekst...',
    'System Logs' => 'Systemlogger',
    'System' => 'System',
    'Current log level' => 'Gjeldende loggnivå',
    'No log files found. Log files are created when plugin activities occur.' => 'Ingen loggfiler funnet. Loggfiler opprettes når plugin-aktiviteter oppstår.',
    'No log entries found for the selected filters.' => 'Ingen loggoppføringer funnet for de valgte filtrene.',
    'No context data available.' => 'Ingen kontekstdata tilgjengelig.',
    'Level' => 'Nivå',
    'User' => 'Bruker',
    'Message' => 'Melding',
    'entry' => 'oppføring',
    'entries' => 'oppføringer',
    'Available Logs' => 'Tilgjengelige logger',
    'Current File' => 'Gjeldende fil',
    'Download File' => 'Last ned fil',
    'Log Location' => 'Loggplassering',
    'Current Level' => 'Gjeldende nivå',
    'Retention' => 'Oppbevaring',
    'days' => 'dager',
    'Context' => 'Kontekst',
    'Entries' => 'Oppføringer',
    'file' => 'fil',
    'files' => 'filer',

    // =========================================================================
    // Site Settings
    // =========================================================================

    'Site Settings' => 'Nettstedsinnstillinger',
    'Enabled Sites' => 'Aktiverte nettsteder',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'Velg hvilke nettsteder {pluginName} skal aktiveres for. La stå tomt for å aktivere for alle nettsteder.',

    // =========================================================================
    // URL Settings
    // =========================================================================

    'URL Settings' => 'URL-innstillinger',
    'Smart Link URL Prefix' => 'Smart Link URL-prefiks',
    '{singularName} URL Prefix' => '{singularName} URL-prefiks',
    'QR Code URL Prefix' => 'QR Code URL-prefiks',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link)' => 'URL-prefiks for {pluginName} (f.eks. \'go\' oppretter /go/your-link). Tøm routes-cachen etter endring (php craft clear-caches/compiled-templates).',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link/view or \'go/qr\' creates /go/qr/your-link/view)' => 'URL-prefiks for QR Code-sider (f.eks. \'qr\' oppretter /qr/your-link/view eller \'go/qr\' oppretter /go/qr/your-link/view)',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'Tøm routes-cachen etter denne endringen (php craft clear-caches/compiled-templates).',
    'Smart Link Base URL' => 'Smart Link basis-URL',
    '{singularName} Base URL' => '{singularName} basis-URL',
    'Optional absolute URL used for generated smart links and QR URLs. Leave empty to use each site\'s base URL.' => 'Valgfri absolutt URL for genererte smart links og QR-URL-er. La stå tomt for å bruke hvert nettsted sin basis-URL.',
    'Base URL for generated smart links and QR URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'Basis-URL for genererte smart links og QR-URL-er. For multisett kan du bruke tokens: {siteHandle}, {siteId}, {siteUid} (f.eks. https://go.example.com/{siteHandle}). La stå tomt for å bruke hvert nettsted sin basis-URL.',
    'Base URL for {singularName} and QR code URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'Basis-URL for {singularName} og QR Code-URL-er. For multisett kan du bruke tokens: {siteHandle}, {siteId}, {siteUid} (f.eks. https://go.example.com/{siteHandle}). La stå tomt for å bruke hvert nettsted sin basis-URL.',
    'Changing the URL prefix will break all existing {pluginName}. Only change this before creating your first {singularName}.' => 'Endring av URL-prefikset vil ødelegge alle eksisterende {pluginName}. Endre kun dette før du oppretter din første {singularName}.',
    'Multisite detected: <code>Smart Link Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multisett registrert: <code>Smart Link Base URL</code> er angitt uten et nettstedstoken. Genererte URL-er kan peke til kun ett nettsted. Bruk en tokenisert URL som <code>https://go.example.com/{siteHandle}</code> for å bevare nettstedsspecifikk ruting.',
    'Multisite detected: <code>{singularName} Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'Multisett registrert: <code>{singularName} basis-URL</code> er angitt uten et nettstedstoken. Genererte URL-er kan peke til kun ett nettsted. Bruk en tokenisert URL som <code>https://go.example.com/{siteHandle}</code> for å bevare nettstedsspecifikk ruting.',
    'Use URL Prefix' => 'Bruk URL-prefiks',
    'Enable to generate {singularName} URLs as /{prefix}/{slug}. Disable to generate root URLs as /{slug}.' => 'Aktiver for å generere {singularName}-URL-er som /{prefix}/{slug}. Deaktiver for å generere rot-URL-er som /{slug}.',
    'Both {smartName} and {shortName} are set to root URLs (no prefix) and share at least one host. Redirect routes can collide (e.g., <code>/slug</code>), and QR routes can also collide when both plugins use the same QR prefix (e.g., <code>/qr/slug</code>).' => 'Både {smartName} og {shortName} er satt til rot-URL-er (uten prefiks) og deler minst én vert. Viderestillingsruter kan kollidere (f.eks. <code>/slug</code>), og QR-ruter kan også kollidere hvis begge plugin-programmer bruker det samme QR-prefikset (f.eks. <code>/qr/slug</code>).',
    'Both {smartName} and {shortName} are set to root URLs (no prefix). Host overlap could not be fully resolved from current settings/config, so redirect route collisions are possible. QR routes may also collide if both plugins use the same QR prefix.' => 'Både {smartName} og {shortName} er satt til rot-URL-er (uten prefiks). Vertoverlapp kunne ikke løses fullstendig fra gjeldende innstillinger/konfigurasjon, så kollisjoner i viderestillingsruter er mulige. QR-ruter kan også kollidere hvis begge plugin-programmer bruker det samme QR-prefikset.',
    'URL Prefix is disabled. {singularName} URLs will be generated as root paths like <code>/your-link</code>.' => 'URL-prefiks er deaktivert. {singularName}-URL-er genereres som rotstier som <code>/your-link</code>.',
    'This is being overridden by the <code>usePrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>usePrefix</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>slugPrefix</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrPrefix</code> i <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // Template Settings
    // =========================================================================

    'Template Settings' => 'Malinnstillinger',
    'Redirect Template' => 'Viderestillingsmal',
    'Custom Redirect Template' => 'Tilpasset viderestillingsmal',
    'Template path in your templates/ folder. Leave empty to use the default path.' => 'Malsti i din templates/-mappe. La stå tomt for å bruke standardstien.',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)' => 'Sti til tilpasset mal i din templates/-mappe (f.eks. smartlink-manager/redirect)',
    'QR Code Template' => 'QR Code-mal',
    'Custom QR Code Template' => 'Tilpasset QR Code-mal',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)' => 'Sti til tilpasset mal i din templates/-mappe (f.eks. smartlink-manager/qr)',
    'These templates must exist in your site\'s <code>templates/</code> folder. Copy the reference templates from <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> to <code>templates/smartlink-manager/</code> and customize as needed.' => 'Disse malene må finnes i nettstedets <code>templates/</code>-mappe. Kopier referansemalene fra <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> til <code>templates/smartlink-manager/</code> og tilpass etter behov.',

    // =========================================================================
    // Asset Settings
    // =========================================================================

    'Asset Settings' => 'Ressursinnstillinger',
    'Image Volume' => 'Bildevolum',
    '{singularName} Image Volume' => '{singularName} bildevolum',
    'Which asset volume should be used for {singularName} images' => 'Hvilket ressursvolum skal brukes for {singularName}-bilder',
    'All asset volumes' => 'Alle ressursvolumer',

    // =========================================================================
    // QR Code Settings — Appearance
    // =========================================================================

    'QR Code Settings' => 'QR Code-innstillinger',
    'Appearance & Style' => 'Utseende og stil',
    'Enable QR Code' => 'Aktiver QR Code',
    'Default QR Code Size' => 'Standard QR Code-størrelse',
    'Default size in pixels for generated QR codes' => 'Standardstørrelse i piksler for genererte QR-koder',
    'QR Code Color' => 'QR Code-farge',
    'Default QR Code Color' => 'Standard QR Code-farge',
    'Default QR Background Color' => 'Standard QR Code-bakgrunnsfarge',
    'Background Color' => 'Bakgrunnsfarge',
    'Default QR Code Format' => 'Standard QR Code-format',
    'Default format for generated QR codes' => 'Standardformat for genererte QR-koder',
    'Override the default QR code format' => 'Overstyr standard QR Code-format',
    'Format' => 'Format',
    'Use Default ({format|upper})' => 'Bruk standard ({format|upper})',
    'Color' => 'Farge',
    'Background' => 'Bakgrunn',
    'Eye Color' => 'Øyefarge',
    'Color for position markers (leave empty to use main color)' => 'Farge for posisjonsmarkører (la stå tomt for å bruke hovedfargen)',
    'Size' => 'Størrelse',

    // =========================================================================
    // QR Code Settings — Logo
    // =========================================================================

    'Logo Settings' => 'Logoinnstillinger',
    'Enable QR Code Logo' => 'Aktiver QR Code-logo',
    'Enable Logo Overlay' => 'Aktiver logooverlegg',
    'Add a logo in the center of QR codes' => 'Legg til en logo i midten av QR-koder',
    'Logo Volume' => 'Logosvolum',
    'Logo Asset Volume' => 'Logoressursvolum',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'Hvilket ressursvolum som inneholder QR Code-logoer. Lagre innstillingene etter endring for å oppdatere logovalget nedenfor.',
    'Default Logo' => 'Standardlogo',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'Standardlogo for QR-koder (kan overstyres per smart link)',
    'Default logo is required when logo overlay is enabled.' => 'Standardlogo er påkrevd når logooverlegg er aktivert.',
    'Logo Size (%)' => 'Logostørrelse (%)',
    'Logo Size' => 'Logostørrelse',
    'Logo size as percentage of QR code (10-30%)' => 'Logostørrelse som prosent av QR-koden (10–30 %)',
    'Logo' => 'Logo',
    'Override the default QR code logo' => 'Overstyr standard QR Code-logo',
    'Using default logo from settings (click to override)' => 'Bruker standardlogo fra innstillinger (klikk for å overstyre)',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'Logooverlegg fungerer bare med PNG-format. SVG-format støtter ikke logoer.',
    'Logo requires PNG format' => 'Logo krever PNG-format',
    'Please save settings to apply the volume change to the logo selection field.' => 'Lagre innstillingene for å bruke volumendringen på logovalgsfeltet.',
    'Please save to apply the volume change' => 'Lagre for å bruke volumendringen',

    // =========================================================================
    // QR Code Settings — Technical
    // =========================================================================

    'Technical Options' => 'Tekniske alternativer',
    'Error Correction Level' => 'Feilkorrigeringsnivå',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'Høyere nivåer fungerer bedre hvis QR-koden er skadet, men skaper tettere mønstre',
    'QR Code Margin' => 'QR Code-marg',
    'Margin Size' => 'Margstørrelse',
    'White space around QR code (0-10 modules)' => 'Hvitt rom rundt QR-koden (0–10 moduler)',
    'Module Style' => 'Modulstil',
    'Shape of the QR code modules' => 'Form på QR-kodens moduler',
    'Eye Style' => 'Øyestil',
    'Shape of the position markers (corners)' => 'Form på posisjonsmarkører (hjørner)',

    // =========================================================================
    // QR Code Settings — Downloads
    // =========================================================================

    'Download Settings' => 'Nedlastingsinnstillinger',
    'Enable QR Code Downloads' => 'Aktiver QR Code-nedlastinger',
    'Allow users to download QR codes' => 'Tillat brukere å laste ned QR-koder',
    'Download Filename Pattern' => 'Filnavnsmønster for nedlasting',
    'Available variables: {slug}, {size}, {format}' => 'Tilgjengelige variabler: {slug}, {size}, {format}',
    'Download QR Code' => 'Last ned QR Code',
    'Small (256px)' => 'Liten (256 px)',
    'Medium (512px)' => 'Medium (512 px)',
    'Large (1024px)' => 'Stor (1024 px)',
    'Extra Large (2048px)' => 'Ekstra stor (2048 px)',
    'Custom Size...' => 'Tilpasset størrelse...',

    // =========================================================================
    // QR Code Settings — Actions & Preview
    // =========================================================================

    'QR Code Actions' => 'QR Code-handlinger',
    'View QR Code' => 'Vis QR Code',
    'QR Code Image' => 'QR Code-bilde',
    'QR Code Page' => 'QR Code-side',
    'Reset to Defaults' => 'Tilbakestill til standard',
    'Live Preview' => 'Direkteforhåndsvisning',
    'Preview' => 'Forhåndsvisning',
    'Click to view QR code image' => 'Klikk for å se QR-kodebildet',
    'Click to view QR code page' => 'Klikk for å se QR-kodesiden',
    'Toggle preview' => 'Veksle forhåndsvisning',
    'QR code settings reset to defaults' => 'QR Code-innstillinger tilbakestilt til standard',
    'Performance & Caching' => 'Ytelse og cache',
    'Configure QR code caching to improve performance and reduce server load.' => 'Konfigurer QR Code-caching for å forbedre ytelsen og redusere serverbelastningen.',
    'Go to Cache Settings' => 'Gå til cache-innstillinger',

    // =========================================================================
    // Behavior Settings
    // =========================================================================

    'Behavior Settings' => 'Atferdsinnstillinger',
    'Redirect Behavior' => 'Viderestillingsatferd',
    '404 Redirect URL' => '404-viderestillings-URL',
    'Where to redirect when a {singularName} is not found or disabled' => 'Hvor det skal viderestilles når en {singularName} ikke finnes eller er deaktivert',
    'Can be a relative path (/) or full URL (https://example.com)' => 'Kan være en relativ sti (/) eller full URL (https://example.com)',

    // =========================================================================
    // Analytics Settings
    // =========================================================================

    'Analytics Settings' => 'Analyseinnstillinger',
    'Enable Analytics' => 'Aktiver analyse',
    'Track Analytics' => 'Spor analyse',
    'Track clicks and visitor data for {pluginName}' => 'Spor klikk og besøksdata for {pluginName}',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'Når aktivert sporer {pluginName} besøkendes interaksjoner, enhetstyper, geografiske data og annen analyseinformasjon.',
    'Are you sure you want to disable analytics tracking for this {singularName}? This {singularName} will no longer collect visitor data and interactions.' => 'Er du sikker på at du vil deaktivere analysesporing for denne {singularName}? Denne {singularName} vil ikke lenger samle besøksdata og interaksjoner.',

    // =========================================================================
    // Analytics Settings — IP Privacy
    // =========================================================================

    'IP Address Privacy' => 'IP-adressebeskyttelse',
    'Anonymize IP Addresses' => 'Anonymiser IP-adresser',
    'Mask IP addresses before storage for maximum privacy. <strong>IPv4</strong>: masks last octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: masks last 80 bits. <strong>Trade-off</strong>: Reduces unique visitor accuracy (users on same subnet counted as one visitor). Geo-location still works normally.' => 'Masker IP-adresser før lagring for maksimal personvern. <strong>IPv4</strong>: maskerer siste oktet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: maskerer de siste 80 bitene. <strong>Avveining</strong>: Reduserer nøyaktigheten for unike besøkende (brukere på samme subnett telles som én besøkende). Geolokalisering fungerer fortsatt normalt.',
    'Privacy Levels' => 'Personvernnivåer',
    'Enabled' => 'Aktivert',
    'default' => 'standard',
    'Full IP hashed with salt (accurate unique visitors)' => 'Full IP hashet med salt (nøyaktig telling av unike besøkende)',
    'Subnet masked + hashed with salt (maximum privacy, less accurate)' => 'Subnett maskert + hashet med salt (maksimalt personvern, mindre nøyaktig)',

    // =========================================================================
    // Analytics Settings — Retention & Cleanup
    // =========================================================================

    'Analytics Retention (days)' => 'Analyselagring (dager)',
    'Analytics Retention' => 'Analyselagring',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'Hvor mange dager analysedata skal beholdes (0 for ubegrenset, maks 3650)',
    'Data Retention' => 'Datalagring',
    'Analytics Cleanup' => 'Opprydding av analysedata',
    'Analytics data older than {days} days will be automatically cleaned up daily.' => 'Analysedata eldre enn {days} dager ryddes automatisk opp daglig.',
    'Clean Up Now' => 'Rydd opp nå',
    'Are you sure you want to clean up old analytics data now?' => 'Er du sikker på at du vil rydde opp gammel analysedata nå?',
    'Unlimited Retention Warning' => 'Advarsel om ubegrenset lagring',
    'Warning' => 'Advarsel',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'Analysedata vil lagres på ubestemt tid. Dette kan føre til stor databasestørrelse, tregere ytelse og økte lagringskostnader over tid. Vurder å angi en lagringsperiode (anbefalt: 90–365 dager) for produksjonsnettsteder.',

    // =========================================================================
    // Geo Provider Settings (from base _partials/geo-settings, uses |t(pluginHandle))
    // =========================================================================

    'Geographic Detection' => 'Geografisk registrering',
    'Geographic Analytics' => 'Geografisk analyse',
    'Geographic Distribution' => 'Geografisk fordeling',
    'Enable Geographic Detection' => 'Aktiver geografisk registrering',
    'Detect user location for analytics' => 'Registrer brukerens posisjon for analyse',
    'View Geographic Details' => 'Vis geografiske detaljer',
    'Loading geographic data...' => 'Laster geografiske data...',

    // Geo provider partial (lindemannrock-base/_partials/geo-settings)
    'Geo Provider' => 'Geo-leverandør',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'Velg geo-IP-oppslagsleverandør. HTTPS-leverandører anbefales av personvernhensyn.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP gratis, HTTPS betalt)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS, 1k/dag gratis)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS, 50k/md. gratis)',
    'API Key' => 'API Key',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'Valgfritt. Påkrevd for betalte nivåer (aktiverer HTTPS for ip-api.com Pro).',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'ip-api.com gratisnivå bruker HTTP. IP-adresser overføres ukryptert. Legg til en API Key for HTTPS (Pro-nivå) eller bytt til ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: HTTP gratisnivå (45 forespørsler/min). Legg til API Key for HTTPS (Pro-nivå, $13/md.). IP-adresser overføres ukryptert uten API Key.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS med 1 000 gratis forespørsler/dag. API Key valgfritt (øker hastighetsbegrensninger).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS med 50 000 gratis forespørsler/md. API Key valgfritt (øker hastighetsbegrensninger).',

    // IP salt error banner (from base partial)
    'error' => 'feil',
    'Configuration Required' => 'Konfigurasjon påkrevd',
    'IP hash salt is missing.' => 'IP-hash-salt mangler.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'Analysesporing krever et sikkert salt for personvernbeskyttelse.',
    'Run one of these commands in your terminal:' => 'Kjør en av disse kommandoene i terminalen din:',
    'Standard:' => 'Standard:',
    'COPY' => 'KOPIER',
    'DDEV:' => 'DDEV:',
    'This will automatically add' => 'Dette vil automatisk legge til',
    'to your' => 'i din',
    'file.' => 'fil.',
    'Warning:' => 'Advarsel:',
    'Copy the same salt to staging and production environments.' => 'Kopier det samme saltet til staging- og produksjonsmiljøer.',
    'COPIED!' => 'KOPIERT!',
    'Failed to copy to clipboard' => 'Kunne ikke kopiere til utklippstavlen',

    // =========================================================================
    // Device Detection Settings
    // =========================================================================

    'Cache Device Detection' => 'Cache for enhetsregistrering',
    'Cache device detection results for better performance' => 'Lagre resultater fra enhetsregistrering i cache for bedre ytelse',
    'Device Detection Cache Duration (seconds)' => 'Cachevarighet for enhetsregistrering (sekunder)',

    // =========================================================================
    // Language Detection Settings
    // =========================================================================

    'Language Detection Method' => 'Metode for språkregistrering',
    'How to detect user language preference' => 'Slik registreres brukerens språkpreferanse',
    'Language Detection' => 'Språkregistrering',
    'Enable automatic language detection to redirect users based on their browser or location' => 'Aktiver automatisk språkregistrering for å viderestille brukere basert på nettleser eller posisjon',

    // =========================================================================
    // Cache Settings
    // =========================================================================

    'Cache Settings' => 'Cache-innstillinger',
    'Cache Storage Settings' => 'Innstillinger for cachelagring',
    'Cache Storage Method' => 'Metode for cachelagring',
    'How to store cache data. Use Redis/Database for load-balanced or multi-server environments.' => 'Slik lagres cachedata. Bruk Redis/database for belastningsbalanserte eller flerservermiljøer.',
    'File System (default, single server)' => 'Filsystem (standard, enkeltserver)',
    'Redis/Database (load-balanced, multi-server, cloud hosting)' => 'Redis/Database (belastningsbalansert, flerserver, skyhosting)',
    'QR Code Caching' => 'QR Code-caching',
    'Enable QR Code Cache' => 'Aktiver QR Code-cache',
    'Cache generated QR codes for better performance' => 'Lagre genererte QR-koder i cache for bedre ytelse',
    'QR Code Cache Duration (seconds)' => 'QR Code-cachevarighet (sekunder)',
    'QR Code Cache Duration' => 'QR Code-cachevarighet',
    'How long to cache generated QR codes (in seconds)' => 'Hvor lenge genererte QR-koder skal caches (i sekunder)',
    'Cache duration in seconds' => 'Cachevarighet i sekunder',
    'Min: 60 (1 minute), Max: 604800 (7 days)' => 'Min: 60 (1 minutt), Maks: 604800 (7 dager)',
    'Caching' => 'Caching',
    'Device Detection Caching' => 'Caching for enhetsregistrering',
    'Device Detection Cache Duration' => 'Cachevarighet for enhetsregistrering',
    'Device detection caching is only available when Analytics is enabled. Go to' => 'Caching for enhetsregistrering er bare tilgjengelig når analyse er aktivert. Gå til',
    'to enable analytics.' => 'for å aktivere analyse.',

    // =========================================================================
    // Export Settings
    // =========================================================================

    'Export Settings' => 'Eksportinnstillinger',
    'Analytics Export Options' => 'Eksportalternativer for analyse',
    'Include Disabled Links in Export' => 'Inkluder deaktiverte lenker i eksport',
    'Include Disabled {pluginName} in Export' => 'Inkluder deaktiverte {pluginName} i eksport',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'Når aktivert inkluderer analyseeksporter data fra deaktiverte {pluginName}',
    'Include Expired Links in Export' => 'Inkluder utgåtte lenker i eksport',
    'Include Expired {pluginName} in Export' => 'Inkluder utgåtte {pluginName} i eksport',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'Når aktivert inkluderer analyseeksporter data fra utgåtte {pluginName}',
    'Export as CSV' => 'Eksporter som CSV',

    // =========================================================================
    // Interface Settings
    // =========================================================================

    'Interface Settings' => 'Grensesnittinnstillinger',
    'Items Per Page' => 'Elementer per side',
    'Number of {pluginName} to show per page' => 'Antall {pluginName} som skal vises per side',
    'Allow Multiple' => 'Tillat flere',
    'Whether to allow multiple {pluginName} to be selected' => 'Om det skal tillates å velge flere {pluginName}',
    'The maximum number of {pluginName} that can be selected.' => 'Det maksimale antallet {pluginName} som kan velges.',
    'Which sources should be available to select {pluginName} from?' => 'Hvilke kilder skal være tilgjengelige for å velge {pluginName} fra?',

    // =========================================================================
    // Integration Settings
    // =========================================================================

    'Third-Party Integrations' => 'Tredjepartsintegrasjoner',
    'Integrations Settings' => 'Integrasjonsinnstillinger',
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'Integrer {pluginName} med tredjeparts analyse- og sporingstjenester for å sende klikkhendelser til Google Tag Manager, Google Analytics og andre plattformer.',
    '{pluginName} Integration' => '{pluginName}-integrasjon',
    'Installed & Active' => 'Installert og aktivt',
    'Installed but Disabled' => 'Installert men deaktivert',
    'Not Installed' => 'Ikke installert',
    'Install Plugin' => 'Installer plugin',
    'Push {smartLinksName} click events to Google Tag Manager and analytics platforms for tracking redirects, button clicks, and QR code scans.' => 'Send {smartLinksName}-klikkhendelser til Google Tag Manager og analyseplattformer for sporing av viderestillinger, knappeklikk og QR-kodeskanninger.',
    'Active Tracking Scripts' => 'Aktive sporingsskript',
    'Scripts receiving {pluginName} events' => 'Skript som mottar {pluginName}-hendelser',
    'Note' => 'Merk',
    'No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}.' => 'Ingen sporingsskript er for øyeblikket konfigurert i {pluginName}. Hendelser settes i kø, men sendes ikke før du konfigurerer GTM eller Google Analytics i {pluginName}.',
    'Configuration' => 'Konfigurasjon',
    'Tracking Events' => 'Sporingshendelser',
    'Select which events to send to {pluginName}' => 'Velg hvilke hendelser som skal sendes til {pluginName}',
    'Auto-Redirects' => 'Automatiske viderestillinger',
    'Mobile users automatically redirected' => 'Mobilbrukere viderestilles automatisk',
    'Button Clicks' => 'Knappeklikk',
    'Manual platform selection on landing page' => 'Manuelt plattformvalg på landingssiden',
    'QR Code Scans' => 'QR Code-skanninger',
    'QR code accessed via ?src=qr parameter' => 'QR-koden nås via parameteren ?src=qr',
    'Event Prefix' => 'Hendelsesprefiks',
    'Prefix for event names (e.g., \'smart_links_redirect\')' => 'Prefiks for hendelsesnavn (f.eks. \'smart_links_redirect\')',
    'Event Data Structure' => 'Hendelsesdatastruktur',
    'Click to view the data layer event format' => 'Klikk for å se datalagerhendelsesformatet',
    'How Events Are Sent' => 'Slik sendes hendelser',
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} sender hendelser kun til GTM eller GA4 dataLayer',
    'Only Google Tag Manager and Google Analytics 4 support the dataLayer format in SEOmatic' => 'Kun Google Tag Manager og Google Analytics 4 støtter dataLayer-formatet i SEOmatic',
    'Use GTM to forward to other platforms' => 'Bruk GTM for å videresende til andre plattformer',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'Konfigurer GTM-utløsere og tagger for å videresende {pluginName}-hendelser til Facebook Pixel, LinkedIn, HubSpot osv.',
    'Events are only sent when analytics tracking is enabled both globally and per-link' => 'Hendelser sendes kun når analysesporing er aktivert globalt og per lenke',
    'Architecture' => 'Arkitektur',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'Send {pluginName}-hendelser til SEOmatics Google Tag Manager-datalag for sporing i GTM og Google Analytics.',
    'Select which {pluginName} events to send to SEOmatic' => 'Velg hvilke {pluginName}-hendelser som skal sendes til SEOmatic',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'Fathom, Matomo og Plausible vises ovenfor, men mottar ikke hendelser direkte fra {pluginName}',
    // Redirect Manager Integration
    'Create permanent redirect records when {pluginName} slugs change. Provides centralized redirect management and analytics tracking.' => 'Opprett permanente viderestillingsposter når {pluginName}-sluger endres. Gir sentralisert viderestillingshåndtering og analysesporing.',
    'Creates permanent redirects when {pluginName} slugs change or links are deleted' => 'Oppretter permanente viderestillinger når {pluginName}-sluger endres eller lenker slettes',
    'Automatic Redirect Creation' => 'Automatisk opprettelse av viderestillinger',
    'Select which events should create permanent redirects in {pluginName}' => 'Velg hvilke hendelser som skal opprette permanente viderestillinger i {pluginName}',
    'Slug Changes' => 'Slug-endringer',
    'Change slug from <code>promo-2024</code> to <code>promo-2025</code> → Creates <code>/go/promo-2024</code> → <code>/go/promo-2025</code>' => 'Endre slug fra <code>promo-2024</code> til <code>promo-2025</code> → Oppretter <code>/go/promo-2024</code> → <code>/go/promo-2025</code>',
    'Benefits of This Integration' => 'Fordeler med denne integrasjonen',
    'Centralized Management' => 'Sentralisert administrasjon',
    'View and manage all redirects ({pluginName} + regular pages) in one place' => 'Vis og administrer alle viderestillinger ({pluginName} + vanlige sider) på ett sted',
    'Analytics Tracking' => 'Analysesporing',
    'See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries' => 'Se hvor mange som forsøker å nå slettede eller endrede {pluginName}, deres enheter, nettlesere og land',
    'Persistent Redirects' => 'Vedvarende viderestillinger',
    'Redirects persist even if {pluginName} is deleted, preventing broken links permanently' => 'Viderestillinger bevares selv om {pluginName} slettes, og forhindrer ødelagte lenker permanent',
    'Source Tracking' => 'Kildesporing',
    '{rmPluginName} shows which plugin created each redirect for better organization' => '{rmPluginName} viser hvilket plugin som opprettet hver viderestilling for bedre organisering',
    'Enabled Integrations' => 'Aktiverte integrasjoner',
    // SmartLinkType (Link field integration)
    '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.' => '{pluginName} er ikke aktivert for nettstedet "{site}". Aktiver det i plugin-innstillingene for å bruke {pluginNameLower} her.',
    'Invalid {pluginName} format.' => 'Ugyldig {pluginName}-format.',
    '{pluginName} not found.' => '{pluginName} ble ikke funnet.',

    // =========================================================================
    // Smart Link Fields (edit page)
    // =========================================================================

    'Title' => 'Tittel',
    'The title of this {singularName}' => 'Tittelen på denne {singularName}',
    'Description' => 'Beskrivelse',
    'A brief description of this {singularName}' => 'En kort beskrivelse av denne {singularName}',
    'Icon' => 'Ikon',
    'Icon identifier or URL for this {singularName}' => 'Ikonidentifikator eller URL for denne {singularName}',
    'Image' => 'Bilde',
    'Select an image for this {singularName}' => 'Velg et bilde for denne {singularName}',
    'Image Size' => 'Bildestørrelse',
    'Select the size for the {singularName} image' => 'Velg størrelsen på {singularName}-bildet',
    'Hide Title on Landing Pages' => 'Skjul tittel på landingssider',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'Skjul {singularName}-tittelen på både viderestillings- og QR Code-landingssider',
    'Display Settings' => 'Visningsinnstillinger',
    'Advanced Settings' => 'Avanserte innstillinger',
    'Destination URL' => 'Destinations-URL',
    'Last Destination URL' => 'Siste destinations-URL',
    'Fallback URL' => 'Reserve-URL',
    'The URL to redirect to when no platform-specific URL is available' => 'URL-en det skal viderestilles til når ingen plattformspesifikk URL er tilgjengelig',
    'iOS URL' => 'iOS URL',
    'App Store URL for iOS devices' => 'App Store-URL for iOS-enheter',
    'Android URL' => 'Android URL',
    'Google Play Store URL for Android devices' => 'Google Play Store-URL for Android-enheter',
    'Huawei URL' => 'Huawei URL',
    'AppGallery URL for Huawei devices' => 'AppGallery-URL for Huawei-enheter',
    'Amazon URL' => 'Amazon URL',
    'Amazon Appstore URL' => 'Amazon Appstore-URL',
    'Windows URL' => 'Windows URL',
    'Microsoft Store URL for Windows devices' => 'Microsoft Store-URL for Windows-enheter',
    'Mac URL' => 'Mac URL',
    'Mac App Store URL' => 'Mac App Store-URL',
    'App Store URLs' => 'App Store-URL-er',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'Angi butikk-URL-er for hver plattform. Systemet vil automatisk viderestille brukere til riktig butikk basert på enheten deres.',
    '{pluginName} URL' => '{pluginName}-URL',
    'URL copied to clipboard' => 'URL kopiert til utklippstavlen',
    'New {singularName}' => 'Ny {singularName}',

    // =========================================================================
    // Field Layout
    // =========================================================================

    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'Legg til tilpassede felt i {singularName}-elementer. Alle felt du legger til her vises på redigeringsskjermen for {singularName}.',
    'No field layout available.' => 'Ingen feltoppsett tilgjengelig.',

    // =========================================================================
    // Smart Link Element — Index & Actions
    // =========================================================================

    'Slug' => 'Slug',
    'Redirect Page' => 'Viderestillingsside',
    'All {pluginName}' => 'Alle {pluginName}',
    'New {name}' => 'Ny {name}',
    'Are you sure you want to delete the selected smart links?' => 'Er du sikker på at du vil slette de valgte smart links?',
    'Smart links deleted.' => 'Smart links slettet.',
    'Smart links restored.' => 'Smart links gjenopprettet.',
    'Some smart links restored.' => 'Noen smart links gjenopprettet.',
    'Smart links not restored.' => 'Smart links kunne ikke gjenopprettes.',
    'Add a smart link' => 'Legg til en smart link',
    'No smart links selected' => 'Ingen smart links valgt',
    'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.' => 'Du kan bare velge opptil {limit} {limit, plural, =1{smart link} other{smart links}}.',
    'Create a new smart link' => 'Opprett en ny smart link',

    // =========================================================================
    // Analytics Dashboard — Overview Tab
    // =========================================================================

    'View Analytics' => 'Vis analyse',
    'Traffic Overview' => 'Trafikkoverblick',
    'Traffic & Devices' => 'Trafikk og enheter',
    'Geographic' => 'Geografisk',
    'Total Links' => 'Totalt antall lenker',
    'Active Links' => 'Aktive lenker',
    'Total Clicks' => 'Totalt antall klikk',
    'total clicks' => 'totalt antall klikk',
    'Clicks' => 'Klikk',
    'Unique Visitors' => 'Unike besøkende',
    'Total Interactions' => 'Totalt antall interaksjoner',
    'Avg. Clicks/Day' => 'Gjennomsn. klikk/dag',
    'Avg. Interactions/Day' => 'Gjennomsn. interaksjoner/dag',
    'Engagement Rate' => 'Engasjementsrate',
    'Top {pluginName} (Top 20)' => 'Topp {pluginName} (Topp 20)',
    'Latest Interactions (Top 20)' => 'Nyeste interaksjoner (Topp 20)',
    'Interactions (Last 20)' => 'Interaksjoner (Siste 20)',
    'No analytics data yet' => 'Ingen analysedata ennå',
    'Analytics will appear here once your {singularName} starts receiving clicks.' => 'Analyse vises her når din {singularName} begynner å motta klikk.',
    'Failed to load analytics data' => 'Kunne ikke laste analysedata',
    'Failed to load countries data' => 'Kunne ikke laste landsdata',
    'No data for selected period' => 'Ingen data for valgt periode',

    // =========================================================================
    // Analytics Dashboard — Traffic & Devices Tab
    // =========================================================================

    'Device Analytics' => 'Enhetsanalyse',
    'Device Types' => 'Enhetstyper',
    'Device Brands' => 'Enhetsmerkevarer',
    'Operating Systems' => 'Operativsystemer',
    'Browser Usage' => 'Nettleserbruk',
    'Usage Patterns' => 'Bruksmønstre',
    'Peak Usage Hours' => 'Topptimer for bruk',
    'Peak usage at {hour}' => 'Toppbruk ved {hour}',
    'Daily Clicks' => 'Daglige klikk',

    // =========================================================================
    // Analytics Dashboard — Geographic Tab
    // =========================================================================

    'Top Countries' => 'Toppland',
    'Top Cities' => 'Toppbyer',
    'Top Cities Worldwide' => 'Toppbyer verden over',
    'No country data available' => 'Ingen landsdata tilgjengelig',
    'No city data available' => 'Ingen bydata tilgjengelig',
    'Geographic detection is disabled.' => 'Geografisk registrering er deaktivert.',
    'Enable in Settings' => 'Aktiver i innstillinger',

    // =========================================================================
    // Analytics Data — Table Columns & Labels
    // =========================================================================

    'Date' => 'Dato',
    'Time' => 'Tid',
    'Device' => 'Enhet',
    'Location' => 'Posisjon',
    'Country' => 'Land',
    'Countries' => 'Land',
    'City' => 'By',
    'Site' => 'Nettsted',
    'Source' => 'Kilde',
    'Type' => 'Type',
    'OS' => 'OS',
    'Operating System' => 'Operativsystem',
    'Browser' => 'Nettleser',
    'Interactions' => 'Interaksjoner',
    'Latest Interactions' => 'Nyeste interaksjoner',
    'No interactions recorded yet' => 'Ingen interaksjoner registrert ennå',
    'Last Interaction' => 'Siste interaksjon',
    'Last Interaction Type' => 'Siste interaksjonstype',
    'Last Click' => 'Siste klikk',
    'Device information not available' => 'Enhetsinformasjon ikke tilgjengelig',
    'OS information not available' => 'OS-informasjon ikke tilgjengelig',
    'Name' => 'Navn',
    'Percentage' => 'Prosent',

    // =========================================================================
    // Analytics Dashboard — JS strings (passed to JavaScript)
    // =========================================================================

    'No interaction data available for the selected filters.' => 'Ingen interaksjonsdata tilgjengelig for de valgte filtrene.',
    'No device data available for the selected filters.' => 'Ingen enhetsdata tilgjengelig for de valgte filtrene.',
    'No device brand data available for the selected filters.' => 'Ingen enhetsmerkedata tilgjengelig for de valgte filtrene.',
    'No OS data available for the selected filters.' => 'Ingen OS-data tilgjengelig for de valgte filtrene.',
    'No browser data available for the selected filters.' => 'Ingen nettleserdata tilgjengelig for de valgte filtrene.',
    'No hourly data available for the selected filters.' => 'Ingen timedata tilgjengelig for de valgte filtrene.',
    'Peak usage at' => 'Toppbruk ved',

    // =========================================================================
    // Interaction Types
    // =========================================================================

    'Direct' => 'Direkte',
    'Direct Visits' => 'Direkte besøk',
    'QR' => 'QR',
    'QR Scans' => 'QR-skanninger',
    'Button' => 'Knapp',
    'Landing' => 'Landing',

    // =========================================================================
    // Analytics Export — CSV/Excel Column Headers
    // =========================================================================

    'Date/Time' => 'Dato/tid',
    'Status' => 'Status',
    'Smart Link URL' => 'Smart Link URL',
    'Referrer' => 'Referrer',
    'Device Type' => 'Enhetstype',
    'Device Brand' => 'Enhetsmerkevare',
    'Device Model' => 'Enhetsmodell',
    'OS Version' => 'OS-versjon',
    'Browser Version' => 'Nettleserversjon',
    'Language' => 'Språk',
    'User Agent' => 'User Agent',

    // =========================================================================
    // Time Periods
    // =========================================================================

    'Today' => 'I dag',
    'Yesterday' => 'I går',
    'Last 7 days' => 'Siste 7 dager',
    'Last 30 days' => 'Siste 30 dager',
    'Last 90 days' => 'Siste 90 dager',
    'All time' => 'All tid',
    'Date Range' => 'Datoperiode',

    // =========================================================================
    // Utilities
    // =========================================================================

    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'Overvåk lenkeytelse, spor analyse og administrer cache for dine {singularName}-viderestillinger og QR-koder.',
    'Active {pluginName}' => 'Aktive {pluginName}',
    'Links Status' => 'Lenkestatus',
    'Total {pluginName}' => 'Totalt {pluginName}',
    'Performance' => 'Ytelse',
    'Total interactions tracked' => 'Totalt antall sporede interaksjoner',
    'Redirects' => 'Viderestillinger',
    'QR Codes' => 'QR-koder',
    'Devices' => 'Enheter',
    'Cache Status' => 'Cache-status',
    'Total cached entries' => 'Totalt antall cachede oppføringer',
    'Active' => 'Aktiv',
    'Pending' => 'Venter',
    'Expired' => 'Utgått',
    'Disabled' => 'Deaktivert',
    'Navigation' => 'Navigasjon',
    'Access main plugin sections' => 'Åpne plugin-programmets hoveddeler',
    'Manage {pluginName}' => 'Administrer {pluginName}',
    'View Settings' => 'Vis innstillinger',
    'Cache Management' => 'Cache-administrasjon',
    'Clear cached data to force regeneration. Useful after changing QR code settings or when troubleshooting.' => 'Tøm cachet data for å tvinge regenerering. Nyttig etter endring av QR Code-innstillinger eller ved feilsøking.',
    'Clear QR Cache' => 'Tøm QR-cache',
    'Clear Device Cache' => 'Tøm enhetscache',
    'Clear All Caches' => 'Tøm alle cacher',
    'Analytics Data Management' => 'Administrasjon av analysedata',
    'Permanently delete all analytics tracking data. This action cannot be undone!' => 'Slett alle analysedata permanent. Denne handlingen kan ikke angres!',
    'Clear All Analytics' => 'Tøm all analyse',
    'Are you sure you want to permanently delete ALL analytics data? This action cannot be undone!' => 'Er du sikker på at du vil slette ALL analysedata permanent? Denne handlingen kan ikke angres!',
    'This will delete all click tracking data and reset all click counts. Are you absolutely sure?' => 'Dette sletter alle klikksporingsdata og tilbakestiller alle klikktellere. Er du helt sikker?',
    'Failed to clear QR cache' => 'Kunne ikke tømme QR-cache',
    'Failed to clear device cache' => 'Kunne ikke tømme enhetscache',
    'Failed to clear caches' => 'Kunne ikke tømme cacher',
    'Failed to clear analytics' => 'Kunne ikke tømme analyse',

    // =========================================================================
    // Widgets — Analytics Summary
    // =========================================================================

    '{pluginName} - Analytics' => '{pluginName} – Analyse',
    'Top Performer' => 'Best presterende',
    'interactions' => 'interaksjoner',
    'View full analytics' => 'Vis full analyse',
    'You don\'t have permission to view analytics.' => 'Du har ikke tillatelse til å se analyse.',
    'Analytics are disabled in plugin settings.' => 'Analyse er deaktivert i plugin-innstillingene.',

    // =========================================================================
    // Widgets — Top Links
    // =========================================================================

    '{pluginName} - Top Links' => '{pluginName} – Topplenker',
    'Link' => 'Lenke',
    'Number of Links' => 'Antall lenker',
    'How many top links to display (1-20)' => 'Hvor mange topplenker som skal vises (1–20)',
    'View all {pluginName}' => 'Vis alle {pluginName}',
    'No {pluginName} yet' => 'Ingen {pluginName} ennå',
    'Create your first {singularName} to see it here.' => 'Opprett din første {singularName} for å se den her.',

    // =========================================================================
    // Public Templates — Redirect Page (redirect.twig)
    // =========================================================================

    'App Store' => 'App Store',
    'Google Play' => 'Google Play',
    'AppGallery' => 'AppGallery',
    'Amazon' => 'Amazon',
    'Windows Store' => 'Windows Store',
    'Mac App Store' => 'Mac App Store',
    'Continue to Website' => 'Fortsett til nettstedet',

    // =========================================================================
    // Public Templates — QR Code Page (qr.twig)
    // =========================================================================

    'Scan with your phone\'s camera to download' => 'Skann med telefonens kamera for å laste ned',

    // =========================================================================
    // Controller Messages — Flash Notices & Errors
    // =========================================================================

    // SmartlinksController
    'Smart link saved.' => 'Smart link lagret.',
    'Couldn\'t save smart link.' => 'Kunne ikke lagre smart link.',
    'Error saving smart link: {error}' => 'Feil ved lagring av smart link: {error}',
    'Could not save smart link.' => 'Kunne ikke lagre smart link.',
    'Smart link deleted.' => 'Smart link slettet.',
    'Couldn\'t delete smart link.' => 'Kunne ikke slette smart link.',
    'Smart link restored.' => 'Smart link gjenopprettet.',
    'Couldn\'t restore smart link.' => 'Kunne ikke gjenopprette smart link.',
    'Smart link permanently deleted.' => 'Smart link permanent slettet.',
    'Couldn\'t delete smart link permanently.' => 'Kunne ikke slette smart link permanent.',
    'Smart link not found' => 'Smart link ikke funnet',
    'Cannot edit trashed smart links.' => 'Kan ikke redigere papirkurv-plasserte smart links.',
    'Failed to generate QR code.' => 'Kunne ikke generere QR-kode.',
    // SettingsController
    'Settings saved.' => 'Innstillinger lagret.',
    'Couldn\'t save settings.' => 'Kunne ikke lagre innstillinger.',
    'Field layout saved.' => 'Feltoppsett lagret.',
    'Couldn\'t save field layout.' => 'Kunne ikke lagre feltoppsett.',
    'Analytics cleanup job has been queued. It will run in the background.' => 'Oppryddingsjobb for analyse er lagt i kø. Den kjører i bakgrunnen.',
    'QR code cache cleared successfully.' => 'QR-kodecache tømt.',
    'Cleared {count} QR code caches.' => '{count} QR-kodecacher tømt.',
    'Device cache cleared successfully.' => 'Enhetscache tømt.',
    'Cleared {count} device detection caches.' => '{count} enhetsregistreringscacher tømt.',
    'All caches cleared successfully.' => 'Alle cacher tømt.',
    'Cleared {count} cache entries.' => '{count} cache-oppføringer tømt.',
    'Cleared {count} analytics records and reset all click counts.' => '{count} analyseposter tømt og alle klikktellere tilbakestilt.',
    'An unexpected error occurred.' => 'En uventet feil oppstod.',
    // AnalyticsController
    'No analytics data to export.' => 'Ingen analysedata å eksportere.',
    // JS notices
    'Enter custom size (100-4096 pixels):' => 'Angi tilpasset størrelse (100–4096 piksler):',
    'Please enter a valid size between 100 and 4096 pixels' => 'Angi en gyldig størrelse mellom 100 og 4096 piksler',
    'Reset QR code settings to plugin defaults?' => 'Tilbakestille QR Code-innstillinger til plugin-standarder?',

    // =========================================================================
    // Job Messages
    // =========================================================================

    '{pluginName}: Cleaning up old analytics' => '{pluginName}: Rydder opp gammel analysedata',
    'Deleting {count} old analytics records' => 'Sletter {count} gamle analyseposter',
    'Deleted {deleted} of {total} records' => 'Slettet {deleted} av {total} poster',

    // =========================================================================
    // Validation Messages
    // =========================================================================

    'Only letters, numbers, hyphens, and underscores are allowed.' => 'Kun bokstaver, tall, bindestreker og understreker er tillatt.',
    'Only letters, numbers, hyphens, underscores, and slashes are allowed.' => 'Kun bokstaver, tall, bindestreker, understreker og skråstreker er tillatt.',
    'Only lowercase letters, numbers, and underscores are allowed.' => 'Kun små bokstaver, tall og understreker er tillatt.',
    '{attribute} should only contain letters, numbers, underscores, and hyphens.' => '{attribute} bør kun inneholde bokstaver, tall, understreker og bindestreker.',
    'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'Slug-prefiks "{prefix}" er i konflikt med: {conflicts}. Forslag: {suggestions}',
    'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr' => 'QR-prefiks kan ikke være det samme som slug-prefikset ditt. Prøv: qr, code, qrc, eller {slug}/qr',
    'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"' => 'Nestet QR-prefiks må starte med slug-prefikset ditt "{slug}". Bruk: {slug}/{qr} eller frittstående som "qr"',
    'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'QR-prefiks "{prefix}" er i konflikt med: {conflicts}. Forslag: {suggestions}',
    'Smart link base URL must start with http:// or https://' => 'Smart link basis-URL må starte med http:// eller https://',
    'Smart link base URL cannot contain spaces.' => 'Smart link basis-URL kan ikke inneholde mellomrom.',
    'Unsupported token in smart link base URL. Supported tokens: {siteHandle}, {siteId}, {siteUid}.' => 'Token som ikke støttes i smart link basis-URL. Støttede tokens: {siteHandle}, {siteId}, {siteUid}.',

    // =========================================================================
    // Config Override Warnings
    // =========================================================================

    'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>pluginName</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enableAnalytics</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>analyticsRetention</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>includeDisabledInExport</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>includeExpiredInExport</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>defaultQrSize</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>defaultQrColor</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>defaultQrBgColor</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>defaultQrFormat</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrCodeCacheDuration</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>defaultQrErrorCorrection</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>defaultQrMargin</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrModuleStyle</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrEyeStyle</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrEyeColor</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enableQrLogo</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrLogoVolumeUid</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>imageVolumeUid</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrLogoSize</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enableQrDownload</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrDownloadFilename</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>redirectTemplate</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>qrTemplate</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enableGeoDetection</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>cacheDeviceDetection</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>deviceDetectionCacheDuration</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>languageDetectionMethod</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>itemsPerPage</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>notFoundRedirectUrl</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enabledSites</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledIntegrations</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enabledIntegrations</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticTrackingEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>seomaticTrackingEvents</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticEventPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>seomaticEventPrefix</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheStorageMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>cacheStorageMethod</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrCodeCache</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>enableQrCodeCache</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>anonymizeIpAddress</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>anonymizeIpAddress</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectManagerEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>redirectManagerEvents</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>logLevel</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>logLevel</code> i <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>smartlinkBaseUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'Dette overstyres av innstillingen <code>smartlinkBaseUrl</code> i <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // General Interface
    // =========================================================================

    'Save Settings' => 'Lagre innstillinger',
    'Actions' => 'Handlinger',
    'Loading...' => 'Laster...',
    'Error' => 'Feil',

    // =========================================================================
    // Behavior Settings — Select Options
    // =========================================================================

    'Browser preference' => 'Nettleserpreferanse',
    'IP geolocation' => 'IP-geolokalisering',
    'Both' => 'Begge',

    // =========================================================================
    // General Settings — URL Tips (Redirect Manager integration)
    // =========================================================================

    'Changing will break existing URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard)' => 'Endring vil ødelegge eksisterende URL-er. Opprett en wildcard-viderestilling i {redirectPluginName} for å migrere: Kilde \'/old/*\' → Destinasjon \'/new/$1\' (Matchtype: Wildcard)',
    'Changing will break existing QR URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard). Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns.' => 'Endring vil ødelegge eksisterende QR-URL-er. Opprett en wildcard-viderestilling i {redirectPluginName} for å migrere: Kilde \'/old/*\' → Destinasjon \'/new/$1\' (Matchtype: Wildcard). Støtter frittstående (f.eks. \'qr\') eller nestete (f.eks. \'go/qr\') mønstre.',
    'Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns. Checked for conflicts with ShortLink Manager.' => 'Støtter frittstående (f.eks. \'qr\') eller nestete (f.eks. \'go/qr\') mønstre. Kontrollert for konflikter med ShortLink Manager.',

    // =========================================================================
    // QR Code Settings — Select Options
    // =========================================================================

    'Square' => 'Kvadrat',
    'Rounded' => 'Avrundet',
    'Dots' => 'Punkter',
    'Leaf' => 'Blad',
    'Low (~7% correction)' => 'Lav (~7 % korreksjon)',
    'Medium (~15% correction)' => 'Medium (~15 % korreksjon)',
    'Quartile (~25% correction)' => 'Kvartil (~25 % korreksjon)',
    'High (~30% correction)' => 'Høy (~30 % korreksjon)',
    'Failed to generate preview' => 'Kunne ikke generere forhåndsvisning',

    // =========================================================================
    // Smart Link Fields — Image Size Options
    // =========================================================================

    'Extra Large' => 'Ekstra stor',
    'Large' => 'Stor',
    'Medium' => 'Medium',
    'Small' => 'Liten',

    // =========================================================================
    // Smart Link Field Input — Tooltip
    // =========================================================================

    'Clicks:' => 'Klikk:',

    // =========================================================================
    // Cache Settings — Info Boxes & Durations
    // =========================================================================

    'Cache Location' => 'Cache-plassering',
    'Using Craft\'s configured Redis cache from <code>config/app.php</code>' => 'Bruker Crafts konfigurerte Redis-cache fra <code>config/app.php</code>',
    'Redis Not Configured' => 'Redis ikke konfigurert',
    'To use Redis caching, install <code>yiisoft/yii2-redis</code> and configure it in <code>config/app.php</code>.' => 'For å bruke Redis-caching, installer <code>yiisoft/yii2-redis</code> og konfigurer det i <code>config/app.php</code>.',
    'How it works' => 'Slik fungerer det',
    'Device detection parses user-agent strings to identify devices, browsers, and operating systems' => 'Enhetsregistrering analyserer user-agent-strenger for å identifisere enheter, nettlesere og operativsystemer',
    'Results are cached to avoid re-parsing the same user-agent repeatedly' => 'Resultater caches for å unngå gjentatt analyse av den samme user-agenten',
    'Recommended to keep enabled for production sites' => 'Anbefales å holde aktivert for produksjonsnettsteder',
    'Cache duration in seconds. Current:' => 'Cachevarighet i sekunder. Gjeldende:',

    // =========================================================================
    // Time Unit Strings (for JS secondsToHuman)
    // =========================================================================

    '{count} second' => '{count} sekund',
    '{count} seconds' => '{count} sekunder',
    '{count} minute' => '{count} minutt',
    '{count} minutes' => '{count} minutter',
    '{count} hour' => '{count} time',
    '{count} hours' => '{count} timer',
    '{count} day' => '{count} dag',
    '{count} days' => '{count} dager',

    // =========================================================================
    // Template Settings — Copy hints
    // =========================================================================

    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> to <code>templates/smartlink-manager/redirect.twig</code>' => 'Påkrevd: kopier <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> til <code>templates/smartlink-manager/redirect.twig</code>',
    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> to <code>templates/smartlink-manager/qr.twig</code>' => 'Påkrevd: kopier <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> til <code>templates/smartlink-manager/qr.twig</code>',

    // =========================================================================
    // Import/Export
    // =========================================================================

    'Manage import/export' => 'Administrer import/export',
    'Import links' => 'Importer lenker',
    'Export links' => 'Eksporter lenker',
    'Clear import history' => 'Tøm importhistorikk',
    'Export Smart Links' => 'Eksporter Smart Links',
    'Export All Smart Links as CSV' => 'Eksporter alle Smart Links som CSV',
    'Import Smart Links' => 'Importer Smart Links',
    'You do not have permission to export smart links.' => 'Du har ikke tillatelse til å eksportere smart links.',
    'You do not have permission to import smart links.' => 'Du har ikke tillatelse til å importere smart links.',
    'Download all your current smart links as a CSV file for backup or migration to another site.' => 'Last ned alle dine nåværende smart links som en CSV-fil for sikkerhetskopiering eller migrering til et annet nettsted.',
    'Import smart links from CSV. You\'ll map columns and preview before importing.' => 'Importer smart links fra CSV. Du kartlegger kolonner og forhåndsviser før import.',
    'Select a CSV file to import smart links' => 'Velg en CSV-fil for å importere smart links',
    'No smart links to export.' => 'Ingen smart links å eksportere.',
    'Map your CSV columns to smart link fields. Required fields must be mapped.' => 'Kart dine CSV-kolonner til smart link-felt. Påkrevde felt må kartlegges.',
    'Valid Smart Links to Import' => 'Gyldige Smart Links å importere',
    'No valid smart links found to import.' => 'Ingen gyldige smart links funnet for import.',
    'Import {count} Smart Links' => 'Importer {count} Smart Links',
    'No Valid Smart Links to Import' => 'Ingen gyldige Smart Links å importere',
    'Click the button below to import {count} valid smart link(s).' => 'Klikk på knappen nedenfor for å importere {count} gyldig(e) smart link(s).',
    'Import completed: {imported} smart links imported.' => 'Import fullført: {imported} smart links importert.',
    'Import completed: {imported} imported, {failed} failed.' => 'Import fullført: {imported} importert, {failed} mislyktes.',
    'Import completed: {imported} {pluginName} imported.' => 'Import fullført: {imported} {pluginName} importert.',
    'Import completed: {imported} {pluginName} imported, {failed} failed.' => 'Import fullført: {imported} {pluginName} importert, {failed} mislyktes.',
    'Failed to clear import history.' => 'Kunne ikke tømme importhistorikk.',
    'Slug must be mapped.' => 'Slug må kartlegges.',
    'Slug (required)' => 'Slug (påkrevd)',
    'Fallback URL (required)' => 'Reserve-URL (påkrevd)',
    'Image Asset ID' => 'Bilderessurs-ID',
    'Image Size (xl/lg/md/sm)' => 'Bildestørrelse (xl/lg/md/sm)',
    'QR Enabled (1/0)' => 'QR aktivert (1/0)',
    'QR Size' => 'QR-størrelse',
    'QR Color (#RRGGBB)' => 'QR-farge (#RRGGBB)',
    'QR Background (#RRGGBB)' => 'QR-bakgrunn (#RRGGBB)',
    'QR Eye Color (#RRGGBB)' => 'QR-øyefarge (#RRGGBB)',
    'QR Format (png/svg)' => 'QR-format (png/svg)',
    'QR Logo Asset ID' => 'QR-logoressurs-ID',
    'Hide Title (1/0)' => 'Skjul tittel (1/0)',
    'Language Detection (1/0)' => 'Språkregistrering (1/0)',
    'Metadata (JSON)' => 'Metadata (JSON)',

    // Import/Export — Controller messages
    'Unknown' => 'Ukjent',
    'Please select a CSV file to upload.' => 'Velg en CSV-fil å laste opp.',
    'Failed to parse CSV: {error}' => 'Kunne ikke tolke CSV: {error}',
    'No import data found. Please upload a CSV file.' => 'Ingen importdata funnet. Last opp en CSV-fil.',
    'No preview data found. Please map columns first.' => 'Ingen forhåndsvisningsdata funnet. Kartlegg kolonner først.',
    'Import session expired. Please upload the file again.' => 'Importøkt utløpt. Last opp filen igjen.',

    // Import/Export — Template UI
    'Import History' => 'Importhistorikk',
    'CSV Format' => 'CSV-format',
    'Required columns:' => 'Påkrevde kolonner:',
    'Optional columns:' => 'Valgfrie kolonner:',
    'Import from CSV' => 'Importer fra CSV',
    'CSV File' => 'CSV-fil',
    'CSV Delimiter' => 'CSV-skilletegn',
    'Character used to separate values in your CSV (auto-detect is default)' => 'Tegn som brukes til å skille verdier i din CSV (automatisk registrering er standard)',
    'Auto (detect)' => 'Auto (registrer)',
    'Comma (,)' => 'Komma (,)',
    'Semicolon (;)' => 'Semikolon (;)',
    'Tab' => 'Tabulator',
    'Pipe (|)' => 'Pipe (|)',
    'The maximum file size is {size} and the import is limited to {rows} rows per file.' => 'Maksimal filstørrelse er {size} og importen er begrenset til {rows} rader per fil.',
    'Upload & Map Columns' => 'Last opp og kartlegg kolonner',
    'Clear history' => 'Tøm historikk',
    'No import history yet.' => 'Ingen importhistorikk ennå.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'Er du sikker på at du vil tømme alle importlogger? Denne handlingen kan ikke angres.',
    'Failed to clear history.' => 'Kunne ikke tømme historikk.',
    'Map CSV Columns' => 'Kartlegg CSV-kolonner',
    'Your CSV has {count} rows. Map each CSV column to a smart link field.' => 'Din CSV har {count} rader. Kartlegg hver CSV-kolonne til et smart link-felt.',
    'Preview of CSV Data' => 'Forhåndsvisning av CSV-data',
    'Showing first 5 rows. {total} total rows will be imported.' => 'Viser de første 5 radene. Totalt {total} rader vil bli importert.',
    'Column Mapping' => 'Kolonnekartlegging',
    'Note: only columns mapped to a field will be imported.' => 'Merk: kun kolonner kartlagt til et felt importeres.',
    '-- Do not import --' => '-- Ikke importer --',
    'Enabled (1/0)' => 'Aktivert (1/0)',
    'Site ID' => 'Nettsted-ID',
    'Site Handle' => 'Nettstedshåndtak',
    'Track Analytics (1/0)' => 'Spor analyse (1/0)',
    'Post Date (YYYY-MM-DD HH:MM:SS)' => 'Publiseringsdato (ÅÅÅÅ-MM-DD TT:MM:SS)',
    'Date Expired (YYYY-MM-DD HH:MM:SS)' => 'Utløpsdato (ÅÅÅÅ-MM-DD TT:MM:SS)',
    'CSV Column' => 'CSV-kolonne',
    'Maps to Field' => 'Kartlegges til felt',
    'Sample Data' => 'Eksempeldata',
    'Map Columns' => 'Kartlegg kolonner',
    'Cancel' => 'Avbryt',
    'Preview Import' => 'Forhåndsvis import',
    'Import Preview' => 'Importforhåndsvisning',
    'Total Rows' => 'Totalt antall rader',
    'Valid' => 'Gyldig',
    'Duplicates' => 'Duplikater',
    'Errors' => 'Feil',
    'Duplicates (will be skipped)' => 'Duplikater (hoppes over)',
    'Invalid Rows (will be skipped)' => 'Ugyldige rader (hoppes over)',
    'Row' => 'Rad',
    'Reason' => 'Årsak',
    'Image ID' => 'Bilde-ID',
    'Ready to Import' => 'Klar til import',

    // Base partial: import-history
    'Created By' => 'Opprettet av',
    'Filename' => 'Filnavn',
    'Imported' => 'Importert',
    'Failed' => 'Mislyktes',

    // Analytics partial
    'Device Breakdown' => 'Enhetsfordeling',

];
