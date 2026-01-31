<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * Intelligent device detection and app store routing
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterCacheOptionsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\fields\Link as LinkField;
use craft\services\Dashboard;
use craft\services\Elements;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\services\Utilities;
use craft\utilities\ClearCaches;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\logginglibrary\LoggingLibrary;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\fields\SmartLinkField;
use lindemannrock\smartlinkmanager\integrations\SmartLinkType;
use lindemannrock\smartlinkmanager\jobs\CleanupAnalyticsJob;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\services\AnalyticsService;
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;
use lindemannrock\smartlinkmanager\services\IntegrationService;
use lindemannrock\smartlinkmanager\services\QrCodeService;
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use lindemannrock\smartlinkmanager\utilities\SmartLinksUtility;
use lindemannrock\smartlinkmanager\variables\SmartLinksVariable;
use lindemannrock\smartlinkmanager\widgets\AnalyticsSummaryWidget;
use lindemannrock\smartlinkmanager\widgets\TopLinksWidget;
use yii\base\Event;

/**
 * SmartLink Manager Plugin
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     1.0.0
 *
 * @property-read SmartLinksService $smartLinks
 * @property-read DeviceDetectionService $deviceDetection
 * @property-read QrCodeService $qrCode
 * @property-read AnalyticsService $analytics
 * @property-read IntegrationService $integration
 * @property-read Settings $settings
 * @method Settings getSettings()
 */
class SmartLinkManager extends Plugin
{
    use LoggingTrait;

    /**
     * @var SmartLinkManager|null Singleton plugin instance
     */
    public static ?SmartLinkManager $plugin = null;

    /**
     * @var string Plugin schema version for migrations
     */
    public string $schemaVersion = '1.0.0';

    /**
     * @var bool Whether the plugin exposes a control panel settings page
     */
    public bool $hasCpSettings = true;

    /**
     * @var bool Whether the plugin registers a control panel section
     */
    public bool $hasCpSection = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        self::$plugin = $this;

        // Bootstrap shared plugin functionality (Twig helper, logging)
        PluginHelper::bootstrap(
            $this,
            'smartlinkHelper',
            ['smartLinkManager:viewLogs'],
            ['smartLinkManager:downloadLogs']
        );
        PluginHelper::applyPluginNameFromConfig($this);

        // Register services
        $this->setComponents([
            'smartLinks' => SmartLinksService::class,
            'deviceDetection' => DeviceDetectionService::class,
            'qrCode' => QrCodeService::class,
            'analytics' => AnalyticsService::class,
            'integration' => IntegrationService::class,
        ]);

        // Schedule analytics cleanup if retention is enabled
        $this->scheduleAnalyticsCleanup();

        // Register project config event handlers
        $this->registerProjectConfigEventHandlers();

        // Register translations
        Craft::$app->i18n->translations['smartlink-manager'] = [
            'class' => \craft\i18n\PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'basePath' => __DIR__ . '/translations',
            'forceTranslation' => true,
            'allowOverrides' => true,
        ];

        // Register template roots
        Event::on(
            View::class,
            View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
            function(RegisterTemplateRootsEvent $event) {
                $event->roots['smartlink-manager'] = __DIR__ . '/templates';
            }
        );

