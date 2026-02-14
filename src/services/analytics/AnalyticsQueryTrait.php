<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services\analytics;

use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use lindemannrock\base\helpers\DateRangeHelper;

/**
 * Shared query utilities for analytics sub-services
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     5.22.0
 */
trait AnalyticsQueryTrait
{
    /**
     * Apply date range filter to query
     *
     * @param Query $query
     * @param string $dateRange
     * @param string $dateColumn
     * @return Query
     */
    protected function applyDateRangeFilter(Query $query, string $dateRange, string $dateColumn = 'dateCreated'): Query
    {
        DateRangeHelper::applyToQuery($query, $dateRange, $dateColumn);
        return $query;
    }

    /**
     * Get start date for date range
     *
     * @param string $range
     * @return string|null
     */
    protected function getStartDateForRange(string $range): ?string
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
    protected function getEndDateForRange(string $range): ?string
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
}
