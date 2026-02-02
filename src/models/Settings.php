<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\models;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\traits\SettingsConfigTrait;
use lindemannrock\base\traits\SettingsDisplayNameTrait;
use lindemannrock\base\traits\SettingsPersistenceTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;

/**
 * SmartLink Manager Settings Model
 *
 * @since 1.0.0
 */
class Settings extends Model
{
    use LoggingTrait;
    use SettingsConfigTrait;
    use SettingsDisplayNameTrait;
    use SettingsPersistenceTrait;

    /**
     * @event Event The event that is triggered after settings are saved
     */
    public const EVENT_AFTER_SAVE_SETTINGS = 'afterSaveSettings';

    /**
     * @var string Plugin display name
     */
    public string $pluginName = 'SmartLink Manager';
    
    /**
     * @var bool Enable analytics tracking
     */
    public bool $enableAnalytics = true;

    /**
     * @var int Analytics data retention in days
     */
    public int $analyticsRetention = 90;

    /**
     * @var bool Include disabled smart links in analytics exports
     */
    public bool $includeDisabledInExport = false;

    /**
     * @var bool Include expired smart links in analytics exports
     */
    public bool $includeExpiredInExport = false;

    /**
     * @var bool Anonymize IP addresses before storing (masks last octet for IPv4, last 80 bits for IPv6)
     */
    public bool $anonymizeIpAddress = false;

    /**
     * @var int Default QR code size
     */
    public int $defaultQrSize = 256;

    /**
     * @var string Default QR code color
     */
    public string $defaultQrColor = '#000000';

    /**
     * @var string Default QR code background color
     */
    public string $defaultQrBgColor = '#FFFFFF';

    /**
     * @var string Default QR code format (png or svg)
     */
    public string $defaultQrFormat = 'png';

    /**
     * @var bool Enable QR code caching
     */
    public bool $enableQrCodeCache = true;

    /**
     * @var int QR code cache duration in seconds (24 hours)
     */
    public int $qrCodeCacheDuration = 86400;

    /**
     * @var string Cache storage method (file or redis)
     */
    public string $cacheStorageMethod = 'file';

    /**
     * @var string Default QR code error correction level (L, M, Q, H)
     */
    public string $defaultQrErrorCorrection = 'M';
    
    /**
     * @var int Default QR code margin/quiet zone (0-10)
     */
    public int $defaultQrMargin = 4;
    
    /**
     * @var string QR code module style (square, rounded, dots)
     */
    public string $qrModuleStyle = 'square';
    
    /**
     * @var string QR code eye style (square, rounded, leaf)
     */
    public string $qrEyeStyle = 'square';
    
    /**
     * @var string|null QR code eye color (null = same as module color)
     */
    public ?string $qrEyeColor = null;
    
    /**
     * @var bool Enable QR code logo overlay
     */
    public bool $enableQrLogo = false;
    
    /**
     * @var string|null Asset volume UID for logo selection (null = all volumes)
     */
    public ?string $qrLogoVolumeUid = null;
    
    /**
     * @var string|null Asset volume UID for smart link image selection (null = all volumes)
     */
    public ?string $imageVolumeUid = null;
    
    /**
     * @var int|null Default QR code logo asset ID
     */
    public ?int $defaultQrLogoId = null;
    
    /**
     * @var int QR code logo size as percentage (10-30)
     */
    public int $qrLogoSize = 20;
    
    /**
     * @var bool Enable QR code downloads
     */
    public bool $enableQrDownload = true;
    
    /**
     * @var string QR code download filename pattern
     */
    public string $qrDownloadFilename = '{slug}-qr-{size}';

    /**
     * @var string|null Custom redirect template path
     */
    public ?string $redirectTemplate = null;

    /**
     * @var string|null Custom QR code display template path
     */
    public ?string $qrTemplate = null;

    /**
     * @var bool Enable geographic detection
     */
    public bool $enableGeoDetection = false;

    /**
     * @var string Geo IP lookup provider (ip-api.com, ipapi.co, ipinfo.io)
     */
    public string $geoProvider = 'ip-api.com';

