<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\web\Controller;
use lindemannrock\base\helpers\DateRangeHelper;
use lindemannrock\base\helpers\ExportHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\Response;

/**
 * Analytics Controller
 *
 * @since 1.0.0
 */
class AnalyticsController extends Controller
{
    use LoggingTrait;
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Analytics dashboard
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionIndex(): Response
    {
        $this->requirePermission('smartLinkManager:viewAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            throw new \yii\web\ForbiddenHttpException('Analytics are disabled in plugin settings.');
        }

        $variables = [
            'title' => Craft::t('smartlink-manager', 'Analytics'),
        ];

        // Get date range and site
        $request = Craft::$app->getRequest();
        $dateRange = $request->getParam('dateRange', DateRangeHelper::getDefaultDateRange(SmartLinkManager::$plugin->id));
        $siteId = $request->getParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;
        $resolvedSiteId = $this->_resolveSiteId($siteId);

        $variables['dateRange'] = $dateRange;
        $variables['siteId'] = $siteId;

        // Get enabled sites for site selector (respects enabledSites + user permissions)
        $settings = SmartLinkManager::$plugin->getSettings();
        $variables['sites'] = SmartLinkManager::$plugin->getEnabledSites();

        // Get analytics data (scoped to user's allowed sites)
        $variables['analyticsData'] = SmartLinkManager::$plugin->analytics->getAnalyticsSummary($dateRange, null, $resolvedSiteId);

        // Pass settings to template
        $variables['settings'] = $settings;
        $variables['pluginHandle'] = SmartLinkManager::$plugin->id;

        return $this->renderTemplate('smartlink-manager/analytics/index', $variables);
    }

