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
use craft\helpers\Json;
use lindemannrock\base\helpers\GeoHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Analytics Export Service
 *
 * Export data formatting and analytics deletion.
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     5.22.0
 */
class AnalyticsExportService
{
    use AnalyticsQueryTrait;

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

        $this->applyDateRangeFilter($query, $dateRange);

        if ($smartLinkId) {
            $query->andWhere(['linkId' => $smartLinkId]);
        }

        if ($siteId) {
            $query->andWhere(['siteId' => $siteId]);
        }

        $results = $query->all();

        $settings = SmartLinkManager::$plugin->getSettings();
        $geoEnabled = $settings->enableGeoDetection ?? true;
        $slugPrefix = $settings->slugPrefix ?? 'go';

        $linkIds = array_unique(array_column($results, 'linkId'));
        $smartLinks = [];
        if (!empty($linkIds)) {
            foreach (SmartLink::find()->id($linkIds)->status(null)->all() as $link) {
                $smartLinks[$link->id] = $link;
            }
        }

        $exportData = [];
        foreach ($results as $row) {
            $smartLink = $smartLinks[$row['linkId']] ?? null;

            if (!$smartLink) {
                continue;
            }

            $status = $smartLink->getStatus();
            $statusLabel = match ($status) {
                SmartLink::STATUS_ENABLED => 'Active',
                SmartLink::STATUS_DISABLED => 'Disabled',
                SmartLink::STATUS_PENDING => 'Pending',
                SmartLink::STATUS_EXPIRED => 'Expired',
                default => 'Unknown'
            };

            $siteName = '';
            $smartLinkUrl = '';
            if (!empty($row['siteId'])) {
                $site = Craft::$app->getSites()->getSiteById($row['siteId']);
                $siteName = $site ? $site->name : '';
                $smartLinkUrl = $settings->buildPublicUrl("{$slugPrefix}/{$smartLink->slug}", (int) $row['siteId']);
            }

            $metadata = !empty($row['metadata']) ? Json::decode($row['metadata']) : [];
            $sourceValue = $metadata['source'] ?? 'direct';
            $clickTypeValue = $metadata['clickType'] ?? 'redirect';

            $source = match ($sourceValue) {
                'qr' => 'QR',
                'landing' => 'Landing',
                default => 'Direct'
            };

            $clickType = match ($clickTypeValue) {
                'button' => 'Button',
                default => 'Redirect'
            };

            $platformLabel = '';
            $destinationUrl = '';
            if ($clickTypeValue === 'button') {
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

            if ($geoEnabled) {
                $record['country'] = GeoHelper::getCountryName($row['country'] ?? '');
                $record['city'] = $row['city'] ?? '';
            }

            $exportData[] = $record;
        }

        return $exportData;
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
}
