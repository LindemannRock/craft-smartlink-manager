<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services\analytics;

use craft\db\Query;

/**
 * Analytics Breakdown Service
 *
 * Device, browser, OS, brand, and language breakdowns.
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     5.22.0
 */
class AnalyticsBreakdownService
{
    use AnalyticsQueryTrait;

    /**
     * Get device breakdown (mobile, tablet, desktop)
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getDeviceBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['deviceType', 'COUNT(*) as count'])
            ->groupBy(['deviceType']);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $labels = [];
        $values = [];

        foreach ($results as $row) {
            $labels[] = ucfirst($row['deviceType']);
            $values[] = (int) $row['count'];
        }

        if (empty($labels)) {
            return [
                'labels' => ['No data yet'],
                'values' => [1],
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get detailed device type breakdown
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getDeviceTypeBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
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

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

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
            $values[] = (int) $row['clicks'];
            $categories[$deviceInfo['category']] += (int) $row['clicks'];
        }

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
     * Get device brand breakdown
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getDeviceBrandBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
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

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

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
            $values[] = (int) $row['clicks'];
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
     * Get platform breakdown (iOS, Android, Windows, macOS, Linux)
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getPlatformBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['osName', 'COUNT(*) as count'])
            ->groupBy(['osName']);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $labels = [];
        $values = [];

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
            $values[] = (int) $row['count'];
        }

        if (empty($labels)) {
            return [
                'labels' => ['No data yet'],
                'values' => [1],
            ];
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get OS breakdown with versions
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getOsBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
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

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

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

            $osData[$osName]['totalClicks'] += (int) $row['clicks'];

            if ($row['osVersion']) {
                $osData[$osName]['versions'][] = [
                    'version' => $row['osVersion'],
                    'clicks' => (int) $row['clicks'],
                ];
            }
        }

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
                'versions' => array_slice($os['versions'], 0, 5),
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
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getBrowserBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
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

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();
        $totalClicks = array_sum(array_column($results, 'clicks'));

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

            $browserData[$browserName]['totalClicks'] += (int) $row['clicks'];

            if ($row['browserVersion']) {
                $versionParts = explode('.', $row['browserVersion']);
                $simplifiedVersion = count($versionParts) >= 2
                    ? $versionParts[0] . '.' . $versionParts[1]
                    : $row['browserVersion'];

                if (!isset($browserData[$browserName]['versions'][$simplifiedVersion])) {
                    $browserData[$browserName]['versions'][$simplifiedVersion] = 0;
                }
                $browserData[$browserName]['versions'][$simplifiedVersion] += (int) $row['clicks'];
            }
        }

        uasort($browserData, function($a, $b) {
            return $b['totalClicks'] - $a['totalClicks'];
        });

        $labels = [];
        $values = [];
        $details = [];

        foreach ($browserData as $browser) {
            $labels[] = $browser['name'];
            $values[] = $browser['totalClicks'];

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
     * Get language breakdown
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getLanguageBreakdown(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        return [
            'labels' => [],
            'values' => [],
        ];
    }
}
