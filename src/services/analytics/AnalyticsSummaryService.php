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
use craft\helpers\Db;
use craft\helpers\Json;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\DbHelper;
use lindemannrock\base\helpers\GeoHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Analytics Summary Service
 *
 * Summaries, top links, recent clicks, geo top-N, and button clicks.
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     5.22.0
 */
class AnalyticsSummaryService
{
    use AnalyticsQueryTrait;

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
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}');

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $totalClicks = (int) $query->count();
        $uniqueVisitors = (int) (clone $query)->select('COUNT(DISTINCT ip)')->scalar();

        $activeLinksQuery = SmartLink::find()
            ->status(SmartLink::STATUS_ENABLED);
        if ($siteId) {
            $activeLinksQuery->siteId($siteId);
        }
        $activeLinks = $activeLinksQuery->count();

        $totalLinks = SmartLink::find()->count();

        $linksQuery = (new Query())
            ->from('{{%smartlinkmanager_analytics}} a')
            ->innerJoin('{{%smartlinkmanager}} s', 'a.linkId = s.id')
            ->innerJoin('{{%elements}} e', 's.id = e.id')
            ->innerJoin('{{%elements_sites}} es', 'e.id = es.elementId')
            ->select('COUNT(DISTINCT a.linkId)')
            ->where(['es.enabled' => true]);

        $this->applyDateRangeFilter($linksQuery, $dateRange, 'a.dateCreated');

        if ($siteId) {
            $linksQuery->andWhere(['a.siteId' => $siteId]);
        }

        $linksWithClicks = (int) $linksQuery->scalar();

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
     * @param int|int[]|null $siteId
     * @return array
     * @since 1.0.0
     */
    public function getSmartLinkAnalytics(int $smartLinkId, string $dateRange = 'last7days', int|array|null $siteId = null): array
    {
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $smartLinkId]);

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $this->applyDateRangeFilter($query, $dateRange);

        $totalClicks = (int) $query->count();
        $uniqueClicks = (int) (clone $query)->select('COUNT(DISTINCT ip)')->scalar();

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

        $buttonClicks = $this->getButtonClicks($smartLinkId, $dateRange, $siteId);

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

        if (isset($criteria['from'])) {
            $query->andWhere(['>=', 'dateCreated', Db::prepareDateForDb($criteria['from'])]);
        }

        if (isset($criteria['to'])) {
            $query->andWhere(['<=', 'dateCreated', Db::prepareDateForDb($criteria['to'])]);
        }

        if (isset($criteria['os'])) {
            $query->andWhere(['osName' => $criteria['os']]);
        }

        $total = (clone $query)->count();

        $devices = (clone $query)
            ->select(['deviceType', 'COUNT(*) as count'])
            ->groupBy(['deviceType'])
            ->indexBy('deviceType')
            ->column();

        $daily = [];
        if (!isset($criteria['skipDaily']) || !$criteria['skipDaily']) {
            $thirtyDaysAgo = (new \DateTime())->modify('-30 days');
            $localDate = DateFormatHelper::localDateExpression('dateCreated');

            $dailyQuery = (clone $query)
                ->select(['date' => $localDate, 'COUNT(*) as count'])
                ->andWhere(['>=', 'dateCreated', Db::prepareDateForDb($thirtyDaysAgo)])
                ->groupBy([$localDate])
                ->orderBy(['date' => SORT_ASC]);

            foreach ($dailyQuery->all() as $row) {
                $daily[$row['date']] = (int) $row['count'];
            }
        }

        $languages = (clone $query)
            ->select(['language', 'COUNT(*) as count'])
            ->andWhere(['not', ['language' => null]])
            ->groupBy(['language'])
            ->indexBy('language')
            ->column();

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
            'total' => (int) $total,
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

        $seconds = $this->_periodToSeconds($period);
        if ($seconds > 0) {
            $cutoff = (new \DateTime())->modify("-{$seconds} seconds");
            $query->andWhere(['>=', 'dateCreated', Db::prepareDateForDb($cutoff)]);
        }

        $stats = [];
        foreach ($linkIds as $linkId) {
            $linkQuery = (clone $query)->andWhere(['linkId' => $linkId]);
            $stats[$linkId] = [
                'total' => (int) $linkQuery->count(),
                'devices' => $linkQuery
                    ->select(['deviceType', 'COUNT(*) as count'])
                    ->groupBy(['deviceType'])
                    ->indexBy('deviceType')
                    ->column(),
            ];
        }

        return $stats;
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
        $query = (new Query())
            ->from(['a' => '{{%smartlinkmanager_analytics}}'])
            ->select([
                'a.linkId',
                'a.siteId',
                'COUNT(*) as clicks',
                'MAX(a.dateCreated) as lastClick',
                'SUM(CASE WHEN ' . DbHelper::jsonExtract('a.metadata', 'source') . ' = \'qr\' THEN 1 ELSE 0 END) as qrScans',
                'SUM(CASE WHEN ' . DbHelper::jsonExtract('a.metadata', 'source') . ' != \'qr\' OR ' . DbHelper::jsonExtract('a.metadata', 'source') . ' IS NULL THEN 1 ELSE 0 END) as directVisits',
            ])
            ->groupBy(['a.linkId', 'a.siteId'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);

        $this->applyDateRangeFilter($query, $dateRange, 'a.dateCreated');

        if ($siteId) {
            $query->andWhere(['a.siteId' => $siteId]);
        }

        $results = $query->all();
        $topLinks = [];

        $linkIds = array_unique(array_column($results, 'linkId'));
        $smartLinksMap = [];
        if (!empty($linkIds)) {
            foreach (SmartLink::find()->id($linkIds)->status(null)->all() as $link) {
                $smartLinksMap[$link->id] = $link;
            }
        }

        $lastInteractionsMap = [];
        if (!empty($linkIds)) {
            $maxDatesQuery = (new Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->select(['linkId', 'MAX(dateCreated) as maxDate'])
                ->where(['linkId' => $linkIds])
                ->groupBy(['linkId']);
            $this->applyDateRangeFilter($maxDatesQuery, $dateRange);

            $lastInteractions = (new Query())
                ->from(['a' => '{{%smartlinkmanager_analytics}}'])
                ->innerJoin(
                    ['m' => $maxDatesQuery],
                    '[[a.linkId]] = [[m.linkId]] AND [[a.dateCreated]] = [[m.maxDate]]'
                )
                ->all();

            foreach ($lastInteractions as $interaction) {
                if (!isset($lastInteractionsMap[$interaction['linkId']])) {
                    $lastInteractionsMap[$interaction['linkId']] = $interaction;
                }
            }
        }

        foreach ($results as $row) {
            $smartLink = $smartLinksMap[$row['linkId']] ?? null;

            if ($smartLink && $smartLink->getStatus() === SmartLink::STATUS_ENABLED) {
                $lastInteraction = $lastInteractionsMap[$row['linkId']] ?? null;

                $lastInteractionType = 'Unknown';
                $lastDestinationUrl = '';

                if ($lastInteraction && !empty($lastInteraction['metadata'])) {
                    $metadata = Json::decodeIfJson($lastInteraction['metadata']);

                    if (isset($metadata['action'])) {
                        $lastInteractionType = $metadata['action'] === 'redirect' ? 'Redirect' : 'Button';
                    } elseif (isset($metadata['clickType'])) {
                        $lastInteractionType = $metadata['clickType'] === 'button' ? 'Button' : 'Redirect';
                    } elseif (isset($metadata['redirectUrl'])) {
                        $lastInteractionType = 'Redirect';
                    } elseif (isset($metadata['buttonUrl'])) {
                        $lastInteractionType = 'Button';
                    }

                    if (isset($metadata['buttonUrl'])) {
                        $lastDestinationUrl = $metadata['buttonUrl'];
                    } elseif (isset($metadata['redirectUrl'])) {
                        $lastDestinationUrl = $metadata['redirectUrl'];
                    } elseif (isset($metadata['destinationUrl'])) {
                        $lastDestinationUrl = $metadata['destinationUrl'];
                    }
                }

                $topLinkSite = $smartLink->getSite();

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
                    'clicks' => (int) $row['clicks'],
                    'lastClick' => $lastClick,
                    'lastClickFormatted' => $lastClickFormatted,
                    'lastInteractionType' => $lastInteractionType,
                    'lastDestinationUrl' => $lastDestinationUrl,
                    'qrScans' => (int) $row['qrScans'],
                    'directVisits' => (int) $row['directVisits'],
                ];
            }
        }

        return $topLinks;
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

        $this->applyDateRangeFilter($query, $dateRange);

        $results = $query->all();

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

        $this->applyDateRangeFilter($query, $dateRange, 'a.dateCreated');

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
                $destinationUrl = $metadata['redirectUrl'] ?? $metadata['buttonUrl'] ?? '';
            }

            $site = !empty($row['siteId']) ? Craft::$app->getSites()->getSiteById($row['siteId']) : null;

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
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['country', 'COUNT(*) as clicks'])
            ->where(['not', ['country' => null]])
            ->groupBy(['country'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

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
                'clicks' => (int) $row['clicks'],
                'percentage' => $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0,
            ];
        }

        return $countries;
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
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->select(['city', 'country', 'COUNT(*) as clicks'])
            ->where(['not', ['city' => null]])
            ->groupBy(['city', 'country'])
            ->orderBy(['clicks' => SORT_DESC])
            ->limit($limit);

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

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
                'clicks' => (int) $row['clicks'],
                'percentage' => $totalClicks > 0 ? round(($row['clicks'] / $totalClicks) * 100, 1) : 0,
            ];
        }

        return $cities;
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
        return $this->getTopCountries($smartLinkId, $dateRange, 9999, $siteId);
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
        $query = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $smartLinkId])
            ->andWhere([DbHelper::jsonExtract('metadata', 'clickType') => 'button']);

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $this->applyDateRangeFilter($query, $dateRange);

        $records = $query->all();

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

        arsort($platformCounts);

        return [
            'total' => $totalButtonClicks,
            'byPlatform' => $platformCounts,
        ];
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

        $value = (int) $matches[1];
        $unit = $matches[2];

        return match ($unit) {
            'h' => $value * 3600,
            'd' => $value * 86400,
            'w' => $value * 604800,
            'm' => $value * 2592000,
            'y' => $value * 31536000,
        };
    }
}