    /**
     * @var string|null API key for paid provider tiers (enables HTTPS for ip-api.com)
     */
    public ?string $geoApiKey = null;

    /**
     * @var string|null Default country for local development (when IP is private)
     */
    public ?string $defaultCountry = null;

    /**
     * @var string|null Default city for local development (when IP is private)
     */
    public ?string $defaultCity = null;

    /**
     * @var bool Cache device detection results
     */
    public bool $cacheDeviceDetection = true;

    /**
     * @var int Device detection cache duration in seconds (1 hour)
     */
    public int $deviceDetectionCacheDuration = 3600;

    /**
     * @var string Default language detection method (browser, ip, or both)
     */
    public string $languageDetectionMethod = 'browser';

    /**
     * @var int Items per page in element index
     */
    public int $itemsPerPage = 100;

    /**
     * @var string URL prefix for smart links (default: 'go')
     */
    public string $slugPrefix = 'go';

    /**
     * @var string URL prefix for QR codes (default: 'qr')
     */
    public string $qrPrefix = 'qr';


    /**
     * @var string URL to redirect to when smart link is not found (404)
     */
    public string $notFoundRedirectUrl = '/';

    /**
     * @var array Site IDs where SmartLink Manager should be enabled
     */
    public array $enabledSites = [];

    /**
     * @var string Log level (error, warning, info, debug)
     */
    public string $logLevel = 'error';

    /**
     * @var array Enabled integration handles (e.g., ['seomatic'])
     */
    public array $enabledIntegrations = [];

    /**
     * @var array Event types to track in integrations
     */
    public array $seomaticTrackingEvents = ['redirect', 'button_click', 'qr_scan'];

    /**
     * @var array Event types that create redirects in Redirect Manager
     */
    public array $redirectManagerEvents = ['slug-change', 'delete'];

    /**
     * @var string Event prefix for GTM/GA events
     */
    public string $seomaticEventPrefix = 'smart_links';

    /**
     * @var string|null IP hash salt for privacy protection
     */
    public ?string $ipHashSalt = null;

    /**
     * Database table name for settings persistence
     */
    protected static function tableName(): string
    {
        return 'smartlinkmanager_settings';
    }

    /**
     * Plugin handle for config file lookup
     */
    protected static function pluginHandle(): string
    {
        return 'smartlink-manager';
    }

    /**
     * Boolean fields for type casting from database
     */
    protected static function booleanFields(): array
    {
        return [
            'enableAnalytics',
            'includeDisabledInExport',
            'includeExpiredInExport',
            'anonymizeIpAddress',
            'enableQrCodeCache',
            'enableGeoDetection',
            'cacheDeviceDetection',
            'enableQrLogo',
            'enableQrDownload',
        ];
    }

    /**
     * Integer fields for type casting from database
     */
    protected static function integerFields(): array
    {
        return [
            'analyticsRetention',
            'defaultQrSize',
            'qrCodeCacheDuration',
            'deviceDetectionCacheDuration',
            'itemsPerPage',
            'defaultQrMargin',
            'qrLogoSize',
            'defaultQrLogoId',
        ];
    }

    /**
     * Array fields for JSON serialization/deserialization
     */
    protected static function jsonFields(): array
    {
        return [
            'enabledSites',
            'enabledIntegrations',
            'seomaticTrackingEvents',
            'redirectManagerEvents',
        ];
    }

    /**
     * Fields to exclude from database save (env/config only)
     */
    protected static function excludeFromSave(): array
    {
        return ['ipHashSalt', 'defaultCountry', 'defaultCity'];
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(static::pluginHandle());

        // Fallback to .env if ipHashSalt not set by config file
        if ($this->ipHashSalt === null) {
            $this->ipHashSalt = App::env('SMARTLINK_MANAGER_IP_SALT');
        }

        // Load default location from .env if not set by config file
        if ($this->defaultCountry === null) {
            $this->defaultCountry = App::env('SMARTLINK_MANAGER_DEFAULT_COUNTRY');
        }
        if ($this->defaultCity === null) {
            $this->defaultCity = App::env('SMARTLINK_MANAGER_DEFAULT_CITY');
        }
    }

