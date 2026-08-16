<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\services\CacheStorageService;
use lindemannrock\smartlinkmanager\services\LocalCacheService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.29.0
 */
#[CoversClass(SmartLinkManager::class)]
#[CoversClass(LocalCacheService::class)]
#[CoversClass(CacheStorageService::class)]
class RedisCacheSafeguardTest extends TestCase
{
    public function testRuntimeSourceUsesBackendNeutralScopedCacheWithoutRawRedisOperations(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $sourceFiles = [
            $pluginRoot . '/src/SmartLinkManager.php',
            $pluginRoot . '/src/services/LocalCacheService.php',
            $pluginRoot . '/src/services/QrCodeService.php',
        ];

        foreach ($sourceFiles as $sourceFile) {
            $source = file_get_contents($sourceFile);
            $this->assertIsString($source);
            $this->assertStringNotContainsString('instanceof \yii\redis\Cache', $source);
        }

        foreach ($sourceFiles as $sourceFile) {
            $source = file_get_contents($sourceFile);
            $this->assertIsString($source);
            foreach (['getRedisCacheOrLog', 'clearTrackedRedisKeys', 'SADD', 'SMEMBERS', 'SREM', 'SSCAN'] as $legacyOperation) {
                $this->assertStringNotContainsString($legacyOperation, $source);
            }
        }

        $cacheStorage = file_get_contents($pluginRoot . '/src/services/CacheStorageService.php');
        $this->assertIsString($cacheStorage);
        $this->assertStringContainsString('new DisposableCacheStorageResolver()', $cacheStorage);
        $this->assertStringContainsString('new ScopedCache(', $cacheStorage);
        $this->assertStringContainsString('$decision->applicationCache', $cacheStorage);
    }
}
