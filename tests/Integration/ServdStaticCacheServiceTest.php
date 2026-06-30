<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use Craft;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins Servd static-cache purge URL generation for custom-domain smart links.
 *
 * @since 5.34.0
 */
final class ServdStaticCacheServiceTest extends TestCase
{
    public function testPurgeUrlsUseConfiguredBaseUrlAndEnabledSites(): void
    {
        $sites = Craft::$app->getSites()->getAllSites();
        self::assertNotEmpty($sites);

        $enabledSiteIds = array_map(static fn($site): int => (int)$site->id, $sites);
        $expectedUrls = [];

        foreach ($sites as $site) {
            $expectedUrls[] = 'https://smart.example/' . $site->handle . '/go/smartlink-test-servd-cache';
            $expectedUrls[] = 'https://smart.example/' . $site->handle . '/go/qr/smartlink-test-servd-cache/view';
        }

        $this->withSettings([
            'enabledSites' => $enabledSiteIds,
            'smartlinkBaseUrl' => 'https://smart.example/{siteHandle}',
            'usePrefix' => true,
            'slugPrefix' => 'go',
            'qrPrefix' => 'go/qr',
        ], function() use ($expectedUrls): void {
            $urls = SmartLinkManager::$plugin->servdStaticCache->urlsForSlug('smartlink-test-servd-cache');

            sort($expectedUrls);
            sort($urls);

            self::assertSame($expectedUrls, $urls);
        });
    }

    public function testPurgeUrlsRespectRootSmartLinks(): void
    {
        $primarySite = Craft::$app->getSites()->getPrimarySite();

        $this->withSettings([
            'enabledSites' => [$primarySite->id],
            'smartlinkBaseUrl' => 'https://smart.example',
            'usePrefix' => false,
            'slugPrefix' => 'go',
            'qrPrefix' => 'qr',
        ], function(): void {
            self::assertSame([
                'https://smart.example/smartlink-test-servd-root',
                'https://smart.example/qr/smartlink-test-servd-root/view',
            ], SmartLinkManager::$plugin->servdStaticCache->urlsForSlug('smartlink-test-servd-root'));
        });
    }

    public function testPurgeAllSourceUsesAvailabilityCheckAndPagedSlugIteration(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $source = file_get_contents($pluginRoot . '/src/services/ServdStaticCacheService.php');

        self::assertIsString($source);
        self::assertStringContainsString('if (!$this->isAvailable())', $source);
        self::assertStringContainsString('foreach ($this->eachSlug() as $slug)', $source);
        self::assertStringContainsString('->batch(500)', $source);
        self::assertStringContainsString('PURGE_URL_BATCH_SIZE = 500', $source);
        self::assertStringNotContainsString('function allSlugs', $source);
        self::assertStringNotContainsString('->column()', $source);
    }

    public function testUtilityAndControllerSourcesWireDedicatedServdStaticCachePurge(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $utilitySource = file_get_contents($pluginRoot . '/src/utilities/SmartLinkManagerUtility.php');
        $templateSource = file_get_contents($pluginRoot . '/src/templates/utilities/index.twig');
        $controllerSource = file_get_contents($pluginRoot . '/src/controllers/SettingsController.php');

        self::assertIsString($utilitySource);
        self::assertIsString($templateSource);
        self::assertIsString($controllerSource);

        self::assertStringContainsString("'servdStaticCacheAvailable' => SmartLinkManager::\$plugin->servdStaticCache->isAvailable()", $utilitySource);
        self::assertStringContainsString("'linksName' => \$settings->getPluralLowerDisplayName()", $utilitySource);

        self::assertStringContainsString('hasServdStaticCacheManagement = servdStaticCacheAvailable and canClearCache', $templateSource);
        self::assertStringContainsString("actionUrl('smartlink-manager/settings/purge-servd-static-cache')", $templateSource);
        self::assertStringContainsString('Purge Servd static cache for all {linksName}?', $templateSource);

        self::assertStringContainsString('purge-servd-static-cache', $controllerSource);
        self::assertStringContainsString('public function actionPurgeServdStaticCache(): Response', $controllerSource);
        self::assertStringContainsString("\$this->requirePostRequest();", $controllerSource);
        self::assertStringContainsString("\$this->requirePermission('smartLinkManager:clearCache');", $controllerSource);
        self::assertStringContainsString("\$this->requireAcceptsJson();", $controllerSource);
        self::assertStringContainsString('SmartLinkManager::$plugin->servdStaticCache->isAvailable()', $controllerSource);
        self::assertStringContainsString('SmartLinkManager::$plugin->servdStaticCache->purgeAllUrls();', $controllerSource);
        self::assertStringContainsString('Servd static cache purge queued.', $controllerSource);
        self::assertStringContainsString('Servd static cache is not available.', $controllerSource);
        self::assertStringNotContainsString('purgeAllSmartLinks', $controllerSource);
    }

    public function testServdStaticCacheControllerActionDoesNotClearLocalOrGlobalCaches(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $controllerSource = file_get_contents($pluginRoot . '/src/controllers/SettingsController.php');

        self::assertIsString($controllerSource);

        $actionStart = strpos($controllerSource, 'public function actionPurgeServdStaticCache(): Response');
        $nextAction = strpos($controllerSource, 'public function actionClearAllAnalytics(): Response');

        self::assertIsInt($actionStart);
        self::assertIsInt($nextAction);
        self::assertGreaterThan($actionStart, $nextAction);

        $actionSource = substr($controllerSource, $actionStart, $nextAction - $actionStart);

        self::assertStringContainsString('SmartLinkManager::$plugin->servdStaticCache->purgeAllUrls();', $actionSource);
        self::assertStringNotContainsString('localCache->clearAllCaches()', $actionSource);
        self::assertStringNotContainsString('localCache->clearQrCache()', $actionSource);
        self::assertStringNotContainsString('localCache->clearDeviceCache()', $actionSource);
        self::assertStringNotContainsString('Craft::$app->getCache()->flush', $actionSource);
        self::assertStringNotContainsString('Craft::$app->cache->flush', $actionSource);
    }
}
