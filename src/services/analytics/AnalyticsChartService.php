<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services\analytics;

use Craft;
use craft\db\Query;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\GeoHelper;

/**
 * Analytics Chart Service
 *
 * Daily clicks chart, hourly analytics, and cross-referenced insights.
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     5.22.0
 */
class AnalyticsChartService
{
    use AnalyticsQueryTrait;

    /**
     * Get clicks data for charts
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getClicksData(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        $localDate = DateFormatHelper::localDateExpression('dateCreated');

        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['date' => $localDate, 'COUNT(*) as count'])
            ->groupBy($localDate)
            ->orderBy(['date' => SORT_ASC]);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $timezone = Craft::$app->getTimeZone();

        if ($dateRange === 'today') {
            $now = new \DateTime('now', new \DateTimeZone($timezone));
            $startTimestamp = strtotime($now->format('Y-m-d'));
            $endTimestamp = $startTimestamp;
        } elseif ($dateRange === 'yesterday') {
            $yesterday = new \DateTime('yesterday', new \DateTimeZone($timezone));
            $startTimestamp = strtotime($yesterday->format('Y-m-d'));
            $endTimestamp = $startTimestamp;
        } else {
            $startDate = $this->getStartDateForRange($dateRange);
            $endDate = $this->getEndDateForRange($dateRange);

            if ($startDate) {
                $start = new \DateTime($startDate, new \DateTimeZone('UTC'));
                $start->setTimezone(new \DateTimeZone($timezone));
                $startTimestamp = strtotime($start->format('Y-m-d'));
            } else {
                $startTimestamp = !empty($results) ? strtotime($results[0]['date']) : strtotime('-30 days');
            }

            if ($endDate) {
                $end = new \DateTime($endDate, new \DateTimeZone('UTC'));
                $end->setTimezone(new \DateTimeZone($timezone));
                $endTimestamp = strtotime($end->format('Y-m-d'));
            } else {
                $endTimestamp = strtotime('today');
            }
        }

        $dataMap = [];
        foreach ($results as $row) {
            $dataMap[date('M j', strtotime($row['date']))] = (int) $row['count'];
        }

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
     * Get hourly analytics for peak usage times
     *
     * @param int|null $smartLinkId
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getHourlyAnalytics(?int $smartLinkId, string $dateRange, int|array|null $siteId = null): array
    {
        $localHour = DateFormatHelper::localHourExpression('dateCreated');

        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'hour' => $localHour,
                'COUNT(*) as clicks',
            ])
            ->groupBy($localHour)
            ->orderBy(['hour' => SORT_ASC]);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $hourlyData = array_fill(0, 24, 0);

        foreach ($results as $row) {
            $hourlyData[(int) $row['hour']] = (int) $row['clicks'];
        }

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
     * @param string $dateRange
     * @param int|int[]|null $siteId
     * @return array
     * @since 5.22.0
     */
    public function getInsights(string $dateRange, int|array|null $siteId = null): array
    {
        // Mobile usage by top cities
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select([
                'city',
                "SUM(CASE WHEN osName IN ('iOS', 'Android') THEN 1 ELSE 0 END) as mobile_clicks",
                'COUNT(*) as total_clicks',
            ])
            ->where(['not', ['city' => null]])
            ->groupBy(['city'])
            ->having(['>', 'total_clicks', 10])
            ->orderBy(['total_clicks' => SORT_DESC])
            ->limit(5);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $cityMobileUsage = [];
        foreach ($query->all() as $row) {
            $mobilePercentage = round(($row['mobile_clicks'] / $row['total_clicks']) * 100, 1);
            $cityMobileUsage[] = [
                'city' => $row['city'],
                'mobilePercentage' => $mobilePercentage,
                'totalClicks' => (int) $row['total_clicks'],
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

        $this->applyDateRangeFilter($browserByCountry, $dateRange);

        $browserData = [];
        foreach ($browserByCountry->all() as $row) {
            $countryName = GeoHelper::getCountryName($row['country']);
            if (!isset($browserData[$countryName])) {
                $browserData[$countryName] = [];
            }
            $browserData[$countryName][] = [
                'browser' => $row['browser'],
                'clicks' => (int) $row['clicks'],
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

        $this->applyDateRangeFilter($brandsByCountry, $dateRange);

        $brandsData = [];
        foreach ($brandsByCountry->all() as $row) {
            $countryName = GeoHelper::getCountryName($row['country']);
            if (!isset($brandsData[$countryName])) {
                $brandsData[$countryName] = [];
            }
            if (count($brandsData[$countryName]) < 3) {
                $brandsData[$countryName][] = [
                    'brand' => $row['deviceBrand'],
                    'clicks' => (int) $row['clicks'],
                ];
            }
        }

        return [
            'cityMobileUsage' => $cityMobileUsage,
            'browserByCountry' => $browserData,
            'brandsByCountry' => $brandsData,
        ];
    }
}
