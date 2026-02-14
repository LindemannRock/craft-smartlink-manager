<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services;

use craft\base\Component;
use craft\db\Query;
use lindemannrock\base\helpers\DateRangeHelper;
use lindemannrock\base\traits\GeoLookupTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsBreakdownService;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsChartService;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsExportService;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsSummaryService;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsTrackingService;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Analytics Service
 *
 * Facade that delegates to focused sub-services for analytics functionality.
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     1.0.0
 */
class AnalyticsService extends Component
{
    use LoggingTrait;
    use GeoLookupTrait;

    private AnalyticsTrackingService $_tracking;
    private AnalyticsSummaryService $_summary;
    private AnalyticsBreakdownService $_breakdown;
    private AnalyticsChartService $_chart;
    private AnalyticsExportService $_export;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);

        $this->_tracking = new AnalyticsTrackingService();
        $this->_summary = new AnalyticsSummaryService();
        $this->_breakdown = new AnalyticsBreakdownService();
        $this->_chart = new AnalyticsChartService();
        $this->_export = new AnalyticsExportService();
    }

    // =========================================================================
    // TRACKING
    // =========================================================================

    /**
     * Track a click on a smart link
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $deviceInfo
     * @param array $metadata
     * @since 1.0.0
     */
    public function trackClick(SmartLink $smartLink, DeviceInfo $deviceInfo, array $metadata = []): void
    {
        $this->_tracking->trackClick($smartLink, $deviceInfo, $metadata);
    }

    /**
     * Save analytics record
     *
     * @param int $linkId
     * @param array $deviceInfo
     * @param array $metadata
     * @return bool
     * @since 1.0.0
     */
    public function saveAnalytics(int $linkId, array $deviceInfo, array $metadata = []): bool
    {
        return $this->_tracking->saveAnalytics($linkId, $deviceInfo, $metadata);
    }

    /**
     * Get location data from IP address
     *
     * @param string $ip
     * @return array|null
     * @since 1.0.0
     */
    public function getLocationFromIp(string $ip): ?array
    {
        return $this->_tracking->getLocationFromIp($ip);
    }

    /**
     * Get country from IP address (backward compatibility)
     *
     * @param string $ip
     * @return string|null
     * @since 1.0.0
     */
    public function getCountryFromIp(string $ip): ?string
    {
        return $this->_tracking->getCountryFromIp($ip);
    }

    // =========================================================================
    // SUMMARIES
    // =========================================================================

    /**
     * Get analytics summary
     *
     * @param string $dateRange
     * @param int|null $smartLinkId
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getAnalyticsSummary(string $dateRange = 'last7days', ?int $smartLinkId = null, int|array|null $siteId = null): array
    {
        return $this->_summary->getAnalyticsSummary($dateRange, $smartLinkId, $siteId);
    }

    /**
     * Get analytics for a specific smart link
     *
     * @param int $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getSmartLinkAnalytics(int $smartLinkId, string $dateRange = 'last7days', int|array|null $siteId = null): array
    {
        return $this->_summary->getSmartLinkAnalytics($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get analytics data for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $criteria
     * @return array
     * @since 1.0.0
     */
    public function getAnalytics(SmartLink $smartLink, array $criteria = []): array
    {
        return $this->_summary->getAnalytics($smartLink, $criteria);
    }

    /**
     * Get aggregated stats for multiple smart links
     *
     * @param array $linkIds
     * @param string $period
     * @return array
     * @since 1.0.0
     */
    public function getAggregatedStats(array $linkIds, string $period = '30d'): array
    {
        return $this->_summary->getAggregatedStats($linkIds, $period);
    }

    /**
     * Get top smart links by clicks
     *
     * @param string $dateRange
     * @param int $limit
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getTopLinks(string $dateRange = 'last7days', int $limit = 5, int|array|null $siteId = null): array
    {
        return $this->_summary->getTopLinks($dateRange, $limit, $siteId);
    }

    /**
     * Get recent clicks for a smart link
     *
     * @param int $smartLinkId
     * @param int $limit
     * @param string $dateRange
     * @return array
     * @since 1.0.0
     */
    public function getRecentClicks(int $smartLinkId, int $limit = 20, string $dateRange = 'last7days'): array
    {
        return $this->_summary->getRecentClicks($smartLinkId, $limit, $dateRange);
    }

    /**
     * Get recent clicks across all smart links
     *
     * @param string $dateRange
     * @param int $limit
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getAllRecentClicks(string $dateRange = 'last7days', int $limit = 20, int|array|null $siteId = null): array
    {
        return $this->_summary->getAllRecentClicks($dateRange, $limit, $siteId);
    }

    /**
     * Get top countries
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int $limit
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getTopCountries(?int $smartLinkId, string $dateRange, int $limit = 15, int|array|null $siteId = null): array
    {
        return $this->_summary->getTopCountries($smartLinkId, $dateRange, $limit, $siteId);
    }

    /**
     * Get top cities
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int $limit
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getTopCities(?int $smartLinkId, string $dateRange, int $limit = 15, int|array|null $siteId = null): array
    {
        return $this->_summary->getTopCities($smartLinkId, $dateRange, $limit, $siteId);
    }

    /**
     * Get all countries (no limit)
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getAllCountries(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_summary->getAllCountries($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get button click analytics
     *
     * @param int $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getButtonClicks(int $smartLinkId, string $dateRange = 'last7days', int|array|null $siteId = null): array
    {
        return $this->_summary->getButtonClicks($smartLinkId, $dateRange, $siteId);
    }

    // =========================================================================
    // BREAKDOWNS
    // =========================================================================

    /**
     * Get device breakdown (mobile, tablet, desktop)
     *
     * @since 1.0.0
     */
    public function getDeviceBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getDeviceBreakdown($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get detailed device type breakdown
     *
     * @since 1.0.0
     */
    public function getDeviceTypeBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getDeviceTypeBreakdown($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get device brand breakdown
     *
     * @since 1.0.0
     */
    public function getDeviceBrandBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getDeviceBrandBreakdown($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get platform breakdown (iOS, Android, Windows, macOS, Linux)
     *
     * @since 1.0.0
     */
    public function getPlatformBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getPlatformBreakdown($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get OS breakdown with versions
     *
     * @since 1.0.0
     */
    public function getOsBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getOsBreakdown($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get browser breakdown with versions
     *
     * @since 1.0.0
     */
    public function getBrowserBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getBrowserBreakdown($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get language breakdown
     *
     * @since 1.0.0
     */
    public function getLanguageBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_breakdown->getLanguageBreakdown($smartLinkId, $dateRange, $siteId);
    }

    // =========================================================================
    // CHARTS & INSIGHTS
    // =========================================================================

    /**
     * Get clicks data for charts
     *
     * @since 1.0.0
     */
    public function getClicksData(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_chart->getClicksData($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get hourly analytics for peak usage times
     *
     * @since 1.0.0
     */
    public function getHourlyAnalytics(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_chart->getHourlyAnalytics($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Get insights (cross-referenced analytics)
     *
     * @since 1.0.0
     */
    public function getInsights(string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_chart->getInsights($dateRange, $siteId);
    }

    // =========================================================================
    // EXPORT & MAINTENANCE
    // =========================================================================

    /**
     * Get analytics data formatted for export
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.5.0
     */
    public function getExportData(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return $this->_export->getExportData($smartLinkId, $dateRange, $siteId);
    }

    /**
     * Delete analytics data for a smart link
     *
     * @param SmartLink $smartLink
     * @return int Number of records deleted
     * @since 1.0.0
     */
    public function deleteAnalyticsForLink(SmartLink $smartLink): int
    {
        return $this->_export->deleteAnalyticsForLink($smartLink);
    }

    // =========================================================================
    // SHARED UTILITIES
    // =========================================================================

    /**
     * Apply date range filter to query
     *
     * @param Query $query
     * @param string $dateRange
     * @param string $dateColumn
     * @return Query
     * @since 1.0.0
     */
    public function applyDateRangeFilter(Query $query, string $dateRange, string $dateColumn = 'dateCreated'): Query
    {
        DateRangeHelper::applyToQuery($query, $dateRange, $dateColumn);
        return $query;
    }

    /**
     * Get geo lookup configuration from plugin settings
     *
     * @return array<string, mixed>
     */
    protected function getGeoConfig(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();

        return [
            'provider' => $settings->geoProvider ?? 'ip-api.com',
            'apiKey' => $settings->geoApiKey ?? null,
        ];
    }
}
