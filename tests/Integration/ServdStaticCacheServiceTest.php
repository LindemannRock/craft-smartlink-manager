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
}
