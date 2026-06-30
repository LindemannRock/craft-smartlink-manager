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
    private const SERVD_ENV_VARS = [
        'SERVD_CACHE_ENABLED',
        'REDIS_STATIC_CACHE_DB',
        'REDIS_HOST',
        'REDIS_PORT',
        'ENVIRONMENT',
        'SERVD_PROJECT_SLUG',
    ];

    public function testIsAvailableReturnsFalseWithoutServdRuntimeEnvironment(): void
    {
        $this->withoutServdRuntimeEnvironment(function(): void {
            self::assertFalse(SmartLinkManager::$plugin->servdStaticCache->isAvailable());
        });
    }

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

    public function testIsAvailableSourceMatchesServdRuntimeGuard(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $source = file_get_contents($pluginRoot . '/src/services/ServdStaticCacheService.php');

        self::assertIsString($source);
        self::assertStringContainsString('use craft\helpers\App;', $source);
        self::assertStringContainsString("PluginHelper::isPluginEnabled(self::SERVD_PLUGIN_HANDLE)", $source);
        self::assertStringContainsString('class_exists(self::PURGE_URLS_JOB)', $source);
        self::assertStringContainsString('class_exists(self::STATIC_CACHE)', $source);
        self::assertStringContainsString("extension_loaded('redis')", $source);

        foreach (self::SERVD_ENV_VARS as $name) {
            self::assertStringContainsString("'$name'", $source);
        }

        self::assertStringContainsString('App::env($name)', $source);
        self::assertStringNotContainsString('getenv(', $source);
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
        self::assertStringNotContainsString('servdStaticCache->isAvailable()', $templateSource);
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

    private function withoutServdRuntimeEnvironment(callable $callback): void
    {
        $previousServer = [];
        $previousEnv = [];

        foreach (self::SERVD_ENV_VARS as $name) {
            $previousServer[$name] = array_key_exists($name, $_SERVER) ? $_SERVER[$name] : null;
            $previousEnv[$name] = getenv($name);
            unset($_SERVER[$name]);
            putenv($name);
        }

        try {
            $callback();
        } finally {
            foreach (self::SERVD_ENV_VARS as $name) {
                if ($previousServer[$name] === null) {
                    unset($_SERVER[$name]);
                } else {
                    $_SERVER[$name] = $previousServer[$name];
                }

                if ($previousEnv[$name] === false) {
                    putenv($name);
                } else {
                    putenv($name . '=' . $previousEnv[$name]);
                }
            }
        }
    }
}
