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

        $variables['dateRange'] = $dateRange;
        $variables['siteId'] = $siteId;

        // Get enabled sites for site selector (respects enabledSites setting)
        $settings = SmartLinkManager::$plugin->getSettings();
        $enabledSiteIds = $settings->getEnabledSiteIds();
        $allSites = Craft::$app->getSites()->getAllSites();
        $variables['sites'] = array_filter($allSites, fn($site) => in_array($site->id, $enabledSiteIds));

        // Get analytics data
        $variables['analyticsData'] = SmartLinkManager::$plugin->analytics->getAnalyticsSummary($dateRange, null, $siteId);

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

        // Log the request for debugging
        $this->logInfo('Analytics getData called', ['type' => $type, 'dateRange' => $dateRange, 'smartLinkId' => $smartLinkId ?? null, 'siteId' => $siteId]);

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
                'clicks' => SmartLinkManager::$plugin->analytics->getClicksData($smartLinkId, $dateRange, $siteId),
                'devices' => SmartLinkManager::$plugin->analytics->getDeviceBreakdown($smartLinkId, $dateRange, $siteId),
                'device-types' => SmartLinkManager::$plugin->analytics->getDeviceTypeBreakdown($smartLinkId, $dateRange, $siteId),
                'device-brands' => SmartLinkManager::$plugin->analytics->getDeviceBrandBreakdown($smartLinkId, $dateRange, $siteId),
                'platforms' => SmartLinkManager::$plugin->analytics->getPlatformBreakdown($smartLinkId, $dateRange, $siteId),
                'os-breakdown' => SmartLinkManager::$plugin->analytics->getOsBreakdown($smartLinkId, $dateRange, $siteId),
                'browsers' => SmartLinkManager::$plugin->analytics->getBrowserBreakdown($smartLinkId, $dateRange, $siteId),
                'countries' => SmartLinkManager::$plugin->analytics->getTopCountries($smartLinkId, $dateRange, 15, $siteId),
                'all-countries' => SmartLinkManager::$plugin->analytics->getAllCountries($smartLinkId, $dateRange, $siteId),
                'all-cities' => SmartLinkManager::$plugin->analytics->getTopCities($smartLinkId, $dateRange, 50, $siteId),
                'languages' => SmartLinkManager::$plugin->analytics->getLanguageBreakdown($smartLinkId, $dateRange, $siteId),
                'hourly' => SmartLinkManager::$plugin->analytics->getHourlyAnalytics($smartLinkId, $dateRange, $siteId),
                'insights' => SmartLinkManager::$plugin->analytics->getInsights($dateRange, $siteId),
                default => SmartLinkManager::$plugin->analytics->getAnalyticsSummary($dateRange, $smartLinkId, $siteId),
            };

            return $this->asJson([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            $this->logError('Analytics getData error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get analytics data for AJAX requests
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionGetAnalyticsData(): Response
    {
        $this->requirePostRequest();
        $this->requireLogin();
        $this->requirePermission('smartLinkManager:viewAnalytics');
        $this->requireAcceptsJson();

        // Check if analytics are globally enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            return $this->asJson([
                'success' => false,
                'error' => 'Analytics are disabled in plugin settings.',
            ]);
        }

        $smartLinkId = Craft::$app->getRequest()->getParam('smartLinkId');
        $range = Craft::$app->getRequest()->getParam('range', DateRangeHelper::getDefaultDateRange(SmartLinkManager::$plugin->id));

        if (!$smartLinkId) {
            return $this->asJson([
                'success' => false,
                'error' => 'Smart link ID is required',
            ]);
        }

        try {
            // Get the smart link (including disabled ones)
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

            // Check if analytics tracking is enabled for this smart link
            if (!($smartLink->trackAnalytics ?? true)) {
                return $this->asJson([
                    'success' => false,
                    'error' => 'Analytics tracking is disabled for this smart link',
                ]);
            }

            // Get analytics service
            $analyticsService = SmartLinkManager::$plugin->analytics;

            // Set the range parameter in the request so the template can access it
            $_GET['range'] = $range;
            Craft::$app->getRequest()->setQueryParams(array_merge(Craft::$app->getRequest()->getQueryParams(), ['range' => $range]));
            
            // Render only the content part for AJAX
            $html = Craft::$app->getView()->renderTemplate('smartlink-manager/smartlinks/_partials/analytics-content', [
                'smartLink' => $smartLink,
                'analyticsService' => $analyticsService,
                'dateRange' => $range,  // Pass the range directly
                'settings' => SmartLinkManager::$plugin->getSettings(),
            ]);

            return $this->asJson([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            $this->logError('Failed to get analytics data', ['error' => $e->getMessage()]);
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage(),
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

        // Get export data
        $exportData = SmartLinkManager::$plugin->analytics->getExportData(
            $smartLinkId ? (int)$smartLinkId : null,
            $dateRange,
            $siteId
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
}
