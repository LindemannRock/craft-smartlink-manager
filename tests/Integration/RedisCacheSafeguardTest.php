<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.29.0
 */
#[CoversClass(SmartLinkManager::class)]
#[CoversClass(PluginHelper::class)]
class RedisCacheSafeguardTest extends TestCase
{
    public function testRuntimeSourceUsesRedisSafeguardHelper(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $sourceFiles = [
            $pluginRoot . '/src/SmartLinkManager.php',
            $pluginRoot . '/src/controllers/SettingsController.php',
            $pluginRoot . '/src/services/QrCodeService.php',
        ];

        foreach ($sourceFiles as $sourceFile) {
            $source = file_get_contents($sourceFile);
            $this->assertIsString($source);
            $this->assertStringNotContainsString('instanceof \yii\redis\Cache', $source);
            $this->assertStringContainsString('PluginHelper::getRedisCacheOrLog', $source);
        }
    }
}