    /**
     * @inheritdoc
     */
    protected function defineBehaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => [
                    'redirectTemplate',
                    'qrTemplate',
                    'imageVolumeUid',
                    'qrLogoVolumeUid',
                    'ipHashSalt',
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['pluginName', 'slugPrefix', 'qrPrefix'], 'required'],
            [['pluginName'], 'string', 'max' => 255],
            [['slugPrefix', 'qrPrefix'], 'string', 'max' => 50],
            [['slugPrefix'], 'match', 'pattern' => '/^[a-zA-Z0-9\-\_]+$/', 'message' => Craft::t('smartlink-manager', 'Only letters, numbers, hyphens, and underscores are allowed.')],
            [['qrPrefix'], 'match', 'pattern' => '/^[a-zA-Z0-9\-\_\/]+$/', 'message' => Craft::t('smartlink-manager', 'Only letters, numbers, hyphens, underscores, and slashes are allowed.')],
            [['slugPrefix'], 'validateSlugPrefix'],
            [['qrPrefix'], 'validateQrPrefix'],
            [['enableAnalytics', 'enableGeoDetection', 'cacheDeviceDetection', 'includeDisabledInExport', 'includeExpiredInExport', 'anonymizeIpAddress'], 'boolean'],
            [['analyticsRetention', 'defaultQrSize', 'qrCodeCacheDuration', 'deviceDetectionCacheDuration', 'itemsPerPage'], 'integer'],
            [['analyticsRetention'], 'integer', 'min' => 0, 'max' => 3650], // 0 for unlimited, up to 10 years
            [['defaultQrSize'], 'integer', 'min' => 100, 'max' => 1000],
            [['itemsPerPage'], 'integer', 'min' => 10, 'max' => 500],
            [['defaultQrColor', 'defaultQrBgColor', 'qrEyeColor'], 'string'],
            [['defaultQrColor', 'defaultQrBgColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i'],
            [['qrEyeColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i', 'skipOnEmpty' => true],
            [['defaultQrFormat'], 'in', 'range' => ['png', 'svg']],
            [['defaultQrErrorCorrection'], 'in', 'range' => ['L', 'M', 'Q', 'H']],
            [['cacheStorageMethod'], 'in', 'range' => ['file', 'redis']],
            [['geoProvider'], 'in', 'range' => ['ip-api.com', 'ipapi.co', 'ipinfo.io']],
            [['geoApiKey'], 'string', 'max' => 255, 'skipOnEmpty' => true],
            [['qrModuleStyle'], 'in', 'range' => ['square', 'rounded', 'dots']],
            [['qrEyeStyle'], 'in', 'range' => ['square', 'rounded', 'leaf']],
            [['qrLogoSize'], 'integer', 'min' => 10, 'max' => 30],
            [['defaultQrMargin'], 'integer', 'min' => 0, 'max' => 10],
            [['qrDownloadFilename'], 'string'],
            [['enableQrLogo', 'enableQrDownload'], 'boolean'],
            [['qrLogoVolumeUid', 'imageVolumeUid'], 'string'],
            [['defaultQrLogoId'], 'integer'],
            // Require default logo when logo overlay is enabled
            [['defaultQrLogoId'], 'required', 'when' => function($model) {
                return $model->enableQrLogo;
            }, 'message' => Craft::t('smartlink-manager', 'Default logo is required when logo overlay is enabled.')],
            [['redirectTemplate', 'qrTemplate', 'notFoundRedirectUrl'], 'string'],
            [['languageDetectionMethod'], 'in', 'range' => ['browser', 'ip', 'both']],
            [['enabledSites'], 'each', 'rule' => ['integer']],
            [['logLevel'], 'in', 'range' => ['debug', 'info', 'warning', 'error']],
            [['logLevel'], 'validateLogLevel'],
            [['enabledIntegrations', 'seomaticTrackingEvents'], 'each', 'rule' => ['string']],
            [['seomaticEventPrefix'], 'string', 'max' => 50],
            [['seomaticEventPrefix'], 'match', 'pattern' => '/^[a-z0-9\_]+$/', 'message' => Craft::t('smartlink-manager', 'Only lowercase letters, numbers, and underscores are allowed.')],
        ];
    }

