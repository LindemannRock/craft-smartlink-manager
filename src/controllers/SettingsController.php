<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\helpers\Json;
use craft\web\Controller;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\jobs\CleanupAnalyticsJob;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Settings Controller
 *
 * @since 1.0.0
 */
class SettingsController extends Controller
{
    use LoggingTrait;

    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * @var bool
     */
    private bool $readOnly;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action): bool
    {
        $this->requirePermission('smartLinkManager:manageSettings');

        // Only field layouts respect allowAdminChanges (system config)
        // All other settings are operational and should always be editable
        if ($action->id === 'save-field-layout') {
            if (!Craft::$app->getConfig()->getGeneral()->allowAdminChanges) {
                throw new ForbiddenHttpException('Administrative changes are disallowed in this environment.');
            }
        }

        $this->readOnly = ($action->id === 'field-layout' || $action->id === 'save-field-layout') && !Craft::$app->getConfig()->getGeneral()->allowAdminChanges;
        return parent::beforeAction($action);
    }

    /**
     * Settings index - redirect to general
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionIndex(): Response
    {
        return $this->redirect('smartlink-manager/settings/general');
    }

    /**
     * Debug settings loading
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionDebug(): Response
    {
        $this->requirePermission('smartLinkManager:manageSettings');

        // Test database query directly
        $row = (new \craft\db\Query())
            ->from('{{%smartlinkmanager_settings}}')
            ->where(['id' => 1])
            ->one();

        $settings = Settings::loadFromDatabase();

        return $this->asJson([
            'database_row' => $row,
            'loaded_settings' => $settings->getAttributes(),
            'settings_class' => get_class($settings),
        ]);
    }

    /**
     * General settings
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionGeneral(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        // Debug: Make absolutely sure we have a settings object
        if (!$settings instanceof Settings) {
            throw new \Exception('Settings is not an instance of Settings class');
        }

        // Minimal test
        try {
            return $this->renderTemplate('smartlink-manager/settings/general', [
                'settings' => $settings,
                'plugin' => $plugin,
                'readOnly' => $this->readOnly,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Template render error: ' . $e->getMessage());
        }
    }

    /**
     * Analytics settings
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionAnalytics(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/analytics', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Integrations settings
     *
     * @return Response
     * @since 1.23.0
     */
    public function actionIntegrations(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/integrations', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Export settings
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionExport(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/export', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * QR Code settings
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionQrCode(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/qr-code', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Behavior settings
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionBehavior(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/behavior', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Interface settings
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionInterface(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/interface', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Cache settings
     *
     * @return Response
     * @since 5.9.0
     */
    public function actionCache(): Response
    {
        // Get settings from plugin (includes config overrides)
        $plugin = SmartLinkManager::getInstance();
        $settings = $plugin->getSettings();

        return $this->renderTemplate('smartlink-manager/settings/cache', [
            'settings' => $settings,
            'readOnly' => $this->readOnly,
        ]);
    }

    /**
     * Field Layout settings
     *
     * @return Response
     * @since 1.14.0
     */
    public function actionFieldLayout(): Response
    {
        // Try new format first (smartlink-manager.fieldLayouts)
        $fieldLayouts = Craft::$app->getProjectConfig()->get('smartlink-manager.fieldLayouts') ?? [];

        $fieldLayout = null;

        if (!empty($fieldLayouts)) {
            // Get the first (and only) field layout
            $fieldLayoutUid = array_key_first($fieldLayouts);
            $fieldLayout = Craft::$app->getFields()->getLayoutByUid($fieldLayoutUid);
        }

        // Backwards compatibility: try old format (smartlink-manager.fieldLayout with just UID)
        if (!$fieldLayout) {
            $oldUid = Craft::$app->getProjectConfig()->get('smartlink-manager.fieldLayout');
            if ($oldUid) {
                $fieldLayout = Craft::$app->getFields()->getLayoutByUid($oldUid);
            }
        }

        // Fallback: try to get by type (in case it exists in database)
        if (!$fieldLayout) {
            $fieldLayout = Craft::$app->getFields()->getLayoutByType(\lindemannrock\smartlinkmanager\elements\SmartLink::class);
        }

        if (!$fieldLayout) {
            // Create a new field layout if none exists
            $fieldLayout = new \craft\models\FieldLayout([
                'type' => \lindemannrock\smartlinkmanager\elements\SmartLink::class,
            ]);

            // Save the empty field layout so it has an ID (needed for designer to work)
            Craft::$app->getFields()->saveLayout($fieldLayout);

            // Save to project config only if not in read-only mode
            if (!$this->readOnly) {
                $fieldLayoutConfig = $fieldLayout->getConfig();
                if ($fieldLayoutConfig) {
                    Craft::$app->getProjectConfig()->set(
                        "smartlink-manager.fieldLayouts.{$fieldLayout->uid}",
                        $fieldLayoutConfig,
                        "Create SmartLink Manager field layout"
                    );
                }
            }
        }

        // Debug field layout
        $this->logDebug('Field Layout debug info', [
            'id' => $fieldLayout->id ?? 'null',
            'uid' => $fieldLayout->uid ?? 'null',
            'type' => $fieldLayout->type ?? 'null',
            'class' => get_class($fieldLayout),
        ]);

        $variables = [
            'fieldLayout' => $fieldLayout,
            'readOnly' => $this->readOnly,
        ];

        // Debug logging
        $this->logDebug('actionFieldLayout called', [
            'fieldLayout_exists' => $fieldLayout !== null,
            'fieldLayout_id' => $fieldLayout->id,
            'readOnly' => $this->readOnly,
        ]);

        return $this->renderTemplate('smartlink-manager/settings/field-layout', $variables);
    }

    /**
     * Save field layout
     *
     * @return Response|null
     * @since 1.14.0
     */
    public function actionSaveFieldLayout(): ?Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinkManager:manageSettings');

        $fieldLayout = Craft::$app->getFields()->assembleLayoutFromPost();
        $fieldLayout->type = \lindemannrock\smartlinkmanager\elements\SmartLink::class;

        if (!Craft::$app->getFields()->saveLayout($fieldLayout)) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Couldn\'t save field layout.'));
            return null;
        }

        // Save field layout config to project config so it syncs across environments
        $fieldLayoutConfig = $fieldLayout->getConfig();
        if ($fieldLayoutConfig) {
            Craft::$app->getProjectConfig()->set(
                "smartlink-manager.fieldLayouts.{$fieldLayout->uid}",
                $fieldLayoutConfig,
                "Save SmartLink Manager field layout"
            );

            // Remove old format if it exists (migration)
            if (Craft::$app->getProjectConfig()->get('smartlink-manager.fieldLayout')) {
                Craft::$app->getProjectConfig()->remove('smartlink-manager.fieldLayout');
            }
        }

        Craft::$app->getSession()->setNotice(Craft::t('smartlink-manager', 'Field layout saved.'));
        return $this->redirectToPostedUrl();
    }

    /**
     * Save settings
     *
     * @return Response|null
     * @since 1.0.0
     */
    public function actionSave(): ?Response
    {
        // Basic debug - write to file
        file_put_contents('/tmp/smartlink-manager-debug.log', date('Y-m-d H:i:s') . " - Save action called\n", FILE_APPEND);

        $this->requirePostRequest();

        // Check permission first
        $this->requirePermission('smartLinkManager:manageSettings');

        // No need to check allowAdminChanges since settings are stored in database
        // not in project config

        // Load current settings from database
        $settings = Settings::loadFromDatabase();

        $settingsData = Craft::$app->getRequest()->getBodyParam('settings');

        // Debug: Log what we received
        $this->logDebug('Settings data received', ['settingsData' => $settingsData]);

        // Debug: Specifically check imageVolumeUid
        if (isset($settingsData['imageVolumeUid'])) {
            $this->logDebug('imageVolumeUid debug', [
                'type' => gettype($settingsData['imageVolumeUid']),
                'value' => $settingsData['imageVolumeUid'],
            ]);
        }

        // Debug: Log all POST data
        $this->logDebug('All POST data', ['bodyParams' => Craft::$app->getRequest()->getBodyParams()]);

        // Handle enabledSites checkbox group
        if (isset($settingsData['enabledSites'])) {
            if (is_array($settingsData['enabledSites'])) {
                // Convert string values to integers
                $settingsData['enabledSites'] = array_map('intval', array_filter($settingsData['enabledSites']));
            } else {
                $settingsData['enabledSites'] = [];
            }
        } else {
            // No sites selected = empty array (which means all sites enabled)
            $settingsData['enabledSites'] = [];
        }

        // Handle asset field (returns array)
        if (isset($settingsData['defaultQrLogoId']) && is_array($settingsData['defaultQrLogoId'])) {
            $settingsData['defaultQrLogoId'] = $settingsData['defaultQrLogoId'][0] ?? null;
        }

        // Auto-set qrLogoVolumeUid to same value as imageVolumeUid
        if (isset($settingsData['imageVolumeUid'])) {
            $settingsData['qrLogoVolumeUid'] = $settingsData['imageVolumeUid'];
            $this->logDebug('Auto-setting qrLogoVolumeUid to match imageVolumeUid', ['uid' => $settingsData['imageVolumeUid']]);
        }

        // Fix color fields - add # if missing
        if (isset($settingsData['defaultQrColor']) && !str_starts_with($settingsData['defaultQrColor'], '#')) {
            $settingsData['defaultQrColor'] = '#' . $settingsData['defaultQrColor'];
        }
        if (isset($settingsData['defaultQrBgColor']) && !str_starts_with($settingsData['defaultQrBgColor'], '#')) {
            $settingsData['defaultQrBgColor'] = '#' . $settingsData['defaultQrBgColor'];
        }
        if (isset($settingsData['qrEyeColor'])) {
            if (empty($settingsData['qrEyeColor'])) {
                // If empty, set to null
                $settingsData['qrEyeColor'] = null;
            } elseif (!str_starts_with($settingsData['qrEyeColor'], '#')) {
                // If not empty and doesn't start with #, add it
                $settingsData['qrEyeColor'] = '#' . $settingsData['qrEyeColor'];
            }
        }

        // Only update fields that were posted and are not overridden by config
        foreach ($settingsData as $key => $value) {
            if (!$settings->isOverriddenByConfig($key) && property_exists($settings, $key)) {
                // Handle special array field conversions
                if ($key === 'enabledIntegrations') {
                    // Decode JSON string from hidden field
                    $settings->enabledIntegrations = is_string($value) ? json_decode($value, true) : (is_array($value) ? $value : []);
                } elseif ($key === 'redirectManagerEvents') {
                    // Already an array from checkbox fields
                    $settings->redirectManagerEvents = is_array($value) ? $value : [];
                } else {
                    // Check for setter method first (handles array conversions, etc.)
                    $setterMethod = 'set' . ucfirst($key);
                    if (method_exists($settings, $setterMethod)) {
                        $settings->$setterMethod($value);
                    } else {
                        $settings->$key = $value;
                    }
                }
            }
        }

        // Debug: Log what's in settings after updates
        $this->logDebug('Settings after updates', ['enabledSites' => $settings->enabledSites]);

        // Validate (includes conflict checking via validateSlugPrefix and validateQrPrefix)
        if (!$settings->validate()) {
            // Log validation errors for debugging
            $this->logError('Settings validation failed', ['errors' => $settings->getErrors()]);

            // Standard Craft way: Pass errors back to template
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Couldn\'t save settings.'));

            // Re-render the template with errors
            // Get the section from the request to render the correct template
            $section = Craft::$app->getRequest()->getBodyParam('section', 'general');
            $template = "smartlink-manager/settings/{$section}";

            return $this->renderTemplate($template, [
                'settings' => $settings,
            ]);
        }

        // Save settings to database
        if ($settings->saveToDatabase()) {
            // Update the plugin's cached settings if plugin is available
            $plugin = SmartLinkManager::getInstance();
            if ($plugin) {
                // setSettings expects an array, not an object
                $plugin->setSettings($settings->getAttributes());
            }

            Craft::$app->getSession()->setNotice(Craft::t('smartlink-manager', 'Settings saved.'));
        } else {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Couldn\'t save settings.'));

            // Get the section to re-render the correct template with errors
            $section = $this->request->getBodyParam('section', 'general');
            $template = "smartlink-manager/settings/{$section}";

            return $this->renderTemplate($template, [
                'settings' => $settings,
                'readOnly' => $this->readOnly,
            ]);
        }

        return $this->redirectToPostedUrl();
    }

    /**
     * Cleanup analytics data
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @since 1.0.0
     */
    public function actionCleanupAnalytics(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Check admin permissions
        if (!Craft::$app->getUser()->getIsAdmin()) {
            throw new ForbiddenHttpException('User is not an admin');
        }

        try {
            // Queue the cleanup job
            Craft::$app->queue->push(new CleanupAnalyticsJob());

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smartlink-manager', 'Analytics cleanup job has been queued. It will run in the background.'),
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear QR code cache
     *
     * @return Response
     * @since 5.9.0
     */
    public function actionClearQrCache(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinkManager:clearCache');
        $this->requireAcceptsJson();

        try {
            $settings = SmartLinkManager::$plugin->getSettings();
            $cleared = 0;

            if ($settings->cacheStorageMethod === 'redis') {
                // Clear Redis cache
                $cache = Craft::$app->cache;
                if ($cache instanceof \yii\redis\Cache) {
                    $redis = $cache->redis;

                    // Get all QR cache keys from tracking set
                    $keys = $redis->executeCommand('SMEMBERS', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'qr')]) ?: [];

                    // Delete QR cache keys using Craft's cache component
                    foreach ($keys as $key) {
                        $cache->delete($key);
                    }

                    // Clear the tracking set
                    $redis->executeCommand('DEL', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'qr')]);
                }
            } else {
                // Clear file cache
                $cachePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'qr');
                if (is_dir($cachePath)) {
                    $files = glob($cachePath . '*.cache');
                    foreach ($files as $file) {
                        if (@unlink($file)) {
                            $cleared++;
                        }
                    }
                }
            }

            $message = $settings->cacheStorageMethod === 'redis'
                ? Craft::t('smartlink-manager', 'QR code cache cleared successfully.')
                : Craft::t('smartlink-manager', 'Cleared {count} QR code caches.', ['count' => $cleared]);

            return $this->asJson([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear device detection cache
     *
     * @return Response
     * @since 5.9.0
     */
    public function actionClearDeviceCache(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinkManager:clearCache');
        $this->requireAcceptsJson();

        try {
            $settings = SmartLinkManager::$plugin->getSettings();
            $cleared = 0;

            if ($settings->cacheStorageMethod === 'redis') {
                // Clear Redis cache
                $cache = Craft::$app->cache;
                if ($cache instanceof \yii\redis\Cache) {
                    $redis = $cache->redis;

                    // Get all device cache keys from tracking set
                    $keys = $redis->executeCommand('SMEMBERS', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'device')]) ?: [];

                    // Delete device cache keys using Craft's cache component
                    foreach ($keys as $key) {
                        $cache->delete($key);
                    }

                    // Clear the tracking set
                    $redis->executeCommand('DEL', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'device')]);
                }
            } else {
                // Clear file cache
                $cachePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'device');
                if (is_dir($cachePath)) {
                    $files = glob($cachePath . '*.cache');
                    foreach ($files as $file) {
                        if (@unlink($file)) {
                            $cleared++;
                        }
                    }
                }
            }

            $message = $settings->cacheStorageMethod === 'redis'
                ? Craft::t('smartlink-manager', 'Device cache cleared successfully.')
                : Craft::t('smartlink-manager', 'Cleared {count} device detection caches.', ['count' => $cleared]);

            return $this->asJson([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear all SmartLink Manager caches
     *
     * @return Response
     * @since 5.9.0
     */
    public function actionClearAllCaches(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinkManager:clearCache');
        $this->requireAcceptsJson();

        try {
            $settings = SmartLinkManager::$plugin->getSettings();
            $totalCleared = 0;

            if ($settings->cacheStorageMethod === 'redis') {
                // Clear Redis cache
                $cache = Craft::$app->cache;
                if ($cache instanceof \yii\redis\Cache) {
                    $redis = $cache->redis;

                    // Get all QR cache keys from tracking set
                    $qrKeys = $redis->executeCommand('SMEMBERS', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'qr')]) ?: [];

                    // Delete QR cache keys using Craft's cache component
                    foreach ($qrKeys as $key) {
                        $cache->delete($key);
                    }

                    // Get all device cache keys from tracking set
                    $deviceKeys = $redis->executeCommand('SMEMBERS', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'device')]) ?: [];

                    // Delete device cache keys using Craft's cache component
                    foreach ($deviceKeys as $key) {
                        $cache->delete($key);
                    }

                    // Clear the tracking sets
                    $redis->executeCommand('DEL', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'qr')]);
                    $redis->executeCommand('DEL', [PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'device')]);
                }
            } else {
                // Clear QR code file caches
                $qrPath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'qr');
                if (is_dir($qrPath)) {
                    $files = glob($qrPath . '*.cache');
                    foreach ($files as $file) {
                        if (@unlink($file)) {
                            $totalCleared++;
                        }
                    }
                }

                // Clear device detection file caches
                $devicePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'device');
                if (is_dir($devicePath)) {
                    $files = glob($devicePath . '*.cache');
                    foreach ($files as $file) {
                        if (@unlink($file)) {
                            $totalCleared++;
                        }
                    }
                }
            }

            $message = $settings->cacheStorageMethod === 'redis'
                ? Craft::t('smartlink-manager', 'All caches cleared successfully.')
                : Craft::t('smartlink-manager', 'Cleared {count} cache entries.', ['count' => $totalCleared]);

            return $this->asJson([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear all analytics data
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionClearAllAnalytics(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('smartLinkManager:clearAnalytics');

        try {
            // Get count before deleting
            $count = (new \craft\db\Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->count();

            // Delete all analytics records
            Craft::$app->db->createCommand()
                ->delete('{{%smartlinkmanager_analytics}}')
                ->execute();

            // Reset click counts in metadata on all smart links
            $smartLinks = SmartLink::find()->all();
            foreach ($smartLinks as $smartLink) {
                $metadata = $smartLink->metadata ?? [];
                $metadata['clicks'] = 0;
                $metadata['lastClick'] = null;
                Craft::$app->db->createCommand()
                    ->update('{{%smartlinkmanager}}', [
                        'metadata' => Json::encode($metadata),
                    ], ['id' => $smartLink->id])
                    ->execute();
            }

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smartlink-manager', 'Cleared {count} analytics records and reset all click counts.', ['count' => $count]),
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean up invalid platform values in analytics data
     *
     * @return Response
     * @since 1.18.0
     */
    public function actionCleanupPlatformValues(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        // Require admin permission
        if (!Craft::$app->getUser()->getIsAdmin()) {
            return $this->asJson([
                'success' => false,
                'error' => Craft::t('smartlink-manager', 'Only administrators can clean up analytics data.'),
            ]);
        }

        try {
            $updated = SmartLinkManager::$plugin->analytics->cleanupPlatformValues();

            return $this->asJson([
                'success' => true,
                'message' => Craft::t('smartlink-manager', 'Cleaned up {count} analytics records with invalid platform values.', ['count' => $updated]),
            ]);
        } catch (\Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
