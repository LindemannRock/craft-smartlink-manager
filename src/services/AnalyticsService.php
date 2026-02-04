<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services;

use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use craft\helpers\UrlHelper;
use lindemannrock\base\helpers\DateRangeHelper;
use lindemannrock\base\helpers\GeoHelper;
use lindemannrock\base\traits\GeoLookupTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Analytics Service
 *
 * @since 1.0.0
 */
class AnalyticsService extends Component
{
    use LoggingTrait;
    use GeoLookupTrait;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Get analytics summary
     *
     * @param string $dateRange
     * @param int|null $smartLinkId
     * @param int|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getAnalyticsSummary(string $dateRange = 'last7days', ?int $smartLinkId = null, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}');

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $totalClicks = (int) $query->count();
        $uniqueVisitors = (int) (clone $query)->select('COUNT(DISTINCT ip)')->scalar();

        // Get active links count
        $activeLinksQuery = SmartLink::find()
            ->status(SmartLink::STATUS_ENABLED);
        if ($siteId) {
            $activeLinksQuery->siteId($siteId);
        }
        $activeLinks = $activeLinksQuery->count();

        // Get total links
        $totalLinks = SmartLink::find()->count();

        // Get count of ACTIVE links that have been clicked in this period
        $linksQuery = (new Query())
            ->from('{{%smartlinkmanager_analytics}} a')
            ->innerJoin('{{%smartlinkmanager}} s', 'a.linkId = s.id')
            ->innerJoin('{{%elements}} e', 's.id = e.id')
            ->innerJoin('{{%elements_sites}} es', 'e.id = es.elementId')
            ->select('COUNT(DISTINCT a.linkId)')
            ->where(['es.enabled' => true]);

        // Apply date filter to analytics table
        $this->applyDateRangeFilter($linksQuery, $dateRange, 'a.dateCreated');

        // Filter by site if specified
        if ($siteId) {
            $linksQuery->andWhere(['a.siteId' => $siteId]);
        }

        $linksWithClicks = (int) $linksQuery->scalar();

        // Calculate what percentage of active links have been used
        // Cap at 100% to avoid confusion
        $linksUsedPercentage = $activeLinks > 0 ? min(100, round(($linksWithClicks / $activeLinks) * 100, 0)) : 0;