    /**
     * Set enabled integrations from string (for form submission)
     *
     * @param string|array $value
     */
    public function setEnabledIntegrations($value): void
    {
        if (is_string($value)) {
            // If empty string, set to empty array
            if (trim($value) === '') {
                $this->enabledIntegrations = [];
            } else {
                // Single integration handle as string, convert to array
                $this->enabledIntegrations = [$value];
            }
        } elseif (is_array($value)) {
            $this->enabledIntegrations = $value;
        } else {
            $this->enabledIntegrations = [];
        }
    }

    /**
     * Set default QR logo ID from asset field (handles array input)
     *
     * @param int|array|null $value
     */
    public function setDefaultQrLogoId(int|array|null $value): void
    {
        if (is_array($value)) {
            $this->defaultQrLogoId = !empty($value) ? (int) reset($value) : null;
        } else {
            $this->defaultQrLogoId = $value !== null ? (int) $value : null;
        }
    }

    /**
     * Validate log level - debug requires devMode
     */
    public function validateLogLevel($attribute, $params, $validator)
    {
        $logLevel = $this->$attribute;

        // Reset session warning when devMode is true - allows warning to show again if devMode changes
        if (Craft::$app->getConfig()->getGeneral()->devMode && !Craft::$app->getRequest()->getIsConsoleRequest()) {
            Craft::$app->getSession()->remove('sl_debug_config_warning');
        }

        // Debug level is only allowed when devMode is enabled
        if ($logLevel === 'debug' && !Craft::$app->getConfig()->getGeneral()->devMode) {
            $this->$attribute = 'info';

            if ($this->isOverriddenByConfig('logLevel')) {
                if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
                    if (Craft::$app->getSession()->get('sl_debug_config_warning') === null) {
                        $this->logWarning('Log level "debug" from config file changed to "info" because devMode is disabled', [
                            'configFile' => 'config/smartlink-manager.php',
                        ]);
                        Craft::$app->getSession()->set('sl_debug_config_warning', true);
                    }
                } else {
                    $this->logWarning('Log level "debug" from config file changed to "info" because devMode is disabled', [
                        'configFile' => 'config/smartlink-manager.php',
                    ]);
                }
            } else {
                $this->logWarning('Log level automatically changed from "debug" to "info" because devMode is disabled');
                $this->saveToDatabase();
            }
        }
    }

    /**
     * Validate slug prefix to prevent conflicts
     */
    public function validateSlugPrefix($attribute, $params, $validator)
    {
        $slugPrefix = $this->$attribute;

        if (empty($slugPrefix)) {
            return;
        }

        $conflicts = [];

        // Check against ShortLink Manager if installed
        if (PluginHelper::isPluginInstalled('shortlink-manager')) {
            try {
                $shortlinkPlugin = PluginHelper::getPlugin('shortlink-manager');
                if ($shortlinkPlugin) {
                    $shortlinkSettings = $shortlinkPlugin->getSettings();
                    $shortlinkPluginName = $shortlinkSettings->pluginName ?? 'ShortLink Manager';

                    // Check against ShortLink Manager slugPrefix
                    /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                    $shortlinkSlugPrefix = property_exists($shortlinkSettings, 'slugPrefix') ? $shortlinkSettings->slugPrefix : 's';
                    if ($slugPrefix === $shortlinkSlugPrefix) {
                        $conflicts[] = "{$shortlinkPluginName} slug prefix ('{$shortlinkSlugPrefix}')";
                    }

                    // Check against ShortLink Manager qrPrefix
                    /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                    $shortlinkQrPrefix = property_exists($shortlinkSettings, 'qrPrefix') ? $shortlinkSettings->qrPrefix : 'qr';
                    if ($slugPrefix === $shortlinkQrPrefix) {
                        $conflicts[] = "{$shortlinkPluginName} QR prefix ('{$shortlinkQrPrefix}')";
                    }
                }
            } catch (\Exception $e) {
                // Silently continue if we can't check shortlink-manager
            }
        }

        if (!empty($conflicts)) {
            $suggestions = ['go', 'link', 'links', 'l'];
            $this->addError($attribute, Craft::t('smartlink-manager', 'Slug prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}', [
                'prefix' => $slugPrefix,
                'conflicts' => implode(', ', $conflicts),
                'suggestions' => implode(', ', $suggestions),
            ]));
        }
    }

    /**
     * Validate QR prefix to prevent conflicts
     */
    public function validateQrPrefix($attribute, $params, $validator)
    {
        $qrPrefix = $this->$attribute;

        if (empty($qrPrefix)) {
            return;
        }

        $conflicts = [];

        // Parse the prefix (supports both "qr" and "go/qr" patterns)
        $segments = explode('/', $qrPrefix);
        $isNested = count($segments) > 1;

        // Check against own slugPrefix
        if (!$isNested && $qrPrefix === $this->slugPrefix) {
            $this->addError($attribute, Craft::t('smartlink-manager', 'QR prefix cannot be the same as your slug prefix. Try: qr, code, qrc, or {slug}/qr', [
                'slug' => $this->slugPrefix,
            ]));
            return;
        }

        // Check if nested pattern conflicts with own slugPrefix
        if ($isNested) {
            $baseSegment = $segments[0];
            if ($baseSegment !== $this->slugPrefix) {
                $this->addError($attribute, Craft::t('smartlink-manager', 'Nested QR prefix must start with your slug prefix "{slug}". Use: {slug}/{qr} or use standalone like "qr"', [
                    'slug' => $this->slugPrefix,
                    'qr' => $segments[1] ?? 'qr',
                ]));
                return;
            }
        }

        // Check against ShortLink Manager if installed
        if (PluginHelper::isPluginInstalled('shortlink-manager')) {
            try {
                $shortlinkPlugin = PluginHelper::getPlugin('shortlink-manager');
                if ($shortlinkPlugin) {
                    $shortlinkSettings = $shortlinkPlugin->getSettings();
                    $shortlinkPluginName = $shortlinkSettings->pluginName ?? 'ShortLink Manager';

                    // Only check standalone patterns (nested patterns are already validated above)
                    if (!$isNested) {
                        // Check against ShortLink Manager slugPrefix
                        /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                        $shortlinkSlugPrefix = property_exists($shortlinkSettings, 'slugPrefix') ? $shortlinkSettings->slugPrefix : 's';
                        if ($qrPrefix === $shortlinkSlugPrefix) {
                            $conflicts[] = "{$shortlinkPluginName} slug prefix ('{$shortlinkSlugPrefix}')";
                        }

                        // Check against ShortLink Manager qrPrefix
                        /** @phpstan-ignore-next-line - Dynamic property access on plugin settings */
                        $shortlinkQrPrefix = property_exists($shortlinkSettings, 'qrPrefix') ? $shortlinkSettings->qrPrefix : 'qr';
                        if ($qrPrefix === $shortlinkQrPrefix) {
                            $conflicts[] = "{$shortlinkPluginName} QR prefix ('{$shortlinkQrPrefix}')";
                        }
                    }
                }
            } catch (\Exception $e) {
                // Silently continue if we can't check shortlink-manager
            }
        }

        if (!empty($conflicts)) {
            $suggestions = ['qr', 'qrc', 'code', $this->slugPrefix . '/qr'];
            $this->addError($attribute, Craft::t('smartlink-manager', 'QR prefix "{prefix}" conflicts with: {conflicts}. Suggestions: {suggestions}', [
                'prefix' => $qrPrefix,
                'conflicts' => implode(', ', $conflicts),
                'suggestions' => implode(', ', $suggestions),
            ]));
        }
    }

    /**
     * Check if a site is enabled for SmartLink Manager
     *
     * @param int $siteId
     * @return bool
     */
    public function isSiteEnabled(int $siteId): bool
    {
        // If no sites are specifically enabled, assume all sites are enabled (backwards compatibility)
        if (empty($this->enabledSites)) {
            return true;
        }

        return in_array($siteId, $this->enabledSites);
    }

    /**
     * Get enabled site IDs, defaulting to all sites if none specified
     *
     * @return array
     */
    public function getEnabledSiteIds(): array
    {
        if (empty($this->enabledSites)) {
            // Return all site IDs if none specifically enabled
            return array_map(function($site) {
                return $site->id;
            }, Craft::$app->getSites()->getAllSites());
        }

        return $this->enabledSites;
    }

    /**
     * Get attribute labels
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'pluginName' => Craft::t('smartlink-manager', 'Plugin Name'),
            'slugPrefix' => Craft::t('smartlink-manager', 'Smart Link URL Prefix'),
            'qrPrefix' => Craft::t('smartlink-manager', 'QR Code URL Prefix'),
            'enableAnalytics' => Craft::t('smartlink-manager', 'Enable Analytics'),
            'analyticsRetention' => Craft::t('smartlink-manager', 'Analytics Retention (days)'),
            'includeDisabledInExport' => Craft::t('smartlink-manager', 'Include Disabled Links in Export'),
            'includeExpiredInExport' => Craft::t('smartlink-manager', 'Include Expired Links in Export'),
            'defaultQrSize' => Craft::t('smartlink-manager', 'Default QR Code Size'),
            'defaultQrColor' => Craft::t('smartlink-manager', 'Default QR Code Color'),
            'defaultQrBgColor' => Craft::t('smartlink-manager', 'Default QR Background Color'),
            'defaultQrFormat' => Craft::t('smartlink-manager', 'Default QR Code Format'),
            'qrCodeCacheDuration' => Craft::t('smartlink-manager', 'QR Code Cache Duration (seconds)'),
            'cacheStorageMethod' => Craft::t('smartlink-manager', 'Cache Storage Method'),
            'defaultQrErrorCorrection' => Craft::t('smartlink-manager', 'Error Correction Level'),
            'defaultQrMargin' => Craft::t('smartlink-manager', 'QR Code Margin'),
            'qrModuleStyle' => Craft::t('smartlink-manager', 'Module Style'),
            'qrEyeStyle' => Craft::t('smartlink-manager', 'Eye Style'),
            'qrEyeColor' => Craft::t('smartlink-manager', 'Eye Color'),
            'enableQrLogo' => Craft::t('smartlink-manager', 'Enable QR Code Logo'),
            'qrLogoVolumeUid' => Craft::t('smartlink-manager', 'Logo Volume'),
            'imageVolumeUid' => Craft::t('smartlink-manager', 'Image Volume'),
            'defaultQrLogoId' => Craft::t('smartlink-manager', 'Default Logo'),
            'qrLogoSize' => Craft::t('smartlink-manager', 'Logo Size (%)'),
            'enableQrDownload' => Craft::t('smartlink-manager', 'Enable QR Code Downloads'),
            'qrDownloadFilename' => Craft::t('smartlink-manager', 'Download Filename Pattern'),
            'redirectTemplate' => Craft::t('smartlink-manager', 'Custom Redirect Template'),
            'qrTemplate' => Craft::t('smartlink-manager', 'Custom QR Code Template'),
            'enableGeoDetection' => Craft::t('smartlink-manager', 'Enable Geographic Detection'),
            'cacheDeviceDetection' => Craft::t('smartlink-manager', 'Cache Device Detection'),
            'deviceDetectionCacheDuration' => Craft::t('smartlink-manager', 'Device Detection Cache Duration (seconds)'),
            'languageDetectionMethod' => Craft::t('smartlink-manager', 'Language Detection Method'),
            'itemsPerPage' => Craft::t('smartlink-manager', 'Items Per Page'),
            'notFoundRedirectUrl' => Craft::t('smartlink-manager', '404 Redirect URL'),
            'enabledSites' => Craft::t('smartlink-manager', 'Enabled Sites'),
            'logLevel' => Craft::t('smartlink-manager', 'Log Level'),
            'enabledIntegrations' => Craft::t('smartlink-manager', 'Enabled Integrations'),
            'seomaticTrackingEvents' => Craft::t('smartlink-manager', 'Tracking Events'),
            'seomaticEventPrefix' => Craft::t('smartlink-manager', 'Event Prefix'),
        ];
    }
}