        // Register element type
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLink::class;
            }
        );

        // Register field type
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLinkField::class;
            }
        );

        // Register Link field integration
        Event::on(
            LinkField::class,
            LinkField::EVENT_REGISTER_LINK_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLinkType::class;
            }
        );

        // Register CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->getCpUrlRules());
            }
        );

        // Register site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function(RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
            }
        );

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function(Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('smartLinks', SmartLinksVariable::class);
            }
        );

        // Register permissions
        Event::on(
            UserPermissions::class,
            UserPermissions::EVENT_REGISTER_PERMISSIONS,
            function(RegisterUserPermissionsEvent $event) {
                $event->permissions[] = [
                    'heading' => Craft::t('smartlink-manager', 'SmartLink Manager'),
                    'permissions' => $this->getPluginPermissions(),
                ];
            }
        );

        // Register utilities
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITIES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = SmartLinksUtility::class;
            }
        );

        // Register dashboard widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = AnalyticsSummaryWidget::class;
                $event->types[] = TopLinksWidget::class;
            }
        );

        // Register cache clearing options
        Event::on(
            ClearCaches::class,
            ClearCaches::EVENT_REGISTER_CACHE_OPTIONS,
            function(RegisterCacheOptionsEvent $event) {
                // Only show cache option if user has permission to clear cache
                if (!Craft::$app->getUser()->checkPermission('smartLinkManager:clearCache')) {
                    return;
                }

                $settings = $this->getSettings();
                $displayName = $settings->getDisplayName();

                $event->options[] = [
                    'key' => 'smartlink-manager-cache',
                    'label' => Craft::t('smartlink-manager', '{displayName} caches', ['displayName' => $displayName]),
                    'action' => function() use ($settings) {
                        $cleared = 0;

                        if ($settings->cacheStorageMethod === 'redis') {
                            // Clear Redis cache
                            $cache = Craft::$app->cache;
                            if ($cache instanceof \yii\redis\Cache) {
                                $redis = $cache->redis;

                                // Get all keys from tracking sets
                                $qrKeys = $redis->executeCommand('SMEMBERS', ['smartlinkmanager-qr-keys']) ?: [];
                                $deviceKeys = $redis->executeCommand('SMEMBERS', ['smartlinkmanager-device-keys']) ?: [];

                                // Delete QR cache keys using Craft's cache component
                                foreach ($qrKeys as $key) {
                                    $cache->delete($key);
                                }

                                // Delete device cache keys using Craft's cache component
                                foreach ($deviceKeys as $key) {
                                    $cache->delete($key);
                                }

                                // Clear the tracking sets
                                $redis->executeCommand('DEL', ['smartlinkmanager-qr-keys']);
                                $redis->executeCommand('DEL', ['smartlinkmanager-device-keys']);
                            }
                        } else {
                            // Clear QR code file caches
                            $qrPath = PluginHelper::getCachePath(self::$plugin, 'qr');
                            if (is_dir($qrPath)) {
                                $files = glob($qrPath . '*.cache');
                                foreach ($files as $file) {
                                    if (@unlink($file)) {
                                        $cleared++;
                                    }
                                }
                            }

                            // Clear device detection file caches
                            $devicePath = PluginHelper::getCachePath(self::$plugin, 'device');
                            if (is_dir($devicePath)) {
                                $files = glob($devicePath . '*.cache');
                                foreach ($files as $file) {
                                    if (@unlink($file)) {
                                        $cleared++;
                                    }
                                }
                            }
                        }

                        $this->logInfo('Cleared cache entries', [
                            'pluginName' => $this->getSettings()->getFullName(),
                            'count' => $cleared,
                        ]);
                    },
                ];
            }
        );

        // Listen for settings changes to reschedule cleanup
        Event::on(
            Settings::class,
            Settings::EVENT_AFTER_SAVE_SETTINGS,
            function(Event $event) {
                /** @var Settings $settings */
                $settings = $event->sender;

                // The cleanup job will check the current settings when it runs
                // No need to clear existing jobs as they will adapt to new settings

                // If retention was just enabled (from 0), schedule a new cleanup
                if ($settings->analyticsRetention > 0) {
                    // Check if we need to schedule a new cleanup
                    // The job will handle re-queuing itself after each run
                    $this->scheduleAnalyticsCleanup();
                }

                $this->logInfo('Analytics cleanup settings updated');
            }
        );

        // DO NOT log in init() - it's called on every request
    }

    /**
     * @inheritdoc
     */
    public function getCpNavItem(): ?array
    {
        $settings = $this->getSettings();

        // Show nav if enabled for ANY site (not just current site)
        // This allows managing smartlinks from any CP context, even if
        // smartlinks are only enabled for a dedicated short URL site
        $enabledSiteIds = $settings->getEnabledSiteIds();
        if (empty($enabledSiteIds)) {
            return null; // Hide navigation item if not enabled for any site
        }

        $item = parent::getCpNavItem();
        $user = Craft::$app->getUser();

        // Check if user has view access to each section
        $hasLinksAccess = $user->checkPermission('smartLinkManager:viewLinks');
        $hasAnalyticsAccess = $user->checkPermission('smartLinkManager:viewAnalytics') && $settings->enableAnalytics;
        $hasLogsAccess = $user->checkPermission('smartLinkManager:viewLogs');
        $hasSettingsAccess = $user->checkPermission('smartLinkManager:manageSettings');

        // If no access at all, hide the plugin from nav
        if (!$hasLinksAccess && !$hasAnalyticsAccess && !$hasLogsAccess && !$hasSettingsAccess) {
            return null;
        }

        if ($item) {
            $item['label'] = $settings->getFullName();

            // Use Craft's built-in link icon
            $item['icon'] = '@appicons/link.svg';

            $item['subnav'] = [];

            if ($hasLinksAccess) {
                $item['subnav']['links'] = [
                    'label' => 'Links',
                    'url' => 'smartlink-manager',
                ];
            }

            if ($hasAnalyticsAccess) {
                $item['subnav']['analytics'] = [
                    'label' => Craft::t('smartlink-manager', 'Analytics'),
                    'url' => 'smartlink-manager/analytics',
                ];
            }

            // Add logs section using the logging library
            if (Craft::$app->getPlugins()->isPluginInstalled('logging-library') &&
                Craft::$app->getPlugins()->isPluginEnabled('logging-library')) {
                $item = LoggingLibrary::addLogsNav($item, $this->handle, [
                    'smartLinkManager:viewLogs',
                ]);
            }

            if ($hasSettingsAccess) {
                $item['subnav']['settings'] = [
                    'label' => Craft::t('smartlink-manager', 'Settings'),
                    'url' => 'smartlink-manager/settings',
                ];
            }
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?Model
    {
        // Always load fresh settings from database
        $settings = Settings::loadFromDatabase();

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): ?Model
    {
        $settings = parent::getSettings();

        if ($settings) {
            // Override with config file values using Craft's native multi-environment handling
            // This properly merges '*' with environment-specific configs (e.g., 'production')
            $config = Craft::$app->getConfig()->getConfigFromFile('smartlink-manager');
            if (!empty($config) && is_array($config)) {
                foreach ($config as $key => $value) {
                    if (property_exists($settings, $key)) {
                        $settings->$key = $value;
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Get sites where SmartLink Manager is enabled
     *
     * @return array
     */
    public function getEnabledSites(): array
    {
        $settings = $this->getSettings();
        $enabledSiteIds = $settings->getEnabledSiteIds();


        // Return only enabled sites
        return array_filter(Craft::$app->getSites()->getAllSites(), function($site) use ($enabledSiteIds) {
            return in_array($site->id, $enabledSiteIds);
        });
    }

    /**
     * @inheritdoc
     */
    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect('smartlink-manager/settings');
    }

    /**
     * Get CP URL rules
     */
    private function getCpUrlRules(): array
    {
        return [
            // SmartLink Manager routes
            'smartlink-manager' => 'smartlink-manager/smartlinks/index',
            'smartlink-manager/smartlinks' => 'smartlink-manager/smartlinks/index',
            'smartlink-manager/new' => 'smartlink-manager/smartlinks/edit',
            'smartlink-manager/smartlinks/new' => 'smartlink-manager/smartlinks/edit',
            'smartlink-manager/<smartLinkId:\d+>' => 'smartlink-manager/smartlinks/edit',
            'smartlink-manager/smartlinks/<smartLinkId:\d+>' => 'smartlink-manager/smartlinks/edit',
            'smartlink-manager/analytics' => 'smartlink-manager/analytics/index',
            'smartlink-manager/analytics/<linkId:\d+>' => 'smartlink-manager/analytics/link',
            'smartlink-manager/settings' => 'smartlink-manager/settings/index',
            'smartlink-manager/settings/general' => 'smartlink-manager/settings/general',
            'smartlink-manager/settings/analytics' => 'smartlink-manager/settings/analytics',
            'smartlink-manager/settings/integrations' => 'smartlink-manager/settings/integrations',
            'smartlink-manager/settings/export' => 'smartlink-manager/settings/export',
            'smartlink-manager/settings/qr-code' => 'smartlink-manager/settings/qr-code',
            'smartlink-manager/settings/behavior' => 'smartlink-manager/settings/behavior',
            'smartlink-manager/settings/interface' => 'smartlink-manager/settings/interface',
            'smartlink-manager/settings/cache' => 'smartlink-manager/settings/cache',
            'smartlink-manager/settings/field-layout' => 'smartlink-manager/settings/field-layout',
            'smartlink-manager/settings/save' => 'smartlink-manager/settings/save',
            'smartlink-manager/settings/save-field-layout' => 'smartlink-manager/settings/save-field-layout',
            'smartlink-manager/settings/cleanup-analytics' => 'smartlink-manager/settings/cleanup-analytics',
            // QR Code generation for preview
            'smartlink-manager/qr-code/generate' => 'smartlink-manager/qr-code/generate',
        ];
    }

    /**
     * Get site URL rules
     */
    private function getSiteUrlRules(): array
    {
        $settings = $this->getSettings();
        $slugPrefix = $settings->slugPrefix ?? 'go';
        $qrPrefix = $settings->qrPrefix ?? 'qr';

        return [
            $slugPrefix . '/<slug:[a-zA-Z0-9\-\_]+>' => 'smartlink-manager/redirect/index',
            $qrPrefix . '/<slug:[a-zA-Z0-9\-\_]+>' => 'smartlink-manager/qr-code/generate',
            $qrPrefix . '/<slug:[a-zA-Z0-9\-\_]+>/view' => 'smartlink-manager/qr-code/display',
            'smartlink-manager/qr-code/generate' => 'smartlink-manager/qr-code/generate',
        ];
    }

    /**
     * Get plugin permissions
     */
    private function getPluginPermissions(): array
    {
        $settings = $this->getSettings();
        $plural = $settings->getPluralLowerDisplayName();

        return [
            // Smart links - grouped
            'smartLinkManager:manageLinks' => [
                'label' => Craft::t('smartlink-manager', 'Manage {plural}', ['plural' => $plural]),
                'nested' => [
                    'smartLinkManager:viewLinks' => [
                        'label' => Craft::t('smartlink-manager', 'View {plural}', ['plural' => $plural]),
                    ],
                    'smartLinkManager:createLinks' => [
                        'label' => Craft::t('smartlink-manager', 'Create {plural}', ['plural' => $plural]),
                    ],
                    'smartLinkManager:editLinks' => [
                        'label' => Craft::t('smartlink-manager', 'Edit {plural}', ['plural' => $plural]),
                    ],
                    'smartLinkManager:deleteLinks' => [
                        'label' => Craft::t('smartlink-manager', 'Delete {plural}', ['plural' => $plural]),
                    ],
                ],
            ],
            'smartLinkManager:viewAnalytics' => [
                'label' => Craft::t('smartlink-manager', 'View analytics'),
                'nested' => [
                    'smartLinkManager:exportAnalytics' => [
                        'label' => Craft::t('smartlink-manager', 'Export analytics'),
                    ],
                    'smartLinkManager:clearAnalytics' => [
                        'label' => Craft::t('smartlink-manager', 'Clear analytics'),
                    ],
                ],
            ],
            'smartLinkManager:clearCache' => [
                'label' => Craft::t('smartlink-manager', 'Clear cache'),
            ],
            'smartLinkManager:viewLogs' => [
                'label' => Craft::t('smartlink-manager', 'View system logs'),
                'nested' => [
                    'smartLinkManager:downloadLogs' => [
                        'label' => Craft::t('smartlink-manager', 'Download system logs'),
                    ],
                ],
            ],
            'smartLinkManager:manageSettings' => [
                'label' => Craft::t('smartlink-manager', 'Manage settings'),
            ],
        ];
    }

    /**
     * Schedule analytics cleanup job
     *
     * @return void
     */
    private function scheduleAnalyticsCleanup(): void
    {
        $settings = $this->getSettings();

        // Only schedule cleanup if analytics is enabled and retention is set
        if ($settings->enableAnalytics && $settings->analyticsRetention > 0) {
            // Check if a cleanup job is already scheduled
            $existingJob = (new \craft\db\Query())
                ->from('{{%queue}}')
                ->where(['like', 'job', 'smartlinkmanager'])
                ->andWhere(['like', 'job', 'CleanupAnalyticsJob'])
                ->exists();

            if (!$existingJob) {
                // Create cleanup job
                $job = new CleanupAnalyticsJob([
                    'reschedule' => true,
                ]);

                // Add to queue with a small initial delay
                // The job will re-queue itself to run every 24 hours
                Craft::$app->queue->delay(5 * 60)->push($job);

                $this->logInfo('Scheduled initial analytics cleanup job', ['interval' => '24 hours']);
            }
        }
    }

    /**
     * Register project config event handlers
     *
     * @return void
     */
    private function registerProjectConfigEventHandlers(): void
    {
        // Listen for project config changes to field layouts
        Craft::$app->getProjectConfig()
            ->onAdd('smartlink-manager.fieldLayouts.{uid}', [$this, 'handleChangedFieldLayout'])
            ->onUpdate('smartlink-manager.fieldLayouts.{uid}', [$this, 'handleChangedFieldLayout'])
            ->onRemove('smartlink-manager.fieldLayouts.{uid}', [$this, 'handleDeletedFieldLayout']);
    }

    /**
     * Handle field layout changes from project config
     *
     * @param \craft\events\ConfigEvent $event
     * @return void
     */
    public function handleChangedFieldLayout(\craft\events\ConfigEvent $event): void
    {
        // Rebuild field layout from config
        $uid = $event->tokenMatches[0];
        $data = $event->newValue;

        $fieldLayout = \craft\models\FieldLayout::createFromConfig($data);
        $fieldLayout->uid = $uid;
        $fieldLayout->type = \lindemannrock\smartlinkmanager\elements\SmartLink::class;

        Craft::$app->getFields()->saveLayout($fieldLayout, false);

        $this->logInfo('Applied SmartLink Manager field layout from project config', ['uid' => $uid]);
    }

    /**
     * Handle field layout deletion from project config
     *
     * @param \craft\events\ConfigEvent $event
     * @return void
     */
    public function handleDeletedFieldLayout(\craft\events\ConfigEvent $event): void
    {
        $uid = $event->tokenMatches[0];
        $fieldLayout = Craft::$app->getFields()->getLayoutByUid($uid);

        if ($fieldLayout) {
            Craft::$app->getFields()->deleteLayoutById($fieldLayout->id);
        }
    }
}
