<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

return [

    // =========================================================================
    // Plugin Meta
    // =========================================================================

    'SmartLink Manager' => 'SmartLink Manager',
    'Manage smart links, route users by device, and track engagement from one control panel workspace.' => 'إدارة الروابط الذكية وتوجيه المستخدمين حسب الجهاز وتتبع التفاعل من مساحة عمل واحدة في لوحة التحكم.',
    'Open SmartLink Manager' => 'فتح SmartLink Manager',
    '{name} plugin loaded' => 'تم تحميل إضافة {name}',
    '{displayName} caches' => 'ذاكرة تخزين {displayName} المؤقتة',

    // =========================================================================
    // Element Names
    // =========================================================================

    'Smart Link' => 'رابط ذكي',
    'smart link' => 'رابط ذكي',
    'smart links' => 'روابط ذكية',
    'New smart link' => 'رابط ذكي جديد',

    // =========================================================================
    // Permissions
    // =========================================================================

    'Manage {plural}' => 'إدارة {plural}',
    'Create {plural}' => 'إنشاء {plural}',
    'Edit {plural}' => 'تعديل {plural}',
    'Delete {plural}' => 'حذف {plural}',
    'View analytics' => 'عرض التحليلات',
    'Export analytics' => 'تصدير التحليلات',
    'Clear analytics' => 'مسح التحليلات',
    'Clear cache' => 'مسح ذاكرة التخزين المؤقت',
    'View logs' => 'عرض السجلات',
    'View system logs' => 'عرض سجلات النظام',
    'Download system logs' => 'تحميل سجلات النظام',
    'Manage settings' => 'إدارة الإعدادات',

    // =========================================================================
    // Navigation & Breadcrumbs
    // =========================================================================

    'Links' => 'الروابط',
    'Analytics' => 'التحليلات',
    'Logs' => 'السجلات',
    'Settings' => 'الإعدادات',
    'General' => 'عام',
    'QR Code' => 'QR Code',
    'Redirect' => 'إعادة التوجيه',
    'Export' => 'تصدير',
    'Advanced' => 'متقدم',
    'Interface' => 'الواجهة',
    'Behavior' => 'السلوك',
    'Integrations' => 'التكاملات',
    'Cache' => 'Cache',
    'Field Layout' => 'تخطيط الحقول',
    'Overview' => 'نظرة عامة',
    'Import/Export' => 'استيراد/تصدير',

    // =========================================================================
    // General Settings
    // =========================================================================

    'General Settings' => 'الإعدادات العامة',
    'Plugin Name' => 'اسم الإضافة',
    'The name of the plugin as it appears in the Control Panel menu' => 'اسم الإضافة كما يظهر في قائمة لوحة التحكم',
    'Plugin Settings' => 'إعدادات الإضافة',
    'Log Level' => 'مستوى السجل',
    'Choose what types of messages to log. Debug level requires devMode to be enabled.' => 'اختر أنواع الرسائل المراد تسجيلها. يتطلب مستوى Debug تفعيل devMode.',
    'Error (Critical errors only)' => 'خطأ (الأخطاء الحرجة فقط)',
    'Warning (Errors and warnings)' => 'تحذير (الأخطاء والتحذيرات)',
    'Info (General information)' => 'معلومات (معلومات عامة)',
    'Debug (Detailed debugging)' => 'Debug (تشخيص تفصيلي)',
    'Logging Settings' => 'إعدادات التسجيل',

    // Logs viewer (logging-library)
    'All Levels' => 'جميع المستويات',
    'Info' => 'Info',
    'Debug' => 'Debug',
    'Select File' => 'اختيار ملف',
    'Select Date' => 'اختيار تاريخ',
    'All Sources' => 'جميع المصادر',
    'Search messages and context...' => 'البحث في الرسائل والسياق...',
    'System Logs' => 'سجلات النظام',
    'System' => 'النظام',
    'Current log level' => 'مستوى السجل الحالي',
    'No log files found. Log files are created when plugin activities occur.' => 'لم يتم العثور على ملفات سجل. يتم إنشاء ملفات السجل عند حدوث أنشطة الإضافة.',
    'No log entries found for the selected filters.' => 'لم يتم العثور على إدخالات سجل للمرشحات المحددة.',
    'No context data available.' => 'لا تتوفر بيانات سياقية.',
    'Level' => 'المستوى',
    'User' => 'المستخدم',
    'Message' => 'الرسالة',
    'entry' => 'إدخال',
    'entries' => 'إدخالات',
    'Available Logs' => 'السجلات المتاحة',
    'Current File' => 'الملف الحالي',
    'Download File' => 'تنزيل الملف',
    'Log Location' => 'موقع السجل',
    'Current Level' => 'المستوى الحالي',
    'Retention' => 'الاحتفاظ',
    'days' => 'أيام',
    'Context' => 'السياق',
    'Entries' => 'الإدخالات',
    'file' => 'ملف',
    'files' => 'ملفات',

    // =========================================================================
    // Site Settings
    // =========================================================================

    'Site Settings' => 'إعدادات الموقع',
    'Enabled Sites' => 'المواقع المُفعَّلة',
    'Select which sites {pluginName} should be enabled for. Leave empty to enable for all sites.' => 'اختر المواقع التي يجب تفعيل {pluginName} فيها. اتركها فارغة للتفعيل في جميع المواقع.',

    // =========================================================================
    // URL Settings
    // =========================================================================

    'URL Settings' => 'إعدادات URL',
    'Smart Link URL Prefix' => 'بادئة URL للرابط الذكي',
    '{singularName} URL Prefix' => 'بادئة URL لـ {singularName}',
    'QR Code URL Prefix' => 'بادئة URL لـ QR Code',
    'The URL prefix for {pluginName} (e.g., \'go\' creates /go/your-link)' => 'بادئة URL لـ {pluginName} (مثلاً، \'go\' تنشئ /go/your-link). امسح ذاكرة التخزين المؤقت للمسارات بعد التغيير (php craft clear-caches/compiled-templates).',
    'The URL prefix for QR code pages (e.g., \'qr\' creates /qr/your-link/view or \'go/qr\' creates /go/qr/your-link/view)' => 'بادئة URL لصفحات QR Code (مثلاً، \'qr\' تنشئ /qr/your-link/view أو \'go/qr\' تنشئ /go/qr/your-link/view)',
    'Clear routes cache after changing this (php craft clear-caches/compiled-templates).' => 'امسح ذاكرة التخزين المؤقت للمسارات بعد تغيير هذا (php craft clear-caches/compiled-templates).',
    'Smart Link Base URL' => 'عنوان URL الأساسي للرابط الذكي',
    '{singularName} Base URL' => 'عنوان URL الأساسي لـ {singularName}',
    'Optional absolute URL used for generated smart links and QR URLs. Leave empty to use each site\'s base URL.' => 'عنوان URL مطلق اختياري يُستخدم للروابط الذكية وعناوين QR URL المُنشأة. اتركه فارغاً لاستخدام عنوان URL الأساسي لكل موقع.',
    'Base URL for generated smart links and QR URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'عنوان URL الأساسي للروابط الذكية وعناوين QR URL المُنشأة. للمتعدد المواقع، يمكن استخدام الرموز: {siteHandle}, {siteId}, {siteUid} (مثلاً، https://go.example.com/{siteHandle}). اتركه فارغاً لاستخدام عنوان URL الأساسي لكل موقع.',
    'Base URL for {singularName} and QR code URLs. For multisite, you can use tokens: {siteHandle}, {siteId}, {siteUid} (e.g., https://go.example.com/{siteHandle}). Leave empty to use each site\'s base URL.' => 'عنوان URL الأساسي لـ {singularName} وعناوين QR Code URL. للمتعدد المواقع، يمكن استخدام الرموز: {siteHandle}, {siteId}, {siteUid} (مثلاً، https://go.example.com/{siteHandle}). اتركه فارغاً لاستخدام عنوان URL الأساسي لكل موقع.',
    'Changing the URL prefix will break all existing {pluginName}. Only change this before creating your first {singularName}.' => 'تغيير بادئة URL سيُبطل جميع {pluginName} الموجودة. قم بالتغيير فقط قبل إنشاء أول {singularName}.',
    'Multisite detected: <code>Smart Link Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'تم اكتشاف متعدد المواقع: تم تعيين <code>عنوان URL الأساسي للرابط الذكي</code> بدون رمز موقع. قد تُحلّ عناوين URL المُنشأة لموقع واحد فقط. استخدم عنوان URL يحتوي على رمز مثل <code>https://go.example.com/{siteHandle}</code> للحفاظ على التوجيه الخاص بالموقع.',
    'Multisite detected: <code>{singularName} Base URL</code> is set without a site token. Generated URLs may resolve to only one site. Use a tokenized URL like <code>https://go.example.com/{siteHandle}</code> to preserve site-specific routing.' => 'تم اكتشاف متعدد المواقع: تم تعيين <code>عنوان URL الأساسي لـ {singularName}</code> بدون رمز موقع. قد تُحلّ عناوين URL المُنشأة لموقع واحد فقط. استخدم عنوان URL يحتوي على رمز مثل <code>https://go.example.com/{siteHandle}</code> للحفاظ على التوجيه الخاص بالموقع.',
    'Use URL Prefix' => 'استخدام بادئة URL',
    'Enable to generate {singularName} URLs as /{prefix}/{slug}. Disable to generate root URLs as /{slug}.' => 'فعّل لإنشاء عناوين URL لـ {singularName} بصيغة /{prefix}/{slug}. عطّل لإنشاء عناوين URL الجذرية بصيغة /{slug}.',
    'Both {smartName} and {shortName} are set to root URLs (no prefix) and share at least one host. Redirect routes can collide (e.g., <code>/slug</code>), and QR routes can also collide when both plugins use the same QR prefix (e.g., <code>/qr/slug</code>).' => 'كلٌّ من {smartName} و {shortName} معيَّنان على عناوين URL الجذرية (بدون بادئة) ويتشاركان مضيفاً واحداً على الأقل. قد تتعارض مسارات إعادة التوجيه (مثلاً، <code>/slug</code>)، وقد تتعارض مسارات QR أيضاً عندما يستخدم كلا الإضافتين نفس بادئة QR (مثلاً، <code>/qr/slug</code>).',
    'Both {smartName} and {shortName} are set to root URLs (no prefix). Host overlap could not be fully resolved from current settings/config, so redirect route collisions are possible. QR routes may also collide if both plugins use the same QR prefix.' => 'كلٌّ من {smartName} و {shortName} معيَّنان على عناوين URL الجذرية (بدون بادئة). لم يمكن حل تداخل المضيف بالكامل من الإعدادات الحالية، لذا قد تحدث تعارضات في مسارات إعادة التوجيه. قد تتعارض مسارات QR أيضاً إذا استخدم كلا الإضافتين نفس بادئة QR.',
    'URL Prefix is disabled. {singularName} URLs will be generated as root paths like <code>/your-link</code>.' => 'بادئة URL معطَّلة. سيتم إنشاء عناوين URL لـ {singularName} كمسارات جذرية مثل <code>/your-link</code>.',
    'This is being overridden by the <code>usePrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>usePrefix</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>slugPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>slugPrefix</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrPrefix</code> في <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // Template Settings
    // =========================================================================

    'Template Settings' => 'إعدادات القالب',
    'Redirect Template' => 'قالب إعادة التوجيه',
    'Custom Redirect Template' => 'قالب إعادة توجيه مخصص',
    'Template path in your templates/ folder. Leave empty to use the default path.' => 'مسار القالب في مجلد templates/. اتركه فارغاً لاستخدام المسار الافتراضي.',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/redirect)' => 'مسار القالب المخصص في مجلد templates/ (مثلاً، smartlink-manager/redirect)',
    'QR Code Template' => 'قالب QR Code',
    'Custom QR Code Template' => 'قالب QR Code مخصص',
    'Path to custom template in your templates/ folder (e.g., smartlink-manager/qr)' => 'مسار القالب المخصص في مجلد templates/ (مثلاً، smartlink-manager/qr)',
    'These templates must exist in your site\'s <code>templates/</code> folder. Copy the reference templates from <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> to <code>templates/smartlink-manager/</code> and customize as needed.' => 'يجب أن توجد هذه القوالب في مجلد <code>templates/</code> الخاص بموقعك. انسخ القوالب المرجعية من <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/</code> إلى <code>templates/smartlink-manager/</code> وخصّصها حسب الحاجة.',

    // =========================================================================
    // Asset Settings
    // =========================================================================

    'Asset Settings' => 'إعدادات الملفات',
    'Image Volume' => 'مجلد الصور',
    '{singularName} Image Volume' => 'مجلد صور {singularName}',
    'Which asset volume should be used for {singularName} images' => 'أي مجلد ملفات يجب استخدامه لصور {singularName}',
    'All asset volumes' => 'جميع مجلدات الملفات',

    // =========================================================================
    // QR Code Settings — Appearance
    // =========================================================================

    'QR Code Settings' => 'إعدادات QR Code',
    'Appearance & Style' => 'المظهر والنمط',
    'Enable QR Code' => 'تفعيل QR Code',
    'Default QR Code Size' => 'الحجم الافتراضي لـ QR Code',
    'Default size in pixels for generated QR codes' => 'الحجم الافتراضي بالبكسل لرموز QR المُنشأة',
    'QR Code Color' => 'لون QR Code',
    'Default QR Code Color' => 'اللون الافتراضي لـ QR Code',
    'Default QR Background Color' => 'لون الخلفية الافتراضي لـ QR',
    'Background Color' => 'لون الخلفية',
    'Default QR Code Format' => 'الصيغة الافتراضية لـ QR Code',
    'Default format for generated QR codes' => 'الصيغة الافتراضية لرموز QR المُنشأة',
    'Override the default QR code format' => 'تجاوز الصيغة الافتراضية لـ QR Code',
    'Format' => 'الصيغة',
    'Use Default ({format|upper})' => 'استخدام الافتراضي ({format|upper})',
    'Color' => 'اللون',
    'Background' => 'الخلفية',
    'Eye Color' => 'لون العين',
    'Color for position markers (leave empty to use main color)' => 'لون علامات الموضع (اتركه فارغاً لاستخدام اللون الرئيسي)',
    'Size' => 'الحجم',

    // =========================================================================
    // QR Code Settings — Logo
    // =========================================================================

    'Logo Settings' => 'إعدادات الشعار',
    'Enable QR Code Logo' => 'تفعيل شعار QR Code',
    'Enable Logo Overlay' => 'تفعيل طبقة الشعار',
    'Add a logo in the center of QR codes' => 'إضافة شعار في وسط رموز QR',
    'Logo Volume' => 'مجلد الشعارات',
    'Logo Asset Volume' => 'مجلد ملفات الشعارات',
    'Which asset volume contains QR code logos. Save settings after changing this to update the logo selection below.' => 'أي مجلد ملفات يحتوي على شعارات QR Code. احفظ الإعدادات بعد تغيير هذا لتحديث اختيار الشعار أدناه.',
    'Default Logo' => 'الشعار الافتراضي',
    'Default logo to use for QR codes (can be overridden per smart link)' => 'الشعار الافتراضي لرموز QR (يمكن تجاوزه لكل رابط ذكي)',
    'Default logo is required when logo overlay is enabled.' => 'الشعار الافتراضي مطلوب عند تفعيل طبقة الشعار.',
    'Logo Size (%)' => 'حجم الشعار (%)',
    'Logo Size' => 'حجم الشعار',
    'Logo size as percentage of QR code (10-30%)' => 'حجم الشعار كنسبة مئوية من QR Code (10-30%)',
    'Logo' => 'الشعار',
    'Override the default QR code logo' => 'تجاوز شعار QR Code الافتراضي',
    'Using default logo from settings (click to override)' => 'استخدام الشعار الافتراضي من الإعدادات (اضغط للتجاوز)',
    'Logo overlay only works with PNG format. SVG format does not support logos.' => 'طبقة الشعار تعمل فقط مع صيغة PNG. صيغة SVG لا تدعم الشعارات.',
    'Logo requires PNG format' => 'الشعار يتطلب صيغة PNG',
    'Please save settings to apply the volume change to the logo selection field.' => 'يرجى حفظ الإعدادات لتطبيق تغيير المجلد على حقل اختيار الشعار.',
    'Please save to apply the volume change' => 'يرجى الحفظ لتطبيق تغيير المجلد',

    // =========================================================================
    // QR Code Settings — Technical
    // =========================================================================

    'Technical Options' => 'خيارات تقنية',
    'Error Correction Level' => 'مستوى تصحيح الأخطاء',
    'Higher levels work better if QR code is damaged but create denser patterns' => 'المستويات الأعلى تعمل بشكل أفضل إذا تضرر QR Code لكنها تنشئ أنماطاً أكثر كثافة',
    'QR Code Margin' => 'هامش QR Code',
    'Margin Size' => 'حجم الهامش',
    'White space around QR code (0-10 modules)' => 'المسافة البيضاء حول QR Code (0-10 وحدات)',
    'Module Style' => 'نمط الوحدة',
    'Shape of the QR code modules' => 'شكل وحدات QR Code',
    'Eye Style' => 'نمط العين',
    'Shape of the position markers (corners)' => 'شكل علامات الموضع (الزوايا)',

    // =========================================================================
    // QR Code Settings — Downloads
    // =========================================================================

    'Download Settings' => 'إعدادات التحميل',
    'Enable QR Code Downloads' => 'تفعيل تحميل QR Code',
    'Allow users to download QR codes' => 'السماح للمستخدمين بتحميل رموز QR',
    'Download Filename Pattern' => 'نمط اسم ملف التحميل',
    'Available variables: {slug}, {size}, {format}' => 'المتغيرات المتاحة: {slug}, {size}, {format}',
    'Download QR Code' => 'تحميل QR Code',
    'Small (256px)' => 'صغير (256 بكسل)',
    'Medium (512px)' => 'متوسط (512 بكسل)',
    'Large (1024px)' => 'كبير (1024 بكسل)',
    'Extra Large (2048px)' => 'كبير جداً (2048 بكسل)',
    'Custom Size...' => 'حجم مخصص...',

    // =========================================================================
    // QR Code Settings — Actions & Preview
    // =========================================================================

    'QR Code Actions' => 'إجراءات QR Code',
    'View QR Code' => 'عرض QR Code',
    'QR Code Image' => 'صورة QR Code',
    'QR Code Page' => 'صفحة QR Code',
    'Reset to Defaults' => 'إعادة تعيين للافتراضي',
    'Live Preview' => 'معاينة مباشرة',
    'Preview' => 'معاينة',
    'Click to view QR code image' => 'اضغط لعرض صورة QR Code',
    'Click to view QR code page' => 'اضغط لعرض صفحة QR Code',
    'Toggle preview' => 'تبديل المعاينة',
    'QR code settings reset to defaults' => 'تم إعادة تعيين إعدادات QR Code إلى الافتراضية',
    'Performance & Caching' => 'الأداء والتخزين المؤقت',
    'Configure QR code caching to improve performance and reduce server load.' => 'كوّن تخزين QR Code المؤقت لتحسين الأداء وتقليل الحمل على الخادم.',
    'Go to Cache Settings' => 'الانتقال إلى إعدادات Cache',

    // =========================================================================
    // Behavior Settings
    // =========================================================================

    'Behavior Settings' => 'إعدادات السلوك',
    'Redirect Behavior' => 'سلوك إعادة التوجيه',
    '404 Redirect URL' => 'رابط إعادة توجيه 404',
    'Where to redirect when a {singularName} is not found or disabled' => 'إلى أين يتم التوجيه عندما لا يُعثر على {singularName} أو يكون معطلاً',
    'Can be a relative path (/) or full URL (https://example.com)' => 'يمكن أن يكون مساراً نسبياً (/) أو رابطاً كاملاً (https://example.com)',

    // =========================================================================
    // Analytics Settings
    // =========================================================================

    'Analytics Settings' => 'إعدادات التحليلات',
    'Enable Analytics' => 'تفعيل التحليلات',
    'Track Analytics' => 'تتبع التحليلات',
    'Track clicks and visitor data for {pluginName}' => 'تتبع النقرات وبيانات الزوار لـ {pluginName}',
    'When enabled, {pluginName} will track visitor interactions, device types, geographic data, and other analytics information.' => 'عند التفعيل، ستتتبع {pluginName} تفاعلات الزوار وأنواع الأجهزة والبيانات الجغرافية ومعلومات تحليلية أخرى.',
    'Are you sure you want to disable analytics tracking for this {singularName}? This {singularName} will no longer collect visitor data and interactions.' => 'هل أنت متأكد من تعطيل تتبع التحليلات لهذا {singularName}؟ لن يعود {singularName} يجمع بيانات الزوار والتفاعلات.',

    // =========================================================================
    // Analytics Settings — IP Privacy
    // =========================================================================

    'IP Address Privacy' => 'خصوصية عنوان IP',
    'Anonymize IP Addresses' => 'إخفاء هوية عناوين IP',
    'Mask IP addresses before storage for maximum privacy. <strong>IPv4</strong>: masks last octet (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: masks last 80 bits. <strong>Trade-off</strong>: Reduces unique visitor accuracy (users on same subnet counted as one visitor). Geo-location still works normally.' => 'إخفاء عناوين IP قبل التخزين لأقصى قدر من الخصوصية. <strong>IPv4</strong>: يخفي الجزء الأخير (192.168.1.123 → 192.168.1.0). <strong>IPv6</strong>: يخفي آخر 80 بت. <strong>المقايضة</strong>: تقليل دقة الزوار الفريدين (يُحسب المستخدمون في نفس الشبكة الفرعية كزائر واحد). يعمل تحديد الموقع الجغرافي بشكل طبيعي.',
    'Privacy Levels' => 'مستويات الخصوصية',
    'Enabled' => 'مُفعَّل',
    'default' => 'افتراضي',
    'Full IP hashed with salt (accurate unique visitors)' => 'IP كاملة مُجزَّأة مع salt (دقة عالية للزوار الفريدين)',
    'Subnet masked + hashed with salt (maximum privacy, less accurate)' => 'شبكة فرعية مُخفاة + مُجزَّأة مع salt (أقصى خصوصية، أقل دقة)',

    // =========================================================================
    // Analytics Settings — Retention & Cleanup
    // =========================================================================

    'Analytics Retention (days)' => 'الاحتفاظ بالتحليلات (بالأيام)',
    'Analytics Retention' => 'الاحتفاظ بالتحليلات',
    'How many days to keep analytics data (0 for unlimited, max 3650)' => 'عدد الأيام للاحتفاظ ببيانات التحليلات (0 لغير محدود، الحد الأقصى 3650)',
    'Data Retention' => 'الاحتفاظ بالبيانات',
    'Analytics Cleanup' => 'تنظيف التحليلات',
    'Analytics data older than {days} days will be automatically cleaned up daily.' => 'سيتم تنظيف بيانات التحليلات الأقدم من {days} يوماً تلقائياً كل يوم.',
    'Clean Up Now' => 'تنظيف الآن',
    'Are you sure you want to clean up old analytics data now?' => 'هل أنت متأكد من تنظيف بيانات التحليلات القديمة الآن؟',
    'Unlimited Retention Warning' => 'تحذير الاحتفاظ غير المحدود',
    'Warning' => 'تحذير',
    'Analytics data will be retained indefinitely. This could result in large database size, slower performance, and increased storage costs over time. Consider setting a retention period (recommended: 90-365 days) for production sites.' => 'سيتم الاحتفاظ ببيانات التحليلات إلى أجل غير مسمى. قد يؤدي هذا إلى حجم كبير لقاعدة البيانات وأداء أبطأ وزيادة تكاليف التخزين بمرور الوقت. فكر في تحديد فترة احتفاظ (مستحسن: 90-365 يوماً) لمواقع الإنتاج.',

    // =========================================================================
    // Geo Provider Settings (from base _partials/geo-settings, uses |t(pluginHandle))
    // =========================================================================

    'Geographic Detection' => 'الكشف الجغرافي',
    'Geographic Analytics' => 'التحليلات الجغرافية',
    'Geographic Distribution' => 'التوزيع الجغرافي',
    'Enable Geographic Detection' => 'تفعيل الكشف الجغرافي',
    'Detect user location for analytics' => 'كشف موقع المستخدم للتحليلات',
    'View Geographic Details' => 'عرض التفاصيل الجغرافية',
    'Loading geographic data...' => 'جاري تحميل البيانات الجغرافية...',

    // Geo provider partial (lindemannrock-base/_partials/geo-settings)
    'Geo Provider' => 'مزود الخدمة الجغرافية',
    'Select the geo IP lookup provider. HTTPS providers recommended for privacy.' => 'اختر مزود بحث IP الجغرافي. يُنصح بمزودي HTTPS للخصوصية.',
    'ip-api.com (HTTP free, HTTPS paid)' => 'ip-api.com (HTTP مجاني، HTTPS مدفوع)',
    'ipapi.co (HTTPS, 1k/day free)' => 'ipapi.co (HTTPS، 1000/يوم مجاني)',
    'ipinfo.io (HTTPS, 50k/month free)' => 'ipinfo.io (HTTPS، 50000/شهر مجاني)',
    'API Key' => 'مفتاح API',
    'Optional. Required for paid tiers (enables HTTPS for ip-api.com Pro).' => 'اختياري. مطلوب للباقات المدفوعة (يُفعّل HTTPS لـ ip-api.com Pro).',
    'ip-api.com free tier uses HTTP. IP addresses will be transmitted unencrypted. Add an API key for HTTPS (Pro tier) or switch to ipapi.co/ipinfo.io.' => 'الباقة المجانية لـ ip-api.com تستخدم HTTP. سيتم إرسال عناوين IP غير مشفرة. أضف مفتاح API لـ HTTPS (الباقة Pro) أو انتقل إلى ipapi.co/ipinfo.io.',
    'ip-api.com: HTTP free tier (45 requests/min). Add API key for HTTPS (Pro tier, $13/month). IP addresses transmitted unencrypted without API key.' => 'ip-api.com: الباقة المجانية HTTP (45 طلب/دقيقة). أضف مفتاح API لـ HTTPS (الباقة Pro، 13$/شهر). تُرسل عناوين IP غير مشفرة بدون مفتاح API.',
    'ipapi.co: HTTPS with 1,000 free requests/day. API key optional (increases rate limits).' => 'ipapi.co: HTTPS مع 1000 طلب مجاني/يوم. مفتاح API اختياري (يزيد حدود الطلبات).',
    'ipinfo.io: HTTPS with 50,000 free requests/month. API key optional (increases rate limits).' => 'ipinfo.io: HTTPS مع 50000 طلب مجاني/شهر. مفتاح API اختياري (يزيد حدود الطلبات).',

    // IP salt error banner (from base partial)
    'error' => 'خطأ',
    'Configuration Required' => 'مطلوب تهيئة',
    'IP hash salt is missing.' => 'IP hash salt مفقود.',
    'Analytics tracking requires a secure salt for privacy protection.' => 'تتبع التحليلات يتطلب salt آمناً للحماية.',
    'Run one of these commands in your terminal:' => 'شغّل أحد هذه الأوامر في الطرفية:',
    'Standard:' => 'قياسي:',
    'COPY' => 'نسخ',
    'DDEV:' => 'DDEV:',
    'This will automatically add' => 'سيُضيف هذا تلقائياً',
    'to your' => 'إلى ملف',
    'file.' => 'الخاص بك.',
    'Warning:' => 'تحذير:',
    'Copy the same salt to staging and production environments.' => 'انسخ نفس salt إلى بيئات التطوير والإنتاج.',
    'COPIED!' => 'تم النسخ!',
    'Failed to copy to clipboard' => 'فشل النسخ إلى الحافظة',

    // =========================================================================
    // Device Detection Settings
    // =========================================================================

    'Cache Device Detection' => 'تخزين كشف الجهاز مؤقتاً',
    'Cache device detection results for better performance' => 'تخزين نتائج كشف الجهاز مؤقتاً لأداء أفضل',
    'Device Detection Cache Duration (seconds)' => 'مدة تخزين كشف الجهاز المؤقت (بالثواني)',

    // =========================================================================
    // Language Detection Settings
    // =========================================================================

    'Language Detection Method' => 'طريقة كشف اللغة',
    'How to detect user language preference' => 'كيفية كشف تفضيل لغة المستخدم',
    'Language Detection' => 'كشف اللغة',
    'Enable automatic language detection to redirect users based on their browser or location' => 'تفعيل كشف اللغة التلقائي لإعادة توجيه المستخدمين بناءً على المتصفح أو الموقع',

    // =========================================================================
    // Cache Settings
    // =========================================================================

    'Cache Settings' => 'إعدادات Cache',
    'Cache Storage Settings' => 'إعدادات تخزين Cache',
    'Cache Storage Method' => 'طريقة تخزين Cache',
    'How to store cache data. Use Redis/Database for load-balanced or multi-server environments.' => 'كيفية تخزين بيانات Cache. استخدم Redis/قاعدة البيانات للبيئات ذات توازن الحمل أو متعددة الخوادم.',
    'File System (default, single server)' => 'نظام الملفات (افتراضي، خادم واحد)',
    'Redis/Database (load-balanced, multi-server, cloud hosting)' => 'Redis/قاعدة البيانات (توازن الحمل، متعدد الخوادم، استضافة سحابية)',
    'QR Code Caching' => 'تخزين QR Code المؤقت',
    'Enable QR Code Cache' => 'تفعيل Cache لـ QR Code',
    'Cache generated QR codes for better performance' => 'تخزين رموز QR المُنشأة مؤقتاً لأداء أفضل',
    'QR Code Cache Duration (seconds)' => 'مدة Cache لـ QR Code (بالثواني)',
    'QR Code Cache Duration' => 'مدة Cache لـ QR Code',
    'How long to cache generated QR codes (in seconds)' => 'مدة تخزين رموز QR المُنشأة مؤقتاً (بالثواني)',
    'Cache duration in seconds' => 'مدة Cache بالثواني',
    'Min: 60 (1 minute), Max: 604800 (7 days)' => 'الحد الأدنى: 60 (دقيقة واحدة)، الحد الأقصى: 604800 (7 أيام)',
    'Caching' => 'التخزين المؤقت',
    'Device Detection Caching' => 'تخزين كشف الجهاز المؤقت',
    'Device Detection Cache Duration' => 'مدة Cache كشف الجهاز',
    'Device detection caching is only available when Analytics is enabled. Go to' => 'تخزين كشف الجهاز المؤقت متاح فقط عند تفعيل التحليلات. انتقل إلى',
    'to enable analytics.' => 'لتفعيل التحليلات.',

    // =========================================================================
    // Export Settings
    // =========================================================================

    'Export Settings' => 'إعدادات التصدير',
    'Analytics Export Options' => 'خيارات تصدير التحليلات',
    'Include Disabled Links in Export' => 'تضمين الروابط المعطلة في التصدير',
    'Include Disabled {pluginName} in Export' => 'تضمين {pluginName} المعطلة في التصدير',
    'When enabled, analytics exports will include data from disabled {pluginName}' => 'عند التفعيل، ستتضمن صادرات التحليلات بيانات من {pluginName} المعطلة',
    'Include Expired Links in Export' => 'تضمين الروابط المنتهية في التصدير',
    'Include Expired {pluginName} in Export' => 'تضمين {pluginName} المنتهية في التصدير',
    'When enabled, analytics exports will include data from expired {pluginName}' => 'عند التفعيل، ستتضمن صادرات التحليلات بيانات من {pluginName} المنتهية',
    'Export as CSV' => 'تصدير كملف CSV',

    // =========================================================================
    // Interface Settings
    // =========================================================================

    'Interface Settings' => 'إعدادات الواجهة',
    'Items Per Page' => 'العناصر في الصفحة',
    'Number of {pluginName} to show per page' => 'عدد {pluginName} المعروضة في كل صفحة',
    'Allow Multiple' => 'السماح بتحديد متعدد',
    'Whether to allow multiple {pluginName} to be selected' => 'ما إذا كان يُسمح بتحديد عدة {pluginName}',
    'The maximum number of {pluginName} that can be selected.' => 'الحد الأقصى لعدد {pluginName} التي يمكن تحديدها.',
    'Which sources should be available to select {pluginName} from?' => 'أي المصادر يجب أن تكون متاحة لتحديد {pluginName} منها؟',

    // =========================================================================
    // Integration Settings
    // =========================================================================

    'Third-Party Integrations' => 'تكاملات الطرف الثالث',
    'Integrations Settings' => 'إعدادات التكاملات',
    'Integrate {pluginName} with third-party analytics and tracking services to push click events to Google Tag Manager, Google Analytics, and other platforms.' => 'دمج {pluginName} مع خدمات التحليلات والتتبع من الطرف الثالث لإرسال أحداث النقرات إلى Google Tag Manager وGoogle Analytics ومنصات أخرى.',
    '{pluginName} Integration' => 'تكامل {pluginName}',
    'Installed & Active' => 'مثبَّت ونشط',
    'Installed but Disabled' => 'مثبَّت لكن معطَّل',
    'Not Installed' => 'غير مثبَّت',
    'Install Plugin' => 'تثبيت الإضافة',
    'Push {smartLinksName} click events to Google Tag Manager and analytics platforms for tracking redirects, button clicks, and QR code scans.' => 'إرسال أحداث نقرات {smartLinksName} إلى Google Tag Manager ومنصات التحليلات لتتبع عمليات إعادة التوجيه ونقرات الأزرار ومسح QR Code.',
    'Active Tracking Scripts' => 'سكريبتات التتبع النشطة',
    'Scripts receiving {pluginName} events' => 'السكريبتات التي تستقبل أحداث {pluginName}',
    'Note' => 'ملاحظة',
    'No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}.' => 'لا توجد سكريبتات تتبع مُهيَّأة حالياً في {pluginName}. سيتم وضع الأحداث في قائمة انتظار لكن لن تُرسل حتى تُهيِّئ GTM أو Google Analytics في {pluginName}.',
    'Configuration' => 'التهيئة',
    'Tracking Events' => 'أحداث التتبع',
    'Select which events to send to {pluginName}' => 'اختر الأحداث التي تُرسل إلى {pluginName}',
    'Auto-Redirects' => 'إعادة التوجيه التلقائي',
    'Mobile users automatically redirected' => 'مستخدمو الجوال يُعاد توجيههم تلقائياً',
    'Button Clicks' => 'نقرات الأزرار',
    'Manual platform selection on landing page' => 'اختيار المنصة يدوياً في صفحة الهبوط',
    'QR Code Scans' => 'مسح QR Code',
    'QR code accessed via ?src=qr parameter' => 'تم الوصول إلى QR Code عبر المعامل ?src=qr',
    'Event Prefix' => 'بادئة الحدث',
    'Prefix for event names (e.g., \'smart_links_redirect\')' => 'بادئة لأسماء الأحداث (مثلاً، \'smart_links_redirect\')',
    'Event Data Structure' => 'بنية بيانات الحدث',
    'Click to view the data layer event format' => 'اضغط لعرض صيغة حدث طبقة البيانات',
    'How Events Are Sent' => 'كيفية إرسال الأحداث',
    '{pluginName} pushes events to GTM or GA4 dataLayer only' => '{pluginName} ترسل الأحداث فقط إلى طبقة بيانات GTM أو GA4',
    'Only Google Tag Manager and Google Analytics 4 support the dataLayer format in SEOmatic' => 'يدعم Google Tag Manager وGoogle Analytics 4 فقط صيغة dataLayer في SEOmatic',
    'Use GTM to forward to other platforms' => 'استخدم GTM للإعادة إلى منصات أخرى',
    'Configure GTM triggers and tags to forward {pluginName} events to Facebook Pixel, LinkedIn, HubSpot, etc.' => 'هيِّئ مشغلات وعلامات GTM لإعادة توجيه أحداث {pluginName} إلى Facebook Pixel وLinkedIn وHubSpot وغيرها.',
    'Events are only sent when analytics tracking is enabled both globally and per-link' => 'تُرسل الأحداث فقط عند تفعيل تتبع التحليلات على المستوى العام ولكل رابط',
    'Architecture' => 'البنية',
    'Push {pluginName} events to SEOmatic\'s Google Tag Manager data layer for tracking in GTM and Google Analytics.' => 'إرسال أحداث {pluginName} إلى طبقة بيانات Google Tag Manager الخاصة بـ SEOmatic للتتبع في GTM وGoogle Analytics.',
    'Select which {pluginName} events to send to SEOmatic' => 'اختر أحداث {pluginName} التي ترسلها إلى SEOmatic',
    'Fathom, Matomo, and Plausible are shown above but do not receive events directly from {pluginName}' => 'يظهر Fathom وMatomo وPlausible أعلاه لكنها لا تستقبل الأحداث مباشرة من {pluginName}',
    // Redirect Manager Integration
    'Create permanent redirect records when {pluginName} slugs change. Provides centralized redirect management and analytics tracking.' => 'إنشاء سجلات إعادة توجيه دائمة عند تغيير slugs لـ {pluginName}. يوفر إدارة مركزية لإعادة التوجيه وتتبع التحليلات.',
    'Creates permanent redirects when {pluginName} slugs change or links are deleted' => 'ينشئ إعادة توجيه دائمة عند تغيير slugs لـ {pluginName} أو حذف الروابط',
    'Automatic Redirect Creation' => 'إنشاء إعادة توجيه تلقائي',
    'Select which events should create permanent redirects in {pluginName}' => 'اختر الأحداث التي يجب أن تنشئ إعادة توجيه دائمة في {pluginName}',
    'Slug Changes' => 'تغييرات Slug',
    'Change slug from <code>promo-2024</code> to <code>promo-2025</code> → Creates <code>/go/promo-2024</code> → <code>/go/promo-2025</code>' => 'تغيير slug من <code>promo-2024</code> إلى <code>promo-2025</code> → ينشئ <code>/go/promo-2024</code> → <code>/go/promo-2025</code>',
    'Benefits of This Integration' => 'فوائد هذا التكامل',
    'Centralized Management' => 'إدارة مركزية',
    'View and manage all redirects ({pluginName} + regular pages) in one place' => 'عرض وإدارة جميع عمليات إعادة التوجيه ({pluginName} + الصفحات العادية) في مكان واحد',
    'Analytics Tracking' => 'تتبع التحليلات',
    'See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries' => 'اطّلع على عدد الأشخاص الذين يحاولون الوصول إلى {pluginName} المحذوفة أو المعدَّلة وأجهزتهم ومتصفحاتهم وبلدانهم',
    'Persistent Redirects' => 'إعادة توجيه دائمة',
    'Redirects persist even if {pluginName} is deleted, preventing broken links permanently' => 'تبقى إعادة التوجيه حتى لو تم حذف {pluginName}، مما يمنع الروابط المعطوبة بشكل دائم',
    'Source Tracking' => 'تتبع المصدر',
    '{rmPluginName} shows which plugin created each redirect for better organization' => '{rmPluginName} يُظهر أي إضافة أنشأت كل إعادة توجيه لتنظيم أفضل',
    'Enabled Integrations' => 'التكاملات المُفعَّلة',
    // SmartLinkType (Link field integration)
    '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.' => '{pluginName} غير مُفعَّل للموقع "{site}". فعّله في إعدادات الإضافة لاستخدام {pluginNameLower} هنا.',
    'Invalid {pluginName} format.' => 'صيغة {pluginName} غير صالحة.',
    '{pluginName} not found.' => '{pluginName} غير موجود.',

    // =========================================================================
    // Smart Link Fields (edit page)
    // =========================================================================

    'Title' => 'العنوان',
    'The title of this {singularName}' => 'عنوان {singularName}',
    'Description' => 'الوصف',
    'A brief description of this {singularName}' => 'وصف موجز لـ {singularName}',
    'Icon' => 'الأيقونة',
    'Icon identifier or URL for this {singularName}' => 'معرف الأيقونة أو رابطها لـ {singularName}',
    'Image' => 'الصورة',
    'Select an image for this {singularName}' => 'اختر صورة لـ {singularName}',
    'Image Size' => 'حجم الصورة',
    'Select the size for the {singularName} image' => 'اختر حجم صورة {singularName}',
    'Hide Title on Landing Pages' => 'إخفاء العنوان في صفحات الهبوط',
    'Hide the {singularName} title on both redirect and QR code landing pages' => 'إخفاء عنوان {singularName} في كلٍّ من صفحات إعادة التوجيه وصفحات QR Code',
    'Display Settings' => 'إعدادات العرض',
    'Advanced Settings' => 'إعدادات متقدمة',
    'Destination URL' => 'رابط الوجهة',
    'Last Destination URL' => 'آخر رابط وجهة',
    'Fallback URL' => 'الرابط الاحتياطي',
    'The URL to redirect to when no platform-specific URL is available' => 'الرابط المستخدم لإعادة التوجيه عندما لا يتوفر رابط خاص بالمنصة',
    'iOS URL' => 'رابط iOS',
    'App Store URL for iOS devices' => 'رابط App Store لأجهزة iOS',
    'Android URL' => 'رابط Android',
    'Google Play Store URL for Android devices' => 'رابط Google Play Store لأجهزة Android',
    'Huawei URL' => 'رابط Huawei',
    'AppGallery URL for Huawei devices' => 'رابط AppGallery لأجهزة Huawei',
    'Amazon URL' => 'رابط Amazon',
    'Amazon Appstore URL' => 'رابط Amazon Appstore',
    'Windows URL' => 'رابط Windows',
    'Microsoft Store URL for Windows devices' => 'رابط Microsoft Store لأجهزة Windows',
    'Mac URL' => 'رابط Mac',
    'Mac App Store URL' => 'رابط Mac App Store',
    'App Store URLs' => 'روابط متاجر التطبيقات',
    'Enter the store URLs for each platform. The system will automatically redirect users to the appropriate store based on their device.' => 'أدخل روابط المتاجر لكل منصة. سيقوم النظام تلقائياً بتوجيه المستخدمين إلى المتجر المناسب حسب أجهزتهم.',
    '{pluginName} URL' => 'رابط {pluginName} URL',
    'URL copied to clipboard' => 'تم نسخ URL إلى الحافظة',
    'New {singularName}' => '{singularName} جديد',

    // =========================================================================
    // Field Layout
    // =========================================================================

    'Add custom fields to {singularName} elements. Any fields you add here will appear in the {singularName} edit screen.' => 'إضافة حقول مخصصة إلى عناصر {singularName}. أي حقول تضيفها هنا ستظهر في شاشة تحرير {singularName}.',
    'No field layout available.' => 'لا يوجد تخطيط حقول متاح.',

    // =========================================================================
    // Smart Link Element — Index & Actions
    // =========================================================================

    'Slug' => 'Slug',
    'Redirect Page' => 'صفحة إعادة التوجيه',
    'All {pluginName}' => 'جميع {pluginName}',
    'New {name}' => '{name} جديد',
    'Are you sure you want to delete the selected smart links?' => 'هل أنت متأكد من حذف الروابط الذكية المحددة؟',
    'Smart links deleted.' => 'تم حذف الروابط الذكية.',
    'Smart links restored.' => 'تم استعادة الروابط الذكية.',
    'Some smart links restored.' => 'تم استعادة بعض الروابط الذكية.',
    'Smart links not restored.' => 'تعذّر استعادة الروابط الذكية.',
    'Add a smart link' => 'إضافة رابط ذكي',
    'No smart links selected' => 'لم يتم تحديد روابط ذكية',
    'You can only select up to {limit} {limit, plural, =1{smart link} other{smart links}}.' => 'يمكنك تحديد ما يصل إلى {limit} رابط ذكي.',
    'Create a new smart link' => 'إنشاء رابط ذكي جديد',

    // =========================================================================
    // Analytics Dashboard — Overview Tab
    // =========================================================================

'View Analytics' => 'عرض التحليلات',
    'Traffic Overview' => 'نظرة عامة على الزيارات',
    'Traffic & Devices' => 'الزيارات والأجهزة',
    'Geographic' => 'جغرافي',
    'Total Links' => 'إجمالي الروابط',
    'Active Links' => 'الروابط النشطة',
    'Total Clicks' => 'إجمالي النقرات',
    'total clicks' => 'إجمالي النقرات',
    'Clicks' => 'النقرات',
    'Unique Visitors' => 'الزوار الفريدون',
    'Total Interactions' => 'إجمالي التفاعلات',
    'Avg. Clicks/Day' => 'متوسط النقرات/يوم',
    'Avg. Interactions/Day' => 'متوسط التفاعلات/يوم',
    'Engagement Rate' => 'معدل التفاعل',
    'Top {pluginName} (Top 20)' => 'أفضل {pluginName} (أفضل 20)',
    'Latest Interactions (Top 20)' => 'أحدث التفاعلات (أفضل 20)',
    'Interactions (Last 20)' => 'التفاعلات (آخر 20)',
    'No analytics data yet' => 'لا توجد بيانات تحليلية بعد',
    'Analytics will appear here once your {singularName} starts receiving clicks.' => 'ستظهر التحليلات هنا بمجرد أن يبدأ {singularName} في تلقي النقرات.',
    'Failed to load analytics data' => 'فشل تحميل بيانات التحليلات',
    'Failed to load countries data' => 'فشل تحميل بيانات الدول',
    'No data for selected period' => 'لا توجد بيانات للفترة المحددة',

    // =========================================================================
    // Analytics Dashboard — Traffic & Devices Tab
    // =========================================================================

    'Device Analytics' => 'تحليلات الأجهزة',
    'Device Types' => 'أنواع الأجهزة',
    'Device Brands' => 'ماركات الأجهزة',
    'Operating Systems' => 'أنظمة التشغيل',
    'Browser Usage' => 'استخدام المتصفح',
    'Usage Patterns' => 'أنماط الاستخدام',
    'Peak Usage Hours' => 'ساعات الذروة',
    'Peak usage at {hour}' => 'ذروة الاستخدام عند {hour}',
    'Daily Clicks' => 'النقرات اليومية',

    // =========================================================================
    // Analytics Dashboard — Geographic Tab
    // =========================================================================

    'Top Countries' => 'أفضل الدول',
    'Top Cities' => 'أفضل المدن',
    'Top Cities Worldwide' => 'أفضل المدن عالمياً',
    'No country data available' => 'لا توجد بيانات دول متاحة',
    'No city data available' => 'لا توجد بيانات مدن متاحة',
    'Geographic detection is disabled.' => 'الكشف الجغرافي معطَّل.',
    'Enable in Settings' => 'تفعيل في الإعدادات',

    // =========================================================================
    // Analytics Data — Table Columns & Labels
    // =========================================================================

    'Date' => 'التاريخ',
    'Time' => 'الوقت',
    'Device' => 'الجهاز',
    'Location' => 'الموقع',
    'Country' => 'البلد',
    'Countries' => 'الدول',
    'City' => 'المدينة',
    'Site' => 'الموقع',
    'Source' => 'المصدر',
    'Type' => 'النوع',
    'OS' => 'نظام التشغيل',
    'Operating System' => 'نظام التشغيل',
    'Browser' => 'المتصفح',
    'Interactions' => 'التفاعلات',
    'Latest Interactions' => 'أحدث التفاعلات',
    'No interactions recorded yet' => 'لم يتم تسجيل تفاعلات بعد',
    'Last Interaction' => 'آخر تفاعل',
    'Last Interaction Type' => 'نوع آخر تفاعل',
    'Last Click' => 'آخر نقرة',
    'Device information not available' => 'معلومات الجهاز غير متاحة',
    'OS information not available' => 'معلومات نظام التشغيل غير متاحة',
    'Name' => 'الاسم',
    'Percentage' => 'النسبة المئوية',

    // =========================================================================
    // Analytics Dashboard — JS strings (passed to JavaScript)
    // =========================================================================

    'No interaction data available for the selected filters.' => 'لا توجد بيانات تفاعل متاحة للفلاتر المحددة.',
    'No device data available for the selected filters.' => 'لا توجد بيانات أجهزة متاحة للفلاتر المحددة.',
    'No device brand data available for the selected filters.' => 'لا توجد بيانات ماركات أجهزة متاحة للفلاتر المحددة.',
    'No OS data available for the selected filters.' => 'لا توجد بيانات أنظمة تشغيل متاحة للفلاتر المحددة.',
    'No browser data available for the selected filters.' => 'لا توجد بيانات متصفحات متاحة للفلاتر المحددة.',
    'No hourly data available for the selected filters.' => 'لا توجد بيانات ساعية متاحة للفلاتر المحددة.',
    'Peak usage at' => 'ذروة الاستخدام عند',

    // =========================================================================
    // Interaction Types
    // =========================================================================

    'Direct' => 'مباشر',
    'Direct Visits' => 'زيارات مباشرة',
    'QR' => 'QR',
    'QR Scans' => 'مسح QR',
    'Button' => 'زر',
    'Landing' => 'صفحة هبوط',

    // =========================================================================
    // Analytics Export — CSV/Excel Column Headers
    // =========================================================================

    'Date/Time' => 'التاريخ/الوقت',
    'Status' => 'الحالة',
    'Smart Link URL' => 'رابط Smart Link URL',
    'Referrer' => 'Referrer',
    'Device Type' => 'نوع الجهاز',
    'Device Brand' => 'ماركة الجهاز',
    'Device Model' => 'طراز الجهاز',
    'OS Version' => 'إصدار نظام التشغيل',
    'Browser Version' => 'إصدار المتصفح',
    'Language' => 'اللغة',
    'User Agent' => 'User Agent',

    // =========================================================================
    // Time Periods
    // =========================================================================

    'Today' => 'اليوم',
    'Yesterday' => 'أمس',
    'Last 7 days' => 'آخر 7 أيام',
    'Last 30 days' => 'آخر 30 يوماً',
    'Last 90 days' => 'آخر 90 يوماً',
    'All time' => 'كل الوقت',
    'Date Range' => 'نطاق التاريخ',

    // =========================================================================
    // Utilities
    // =========================================================================

    'Monitor link performance, track analytics, and manage cache for your {singularName} redirects and QR codes.' => 'راقب أداء الروابط وتتبع التحليلات وإدارة Cache لعمليات إعادة توجيه {singularName} ورموز QR.',
    'Active {pluginName}' => '{pluginName} النشطة',
    'Links Status' => 'حالة الروابط',
    'Total {pluginName}' => 'إجمالي {pluginName}',
    'Performance' => 'الأداء',
    'Total interactions tracked' => 'إجمالي التفاعلات المتتبَّعة',
    'Redirects' => 'عمليات إعادة التوجيه',
    'QR Codes' => 'رموز QR',
    'Devices' => 'الأجهزة',
    'Cache Status' => 'حالة Cache',
    'Total cached entries' => 'إجمالي مدخلات Cache',
    'Active' => 'نشط',
    'Pending' => 'معلَّق',
    'Expired' => 'منتهي الصلاحية',
    'Disabled' => 'معطَّل',
    'Navigation' => 'التنقل',
    'Access main plugin sections' => 'الوصول إلى الأقسام الرئيسية للإضافة',
    'Manage {pluginName}' => 'إدارة {pluginName}',
    'View Settings' => 'عرض الإعدادات',
    'Cache Management' => 'إدارة Cache',
    'Clear cached data to force regeneration. Useful after changing QR code settings or when troubleshooting.' => 'امسح البيانات المخزنة مؤقتاً لفرض إعادة التوليد. مفيد بعد تغيير إعدادات QR Code أو عند استكشاف الأخطاء.',
    'Clear QR Cache' => 'مسح Cache لـ QR',
    'Clear Device Cache' => 'مسح Cache الجهاز',
    'Clear All Caches' => 'مسح جميع الـ Cache',
    'Analytics Data Management' => 'إدارة بيانات التحليلات',
    'Permanently delete all analytics tracking data. This action cannot be undone!' => 'حذف جميع بيانات تتبع التحليلات نهائياً. لا يمكن التراجع عن هذا الإجراء!',
    'Clear All Analytics' => 'مسح جميع التحليلات',
    'Are you sure you want to permanently delete ALL analytics data? This action cannot be undone!' => 'هل أنت متأكد من حذف جميع بيانات التحليلات نهائياً؟ لا يمكن التراجع عن هذا الإجراء!',
    'This will delete all click tracking data and reset all click counts. Are you absolutely sure?' => 'سيؤدي هذا إلى حذف جميع بيانات تتبع النقرات وإعادة تعيين جميع أعداد النقرات. هل أنت متأكد تماماً؟',
    'Failed to clear QR cache' => 'فشل مسح Cache لـ QR',
    'Failed to clear device cache' => 'فشل مسح Cache الجهاز',
    'Failed to clear caches' => 'فشل مسح الـ Cache',
    'Failed to clear analytics' => 'فشل مسح التحليلات',

    // =========================================================================
    // Widgets — Analytics Summary
    // =========================================================================

    '{pluginName} - Analytics' => '{pluginName} - التحليلات',
    'Top Performer' => 'الأفضل أداءً',
    'interactions' => 'تفاعلات',
    'View full analytics' => 'عرض التحليلات الكاملة',
    'You don\'t have permission to view analytics.' => 'ليس لديك صلاحية عرض التحليلات.',
    'Analytics are disabled in plugin settings.' => 'التحليلات معطَّلة في إعدادات الإضافة.',

    // =========================================================================
    // Widgets — Top Links
    // =========================================================================

    '{pluginName} - Top Links' => '{pluginName} - أفضل الروابط',
    'Link' => 'رابط',
    'Number of Links' => 'عدد الروابط',
    'How many top links to display (1-20)' => 'عدد أفضل الروابط المعروضة (1-20)',
    'View all {pluginName}' => 'عرض جميع {pluginName}',
    'No {pluginName} yet' => 'لا توجد {pluginName} بعد',
    'Create your first {singularName} to see it here.' => 'أنشئ أول {singularName} لعرضه هنا.',

    // =========================================================================
    // Public Templates — Redirect Page (redirect.twig)
    // =========================================================================

    'App Store' => 'App Store',
    'Google Play' => 'Google Play',
    'AppGallery' => 'AppGallery',
    'Amazon' => 'Amazon',
    'Windows Store' => 'Windows Store',
    'Mac App Store' => 'Mac App Store',
    'Continue to Website' => 'متابعة إلى الموقع',

    // =========================================================================
    // Public Templates — QR Code Page (qr.twig)
    // =========================================================================

    'Scan with your phone\'s camera to download' => 'امسح بكاميرا هاتفك للتحميل',

    // =========================================================================
    // Controller Messages — Flash Notices & Errors
    // =========================================================================

    // SmartlinksController
    'Smart link saved.' => 'تم حفظ الرابط الذكي.',
    'Couldn\'t save smart link.' => 'تعذّر حفظ الرابط الذكي.',
    'Error saving smart link: {error}' => 'خطأ في حفظ الرابط الذكي: {error}',
    'Could not save smart link.' => 'تعذّر حفظ الرابط الذكي.',
    'Smart link deleted.' => 'تم حذف الرابط الذكي.',
    'Couldn\'t delete smart link.' => 'تعذّر حذف الرابط الذكي.',
    'Smart link restored.' => 'تم استعادة الرابط الذكي.',
    'Couldn\'t restore smart link.' => 'تعذّر استعادة الرابط الذكي.',
    'Smart link permanently deleted.' => 'تم حذف الرابط الذكي نهائياً.',
    'Couldn\'t delete smart link permanently.' => 'تعذّر حذف الرابط الذكي نهائياً.',
    'Smart link not found' => 'الرابط الذكي غير موجود',
    'Cannot edit trashed smart links.' => 'لا يمكن تحرير الروابط الذكية المحذوفة.',
    'Failed to generate QR code.' => 'فشل إنشاء QR Code.',
    // SettingsController
    'Settings saved.' => 'تم حفظ الإعدادات.',
    'Couldn\'t save settings.' => 'تعذّر حفظ الإعدادات.',
    'Field layout saved.' => 'تم حفظ تخطيط الحقول.',
    'Couldn\'t save field layout.' => 'تعذّر حفظ تخطيط الحقول.',
    'Analytics cleanup job has been queued. It will run in the background.' => 'تم إضافة مهمة تنظيف التحليلات إلى قائمة الانتظار. ستعمل في الخلفية.',
    'QR code cache cleared successfully.' => 'تم مسح Cache لـ QR Code بنجاح.',
    'Cleared {count} QR code caches.' => 'تم مسح {count} من ذاكرة Cache لـ QR Code.',
    'Device cache cleared successfully.' => 'تم مسح Cache الجهاز بنجاح.',
    'Cleared {count} device detection caches.' => 'تم مسح {count} من ذاكرة Cache لكشف الجهاز.',
    'All caches cleared successfully.' => 'تم مسح جميع الـ Cache بنجاح.',
    'Cleared {count} cache entries.' => 'تم مسح {count} من مدخلات Cache.',
    'Cleared {count} analytics records and reset all click counts.' => 'تم حذف {count} سجل تحليلات وإعادة تعيين جميع أعداد النقرات.',
    'An unexpected error occurred.' => 'حدث خطأ غير متوقع.',
    // AnalyticsController
    'No analytics data to export.' => 'لا توجد بيانات تحليلات للتصدير.',
    // JS notices
    'Enter custom size (100-4096 pixels):' => 'أدخل حجماً مخصصاً (100-4096 بكسل):',
    'Please enter a valid size between 100 and 4096 pixels' => 'يرجى إدخال حجم صالح بين 100 و4096 بكسل',
    'Reset QR code settings to plugin defaults?' => 'إعادة تعيين إعدادات QR Code إلى افتراضيات الإضافة؟',

    // =========================================================================
    // Job Messages
    // =========================================================================

    '{pluginName}: Cleaning up old analytics' => '{pluginName}: جاري تنظيف التحليلات القديمة',
    'Deleting {count} old analytics records' => 'جاري حذف {count} سجل تحليلات قديم',
    'Deleted {deleted} of {total} records' => 'تم حذف {deleted} من أصل {total} سجل',

    // =========================================================================
    // Validation Messages
    // =========================================================================

    'Only letters, numbers, hyphens, and underscores are allowed.' => 'يُسمح فقط بالحروف والأرقام والشُرط والشُرط السفلية.',
    'Only letters, numbers, hyphens, underscores, and slashes are allowed.' => 'يُسمح فقط بالحروف والأرقام والشُرط والشُرط السفلية والشرطات المائلة.',
    'Only lowercase letters, numbers, and underscores are allowed.' => 'يُسمح فقط بالحروف الصغيرة والأرقام والشُرط السفلية.',
    '{attribute} should only contain letters, numbers, underscores, and hyphens.' => 'يجب أن يحتوي {attribute} على حروف وأرقام وشُرط سفلية وشُرط فقط.',
    'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'بادئة Slug "{prefix}" تتعارض مع: {conflicts}. الاقتراحات: {suggestions}',
    'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr' => 'لا يمكن أن تكون بادئة QR مطابقة لبادئة Slug. جرّب: qr, code, qrc, أو {slug}/qr',
    'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"' => 'يجب أن تبدأ بادئة QR المتداخلة ببادئة Slug "{slug}". استخدم: {slug}/{qr} أو كقائمة مستقلة مثل "qr"',
    'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}' => 'بادئة QR "{prefix}" تتعارض مع: {conflicts}. الاقتراحات: {suggestions}',
    'Smart link base URL must start with http:// or https://' => 'يجب أن يبدأ عنوان URL الأساسي للرابط الذكي بـ http:// أو https://',
    'Smart link base URL cannot contain spaces.' => 'لا يمكن أن يحتوي عنوان URL الأساسي للرابط الذكي على مسافات.',
    'Unsupported token in smart link base URL. Supported tokens: {siteHandle}, {siteId}, {siteUid}.' => 'رمز غير مدعوم في عنوان URL الأساسي للرابط الذكي. الرموز المدعومة: {siteHandle}, {siteId}, {siteUid}.',

    // =========================================================================
    // Config Override Warnings
    // =========================================================================

    'This is being overridden by the <code>pluginName</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>pluginName</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableAnalytics</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableAnalytics</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>analyticsRetention</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>analyticsRetention</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeDisabledInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>includeDisabledInExport</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>includeExpiredInExport</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>includeExpiredInExport</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrSize</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrColor</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrBgColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrBgColor</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrFormat</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrFormat</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrCodeCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrCodeCacheDuration</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrErrorCorrection</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrErrorCorrection</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>defaultQrMargin</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>defaultQrMargin</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrModuleStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrModuleStyle</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeStyle</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrEyeStyle</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrEyeColor</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrEyeColor</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrLogo</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableQrLogo</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrLogoVolumeUid</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>imageVolumeUid</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>imageVolumeUid</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrLogoSize</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrLogoSize</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrDownload</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableQrDownload</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrDownloadFilename</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrDownloadFilename</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>redirectTemplate</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>qrTemplate</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>qrTemplate</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableGeoDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableGeoDetection</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheDeviceDetection</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>cacheDeviceDetection</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>deviceDetectionCacheDuration</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>deviceDetectionCacheDuration</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>languageDetectionMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>languageDetectionMethod</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>itemsPerPage</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>itemsPerPage</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>notFoundRedirectUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>notFoundRedirectUrl</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledSites</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enabledSites</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enabledIntegrations</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enabledIntegrations</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticTrackingEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>seomaticTrackingEvents</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>seomaticEventPrefix</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>seomaticEventPrefix</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>cacheStorageMethod</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>cacheStorageMethod</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>enableQrCodeCache</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>enableQrCodeCache</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>anonymizeIpAddress</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>anonymizeIpAddress</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>redirectManagerEvents</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>redirectManagerEvents</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>logLevel</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>logLevel</code> في <code>config/smartlink-manager.php</code>.',
    'This is being overridden by the <code>smartlinkBaseUrl</code> setting in <code>config/smartlink-manager.php</code>.' => 'يتم تجاوز هذا بواسطة إعداد <code>smartlinkBaseUrl</code> في <code>config/smartlink-manager.php</code>.',

    // =========================================================================
    // General Interface
    // =========================================================================

    'Save Settings' => 'حفظ الإعدادات',
    'Actions' => 'الإجراءات',
    'Loading...' => 'جاري التحميل...',
    'Error' => 'خطأ',

    // =========================================================================
    // Behavior Settings — Select Options
    // =========================================================================

    'Browser preference' => 'تفضيل المتصفح',
    'IP geolocation' => 'تحديد الموقع بعنوان IP',
    'Both' => 'كلاهما',

    // =========================================================================
    // General Settings — URL Tips (Redirect Manager integration)
    // =========================================================================

    'Changing will break existing URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard)' => 'التغيير سيُبطل عناوين URL الموجودة. للترحيل، أنشئ إعادة توجيه بدل في {redirectPluginName}: المصدر \'/old/*\' → الوجهة \'/new/$1\' (نوع المطابقة: Wildcard)',
    'Changing will break existing QR URLs. To migrate, create wildcard redirect in {redirectPluginName}: Source \'/old/*\' → Destination \'/new/$1\' (Match Type: Wildcard). Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns.' => 'التغيير سيُبطل عناوين QR URL الموجودة. للترحيل، أنشئ إعادة توجيه بدل في {redirectPluginName}: المصدر \'/old/*\' → الوجهة \'/new/$1\' (نوع المطابقة: Wildcard). يدعم الأنماط المستقلة (مثلاً، \'qr\') أو المتداخلة (مثلاً، \'go/qr\').',
    'Supports standalone (e.g., \'qr\') or nested (e.g., \'go/qr\') patterns. Checked for conflicts with ShortLink Manager.' => 'يدعم الأنماط المستقلة (مثلاً، \'qr\') أو المتداخلة (مثلاً، \'go/qr\'). يتم التحقق من التعارضات مع ShortLink Manager.',

    // =========================================================================
    // QR Code Settings — Select Options
    // =========================================================================

    'Square' => 'مربع',
    'Rounded' => 'مستدير',
    'Dots' => 'نقاط',
    'Leaf' => 'ورقة',
    'Low (~7% correction)' => 'منخفض (~7% تصحيح)',
    'Medium (~15% correction)' => 'متوسط (~15% تصحيح)',
    'Quartile (~25% correction)' => 'ربعي (~25% تصحيح)',
    'High (~30% correction)' => 'مرتفع (~30% تصحيح)',
    'Failed to generate preview' => 'فشل إنشاء المعاينة',

    // =========================================================================
    // Smart Link Fields — Image Size Options
    // =========================================================================

    'Extra Large' => 'كبير جداً',
    'Large' => 'كبير',
    'Medium' => 'متوسط',
    'Small' => 'صغير',

    // =========================================================================
    // Smart Link Field Input — Tooltip
    // =========================================================================

    'Clicks:' => 'النقرات:',

    // =========================================================================
    // Cache Settings — Info Boxes & Durations
    // =========================================================================

    'Cache Location' => 'موقع Cache',
    'Using Craft\'s configured Redis cache from <code>config/app.php</code>' => 'استخدام Redis Cache المُهيَّأ من <code>config/app.php</code>',
    'Redis Not Configured' => 'Redis غير مُهيَّأ',
    'To use Redis caching, install <code>yiisoft/yii2-redis</code> and configure it in <code>config/app.php</code>.' => 'لاستخدام Redis Cache، ثبّت <code>yiisoft/yii2-redis</code> وهيِّئه في <code>config/app.php</code>.',
    'How it works' => 'كيفية عمله',
    'Device detection parses user-agent strings to identify devices, browsers, and operating systems' => 'يُحلِّل كشف الجهاز سلاسل User Agent لتعريف الأجهزة والمتصفحات وأنظمة التشغيل',
    'Results are cached to avoid re-parsing the same user-agent repeatedly' => 'يتم تخزين النتائج مؤقتاً لتجنب إعادة تحليل نفس User Agent مراراً',
    'Recommended to keep enabled for production sites' => 'يُنصح بإبقائه مُفعَّلاً لمواقع الإنتاج',
    'Cache duration in seconds. Current:' => 'مدة Cache بالثواني. الحالي:',

    // =========================================================================
    // Time Unit Strings (for JS secondsToHuman)
    // =========================================================================

    '{count} second' => '{count} ثانية',
    '{count} seconds' => '{count} ثوانٍ',
    '{count} minute' => '{count} دقيقة',
    '{count} minutes' => '{count} دقائق',
    '{count} hour' => '{count} ساعة',
    '{count} hours' => '{count} ساعات',
    '{count} day' => '{count} يوم',
    '{count} days' => '{count} أيام',

    // =========================================================================
    // Template Settings — Copy hints
    // =========================================================================

    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> to <code>templates/smartlink-manager/redirect.twig</code>' => 'مطلوب: انسخ <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig</code> إلى <code>templates/smartlink-manager/redirect.twig</code>',
    'Required: copy <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> to <code>templates/smartlink-manager/qr.twig</code>' => 'مطلوب: انسخ <code>vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig</code> إلى <code>templates/smartlink-manager/qr.twig</code>',

    // =========================================================================
    // Import/Export
    // =========================================================================

    'Manage import/export' => 'إدارة الاستيراد/التصدير',
    'Import links' => 'استيراد الروابط',
    'Export links' => 'تصدير الروابط',
    'Clear import history' => 'مسح سجل الاستيراد',
    'Export Smart Links' => 'تصدير الروابط الذكية',
    'Export All Smart Links as CSV' => 'تصدير جميع الروابط الذكية كملف CSV',
    'Import Smart Links' => 'استيراد الروابط الذكية',
    'You do not have permission to export smart links.' => 'ليس لديك صلاحية تصدير الروابط الذكية.',
    'You do not have permission to import smart links.' => 'ليس لديك صلاحية استيراد الروابط الذكية.',
    'Download all your current smart links as a CSV file for backup or migration to another site.' => 'حمّل جميع روابطك الذكية الحالية كملف CSV للنسخ الاحتياطي أو الترحيل إلى موقع آخر.',
    'Import smart links from CSV. You\'ll map columns and preview before importing.' => 'استيراد الروابط الذكية من CSV. ستقوم بتعيين الأعمدة ومعاينتها قبل الاستيراد.',
    'Select a CSV file to import smart links' => 'اختر ملف CSV لاستيراد الروابط الذكية',
    'No smart links to export.' => 'لا توجد روابط ذكية للتصدير.',
    'Map your CSV columns to smart link fields. Required fields must be mapped.' => 'عيِّن أعمدة CSV إلى حقول الرابط الذكي. يجب تعيين الحقول المطلوبة.',
    'Valid Smart Links to Import' => 'روابط ذكية صالحة للاستيراد',
    'No valid smart links found to import.' => 'لم يتم العثور على روابط ذكية صالحة للاستيراد.',
    'Import {count} Smart Links' => 'استيراد {count} رابط ذكي',
    'No Valid Smart Links to Import' => 'لا توجد روابط ذكية صالحة للاستيراد',
    'Click the button below to import {count} valid smart link(s).' => 'اضغط الزر أدناه لاستيراد {count} رابط ذكي صالح.',
    'Import completed: {imported} smart links imported.' => 'اكتمل الاستيراد: تم استيراد {imported} رابط ذكي.',
    'Import completed: {imported} imported, {failed} failed.' => 'اكتمل الاستيراد: تم استيراد {imported}، فشل {failed}.',
    'Import completed: {imported} {pluginName} imported.' => 'اكتمل الاستيراد: تم استيراد {imported} {pluginName}.',
    'Import completed: {imported} {pluginName} imported, {failed} failed.' => 'اكتمل الاستيراد: تم استيراد {imported} {pluginName}، فشل {failed}.',
    'Failed to clear import history.' => 'فشل مسح سجل الاستيراد.',
    'Slug must be mapped.' => 'يجب تعيين Slug.',
    'Slug (required)' => 'Slug (مطلوب)',
    'Fallback URL (required)' => 'الرابط الاحتياطي (مطلوب)',
    'Image Asset ID' => 'معرف ملف الصورة',
    'Image Size (xl/lg/md/sm)' => 'حجم الصورة (xl/lg/md/sm)',
    'QR Enabled (1/0)' => 'QR مُفعَّل (1/0)',
    'QR Size' => 'حجم QR',
    'QR Color (#RRGGBB)' => 'لون QR (#RRGGBB)',
    'QR Background (#RRGGBB)' => 'خلفية QR (#RRGGBB)',
    'QR Eye Color (#RRGGBB)' => 'لون عين QR (#RRGGBB)',
    'QR Format (png/svg)' => 'صيغة QR (png/svg)',
    'QR Logo Asset ID' => 'معرف ملف شعار QR',
    'Hide Title (1/0)' => 'إخفاء العنوان (1/0)',
    'Language Detection (1/0)' => 'كشف اللغة (1/0)',
    'Metadata (JSON)' => 'البيانات الوصفية (JSON)',

    // Import/Export — Controller messages
    'Unknown' => 'غير معروف',
    'Please select a CSV file to upload.' => 'يرجى اختيار ملف CSV للرفع.',
    'Failed to parse CSV: {error}' => 'فشل تحليل CSV: {error}',
    'No import data found. Please upload a CSV file.' => 'لم يتم العثور على بيانات استيراد. يرجى رفع ملف CSV.',
    'No preview data found. Please map columns first.' => 'لم يتم العثور على بيانات معاينة. يرجى تعيين الأعمدة أولاً.',
    'Import session expired. Please upload the file again.' => 'انتهت مهلة جلسة الاستيراد. يرجى رفع الملف مجدداً.',

    // Import/Export — Template UI
    'Import History' => 'سجل الاستيراد',
    'CSV Format' => 'صيغة CSV',
    'Required columns:' => 'الأعمدة المطلوبة:',
    'Optional columns:' => 'الأعمدة الاختيارية:',
    'Import from CSV' => 'استيراد من CSV',
    'CSV File' => 'ملف CSV',
    'CSV Delimiter' => 'فاصل CSV',
    'Character used to separate values in your CSV (auto-detect is default)' => 'الحرف المستخدم للفصل بين القيم في CSV (الاكتشاف التلقائي هو الافتراضي)',
    'Auto (detect)' => 'تلقائي (اكتشاف)',
    'Comma (,)' => 'فاصلة (,)',
    'Semicolon (;)' => 'فاصلة منقوطة (;)',
    'Tab' => 'مسافة جدولة',
    'Pipe (|)' => 'خط عمودي (|)',
    'The maximum file size is {size} and the import is limited to {rows} rows per file.' => 'الحد الأقصى لحجم الملف هو {size} والاستيراد محدود بـ {rows} صف لكل ملف.',
    'Upload & Map Columns' => 'رفع وتعيين الأعمدة',
    'Clear history' => 'مسح السجل',
    'No import history yet.' => 'لا يوجد سجل استيراد بعد.',
    'Are you sure you want to clear all import logs? This action cannot be undone.' => 'هل أنت متأكد من مسح جميع سجلات الاستيراد؟ لا يمكن التراجع عن هذا الإجراء.',
    'Failed to clear history.' => 'فشل مسح السجل.',
    'Map CSV Columns' => 'تعيين أعمدة CSV',
    'Your CSV has {count} rows. Map each CSV column to a smart link field.' => 'يحتوي ملف CSV على {count} صف. عيِّن كل عمود CSV إلى حقل رابط ذكي.',
    'Preview of CSV Data' => 'معاينة بيانات CSV',
    'Showing first 5 rows. {total} total rows will be imported.' => 'عرض أول 5 صفوف. سيتم استيراد {total} صف إجمالاً.',
    'Column Mapping' => 'تعيين الأعمدة',
    'Note: only columns mapped to a field will be imported.' => 'ملاحظة: سيتم استيراد الأعمدة المعينة لحقل فقط.',
    '-- Do not import --' => '-- لا تستورد --',
    'Enabled (1/0)' => 'مُفعَّل (1/0)',
    'Site ID' => 'معرف الموقع',
    'Site Handle' => 'اسم الموقع',
    'Track Analytics (1/0)' => 'تتبع التحليلات (1/0)',
    'Post Date (YYYY-MM-DD HH:MM:SS)' => 'تاريخ النشر (YYYY-MM-DD HH:MM:SS)',
    'Date Expired (YYYY-MM-DD HH:MM:SS)' => 'تاريخ الانتهاء (YYYY-MM-DD HH:MM:SS)',
    'CSV Column' => 'عمود CSV',
    'Maps to Field' => 'يعيَّن إلى حقل',
    'Sample Data' => 'بيانات نموذجية',
    'Map Columns' => 'تعيين الأعمدة',
    'Cancel' => 'إلغاء',
    'Preview Import' => 'معاينة الاستيراد',
    'Import Preview' => 'معاينة الاستيراد',
    'Total Rows' => 'إجمالي الصفوف',
    'Valid' => 'صالح',
    'Duplicates' => 'مكررات',
    'Errors' => 'أخطاء',
    'Duplicates (will be skipped)' => 'مكررات (سيتم تخطيها)',
    'Invalid Rows (will be skipped)' => 'صفوف غير صالحة (سيتم تخطيها)',
    'Row' => 'صف',
    'Reason' => 'السبب',
    'Image ID' => 'معرف الصورة',
    'Ready to Import' => 'جاهز للاستيراد',

    // Base partial: import-history
    'Created By' => 'أنشأه',
    'Filename' => 'اسم الملف',
    'Imported' => 'مستورد',
    'Failed' => 'فشل',

    // Analytics partial
    'Device Breakdown' => 'توزيع الأجهزة',

];