        return [
            'totalClicks' => $totalClicks,
            'uniqueVisitors' => $uniqueVisitors,
            'activeLinks' => $activeLinks,
            'totalLinks' => $totalLinks,
            'linksUsed' => $linksWithClicks,
            'linksUsedPercentage' => $linksUsedPercentage,
            'topLinks' => $this->getTopLinks($dateRange, 20, $siteId),
            'topCountries' => $this->getTopCountries(null, $dateRange, 15, $siteId),
            'topCities' => $this->getTopCities(null, $dateRange, 15, $siteId),
            'recentClicks' => $this->getAllRecentClicks($dateRange, 20, $siteId),
        ];
    }

    /**
     * Get analytics for a specific smart link
     *
     * @param int $smartLinkId
     * @param string $dateRange
     * @return array
     * @since 1.0.0
     */
    public function getSmartLinkAnalytics(int $smartLinkId, string $dateRange = 'last7days'): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $smartLinkId]);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Get total and unique clicks
        $totalClicks = (int) $query->count();
        $uniqueClicks = (int) (clone $query)->select('COUNT(DISTINCT ip)')->scalar();

        // Get device breakdown
        $deviceResults = (clone $query)
            ->select(['deviceType', 'COUNT(*) as count'])
            ->groupBy('deviceType')
            ->all();

        $deviceBreakdown = [];
        foreach ($deviceResults as $row) {
            if (!empty($row['deviceType'])) {
                $deviceBreakdown[$row['deviceType']] = (int) $row['count'];
            }
        }

        // Get OS breakdown (replacing platform)
        $osResults = (clone $query)
            ->select(['osName', 'COUNT(*) as count'])
            ->groupBy('osName')
            ->all();

        $platformBreakdown = [];
        foreach ($osResults as $row) {
            if (!empty($row['osName'])) {
                $platformBreakdown[$row['osName']] = (int) $row['count'];
            }
        }

        // Get button clicks breakdown
        $buttonClicks = $this->getButtonClicks($smartLinkId, $dateRange);

        // Calculate average clicks per day
        $days = 1;
        $startDate = $this->getStartDateForRange($dateRange);
        if ($startDate) {
            $start = new \DateTime($startDate);
            $end = new \DateTime();
            $interval = $start->diff($end);
            $days = max(1, $interval->days + 1);
        }
        $averageClicksPerDay = $totalClicks / $days;

        return [
            'totalClicks' => $totalClicks,
            'uniqueClicks' => $uniqueClicks,
            'averageClicksPerDay' => $averageClicksPerDay,
            'deviceBreakdown' => $deviceBreakdown,
            'platformBreakdown' => $platformBreakdown,
            'buttonClicks' => $buttonClicks,
        ];
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
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $smartLinkId])
            ->orderBy('dateCreated DESC')
            ->limit($limit);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        $results = $query->all();

        // Convert dateCreated to DateTime objects in user's timezone
        foreach ($results as &$result) {
            if (!empty($result['dateCreated'])) {
                $utcDate = new \DateTime($result['dateCreated'], new \DateTimeZone('UTC'));
                $utcDate->setTimezone(new \DateTimeZone(Craft::$app->getTimeZone()));
                $result['dateCreated'] = $utcDate;
            }
        }

        return $results;
    }

    /**
     * Get button click analytics
     *
     * @param int $smartLinkId
     * @param string $dateRange
     * @return array
     * @since 1.0.0
     */
    public function getButtonClicks(int $smartLinkId, string $dateRange = 'last7days'): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $smartLinkId])
            ->andWhere(['like', 'metadata', '"clickType":"button"']);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Get all button click records
        $records = $query->all();

        // Parse platform data from metadata
        $platformCounts = [];
        $totalButtonClicks = 0;

        foreach ($records as $record) {
            $metadata = Json::decodeIfJson($record['metadata']);
            if (isset($metadata['platform'])) {
                $platform = $metadata['platform'];
                if (!isset($platformCounts[$platform])) {
                    $platformCounts[$platform] = 0;
                }
                $platformCounts[$platform]++;
                $totalButtonClicks++;
            }
        }

        // Sort by count descending
        arsort($platformCounts);

        return [
            'total' => $totalButtonClicks,
            'byPlatform' => $platformCounts,
        ];
    }

    /**
     * Get start date for date range
     *
     * @param string $range
     * @return string|null
     */
    private function getStartDateForRange(string $range): ?string
    {
        $date = null;

        switch ($range) {
            case 'today':
                $date = DateTimeHelper::now()->setTime(0, 0, 0);
                break;
            case 'yesterday':
                $date = DateTimeHelper::now()->modify('-1 day')->setTime(0, 0, 0);
                break;
            case 'last7days':
                $date = DateTimeHelper::now()->modify('-7 days');
                break;
            case 'last30days':
                $date = DateTimeHelper::now()->modify('-30 days');
                break;
            case 'last90days':
                $date = DateTimeHelper::now()->modify('-90 days');
                break;
            case 'all':
            case 'alltime':
            default:
                return null;
        }

        return Db::prepareDateForDb($date);
    }

    /**
     * Get end date for date range (for specific day filtering)
     *
     * @param string $range
     * @return string|null
     */
    private function getEndDateForRange(string $range): ?string
    {
        $date = null;

        switch ($range) {
            case 'today':
                $date = DateTimeHelper::now()->setTime(23, 59, 59);
                break;
            case 'yesterday':
                $date = DateTimeHelper::now()->modify('-1 day')->setTime(23, 59, 59);
                break;
            default:
                return null;
        }

        return Db::prepareDateForDb($date);
    }

    /**
     * Apply date range filter to query
     *
     * @param Query $query
     * @param string $dateRange
     * @param string $dateColumn
     * @return Query
     */
    private function applyDateRangeFilter(Query $query, string $dateRange, string $dateColumn = 'dateCreated'): Query
    {
        DateRangeHelper::applyToQuery($query, $dateRange, $dateColumn);
        return $query;
    }

    /**
     * Get clicks data for charts
     *
     * @since 1.0.0
     */
    public function getClicksData(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        // Get Craft's timezone offset for MySQL CONVERT_TZ
        $timezone = \Craft::$app->getTimeZone();
        $dateTime = new \DateTime('now', new \DateTimeZone($timezone));
        $offset = $dateTime->format('P'); // e.g., +03:00

        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(["DATE(CONVERT_TZ(dateCreated, '+00:00', '{$offset}')) as date", 'COUNT(*) as count'])
            ->groupBy(["DATE(CONVERT_TZ(dateCreated, '+00:00', '{$offset}'))"])
            ->orderBy(['date' => SORT_ASC]);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $labels = [];
        $values = [];

        foreach ($results as $row) {
            $labels[] = date('M j', strtotime($row['date']));
            $values[] = (int)$row['count'];
        }

        // Get the actual date range boundaries in Craft's timezone
        $timezone = \Craft::$app->getTimeZone();

        // For "today", just use today's date in the site timezone
        if ($dateRange === 'today') {
            $now = new \DateTime('now', new \DateTimeZone($timezone));
            $startTimestamp = strtotime($now->format('Y-m-d'));
            $endTimestamp = $startTimestamp;
        } elseif ($dateRange === 'yesterday') {
            $yesterday = new \DateTime('yesterday', new \DateTimeZone($timezone));
            $startTimestamp = strtotime($yesterday->format('Y-m-d'));
            $endTimestamp = $startTimestamp;
        } else {
            // For other ranges, calculate from the query dates
            $startDate = $this->getStartDateForRange($dateRange);
            $endDate = $this->getEndDateForRange($dateRange);

            if ($startDate) {
                // Convert UTC date to local timezone date
                $start = new \DateTime($startDate, new \DateTimeZone('UTC'));
                $start->setTimezone(new \DateTimeZone($timezone));
                $startTimestamp = strtotime($start->format('Y-m-d'));
            } else {
                // For ranges like "all", use the first data point or 30 days ago as fallback
                $startTimestamp = !empty($results) ? strtotime($results[0]['date']) : strtotime('-30 days');
            }

            if ($endDate) {
                // Convert UTC date to local timezone date
                $end = new \DateTime($endDate, new \DateTimeZone('UTC'));
                $end->setTimezone(new \DateTimeZone($timezone));
                $endTimestamp = strtotime($end->format('Y-m-d'));
            } else {
                // For open-ended ranges, use today
                $endTimestamp = strtotime('today');
            }
        }

        // Create a map of existing data
        $dataMap = [];
        foreach ($results as $row) {
            $dataMap[date('M j', strtotime($row['date']))] = (int)$row['count'];
        }

        // Fill in all dates in the range
        $filledLabels = [];
        $filledValues = [];

        for ($timestamp = $startTimestamp; $timestamp <= $endTimestamp; $timestamp += 86400) {
            $label = date('M j', $timestamp);
            $filledLabels[] = $label;
            $filledValues[] = $dataMap[$label] ?? 0;
        }

        return [
            'labels' => $filledLabels,
            'values' => $filledValues,
        ];
    }

    /**
     * Get device breakdown (mobile, tablet, desktop)
     *
     * @since 1.0.0
     */
    public function getDeviceBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['deviceType', 'COUNT(*) as count'])
            ->groupBy(['deviceType']);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $labels = [];
        $values = [];

        foreach ($results as $row) {
            $labels[] = ucfirst($row['deviceType']);
            $values[] = (int)$row['count'];
        }

        // Return empty data if no analytics exist
        if (empty($labels)) {
            return [
                'labels' => ['No data yet'],
                'values' => [1], // Show empty state
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get platform breakdown (iOS, Android, Windows, macOS, Linux)
     *
     * @since 1.0.0
     */
    public function getPlatformBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['osName', 'COUNT(*) as count'])
            ->groupBy(['osName']);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $labels = [];
        $values = [];

        // Map platform names to more user-friendly labels
        $platformLabels = [
            'ios' => 'iOS',
            'android' => 'Android',
            'windows' => 'Windows',
            'macos' => 'macOS',
            'linux' => 'Linux',
            'huawei' => 'HarmonyOS',
            'other' => 'Other',
        ];

        foreach ($results as $row) {
            $osName = strtolower($row['osName'] ?? '');
            $labels[] = $platformLabels[$osName] ?? ucfirst($row['osName'] ?? 'Unknown');
            $values[] = (int)$row['count'];
        }

        // Return empty data if no analytics exist
        if (empty($labels)) {
            return [
                'labels' => ['No data yet'],
                'values' => [1], // Show empty state
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get top countries
     *
     * @since 1.0.0
     */
    public function getTopCountries(?int $smartLinkId, string $dateRange, int $limit = 15, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['country', 'COUNT(*) as clicks'])
            ->where(['not', ['country' => null]])
            ->groupBy(['country'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        $countries = [];

        foreach ($results as $row) {
            $countries[] = [
                'code' => $row['country'],
                'name' => GeoHelper::getCountryName($row['country']),
                'clicks' => (int)$row['clicks'],
                'percentage' => $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0,
            ];
        }

        return $countries;
    }

    /**
     * Get all countries (no limit)
     *
     * @since 1.0.0
     */
    public function getAllCountries(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        return $this->getTopCountries($smartLinkId, $dateRange, 9999, $siteId);
    }

    /**
     * Get top cities
     *
     * @since 1.0.0
     */
    public function getTopCities(?int $smartLinkId, string $dateRange, int $limit = 15, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['city', 'country', 'COUNT(*) as clicks'])
            ->where(['not', ['city' => null]])
            ->groupBy(['city', 'country'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));
        $cities = [];

        foreach ($results as $row) {
            $cities[] = [
                'city' => $row['city'],
                'country' => $row['country'],
                'countryName' => GeoHelper::getCountryName($row['country']),
                'clicks' => (int)$row['clicks'],
                'percentage' => $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0,
            ];
        }

        return $cities;
    }

    /**
     * Get hourly analytics for peak usage times
     *
     * @since 1.0.0
     */
    public function getHourlyAnalytics(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'HOUR(dateCreated) as hour',
                'COUNT(*) as clicks',
            ])
            ->groupBy(['hour'])
            ->orderBy(['hour' => SORT_ASC]);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        // Initialize all hours with 0
        $hourlyData = array_fill(0, 24, 0);

        foreach ($results as $row) {
            $hourlyData[(int)$row['hour']] = (int)$row['clicks'];
        }

        // Find peak hour
        $peakHour = array_search(max($hourlyData), $hourlyData);

        return [
            'data' => $hourlyData,
            'peakHour' => $peakHour,
            'peakHourFormatted' => date('g A', strtotime("{$peakHour}:00")),
        ];
    }

    /**
     * Get insights (cross-referenced analytics)
     *
     * @since 1.0.0
     */
    public function getInsights(string $dateRange, ?int $siteId = null): array
    {
        $insights = [];

        // Mobile usage by top cities
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'city',
                'SUM(CASE WHEN osName IN ("iOS", "Android") THEN 1 ELSE 0 END) as mobile_clicks',
                'COUNT(*) as total_clicks',
            ])
            ->where(['not', ['city' => null]])
            ->groupBy(['city'])
            ->having(['>', 'total_clicks', 10]) // Only cities with significant traffic
            ->orderBy(['total_clicks' => SORT_DESC])
            ->limit(5);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $cityMobileUsage = [];
        foreach ($query->all() as $row) {
            $mobilePercentage = round(($row['mobile_clicks'] / $row['total_clicks']) * 100, 1);
            $cityMobileUsage[] = [
                'city' => $row['city'],
                'mobilePercentage' => $mobilePercentage,
                'totalClicks' => (int)$row['total_clicks'],
            ];
        }

        // Browser usage by country
        $browserByCountry = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'country',
                'browser',
                'COUNT(*) as clicks',
            ])
            ->where(['not', ['country' => null]])
            ->andWhere(['not', ['browser' => null]])
            ->groupBy(['country', 'browser'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(10);

        // Apply date range filter
        $this->applyDateRangeFilter($browserByCountry, $dateRange);

        $browserData = [];
        foreach ($browserByCountry->all() as $row) {
            $countryName = GeoHelper::getCountryName($row['country']);
            if (!isset($browserData[$countryName])) {
                $browserData[$countryName] = [];
            }
            $browserData[$countryName][] = [
                'browser' => $row['browser'],
                'clicks' => (int)$row['clicks'],
            ];
        }

        // Device brands by country
        $brandsByCountry = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'country',
                'deviceBrand',
                'COUNT(*) as clicks',
            ])
            ->where(['not', ['country' => null]])
            ->andWhere(['not', ['deviceBrand' => null]])
            ->groupBy(['country', 'deviceBrand'])
            ->orderBy(['clicks' => SORT_DESC]);

        // Apply date range filter
        $this->applyDateRangeFilter($brandsByCountry, $dateRange);

        $brandsData = [];
        foreach ($brandsByCountry->all() as $row) {
            $countryName = GeoHelper::getCountryName($row['country']);
            if (!isset($brandsData[$countryName])) {
                $brandsData[$countryName] = [];
            }
            // Only keep top 3 brands per country
            if (count($brandsData[$countryName]) < 3) {
                $brandsData[$countryName][] = [
                    'brand' => $row['deviceBrand'],
                    'clicks' => (int)$row['clicks'],
                ];
            }
        }

        return [
            'cityMobileUsage' => $cityMobileUsage,
            'browserByCountry' => $browserData,
            'brandsByCountry' => $brandsData,
        ];
    }

    /**
     * Get language breakdown
     *
     * @since 1.0.0
     */
    public function getLanguageBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        return [
            'labels' => [],
            'values' => [],
        ];
    }

    /**
     * Get device brand breakdown
     *
     * @since 1.0.0
     */
    public function getDeviceBrandBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'deviceBrand',
                'COUNT(*) as clicks',
            ])
            ->where(['not', ['deviceBrand' => null]])
            ->andWhere(['not', ['deviceBrand' => '']])
            ->groupBy(['deviceBrand'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(10);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

        $labels = [];
        $values = [];
        $percentages = [];

        foreach ($results as $row) {
            $labels[] = $row['deviceBrand'] ?: 'Unknown';
            $values[] = (int)$row['clicks'];
            $percentages[] = $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'percentages' => $percentages,
            'totalClicks' => $totalClicks,
        ];
    }

    /**
     * Get OS breakdown with versions
     *
     * @since 1.0.0
     */
    public function getOsBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'osName',
                'osVersion',
                'COUNT(*) as clicks',
            ])
            ->where(['not', ['osName' => null]])
            ->andWhere(['not', ['osName' => '']])
            ->groupBy(['osName', 'osVersion'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(15);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

        // Group by OS name first
        $osData = [];
        foreach ($results as $row) {
            $osName = $row['osName'] ?: 'Unknown';
            if (!isset($osData[$osName])) {
                $osData[$osName] = [
                    'name' => $osName,
                    'totalClicks' => 0,
                    'versions' => [],
                ];
            }

            $osData[$osName]['totalClicks'] += (int)$row['clicks'];

            if ($row['osVersion']) {
                $osData[$osName]['versions'][] = [
                    'version' => $row['osVersion'],
                    'clicks' => (int)$row['clicks'],
                ];
            }
        }

        // Sort by total clicks and prepare final data
        uasort($osData, function($a, $b) {
            return $b['totalClicks'] - $a['totalClicks'];
        });

        $labels = [];
        $values = [];
        $details = [];

        foreach ($osData as $os) {
            $labels[] = $os['name'];
            $values[] = $os['totalClicks'];
            $details[] = [
                'name' => $os['name'],
                'clicks' => $os['totalClicks'],
                'percentage' => $totalClicks > 0 ? round(($os['totalClicks'] / $totalClicks) * 100, 1) : 0,
                'versions' => array_slice($os['versions'], 0, 5), // Top 5 versions per OS
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'details' => $details,
            'totalClicks' => $totalClicks,
        ];
    }

    /**
     * Get browser breakdown with versions
     *
     * @since 1.0.0
     */
    public function getBrowserBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'browser',
                'browserVersion',
                'COUNT(*) as clicks',
            ])
            ->where(['not', ['browser' => null]])
            ->andWhere(['not', ['browser' => '']])
            ->groupBy(['browser', 'browserVersion'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit(20);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

        // Group by browser name first
        $browserData = [];
        foreach ($results as $row) {
            $browserName = $row['browser'] ?: 'Unknown';
            if (!isset($browserData[$browserName])) {
                $browserData[$browserName] = [
                    'name' => $browserName,
                    'totalClicks' => 0,
                    'versions' => [],
                ];
            }

            $browserData[$browserName]['totalClicks'] += (int)$row['clicks'];

            if ($row['browserVersion']) {
                // Simplify version (e.g., "102.0.5005.124" -> "102.0")
                $versionParts = explode('.', $row['browserVersion']);
                $simplifiedVersion = count($versionParts) >= 2
                    ? $versionParts[0] . '.' . $versionParts[1]
                    : $row['browserVersion'];

                if (!isset($browserData[$browserName]['versions'][$simplifiedVersion])) {
                    $browserData[$browserName]['versions'][$simplifiedVersion] = 0;
                }
                $browserData[$browserName]['versions'][$simplifiedVersion] += (int)$row['clicks'];
            }
        }

        // Sort by total clicks and prepare final data
        uasort($browserData, function($a, $b) {
            return $b['totalClicks'] - $a['totalClicks'];
        });

        $labels = [];
        $values = [];
        $details = [];

        foreach ($browserData as $browser) {
            $labels[] = $browser['name'];
            $values[] = $browser['totalClicks'];

            // Sort versions by clicks
            arsort($browser['versions']);
            $versions = [];
            foreach (array_slice($browser['versions'], 0, 5, true) as $version => $clicks) {
                $versions[] = [
                    'version' => $version,
                    'clicks' => $clicks,
                ];
            }

            $details[] = [
                'name' => $browser['name'],
                'clicks' => $browser['totalClicks'],
                'percentage' => $totalClicks > 0 ? round(($browser['totalClicks'] / $totalClicks) * 100, 1) : 0,
                'versions' => $versions,
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'details' => $details,
            'totalClicks' => $totalClicks,
        ];
    }

    /**
     * Get detailed device type breakdown
     *
     * @since 1.0.0
     */
    public function getDeviceTypeBreakdown(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'deviceType',
                'COUNT(*) as clicks',
            ])
            ->where(['not', ['deviceType' => null]])
            ->andWhere(['not', ['deviceType' => '']])
            ->groupBy(['deviceType'])
            ->orderBy(['clicks' => SORT_DESC]);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

        // Map device types to friendly names and categories
        $deviceTypeMap = [
            'smartphone' => ['name' => 'Smartphone', 'category' => 'mobile'],
            'tablet' => ['name' => 'Tablet', 'category' => 'mobile'],
            'phablet' => ['name' => 'Phablet', 'category' => 'mobile'],
            'feature phone' => ['name' => 'Feature Phone', 'category' => 'mobile'],
            'console' => ['name' => 'Game Console', 'category' => 'other'],
            'tv' => ['name' => 'Smart TV', 'category' => 'other'],
            'car browser' => ['name' => 'Car Browser', 'category' => 'other'],
            'smart display' => ['name' => 'Smart Display', 'category' => 'other'],
            'camera' => ['name' => 'Camera', 'category' => 'other'],
            'portable media player' => ['name' => 'Media Player', 'category' => 'other'],
            'desktop' => ['name' => 'Desktop', 'category' => 'desktop'],
            'unknown' => ['name' => 'Unknown', 'category' => 'unknown'],
        ];

        $labels = [];
        $values = [];
        $categories = [
            'mobile' => 0,
            'desktop' => 0,
            'other' => 0,
            'unknown' => 0,
        ];

        foreach ($results as $row) {
            $deviceType = strtolower($row['deviceType'] ?: 'unknown');
            $deviceInfo = $deviceTypeMap[$deviceType] ?? ['name' => ucfirst($deviceType), 'category' => 'other'];

            $labels[] = $deviceInfo['name'];
            $values[] = (int)$row['clicks'];
            $categories[$deviceInfo['category']] += (int)$row['clicks'];
        }

        // Calculate percentages for categories
        $categoryPercentages = [];
        foreach ($categories as $category => $clicks) {
            $categoryPercentages[$category] = $totalClicks > 0 ? round(($clicks / $totalClicks) * 100, 1) : 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'categories' => $categories,
            'categoryPercentages' => $categoryPercentages,
            'totalClicks' => $totalClicks,
        ];
    }

    /**
     * Get analytics data formatted for export
     *
     * Returns an array of data that can be used with ExportHelper.
     *
     * @param int|null $smartLinkId Optional link ID to filter by
     * @param string $dateRange Date range to filter
     * @param int|null $siteId Optional site ID to filter by
     * @return array Array of formatted export data
     * @since 5.5.0
     */
    public function getExportData(?int $smartLinkId, string $dateRange, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'dateCreated',
                'linkId',
                'siteId',
                'metadata',
                'deviceType',
                'deviceBrand',
                'deviceModel',
                'osName',
                'osVersion',
                'browser',
                'browserVersion',
                'country',
                'city',
                'language',
                'referrer',
                'userAgent',
            ])
            ->orderBy(['dateCreated' => SORT_DESC]);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        // Get settings
        $settings = SmartLinkManager::$plugin->getSettings();
        $geoEnabled = $settings->enableGeoDetection ?? true;

        // Format data for export
        $exportData = [];
        foreach ($results as $row) {
            // Get the link
            $smartLink = SmartLink::find()
                ->id($row['linkId'])
                ->status(null)
                ->one();

            if (!$smartLink) {
                continue;
            }

            // Get the actual status
            $status = $smartLink->getStatus();
            $statusLabel = match ($status) {
                SmartLink::STATUS_ENABLED => 'Active',
                SmartLink::STATUS_DISABLED => 'Disabled',
                SmartLink::STATUS_PENDING => 'Pending',
                SmartLink::STATUS_EXPIRED => 'Expired',
                default => 'Unknown'
            };

            // Get site name and build the smart link URL
            $siteName = '';
            $smartLinkUrl = '';
            if (!empty($row['siteId'])) {
                $site = Craft::$app->getSites()->getSiteById($row['siteId']);
                $siteName = $site ? $site->name : '';
                $smartLinkUrl = UrlHelper::siteUrl("go/{$smartLink->slug}", null, null, $row['siteId']);
            }

            // Parse metadata for source, clickType, platform, and destination URL
            $metadata = !empty($row['metadata']) ? Json::decode($row['metadata']) : [];
            $sourceValue = $metadata['source'] ?? 'direct';
            $clickTypeValue = $metadata['clickType'] ?? 'redirect';

            // Format source for display
            $source = match ($sourceValue) {
                'qr' => 'QR',
                'landing' => 'Landing',
                default => 'Direct'
            };

            // Format click type
            $clickType = match ($clickTypeValue) {
                'button' => 'Button',
                default => 'Redirect'
            };

            // Get platform and destination URL based on click type
            $platformLabel = '';
            $destinationUrl = '';
            if ($clickTypeValue === 'button') {
                // For button clicks, show which button URL was clicked
                $destinationUrl = $metadata['buttonUrl'] ?? '';
                if (isset($metadata['platform'])) {
                    $platformDisplay = [
                        'ios' => 'iOS',
                        'android' => 'Android',
                        'macos' => 'macOS',
                        'windows' => 'Windows',
                        'linux' => 'Linux',
                        'huawei' => 'Huawei',
                        'amazon' => 'Amazon',
                        'other' => 'Other',
                    ];
                    $platformLabel = $platformDisplay[$metadata['platform']] ?? ucfirst($metadata['platform']);
                }
            } else {
                // For redirects, show which URL they were sent to
                $destinationUrl = $metadata['redirectUrl'] ?? $metadata['buttonUrl'] ?? '';
            }

            $record = [
                'dateCreated' => $row['dateCreated'],
                'name' => $smartLink->title ?? '',
                'status' => $statusLabel,
                'smartLinkUrl' => $smartLinkUrl,
                'siteName' => $siteName,
                'clickType' => $clickType,
                'platform' => $platformLabel,
                'source' => $source,
                'destinationUrl' => $destinationUrl,
                'referrer' => $row['referrer'] ?? '',
                'deviceType' => $row['deviceType'] ?? '',
                'deviceBrand' => $row['deviceBrand'] ?? '',
                'deviceModel' => $row['deviceModel'] ?? '',
                'osName' => $row['osName'] ?? '',
                'osVersion' => $row['osVersion'] ?? '',
                'browser' => $row['browser'] ?? '',
                'browserVersion' => $row['browserVersion'] ?? '',
                'language' => $row['language'] ?? '',
                'userAgent' => $row['userAgent'] ?? '',
            ];

            // Add geo fields if enabled
            if ($geoEnabled) {
                $record['country'] = GeoHelper::getCountryName($row['country'] ?? '');
                $record['city'] = $row['city'] ?? '';
            }

            $exportData[] = $record;
        }

        return $exportData;
    }

    /**
     * Export analytics data
     *
     * @since 1.0.0
     */
    public function exportAnalytics(?int $smartLinkId, string $dateRange, string $format, ?int $siteId = null): string
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'dateCreated',
                'linkId',
                'siteId',
                'deviceType',
                'deviceBrand',
                'deviceModel',
                'osName',
                'osVersion',
                'browser',
                'browserVersion',
                'country',
                'city',
                'language',
                'referrer',
                'metadata',
                'ip',
                'userAgent',
            ])
            ->orderBy(['dateCreated' => SORT_DESC]);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange);

        // Filter by smart link if specified
        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        // Check if there's any data to export
        if (empty($results)) {
            throw new \Exception('No data to export for the selected period.');
        }

        // Get plugin name for CSV headers
        $settings = SmartLinkManager::$plugin->getSettings();
        $displayName = $settings->getDisplayName();

        // Check if geo detection is enabled
        $geoEnabled = $settings->enableGeoDetection ?? true;

        // Handle JSON format
        if ($format === 'json') {
            return $this->_exportAsJson($results, $geoEnabled);
        }

        // CSV format - conditionally include geo columns
        if ($geoEnabled) {
            $csv = "Date,Time,{$displayName} Title,{$displayName} Status,{$displayName} URL,Site,Type,Button,Source,Destination URL,Referrer,User Device Type,User Device Brand,User Device Model,User OS,User OS Version,User Browser,User Browser Version,User Country,User City,User Language,User Agent\n";
        } else {
            $csv = "Date,Time,{$displayName} Title,{$displayName} Status,{$displayName} URL,Site,Type,Button,Source,Destination URL,Referrer,User Device Type,User Device Brand,User Device Model,User OS,User OS Version,User Browser,User Browser Version,User Language,User Agent\n";
        }

        foreach ($results as $row) {
            // Check settings to determine if we should include disabled/expired links
            $settings = SmartLinkManager::$plugin->getSettings();
            $includeDisabled = $settings->includeDisabledInExport ?? false;
            $includeExpired = $settings->includeExpiredInExport ?? false;

            // Always get the link with all statuses to check if it exists and its status
            // Don't filter by siteId here - just find the element by ID
            $smartLink = SmartLink::find()
                    ->id($row['linkId'])
                    ->status(null)
                    ->one();

            if (!$smartLink) {
                continue;
            }

            // Get the actual status
            $status = $smartLink->getStatus();

            // Skip based on settings
            if (!$includeDisabled && $status === SmartLink::STATUS_DISABLED) {
                continue;
            }

            if (!$includeExpired && $status === SmartLink::STATUS_EXPIRED) {
                continue;
            }

            $linkName = $smartLink->title;
            $linkStatus = match ($status) {
                SmartLink::STATUS_ENABLED => 'Active',
                    SmartLink::STATUS_DISABLED => 'Disabled',
                    SmartLink::STATUS_PENDING => 'Pending',
                    SmartLink::STATUS_EXPIRED => 'Expired',
                    default => 'Unknown'
            };
            $linkUrl = '';

            // Get site name and build the smart link URL
            $siteName = '';
            if (!empty($row['siteId'])) {
                $site = Craft::$app->getSites()->getSiteById($row['siteId']);
                $siteName = $site ? $site->name : '';
                // Generate the URL for the specific site
                $linkUrl = UrlHelper::siteUrl("go/{$smartLink->slug}", null, null, $row['siteId']);
            }

            $date = DateTimeHelper::toDateTime($row['dateCreated']);
            $dateStr = $date ? $date->format('Y-m-d') : '';
            $timeStr = $date ? $date->format('H:i:s') : '';

            // Parse metadata
            $metadata = $row['metadata'] ? Json::decode($row['metadata']) : [];
            $source = $metadata['source'] ?? 'direct';
            $clickType = $metadata['clickType'] ?? 'redirect';
            $buttonPlatform = '';
            $targetUrl = '';

            // Get the URL that was used
            if ($clickType === 'button') {
                // For button clicks, show which button URL was clicked
                $targetUrl = $metadata['buttonUrl'] ?? '';
                if (isset($metadata['platform'])) {
                    $buttonPlatform = ucfirst($metadata['platform']);
                }
            } else {
                // For redirects, show which URL they were sent to (check both old and new formats)
                $targetUrl = $metadata['redirectUrl'] ?? $metadata['buttonUrl'] ?? '';
            }

            // Keep the actual referrer URL
            $referrerDisplay = $row['referrer'] ?? '';

            // Convert source to display format
            $sourceDisplay = match ($source) {
                'qr' => 'QR',
                    'landing' => 'Landing',
                    default => 'Direct'
            };

            if ($geoEnabled) {
                $csv .= sprintf(
                        '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                        $dateStr,
                        $timeStr,
                        $linkName,
                        $linkStatus,
                        $linkUrl,
                        $siteName,
                        ucfirst($clickType),
                        $buttonPlatform,
                        $sourceDisplay,
                        $targetUrl,
                        $referrerDisplay,
                        $row['deviceType'] ?? '',
                        $row['deviceBrand'] ?? '',
                        $row['deviceModel'] ?? '',
                        $row['osName'] ?? '',
                        $row['osVersion'] ?? '',
                        $row['browser'] ?? '',
                        $row['browserVersion'] ?? '',
                        GeoHelper::getCountryName($row['country'] ?? ''),
                        $row['city'] ?? '',
                        $row['language'] ?? '',
                        $row['userAgent'] ?? ''
                    );
            } else {
                $csv .= sprintf(
                        '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                        $dateStr,
                        $timeStr,
                        $linkName,
                        $linkStatus,
                        $linkUrl,
                        $siteName,
                        ucfirst($clickType),
                        $buttonPlatform,
                        $sourceDisplay,
                        $targetUrl,
                        $referrerDisplay,
                        $row['deviceType'] ?? '',
                        $row['deviceBrand'] ?? '',
                        $row['deviceModel'] ?? '',
                        $row['osName'] ?? '',
                        $row['osVersion'] ?? '',
                        $row['browser'] ?? '',
                        $row['browserVersion'] ?? '',
                        $row['language'] ?? '',
                        $row['userAgent'] ?? ''
                    );
            }
        }

        return $csv;
    }
    /**
     * Get top smart links by clicks
     *
     * @param string $dateRange
     * @param int $limit
     * @return array
     * @since 1.0.0
     */
    public function getTopLinks(string $dateRange = 'last7days', int $limit = 5, ?int $siteId = null): array
    {
        $query = (new Query())
            ->from(['a' => '{{%smartlinkmanager_analytics}}'])
            ->select([
                'a.linkId',
                'a.siteId',
                'COUNT(*) as clicks',
                'MAX(a.dateCreated) as lastClick',
                'SUM(CASE WHEN JSON_EXTRACT(a.metadata, \'$.source\') = \'qr\' THEN 1 ELSE 0 END) as qrScans',
                'SUM(CASE WHEN JSON_EXTRACT(a.metadata, \'$.source\') != \'qr\' OR JSON_EXTRACT(a.metadata, \'$.source\') IS NULL THEN 1 ELSE 0 END) as directVisits',
            ])
            ->groupBy(['a.linkId', 'a.siteId'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange, 'a.dateCreated');

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['a.siteId' => $siteId]);
        }

        $results = $query->all();
        $topLinks = [];

        foreach ($results as $row) {
            $smartLink = SmartLink::find()
                ->id($row['linkId'])
                ->siteId($row['siteId'])
                ->status(null)
                ->one();

            if ($smartLink && $smartLink->getStatus() === SmartLink::STATUS_ENABLED) { // Only include active links
                // Get the last interaction details
                $lastInteractionQuery = (new Query())
                    ->from('{{%smartlinkmanager_analytics}}')
                    ->where(['linkId' => $row['linkId']])
                    ->orderBy(['dateCreated' => SORT_DESC]);

                // Apply same date range filter
                $this->applyDateRangeFilter($lastInteractionQuery, $dateRange);

                $lastInteraction = $lastInteractionQuery->one();

                $lastInteractionType = 'Unknown';
                $lastDestinationUrl = '';

                if ($lastInteraction && !empty($lastInteraction['metadata'])) {
                    $metadata = Json::decodeIfJson($lastInteraction['metadata']);

                    // Determine interaction type
                    if (isset($metadata['action'])) {
                        $lastInteractionType = $metadata['action'] === 'redirect' ? 'Redirect' : 'Button';
                    } elseif (isset($metadata['clickType'])) {
                        $lastInteractionType = $metadata['clickType'] === 'button' ? 'Button' : 'Redirect';
                    } elseif (isset($metadata['redirectUrl'])) {
                        // If there's a redirectUrl but no clickType, it's an automatic redirect
                        $lastInteractionType = 'Redirect';
                    } elseif (isset($metadata['buttonUrl'])) {
                        // If there's a buttonUrl but no clickType, it's a button click
                        $lastInteractionType = 'Button';
                    }

                    // Get destination URL
                    if (isset($metadata['buttonUrl'])) {
                        $lastDestinationUrl = $metadata['buttonUrl'];
                    } elseif (isset($metadata['redirectUrl'])) {
                        $lastDestinationUrl = $metadata['redirectUrl'];
                    } elseif (isset($metadata['destinationUrl'])) {
                        $lastDestinationUrl = $metadata['destinationUrl'];
                    }
                }

                $topLinkSite = $smartLink->getSite();

                // Convert lastClick to user's timezone
                $lastClick = null;
                $lastClickFormatted = null;
                if (!empty($row['lastClick'])) {
                    $utcDate = new \DateTime($row['lastClick'], new \DateTimeZone('UTC'));
                    $utcDate->setTimezone(new \DateTimeZone(Craft::$app->getTimeZone()));
                    $lastClick = $utcDate;
                    $lastClickFormatted = Craft::$app->getFormatter()->asDatetime($utcDate, 'short');
                }

                $topLinks[] = [
                    'id' => $smartLink->id,
                    'name' => $smartLink->title,
                    'slug' => $smartLink->slug,
                    'enabled' => $smartLink->enabled,
                    'siteName' => $topLinkSite->name ?? '-',
                    'clicks' => (int)$row['clicks'],
                    'lastClick' => $lastClick,
                    'lastClickFormatted' => $lastClickFormatted,
                    'lastInteractionType' => $lastInteractionType,
                    'lastDestinationUrl' => $lastDestinationUrl,
                    'qrScans' => (int)$row['qrScans'],
                    'directVisits' => (int)$row['directVisits'],
                ];
            }
        }

        return $topLinks;
    }

    /**
     * Get recent clicks across all smart links
     *
     * @param string $dateRange
     * @param int $limit
     * @return array
     * @since 1.0.0
     */
    public function getAllRecentClicks(string $dateRange = 'last7days', int $limit = 20, ?int $siteId = null): array
    {
        // Join with the site where the click happened (a.siteId), not the current CP site
        // This way Arabic clicks show Arabic title, English clicks show English title
        $query = (new Query())
            ->from(['a' => '{{%smartlinkmanager_analytics}}'])
            ->innerJoin(['s' => '{{%smartlinkmanager}}'], 'a.linkId = s.id')
            ->innerJoin(['e' => '{{%elements}}'], 's.id = e.id')
            ->innerJoin(['es' => '{{%elements_sites}}'], 'e.id = es.elementId AND es.siteId = a.siteId')
            ->leftJoin(['c' => '{{%smartlinkmanager_content}}'], 'c.smartLinkId = s.id AND c.siteId = a.siteId')
            ->leftJoin(['sites' => '{{%sites}}'], 'sites.id = a.siteId')
            ->select([
                'a.*',
                'COALESCE(c.title, s.title) as smartLinkTitle',
                's.slug as smartLinkSlug',
                'sites.name as siteName',
            ])
            ->where(['es.enabled' => true])
            ->orderBy('a.dateCreated DESC')
            ->limit($limit);

        // Apply date range filter
        $this->applyDateRangeFilter($query, $dateRange, 'a.dateCreated');

        // Filter by site if specified
        if ($siteId) {
            $query->andWhere(['a.siteId' => $siteId]);
        }

        $results = $query->all();
        $clicks = [];

        foreach ($results as $row) {
            $metadata = $row['metadata'] ? Json::decode($row['metadata']) : [];
            $clickType = $metadata['clickType'] ?? 'redirect';
            $destinationUrl = '';

            if ($clickType == 'button') {
                $destinationUrl = $metadata['buttonUrl'] ?? '';
            } else {
                // For redirects, check both redirectUrl (old format) and buttonUrl (new format)
                $destinationUrl = $metadata['redirectUrl'] ?? $metadata['buttonUrl'] ?? '';
            }

            // Get site name through Site model to parse env vars
            $site = !empty($row['siteId']) ? Craft::$app->getSites()->getSiteById($row['siteId']) : null;

            // Convert dateCreated to user's timezone
            $dateCreated = null;
            $dateCreatedFormatted = null;
            if (!empty($row['dateCreated'])) {
                $utcDate = new \DateTime($row['dateCreated'], new \DateTimeZone('UTC'));
                $utcDate->setTimezone(new \DateTimeZone(Craft::$app->getTimeZone()));
                $dateCreated = $utcDate;
                $dateCreatedFormatted = Craft::$app->getFormatter()->asDatetime($utcDate, 'short');
            }

            $clicks[] = [
                'id' => $row['id'],
                'linkId' => $row['linkId'],
                'smartLinkTitle' => $row['smartLinkTitle'],
                'smartLinkSlug' => $row['smartLinkSlug'],
                'siteName' => $site ? $site->name : '-',
                'dateCreated' => $dateCreated,
                'dateCreatedFormatted' => $dateCreatedFormatted,
                'siteId' => $row['siteId'],
                'deviceType' => $row['deviceType'],
                'browser' => $row['browser'],
                'osName' => $row['osName'],
                'country' => $row['country'],
                'city' => $row['city'],
                'clickType' => $clickType,
                'platform' => $metadata['platform'] ?? null,
                'destinationUrl' => $destinationUrl,
                'source' => $metadata['source'] ?? 'direct',
            ];
        }

        return $clicks;
    }

    /**
     * Track a click on a smart link
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $deviceInfo
     * @param array $metadata
     * @return void
     * @since 1.0.0
     */
    public function trackClick(SmartLink $smartLink, DeviceInfo $deviceInfo, array $metadata = []): void
    {
        // Add IP address to metadata now
        $metadata['ip'] = Craft::$app->request->getUserIP();

        // Save analytics directly (like Retour does)
        try {
            $this->saveAnalytics(
                $smartLink->id,
                $deviceInfo->toArray(),
                $metadata
            );
        } catch (\Exception $e) {
            // Log but don't throw - analytics shouldn't break the redirect
            $this->logError('Failed to save analytics', ['error' => $e->getMessage()]);
        }

        // Update click count in metadata
        $this->_incrementClickCount($smartLink);
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
        $this->logInfo('Saving Smart Link analytics', ['linkId' => $linkId]);

        try {
            $db = Craft::$app->getDb();

            // Anonymize IP address if setting is enabled
            // This must happen BEFORE geo-lookup and hashing
            $settings = SmartLinkManager::$plugin->getSettings();
            if ($settings->anonymizeIpAddress && isset($metadata['ip'])) {
                $metadata['ip'] = $this->_anonymizeIp($metadata['ip']);
            }

            // Hash IP with salt for storage (with error handling)
            $ipHash = null;
            if (isset($metadata['ip'])) {
                try {
                    $ipHash = $this->_hashIpWithSalt($metadata['ip']);
                } catch (\Exception $e) {
                    $this->logError('Failed to hash IP address', ['error' => $e->getMessage()]);
                    $ipHash = null;  // Continue without IP
                }
            }

            // Prepare the data according to actual database columns
            $data = [
                'linkId' => $linkId,
                'siteId' => $metadata['siteId'] ?? Craft::$app->getSites()->getCurrentSite()->id,
                'deviceType' => $deviceInfo['deviceType'] ?? $deviceInfo['type'] ?? null,
                // 'deviceName' => REMOVED
                'deviceBrand' => $deviceInfo['brand'] ?? null,
                'deviceModel' => $deviceInfo['model'] ?? null,
                // 'platform' => REMOVED
                'osName' => $deviceInfo['osName'] ?? null,
                'osVersion' => $deviceInfo['osVersion'] ?? null,
                'browser' => $deviceInfo['browser'] ?? null,
                'browserVersion' => $deviceInfo['browserVersion'] ?? null,
                'browserEngine' => $deviceInfo['browserEngine'] ?? null,
                'clientType' => $deviceInfo['clientType'] ?? null,
                'isRobot' => $deviceInfo['isBot'] ?? $deviceInfo['isRobot'] ?? false,
                'isMobileApp' => $deviceInfo['isMobileApp'] ?? false,
                'botName' => $deviceInfo['botName'] ?? null,
                'country' => null,
                'language' => $metadata['language'] ?? null,
                'referrer' => $metadata['referrer'] ?? null,
                'ip' => $ipHash,
                'userAgent' => $deviceInfo['userAgent'] ?? null,
                'metadata' => Json::encode($metadata),
                'dateCreated' => Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ];

            // Get location data from IP if geo detection is enabled
            // IMPORTANT: This must happen BEFORE we remove IP from metadata
            if (SmartLinkManager::$plugin->getSettings()->enableGeoDetection && isset($metadata['ip'])) {
                $location = $this->getLocationFromIp($metadata['ip']);
                if ($location) {
                    $data['country'] = $location['countryCode'];
                    $data['city'] = $location['city'];
                    $data['region'] = $location['region'];
                    $data['timezone'] = $location['timezone'];
                    $data['latitude'] = $location['lat'];
                    $data['longitude'] = $location['lon'];
                    // 'isp' => REMOVED
                }
            }

            // Remove raw IP from metadata before storage (privacy protection)
            // The hashed IP is already stored in $data['ip']
            unset($metadata['ip']);

            // Re-encode metadata without raw IP
            $data['metadata'] = Json::encode($metadata);

            return (bool)$db->createCommand()
                ->insert('{{%smartlinkmanager_analytics}}', $data)
                ->execute();
        } catch (\Exception $e) {
            $context = ['error' => $e->getMessage(), 'linkId' => $linkId];
            if (isset($data)) {
                $context['data'] = $data;
            }
            $this->logError('Failed to save analytics', $context);
            return false;
        }
    }

    /**
     * Hash IP address with salt for privacy
     *
     * @param string $ip
     * @return string
     * @throws \Exception
     */
    private function _hashIpWithSalt(string $ip): string
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $salt = $settings->ipHashSalt;

        if (!$salt || $salt === '$SMARTLINK_MANAGER_IP_SALT' || trim($salt) === '') {
            $this->logError('IP hash salt not configured - analytics tracking disabled', [
                'ip' => 'hidden',
                'saltValue' => $salt ?? 'NULL',
            ]);
            throw new \Exception('IP hash salt not configured. Run: php craft smartlink-manager/security/generate-salt');
        }

        return hash('sha256', $ip . $salt);
    }

    /**
     * Anonymize IP address for privacy
     * IPv4: Masks last octet (192.168.1.123 → 192.168.1.0)
     * IPv6: Masks last 80 bits (keeps first 48 bits)
     *
     * @param string $ip
     * @return string
     */
    private function _anonymizeIp(string $ip): string
    {
        // Detect if IPv4 or IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // IPv4: Mask last octet
            // 192.168.1.123 → 192.168.1.0
            return preg_replace('/\.\d+$/', '.0', $ip);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // IPv6: Mask last 80 bits (keep first 48 bits = /48 prefix)
            // Standard practice for IPv6 anonymization

            // Convert IPv6 to binary
            $binary = inet_pton($ip);
            if ($binary === false) {
                return $ip; // Return original if conversion fails
            }

            // For IPv6 (128 bits total):
            // - Keep first 48 bits (6 bytes) - ISP/network prefix
            // - Zero out last 80 bits (10 bytes) - host identifier
            $anonymized = substr($binary, 0, 6) . str_repeat("\0", 10);

            // Convert back to IPv6 notation
            $result = inet_ntop($anonymized);
            return $result !== false ? $result : $ip;
        }

        // If neither IPv4 nor IPv6, return as-is
        return $ip;
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
        $query = (new Query())
            ->from(['{{%smartlinkmanager_analytics}}'])
            ->where(['linkId' => $smartLink->id]);

        // Date range filter
        if (isset($criteria['from'])) {
            $query->andWhere(['>=', 'timestamp', Db::prepareDateForDb($criteria['from'])]);
        }

        if (isset($criteria['to'])) {
            $query->andWhere(['<=', 'timestamp', Db::prepareDateForDb($criteria['to'])]);
        }

        // OS filter (replacing platform)
        if (isset($criteria['os'])) {
            $query->andWhere(['osName' => $criteria['os']]);
        }

        // Get total count
        $total = (clone $query)->count();

        // Get device breakdown
        $devices = (clone $query)
            ->select(['devicePlatform', 'COUNT(*) as count'])
            ->groupBy(['devicePlatform'])
            ->indexBy('devicePlatform')
            ->column();

        // Get daily breakdown for last 30 days
        $daily = [];
        if (!isset($criteria['skipDaily']) || !$criteria['skipDaily']) {
            $dailyQuery = (clone $query)
                ->select(['DATE(timestamp) as date', 'COUNT(*) as count'])
                ->andWhere(['>=', 'timestamp', DateTimeHelper::currentTimeStamp() - (30 * 24 * 60 * 60)])
                ->groupBy(['DATE(timestamp)'])
                ->orderBy(['date' => SORT_ASC]);

            foreach ($dailyQuery->all() as $row) {
                $daily[$row['date']] = (int)$row['count'];
            }
        }

        // Get language breakdown
        $languages = (clone $query)
            ->select(['language', 'COUNT(*) as count'])
            ->andWhere(['not', ['language' => null]])
            ->groupBy(['language'])
            ->indexBy('language')
            ->column();

        // Get country breakdown if enabled
        $countries = [];
        if (SmartLinkManager::$plugin->getSettings()->enableGeoDetection) {
            $countries = (clone $query)
                ->select(['country', 'COUNT(*) as count'])
                ->andWhere(['not', ['country' => null]])
                ->groupBy(['country'])
                ->orderBy(['count' => SORT_DESC])
                ->limit(10)
                ->indexBy('country')
                ->column();
        }

        return [
            'total' => (int)$total,
            'devices' => $devices,
            'daily' => $daily,
            'languages' => $languages,
            'countries' => $countries,
        ];
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
        $query = (new Query())
            ->from(['{{%smartlinkmanager_analytics}}'])
            ->where(['in', 'linkId', $linkIds]);

        // Apply period filter
        $seconds = $this->_periodToSeconds($period);
        if ($seconds > 0) {
            $query->andWhere(['>=', 'timestamp', DateTimeHelper::currentTimeStamp() - $seconds]);
        }

        // Get stats
        $stats = [];
        foreach ($linkIds as $linkId) {
            $linkQuery = (clone $query)->andWhere(['linkId' => $linkId]);
            $stats[$linkId] = [
                'total' => (int)$linkQuery->count(),
                'devices' => $linkQuery
                    ->select(['devicePlatform', 'COUNT(*) as count'])
                    ->groupBy(['devicePlatform'])
                    ->indexBy('devicePlatform')
                    ->column(),
            ];
        }

        return $stats;
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
        return Craft::$app->db->createCommand()
            ->delete('{{%smartlinkmanager_analytics}}', ['linkId' => $smartLink->id])
            ->execute();
    }

    /**
     * Clean old analytics data
     *
     * @param int $days
     * @return int Number of records deleted
     * @since 1.0.0
     */
    public function cleanOldAnalytics(int $days): int
    {
        $cutoffDate = DateTimeHelper::currentTimeStamp() - ($days * 24 * 60 * 60);

        return Craft::$app->db->createCommand()
            ->delete('{{%smartlinkmanager_analytics}}', ['<', 'timestamp', $cutoffDate])
            ->execute();
    }

    /**
     * Increment click count in smart link metadata
     *
     * @param SmartLink $smartLink
     * @return void
     */
    private function _incrementClickCount(SmartLink $smartLink): void
    {
        $metadata = $smartLink->metadata ?? [];
        $metadata['clicks'] = ($metadata['clicks'] ?? 0) + 1;
        $metadata['lastClick'] = DateTimeHelper::currentTimeStamp();

        $smartLink->metadata = $metadata;
        $smartLink->clicks = $metadata['clicks'];

        // Update directly in database to avoid triggering events
        Craft::$app->db->createCommand()
            ->update('{{%smartlinkmanager}}', [
                'metadata' => Json::encode($metadata),
            ], ['id' => $smartLink->id])
            ->execute();
    }

    /**
     * Convert period string to seconds
     *
     * @param string $period
     * @return int
     */
    private function _periodToSeconds(string $period): int
    {
        $matches = [];
        if (!preg_match('/^(\d+)([hdwmy])$/', $period, $matches)) {
            return 0;
        }

        $value = (int)$matches[1];
        $unit = $matches[2];

        return match ($unit) {
            'h' => $value * 3600,
            'd' => $value * 86400,
            'w' => $value * 604800,
            'm' => $value * 2592000,
            'y' => $value * 31536000,
        };
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
        // Handle private/local IPs with default location for development
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $this->getDefaultLocation();
        }

        // Use centralized geo lookup from base plugin
        $geoData = $this->lookupGeoIp($ip, $this->getGeoConfig());

        if ($geoData === null) {
            return null;
        }

        // Normalize response to match expected format (lat/lon keys, include timezone)
        return [
            'countryCode' => $geoData['countryCode'] ?? null,
            'country' => $geoData['country'] ?? null,
            'city' => $geoData['city'] ?? null,
            'region' => $geoData['region'] ?? null,
            'timezone' => $geoData['timezone'] ?? null,
            'lat' => $geoData['latitude'] ?? null,
            'lon' => $geoData['longitude'] ?? null,
        ];
    }

    /**
     * Get geo config from plugin settings
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

    /**
     * Get default location for private/local IPs
     *
     * @return array<string, mixed>
     */
    private function getDefaultLocation(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $defaultCountry = $settings->defaultCountry ?: (getenv('SMARTLINK_MANAGER_DEFAULT_COUNTRY') ?: 'AE');
        $defaultCity = $settings->defaultCity ?: (getenv('SMARTLINK_MANAGER_DEFAULT_CITY') ?: 'Dubai');

        // Predefined locations for common cities worldwide
        $locations = [
            'US' => [
                'New York' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'New York', 'region' => 'New York', 'timezone' => 'America/New_York', 'lat' => 40.7128, 'lon' => -74.0060],
                'Los Angeles' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'Los Angeles', 'region' => 'California', 'timezone' => 'America/Los_Angeles', 'lat' => 34.0522, 'lon' => -118.2437],
                'Chicago' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'Chicago', 'region' => 'Illinois', 'timezone' => 'America/Chicago', 'lat' => 41.8781, 'lon' => -87.6298],
                'San Francisco' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'San Francisco', 'region' => 'California', 'timezone' => 'America/Los_Angeles', 'lat' => 37.7749, 'lon' => -122.4194],
            ],
            'GB' => [
                'London' => ['countryCode' => 'GB', 'country' => 'United Kingdom', 'city' => 'London', 'region' => 'England', 'timezone' => 'Europe/London', 'lat' => 51.5074, 'lon' => -0.1278],
                'Manchester' => ['countryCode' => 'GB', 'country' => 'United Kingdom', 'city' => 'Manchester', 'region' => 'England', 'timezone' => 'Europe/London', 'lat' => 53.4808, 'lon' => -2.2426],
            ],
            'AE' => [
                'Dubai' => ['countryCode' => 'AE', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'region' => 'Dubai', 'timezone' => 'Asia/Dubai', 'lat' => 25.2048, 'lon' => 55.2708],
                'Abu Dhabi' => ['countryCode' => 'AE', 'country' => 'United Arab Emirates', 'city' => 'Abu Dhabi', 'region' => 'Abu Dhabi', 'timezone' => 'Asia/Dubai', 'lat' => 24.4539, 'lon' => 54.3773],
            ],
            'SA' => [
                'Riyadh' => ['countryCode' => 'SA', 'country' => 'Saudi Arabia', 'city' => 'Riyadh', 'region' => 'Riyadh Province', 'timezone' => 'Asia/Riyadh', 'lat' => 24.7136, 'lon' => 46.6753],
                'Jeddah' => ['countryCode' => 'SA', 'country' => 'Saudi Arabia', 'city' => 'Jeddah', 'region' => 'Makkah Province', 'timezone' => 'Asia/Riyadh', 'lat' => 21.5433, 'lon' => 39.1728],
            ],
            'DE' => [
                'Berlin' => ['countryCode' => 'DE', 'country' => 'Germany', 'city' => 'Berlin', 'region' => 'Berlin', 'timezone' => 'Europe/Berlin', 'lat' => 52.5200, 'lon' => 13.4050],
                'Munich' => ['countryCode' => 'DE', 'country' => 'Germany', 'city' => 'Munich', 'region' => 'Bavaria', 'timezone' => 'Europe/Berlin', 'lat' => 48.1351, 'lon' => 11.5820],
            ],
            'FR' => [
                'Paris' => ['countryCode' => 'FR', 'country' => 'France', 'city' => 'Paris', 'region' => 'Île-de-France', 'timezone' => 'Europe/Paris', 'lat' => 48.8566, 'lon' => 2.3522],
            ],
            'CA' => [
                'Toronto' => ['countryCode' => 'CA', 'country' => 'Canada', 'city' => 'Toronto', 'region' => 'Ontario', 'timezone' => 'America/Toronto', 'lat' => 43.6532, 'lon' => -79.3832],
                'Vancouver' => ['countryCode' => 'CA', 'country' => 'Canada', 'city' => 'Vancouver', 'region' => 'British Columbia', 'timezone' => 'America/Vancouver', 'lat' => 49.2827, 'lon' => -123.1207],
            ],
            'AU' => [
                'Sydney' => ['countryCode' => 'AU', 'country' => 'Australia', 'city' => 'Sydney', 'region' => 'New South Wales', 'timezone' => 'Australia/Sydney', 'lat' => -33.8688, 'lon' => 151.2093],
                'Melbourne' => ['countryCode' => 'AU', 'country' => 'Australia', 'city' => 'Melbourne', 'region' => 'Victoria', 'timezone' => 'Australia/Melbourne', 'lat' => -37.8136, 'lon' => 144.9631],
            ],
            'JP' => [
                'Tokyo' => ['countryCode' => 'JP', 'country' => 'Japan', 'city' => 'Tokyo', 'region' => 'Tokyo', 'timezone' => 'Asia/Tokyo', 'lat' => 35.6762, 'lon' => 139.6503],
            ],
            'SG' => [
                'Singapore' => ['countryCode' => 'SG', 'country' => 'Singapore', 'city' => 'Singapore', 'region' => 'Singapore', 'timezone' => 'Asia/Singapore', 'lat' => 1.3521, 'lon' => 103.8198],
            ],
            'IN' => [
                'Mumbai' => ['countryCode' => 'IN', 'country' => 'India', 'city' => 'Mumbai', 'region' => 'Maharashtra', 'timezone' => 'Asia/Kolkata', 'lat' => 19.0760, 'lon' => 72.8777],
                'Delhi' => ['countryCode' => 'IN', 'country' => 'India', 'city' => 'Delhi', 'region' => 'Delhi', 'timezone' => 'Asia/Kolkata', 'lat' => 28.7041, 'lon' => 77.1025],
            ],
        ];

        // Return the configured location if it exists
        if (isset($locations[$defaultCountry][$defaultCity])) {
            return $locations[$defaultCountry][$defaultCity];
        }

        // Fallback to Dubai if configuration not found
        return $locations['AE']['Dubai'];
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
        $location = $this->getLocationFromIp($ip);
        return $location ? $location['countryCode'] : null;
    }

    /**
     * Clean up invalid platform values in analytics metadata
     *
     * @return int Number of records updated
     * @since 1.18.0
     */
    public function cleanupPlatformValues(): int
    {
        $db = Craft::$app->getDb();
        $updated = 0;

        // Get all analytics records
        $records = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['id', 'metadata', 'osName'])
            ->all();

        foreach ($records as $record) {
            $metadata = Json::decodeIfJson($record['metadata']);
            if (!$metadata) {
                continue;
            }

            $oldPlatform = $metadata['platform'] ?? null;
            $clickType = $metadata['clickType'] ?? null;
            $buttonUrl = $metadata['buttonUrl'] ?? null;
            $newPlatform = null;
            $shouldUpdate = false;

            // Case 1: Landing page views (clickType: "landing") - remove platform
            if ($clickType === 'landing' && $oldPlatform !== null) {
                unset($metadata['platform']);
                $shouldUpdate = true;
            }
            // Case 2: Button clicks with wrong platform (translated labels, "continue-to-website", or old label formats)
            elseif ($clickType === 'button' && $oldPlatform) {
                // Direct label-to-platform mapping for known old values
                $labelToPlatform = [
                    'app-store' => 'ios',
                    'google-play' => 'android',
                    'appgallery' => 'huawei',
                    'continue-to-website' => null, // Will derive from buttonUrl
                    'fallback' => null, // Will derive from buttonUrl
                ];

                // Check if it's a known old label or contains problematic text
                if (isset($labelToPlatform[$oldPlatform]) ||
                    str_contains($oldPlatform, 'متجر') ||
                    str_contains($oldPlatform, 'AppGallery') ||
                    str_contains($oldPlatform, 'App Store') ||
                    str_contains($oldPlatform, 'Google Play') ||
                    str_contains($oldPlatform, 'Windows Store') ||
                    str_contains($oldPlatform, 'Mac App Store')) {

                    // If we have a direct mapping, use it
                    if (isset($labelToPlatform[$oldPlatform]) && $labelToPlatform[$oldPlatform] !== null) {
                        $newPlatform = $labelToPlatform[$oldPlatform];
                    }
                    // Otherwise derive from buttonUrl
                    elseif ($buttonUrl) {
                        if (str_contains($buttonUrl, 'apps.apple.com')) {
                            $newPlatform = 'ios';
                        } elseif (str_contains($buttonUrl, 'play.google.com')) {
                            $newPlatform = 'android';
                        } elseif (str_contains($buttonUrl, 'appgallery.huawei')) {
                            $newPlatform = 'huawei';
                        } elseif (str_contains($buttonUrl, 'amazon.com') || str_contains($buttonUrl, 'amzn')) {
                            $newPlatform = 'amazon';
                        } elseif (str_contains($buttonUrl, 'microsoft.com/store')) {
                            $newPlatform = 'windows';
                        } else {
                            $newPlatform = 'other';
                        }
                    } else {
                        $newPlatform = 'other';
                    }

                    $metadata['platform'] = $newPlatform;
                    $shouldUpdate = true;
                }
            }
            // Case 3: Redirects with platform: "redirect" - derive from buttonUrl or osName
            elseif ($clickType === 'redirect' && $oldPlatform === 'redirect') {
                // Try to derive from buttonUrl first
                if ($buttonUrl) {
                    if (str_contains($buttonUrl, 'apps.apple.com')) {
                        $newPlatform = 'ios';
                    } elseif (str_contains($buttonUrl, 'play.google.com')) {
                        $newPlatform = 'android';
                    } elseif (str_contains($buttonUrl, 'appgallery.huawei')) {
                        $newPlatform = 'huawei';
                    } elseif (str_contains($buttonUrl, 'amazon.com') || str_contains($buttonUrl, 'amzn')) {
                        $newPlatform = 'amazon';
                    } else {
                        $newPlatform = 'other';
                    }
                }
                // Fallback to osName
                elseif ($record['osName']) {
                    $osNameLower = strtolower($record['osName']);
                    if (str_contains($osNameLower, 'ios')) {
                        $newPlatform = 'ios';
                    } elseif (str_contains($osNameLower, 'android')) {
                        $newPlatform = 'android';
                    } elseif (str_contains($osNameLower, 'windows')) {
                        $newPlatform = 'windows';
                    } elseif (str_contains($osNameLower, 'mac')) {
                        $newPlatform = 'macos';
                    } else {
                        $newPlatform = 'other';
                    }
                } else {
                    $newPlatform = 'other';
                }

                $metadata['platform'] = $newPlatform;
                $shouldUpdate = true;
            }
            // Case 4: Fix capitalization
            elseif ($oldPlatform) {
                $platformMap = [
                    'iOS' => 'ios',
                    'Ios' => 'ios',
                    'IOS' => 'ios',
                    'Android' => 'android',
                    'ANDROID' => 'android',
                    'Windows' => 'windows',
                    'WINDOWS' => 'windows',
                    'macOS' => 'macos',
                    'MacOS' => 'macos',
                    'Mac' => 'macos',
                    'MAC' => 'macos',
                ];

                if (isset($platformMap[$oldPlatform])) {
                    $metadata['platform'] = $platformMap[$oldPlatform];
                    $shouldUpdate = true;
                }
            }

            // Update if needed
            if ($shouldUpdate) {
                $db->createCommand()
                    ->update(
                        '{{%smartlinkmanager_analytics}}',
                        ['metadata' => Json::encode($metadata)],
                        ['id' => $record['id']]
                    )
                    ->execute();

                $updated++;
            }
        }

        return $updated;
    }

    /**
     * Export analytics data as JSON
     *
     * @param array $results Raw query results
     * @param bool $geoEnabled Whether geo detection is enabled
     * @return string JSON string
     */
    private function _exportAsJson(array $results, bool $geoEnabled): string
    {
        $data = [];
        $settings = SmartLinkManager::$plugin->getSettings();
        $includeDisabled = $settings->includeDisabledInExport ?? false;
        $includeExpired = $settings->includeExpiredInExport ?? false;

        foreach ($results as $row) {
            // Get the link
            $smartLink = SmartLink::find()
                ->id($row['linkId'])
                ->status(null)
                ->one();

            if (!$smartLink) {
                continue;
            }

            // Get the actual status
            $status = $smartLink->getStatus();

            // Skip based on settings
            if (!$includeDisabled && $status === SmartLink::STATUS_DISABLED) {
                continue;
            }

            if (!$includeExpired && $status === SmartLink::STATUS_EXPIRED) {
                continue;
            }

            $date = DateTimeHelper::toDateTime($row['dateCreated']);

            // Get site name
            $siteName = null;
            if (!empty($row['siteId'])) {
                $site = Craft::$app->getSites()->getSiteById($row['siteId']);
                $siteName = $site ? $site->name : null;
            }

            // Parse metadata
            $metadata = $row['metadata'] ? Json::decode($row['metadata']) : [];
            $source = $metadata['source'] ?? 'direct';
            $clickType = $metadata['clickType'] ?? 'redirect';
            $buttonPlatform = $metadata['platform'] ?? null;
            $targetUrl = '';

            if ($clickType === 'button') {
                $targetUrl = $metadata['buttonUrl'] ?? '';
            } else {
                $targetUrl = $metadata['redirectUrl'] ?? $metadata['buttonUrl'] ?? '';
            }

            $item = [
                'date' => $date ? $date->format('Y-m-d') : null,
                'time' => $date ? $date->format('H:i:s') : null,
                'datetime' => $date ? $date->format('c') : null,
                'smartLink' => [
                    'id' => $smartLink->id,
                    'title' => $smartLink->title,
                    'slug' => $smartLink->slug,
                    'status' => $status,
                ],
                'siteId' => $row['siteId'] ? (int)$row['siteId'] : null,
                'siteName' => $siteName,
                'type' => $clickType,
                'buttonPlatform' => $buttonPlatform,
                'source' => $source,
                'destinationUrl' => $targetUrl,
                'referrer' => $row['referrer'] ?? null,
                'device' => [
                    'type' => $row['deviceType'] ?? null,
                    'brand' => $row['deviceBrand'] ?? null,
                    'model' => $row['deviceModel'] ?? null,
                ],
                'os' => [
                    'name' => $row['osName'] ?? null,
                    'version' => $row['osVersion'] ?? null,
                ],
                'browser' => [
                    'name' => $row['browser'] ?? null,
                    'version' => $row['browserVersion'] ?? null,
                ],
                'language' => $row['language'] ?? null,
                'userAgent' => $row['userAgent'] ?? null,
            ];

            // Add geo data if enabled
            if ($geoEnabled) {
                $item['location'] = [
                    'country' => $row['country'] ?? null,
                    'city' => $row['city'] ?? null,
                ];
            }

            $data[] = $item;
        }

        return json_encode([
            'exported' => date('c'),
            'count' => count($data),
            'data' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
