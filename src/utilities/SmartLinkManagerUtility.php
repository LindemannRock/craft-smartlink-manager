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
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\DbHelper;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * SmartLink Manager Utility
 *
 * @since 1.0.0
 */
class SmartLinkManagerUtility extends Utility
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
        $settings = $smartLinks->getSettings();
        $pluginName = $settings->getFullName();
        $singularName = $settings->getDisplayName();
        $user = Craft::$app->getUser();

        // Get link stats only if user can view links
        $totalLinks = 0;
        $activeLinks = 0;
        $pendingLinks = 0;
        $expiredLinks = 0;
        $disabledLinks = 0;

        if ($user->getIdentity() && $user->checkPermission('smartLinkManager:viewLinks')) {
            $allowedSiteIds = array_map(fn($s) => $s->id, SmartLinkManager::$plugin->getEnabledSites());

            $totalLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->siteId($allowedSiteIds)->status(null)->count();
            $activeLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->siteId($allowedSiteIds)->status('enabled')->count();
            $pendingLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->siteId($allowedSiteIds)->status('pending')->count();
            $expiredLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->siteId($allowedSiteIds)->status('expired')->count();
            $disabledLinks = \lindemannrock\smartlinkmanager\elements\SmartLink::find()->siteId($allowedSiteIds)->status('disabled')->count();
        }

        // Get analytics data only if user can view analytics
        $totalClicks = 0;
        $qrScans = 0;
        $autoRedirects = 0;
        $buttonClicks = 0;
        $platformStats = [];
        $clickTypes = [];
        $dailyClicks = [];
        $recentAnalytics = [];

        if ($settings->enableAnalytics && $user->getIdentity() && $user->checkPermission('smartLinkManager:viewAnalytics')) {
            $allowedSiteIds = array_map(fn($s) => $s->id, SmartLinkManager::$plugin->getEnabledSites());
            $siteCondition = ['siteId' => $allowedSiteIds];

            $totalClicks = (int) (new Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->count();

            $recentAnalytics = $smartLinks->analytics->getAnalyticsSummary('last7days', null, $allowedSiteIds);

            $qrScans = (int) (new Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->andWhere([DbHelper::jsonExtract('metadata', 'source') => 'qr'])
                ->count();

            $autoRedirects = (int) (new Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->andWhere([DbHelper::jsonExtract('metadata', 'clickType') => 'redirect'])
                ->count();

            $buttonClicks = (int) (new Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->andWhere([DbHelper::jsonExtract('metadata', 'clickType') => 'button'])
                ->count();

            $platformStats = (new Query())
                ->select([DbHelper::jsonExtract('metadata', 'platform') . ' as platform', 'COUNT(*) as count'])
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->groupBy('platform')
                ->orderBy(['count' => SORT_DESC])
                ->all();

            $clickTypes = (new Query())
                ->select([DbHelper::jsonExtract('metadata', 'clickType') . ' as clickType', 'COUNT(*) as count'])
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->groupBy('clickType')
                ->all();

            $localDate = DateFormatHelper::localDateExpression('dateCreated');

            $dailyClicks = (new Query())
                ->select([
                    'date' => $localDate,
                    'COUNT(*) as clicks',
                ])
                ->from('{{%smartlinkmanager_analytics}}')
                ->where($siteCondition)
                ->andWhere(['>=', 'dateCreated', (new \DateTime('-14 days'))->format('Y-m-d H:i:s')])
                ->groupBy($localDate)
                ->orderBy(['date' => SORT_ASC])
                ->all();
        }

        // Get cache counts only if user can clear cache
        $qrCacheFiles = 0;
        $deviceCacheFiles = 0;

        if ($user->getIdentity() && $user->checkPermission('smartLinkManager:clearCache') && $settings->cacheStorageMethod === 'file') {
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
