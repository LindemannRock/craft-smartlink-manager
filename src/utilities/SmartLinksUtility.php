<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\utilities;

use Craft;
use craft\base\Utility;
use craft\db\Query;
use lindemannrock\base\helpers\DbHelper;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * SmartLink Manager Utility
 *
 * @since 1.0.0
 */
class SmartLinksUtility extends Utility
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getFullName();
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'smartlink-manager';
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return 'link';
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        $smartLinks = SmartLinkManager::$plugin;

        // Get basic stats (include all statuses)
        $totalLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->status(null)->count();
        $activeLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->status('enabled')->count();
        $pendingLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->status('pending')->count();
        $expiredLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->status('expired')->count();
        $disabledLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->status('disabled')->count();

        // Get click stats from analytics table
        $totalClicks = (int) (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->count();

        // Get recent analytics (last 7 days)
        $recentAnalytics = $smartLinks->analytics->getAnalyticsSummary('last7days');

        // Get QR code stats
        $qrScans = (int) (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where([DbHelper::jsonExtract('metadata', 'source') => 'qr'])
            ->count();

        // Get auto redirects (clickType = redirect)
        $autoRedirects = (int) (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where([DbHelper::jsonExtract('metadata', 'clickType') => 'redirect'])
            ->count();

        // Get button clicks (clickType = button)
        $buttonClicks = (int) (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where([DbHelper::jsonExtract('metadata', 'clickType') => 'button'])
            ->count();

        // Get platform breakdown from JSON metadata
        $platformStats = (new Query())
            ->select([DbHelper::jsonExtract('metadata', 'platform') . ' as platform', 'COUNT(*) as count'])
            ->from('{{%smartlinkmanager_analytics}}')
            ->groupBy('platform')
            ->orderBy(['count' => SORT_DESC])
            ->all();

        // Get click type breakdown from JSON metadata
        $clickTypes = (new Query())
            ->select([DbHelper::jsonExtract('metadata', 'clickType') . ' as clickType', 'COUNT(*) as count'])
            ->from('{{%smartlinkmanager_analytics}}')
            ->groupBy('clickType')
            ->all();

        // Get daily clicks for last 14 days
        $dailyClicks = (new Query())
            ->select([
                'DATE(dateCreated) as date',
                'COUNT(*) as clicks',
            ])
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['>=', 'dateCreated', (new \DateTime('-14 days'))->format('Y-m-d H:i:s')])
            ->groupBy('DATE(dateCreated)')
            ->orderBy(['date' => SORT_ASC])
            ->all();

        // Cache stats
        $settings = $smartLinks->getSettings();
        $pluginName = $settings->getFullName();
        $singularName = $settings->getDisplayName();

        // Get cache counts (only for file storage)
        $qrCacheFiles = 0;
        $deviceCacheFiles = 0;

        // Only count files when using file storage (Redis counts are not displayed)
        if ($settings->cacheStorageMethod === 'file') {
            $qrCachePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'qr');
            $deviceCachePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'device');

            $qrCacheFiles = is_dir($qrCachePath) ? count(glob($qrCachePath . '*.cache')) : 0;
            $deviceCacheFiles = is_dir($deviceCachePath) ? count(glob($deviceCachePath . '*.cache')) : 0;
        }

        return Craft::$app->getView()->renderTemplate('smartlink-manager/utilities/index', [
            'totalLinks' => $totalLinks,
            'activeLinks' => $activeLinks,
            'pendingLinks' => $pendingLinks,
            'expiredLinks' => $expiredLinks,
            'disabledLinks' => $disabledLinks,
            'totalClicks' => $totalClicks,
            'qrScans' => $qrScans,
            'autoRedirects' => $autoRedirects,
            'buttonClicks' => $buttonClicks,
            'platformStats' => $platformStats,
            'clickTypes' => $clickTypes,
            'dailyClicks' => $dailyClicks,
            'recentAnalytics' => $recentAnalytics,
            'qrCacheFiles' => $qrCacheFiles,
            'deviceCacheFiles' => $deviceCacheFiles,
            'settings' => $settings,
            'pluginName' => $pluginName,
            'singularName' => $singularName,
        ]);
    }
}