    /**
     * Get analytics data via AJAX
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionGetData(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireLogin();
        $this->requirePermission('smartLinkManager:viewAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            return $this->asJson([
                'success' => false,
                'error' => 'Analytics are disabled in plugin settings.',
            ]);
        }

        $request = Craft::$app->getRequest();
        $smartLinkId = $request->getParam('smartLinkId');
        $dateRange = $request->getParam('dateRange', DateRangeHelper::getDefaultDateRange(SmartLinkManager::$plugin->id));
        $type = $request->getParam('type', 'summary');
        $siteId = $request->getParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;
        $resolvedSiteId = $this->_resolveSiteId($siteId);

        // If requesting data for a specific SmartLink, check if it has analytics enabled
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
                ->id($smartLinkId)
                ->status(null)
                ->one();

            if (!$smartLink) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Smart link not found',
                ]);
            }

            if (!($smartLink->trackAnalytics ?? true)) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Analytics tracking is disabled for this smart link',
                ]);
            }
        }

        try {
            $data = match ($type) {
                'clicks' => SmartLinkManager::$plugin->analytics->getClicksData($smartLinkId, $dateRange, $resolvedSiteId),
                'devices' => SmartLinkManager::$plugin->analytics->getDeviceBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'device-types' => SmartLinkManager::$plugin->analytics->getDeviceTypeBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'device-brands' => SmartLinkManager::$plugin->analytics->getDeviceBrandBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'platforms' => SmartLinkManager::$plugin->analytics->getPlatformBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'os-breakdown' => SmartLinkManager::$plugin->analytics->getOsBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'browsers' => SmartLinkManager::$plugin->analytics->getBrowserBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'countries' => SmartLinkManager::$plugin->analytics->getTopCountries($smartLinkId, $dateRange, 15, $resolvedSiteId),
                'all-countries' => SmartLinkManager::$plugin->analytics->getAllCountries($smartLinkId, $dateRange, $resolvedSiteId),
                'all-cities' => SmartLinkManager::$plugin->analytics->getTopCities($smartLinkId, $dateRange, 50, $resolvedSiteId),
                'languages' => SmartLinkManager::$plugin->analytics->getLanguageBreakdown($smartLinkId, $dateRange, $resolvedSiteId),
                'hourly' => SmartLinkManager::$plugin->analytics->getHourlyAnalytics($smartLinkId, $dateRange, $resolvedSiteId),
                'insights' => SmartLinkManager::$plugin->analytics->getInsights($dateRange, $resolvedSiteId),
                default => SmartLinkManager::$plugin->analytics->getAnalyticsSummary($dateRange, $smartLinkId, $resolvedSiteId),
            };

            return $this->asJson([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            $this->logError('Analytics getData error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->asJson([
                'success' => false,
                'error' => Craft::$app->getConfig()->getGeneral()->devMode
                    ? $e->getMessage()
                    : Craft::t('smartlink-manager', 'An unexpected error occurred.'),
            ]);
        }
    }

    /**
     * Export analytics data
     *
     * Supports CSV, JSON, and Excel formats using ExportHelper.
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionExport(): Response
    {
        $this->requirePermission('smartLinkManager:exportAnalytics');

        // Check if analytics are globally enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            Craft::$app->getSession()->setError('Analytics are disabled in plugin settings.');
            return $this->redirect('smartlink-manager');
        }

        $request = Craft::$app->getRequest();
        $smartLinkId = $request->getQueryParam('smartLinkId');
        // Accept both 'range' and 'dateRange' parameter names
        $dateRange = $request->getQueryParam('range') ?? $request->getQueryParam('dateRange', DateRangeHelper::getDefaultDateRange(SmartLinkManager::$plugin->id));
        $format = $request->getQueryParam('format', 'csv');
        $siteId = $request->getQueryParam('siteId');
        $siteId = $siteId ? (int)$siteId : null;
        $resolvedSiteId = $this->_resolveSiteId($siteId);

        if (!ExportHelper::isFormatEnabled($format, SmartLinkManager::$plugin->id)) {
            throw new \yii\web\BadRequestHttpException("Export format '{$format}' is not enabled.");
        }

        // If exporting for a specific SmartLink, check if it has analytics enabled
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
                ->id($smartLinkId)
                ->status(null)
                ->one();

            if (!$smartLink) {
                Craft::$app->getSession()->setError('Smart link not found.');
                return $this->redirect('smartlink-manager');
            }

            if (!($smartLink->trackAnalytics ?? true)) {
                Craft::$app->getSession()->setError('Analytics tracking is disabled for this smart link.');
                return $this->redirect('smartlink-manager/smartlinks/' . $smartLinkId);
            }
        }

        // Get export data (scoped to user's allowed sites)
        $exportData = SmartLinkManager::$plugin->analytics->getExportData(
            $smartLinkId ? (int)$smartLinkId : null,
            $dateRange,
            $resolvedSiteId
        );

        // Check for empty data
        if (empty($exportData)) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'No analytics data to export.'));
            if ($smartLinkId) {
                return $this->redirect('smartlink-manager/smartlinks/' . $smartLinkId . '?range=' . $dateRange);
            }
            return $this->redirect('smartlink-manager/analytics?dateRange=' . $dateRange);
        }

        $settings = SmartLinkManager::$plugin->getSettings();
        $geoEnabled = $settings->enableGeoDetection ?? true;

        // Build filename parts
        $dateRangeLabel = $dateRange === 'all' ? 'alltime' : $dateRange;
        $filenameParts = ['analytics'];

        // Add slug to filename if specific smart link
        if ($smartLinkId) {
            $smartLink = \lindemannrock\smartlinkmanager\elements\SmartLink::find()
                ->id($smartLinkId)
                ->one();
            if ($smartLink) {
                $cleanSlug = preg_replace('/[^a-zA-Z0-9-_]/', '', $smartLink->slug);
                $filenameParts[] = $cleanSlug;
            }
        }

        // Add site to filename if filtered
        if ($siteId) {
            $site = Craft::$app->getSites()->getSiteById($siteId);
            if ($site) {
                $filenameParts[] = strtolower(preg_replace('/[^a-zA-Z0-9-_]/', '', str_replace(' ', '-', $site->name)));
            }
        }

        $filenameParts[] = $dateRangeLabel;

        // Build headers for CSV/Excel
        $headers = [
            'dateCreated' => Craft::t('smartlink-manager', 'Date/Time'),
            'name' => Craft::t('smartlink-manager', 'Name'),
            'status' => Craft::t('smartlink-manager', 'Status'),
            'smartLinkUrl' => Craft::t('smartlink-manager', 'Smart Link URL'),
            'siteName' => Craft::t('smartlink-manager', 'Site'),
            'clickType' => Craft::t('smartlink-manager', 'Type'),
            'platform' => Craft::t('smartlink-manager', 'Button'),
            'source' => Craft::t('smartlink-manager', 'Source'),
            'destinationUrl' => Craft::t('smartlink-manager', 'Destination URL'),
            'referrer' => Craft::t('smartlink-manager', 'Referrer'),
            'deviceType' => Craft::t('smartlink-manager', 'Device Type'),
            'deviceBrand' => Craft::t('smartlink-manager', 'Device Brand'),
            'deviceModel' => Craft::t('smartlink-manager', 'Device Model'),
            'osName' => Craft::t('smartlink-manager', 'OS'),
            'osVersion' => Craft::t('smartlink-manager', 'OS Version'),
            'browser' => Craft::t('smartlink-manager', 'Browser'),
            'browserVersion' => Craft::t('smartlink-manager', 'Browser Version'),
            'language' => Craft::t('smartlink-manager', 'Language'),
            'userAgent' => Craft::t('smartlink-manager', 'User Agent'),
        ];

        // Add geo headers if enabled
        if ($geoEnabled) {
            $headers['country'] = Craft::t('smartlink-manager', 'Country');
            $headers['city'] = Craft::t('smartlink-manager', 'City');
        }

        // Date columns for formatting
        $dateColumns = ['dateCreated'];

        // Export based on format
        $extension = $format === 'excel' ? 'xlsx' : $format;
        $filename = ExportHelper::filename($settings, $filenameParts, $extension);

        return match ($format) {
            'json' => ExportHelper::toJson($exportData, $filename, $dateColumns),
            'excel' => ExportHelper::toExcel($exportData, $headers, $filename, $dateColumns, [
                'sheetTitle' => Craft::t('smartlink-manager', 'Analytics'),
            ]),
            default => ExportHelper::toCsv($exportData, $headers, $filename, $dateColumns),
        };
    }

    /**
     * Get site IDs the current user is allowed to view analytics for
     *
     * Returns the intersection of plugin-enabled sites and user-editable sites.
     *
     * @return int[]
     */
    private function _getAllowedSiteIds(): array
    {
        return array_map(
            fn($site) => $site->id,
            SmartLinkManager::$plugin->getEnabledSites()
        );
    }

    /**
     * Resolve site ID parameter for analytics queries
     *
     * If a specific site ID is provided and the user has access, returns that int.
     * Otherwise returns the array of all allowed site IDs to scope the query.
     *
     * @param int|null $siteId
     * @return int|int[]
     */
    private function _resolveSiteId(?int $siteId): int|array
    {
        $allowedSiteIds = $this->_getAllowedSiteIds();

        if ($siteId !== null && in_array($siteId, $allowedSiteIds)) {
            return $siteId;
        }

        return $allowedSiteIds;
    }
}
