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
use craft\models\Site;
use lindemannrock\base\helpers\CacheHelper;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\DbHelper;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * SmartLink Manager Utility
 *
 * @since 5.21.0
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
        return '@lindemannrock/smartlinkmanager/icon-mask.svg';
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
        $siteSelection = self::siteSelection();
        $selectedSiteIds = $siteSelection['siteIds'];

        // Get link stats only if user can view links
        $totalLinks = 0;
        $activeLinks = 0;
        $pendingLinks = 0;
        $expiredLinks = 0;
        $disabledLinks = 0;

        if ($user->getIdentity() && $user->checkPermission('smartLinkManager:manageLinks')) {
            $linkStats = self::linkStatusCounts($selectedSiteIds);
            $totalLinks = $linkStats['totalLinks'];
            $activeLinks = $linkStats['activeLinks'];
            $pendingLinks = $linkStats['pendingLinks'];
            $expiredLinks = $linkStats['expiredLinks'];
            $disabledLinks = $linkStats['disabledLinks'];
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
            $analyticsStats = self::analyticsStats($selectedSiteIds);
            $totalClicks = $analyticsStats['totalClicks'];
            $qrScans = $analyticsStats['qrScans'];
            $autoRedirects = $analyticsStats['autoRedirects'];
            $buttonClicks = $analyticsStats['buttonClicks'];
            $platformStats = $analyticsStats['platformStats'];
            $clickTypes = $analyticsStats['clickTypes'];
            $dailyClicks = $analyticsStats['dailyClicks'];
            $recentAnalytics = $analyticsStats['recentAnalytics'];
        }

        // Get cache counts only if user can clear cache
        $qrCacheFiles = 0;
        $deviceCacheFiles = 0;

        if ($user->getIdentity() && $user->checkPermission('smartLinkManager:clearCache') && $settings->cacheStorageMethod === 'file') {
            $qrCacheFiles = CacheHelper::countCacheFiles(PluginHelper::getCachePath(SmartLinkManager::$plugin, 'qr'));
            $deviceCacheFiles = CacheHelper::countCacheFiles(PluginHelper::getCachePath(SmartLinkManager::$plugin, 'device'));
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
            'linksName' => $settings->getPluralLowerDisplayName(),
            'servdStaticCacheAvailable' => SmartLinkManager::$plugin->servdStaticCache->isAvailable(),
            'selectedSiteHandle' => $siteSelection['selectedSiteHandle'],
            'selectedSiteLabel' => $siteSelection['selectedSiteLabel'],
            'siteOptions' => $siteSelection['siteOptions'],
        ]);
    }

    /**
     * @param list<int> $siteIds
     * @return array{totalLinks: int, activeLinks: int, pendingLinks: int, expiredLinks: int, disabledLinks: int}
     */
    private static function linkStatusCounts(array $siteIds): array
    {
        return [
            'totalLinks' => (int) SmartLink::find()
                ->siteId($siteIds)
                ->status(null)
                ->count(),
            'activeLinks' => (int) SmartLink::find()
                ->siteId($siteIds)
                ->status('enabled')
                ->count(),
            'pendingLinks' => (int) SmartLink::find()
                ->siteId($siteIds)
                ->status('pending')
                ->count(),
            'expiredLinks' => (int) SmartLink::find()
                ->siteId($siteIds)
                ->status('expired')
                ->count(),
            'disabledLinks' => (int) SmartLink::find()
                ->siteId($siteIds)
                ->status('disabled')
                ->count(),
        ];
    }

    /**
     * @param list<int> $siteIds
     * @return array{
     *     totalClicks: int,
     *     qrScans: int,
     *     autoRedirects: int,
     *     buttonClicks: int,
     *     platformStats: list<array<string, mixed>>,
     *     clickTypes: list<array<string, mixed>>,
     *     dailyClicks: list<array<string, mixed>>,
     *     recentAnalytics: array<string, mixed>
     * }
     */
    private static function analyticsStats(array $siteIds): array
    {
        $siteCondition = ['siteId' => $siteIds];

        $totalClicks = (int) (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where($siteCondition)
            ->count();

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

        return [
            'totalClicks' => $totalClicks,
            'qrScans' => $qrScans,
            'autoRedirects' => $autoRedirects,
            'buttonClicks' => $buttonClicks,
            'platformStats' => $platformStats,
            'clickTypes' => $clickTypes,
            'dailyClicks' => $dailyClicks,
            'recentAnalytics' => SmartLinkManager::$plugin->analytics->getAnalyticsSummary('last7days', null, $siteIds),
        ];
    }

    /**
     * Resolve the utility overview site selector from the `site` query param.
     *
     * Missing, empty, `all`, invalid, and disabled handles all map to the
     * aggregate enabled-site scope.
     *
     * @return array{selectedSiteHandle: string, selectedSiteLabel: string, siteOptions: array<string, string>, siteIds: list<int>}
     */
    private static function siteSelection(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $enabledSiteIds = $settings->getEnabledSiteIds();
        $siteOptions = [
            'all' => Craft::t('lindemannrock-base', 'All Sites'),
        ];
        $sitesByHandle = [];

        foreach ($enabledSiteIds as $siteId) {
            $site = Craft::$app->getSites()->getSiteById((int) $siteId);
            if (!$site instanceof Site) {
                continue;
            }

            $siteOptions[$site->handle] = $site->name ?: $site->handle;
            $sitesByHandle[$site->handle] = $site;
        }

        $siteIds = array_map(
            static fn(Site $site): int => (int) $site->id,
            array_values($sitesByHandle),
        );

        $selectedSiteHandle = 'all';
        $selectedSiteLabel = $siteOptions['all'];
        $requestedSite = self::requestedSiteHandle();

        if ($requestedSite !== '' && $requestedSite !== 'all' && isset($sitesByHandle[$requestedSite])) {
            $site = $sitesByHandle[$requestedSite];
            $selectedSiteHandle = $site->handle;
            $selectedSiteLabel = $site->name ?: $site->handle;
            $siteIds = [(int) $site->id];
        }

        return [
            'selectedSiteHandle' => $selectedSiteHandle,
            'selectedSiteLabel' => $selectedSiteLabel,
            'siteOptions' => $siteOptions,
            'siteIds' => $siteIds,
        ];
    }

    private static function requestedSiteHandle(): string
    {
        $request = Craft::$app->getRequest();
        $site = method_exists($request, 'getQueryParam')
            ? $request->getQueryParam('site', 'all')
            : $request->getParam('site', 'all');

        return trim((string) $site);
    }
}
