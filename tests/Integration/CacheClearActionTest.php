<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use Craft;
use craft\cachecascade\CascadeCache;
use craft\console\Request;
use craft\console\User;
use craft\helpers\FileHelper;
use craft\web\Response;
use lindemannrock\base\cache\ScopedCache;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\smartlinkmanager\controllers\SettingsController;
use lindemannrock\smartlinkmanager\services\CacheStorageService;
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use yii\caching\ArrayCache;
use yii\caching\CacheInterface;

require_once dirname(__DIR__) . '/Fixtures/CascadeCache.php';

/**
 * @since 5.38.0
 */
#[CoversClass(SettingsController::class)]
final class CacheClearActionTest extends TestCase
{
    private CacheInterface $originalCache;
    private object $originalRequest;
    private object $originalUser;
    private object $originalResponse;
    private string $originalRuntimePath;
    private bool $hadEphemeralSetting;
    private mixed $originalEphemeralSetting;

    protected function setUp(): void
    {
        parent::setUp();
        $cache = Craft::$app->getCache();
        self::assertInstanceOf(CacheInterface::class, $cache);
        $this->originalCache = $cache;
        $this->originalRequest = Craft::$app->getRequest();
        $this->originalUser = Craft::$app->getUser();
        $this->originalResponse = Craft::$app->getResponse();
        $this->originalRuntimePath = Craft::$app->getRuntimePath();
        $this->hadEphemeralSetting = array_key_exists('CRAFT_EPHEMERAL', $_SERVER);
        $this->originalEphemeralSetting = $_SERVER['CRAFT_EPHEMERAL'] ?? null;

        $_SERVER['CRAFT_EPHEMERAL'] = false;
        Craft::$app->setRuntimePath($this->createTrackedTempDirectory('smartlink-clear-action-'));
        Craft::$app->set('request', new CacheClearRequest());
        Craft::$app->set('user', new CacheClearUser());
        Craft::$app->set('response', new Response());
    }

    protected function tearDown(): void
    {
        Craft::$app->set('cache', $this->originalCache);
        Craft::$app->set('request', $this->originalRequest);
        Craft::$app->set('user', $this->originalUser);
        Craft::$app->set('response', $this->originalResponse);
        Craft::$app->setRuntimePath($this->originalRuntimePath);
        SmartLinkManager::$plugin->set('deviceDetection', DeviceDetectionService::class);
        if ($this->hadEphemeralSetting) {
            $_SERVER['CRAFT_EPHEMERAL'] = $this->originalEphemeralSetting;
        } else {
            unset($_SERVER['CRAFT_EPHEMERAL']);
        }

        parent::tearDown();
    }

    public function testApplicationClearActionsReturnBoundedSuccessAndKeepFamiliesIsolated(): void
    {
        $cache = new CascadeCache();
        Craft::$app->set('cache', $cache);

        $this->withSettings(['cacheStorageMethod' => 'craft'], function() use ($cache): void {
            $qr = new ScopedCache($cache, SmartLinkManager::$plugin->id, CacheStorageService::FAMILY_QR);
            $device = new ScopedCache($cache, SmartLinkManager::$plugin->id, CacheStorageService::FAMILY_DEVICE);
            $sentinel = new ScopedCache($cache, 'unrelated-plugin', 'sentinel');
            self::assertTrue($qr->set('owned', 'qr', 60));
            self::assertTrue($device->set('owned', 'device', 60));
            self::assertTrue($sentinel->set('owned', 'safe', 60));

            $qrResponse = $this->controller()->actionClearQrCache();
            self::assertSame([
                'success' => true,
                'message' => 'QR code cache cleared successfully.',
            ], $qrResponse->data);
            self::assertTrue($qr->get('owned')->isMiss());
            self::assertSame('device', $device->get('owned')->value);
            self::assertSame('safe', $sentinel->get('owned')->value);

            $deviceResponse = $this->controller()->actionClearDeviceCache();
            self::assertSame([
                'success' => true,
                'message' => 'Device cache cleared successfully.',
            ], $deviceResponse->data);
            self::assertTrue($device->get('owned')->isMiss());
            self::assertSame('safe', $sentinel->get('owned')->value);

            self::assertTrue($qr->set('owned-again', 'qr', 60));
            self::assertTrue($device->set('owned-again', 'device', 60));
            $combinedResponse = $this->controller()->actionClearAllCaches();
            self::assertSame([
                'success' => true,
                'message' => 'All caches cleared successfully.',
            ], $combinedResponse->data);
            self::assertTrue($qr->get('owned-again')->isMiss());
            self::assertTrue($device->get('owned-again')->isMiss());
            self::assertSame('safe', $sentinel->get('owned')->value);
        });
    }

    public function testDurableFileClearResponseReportsOwnedQrAndDeviceCounts(): void
    {
        $this->withSettings(['cacheStorageMethod' => 'file'], function(): void {
            self::assertTrue(SmartLinkManager::$plugin->cacheStorage->writeQrCode('owned', 'qr', 60));
            $devicePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, CacheStorageService::FAMILY_DEVICE);
            FileHelper::createDirectory($devicePath);
            file_put_contents($devicePath . 'owned.cache', '{"platform":"ios"}');

            $response = $this->controller()->actionClearAllCaches();
            self::assertSame(true, $response->data['success']);
            self::assertSame('Cleared 1 QR code cache and 1 device cache.', $response->data['message']);
            self::assertSame(0, SmartLinkManager::$plugin->cacheStorage->countFiles(CacheStorageService::FAMILY_QR));
            self::assertSame(0, SmartLinkManager::$plugin->cacheStorage->countFiles(CacheStorageService::FAMILY_DEVICE));
        });
    }

    public function testDisabledClearActionIsSuccessfulNoOpWithoutRuntimePathAccess(): void
    {
        $_SERVER['CRAFT_EPHEMERAL'] = true;
        Craft::$app->set('cache', new ArrayCache());
        $pluginRuntimePath = Craft::$app->getRuntimePath() . '/smartlink-manager';

        $this->withSettings(['cacheStorageMethod' => 'file'], function() use ($pluginRuntimePath): void {
            $response = $this->controller()->actionClearAllCaches();
            self::assertSame([
                'success' => true,
                'message' => 'All caches cleared successfully.',
            ], $response->data);
            self::assertDirectoryDoesNotExist($pluginRuntimePath);
        });
    }

    private function controller(): SettingsController
    {
        return new SettingsController('settings', SmartLinkManager::$plugin);
    }
}

/**
 * @since 5.38.0
 */
final class CacheClearRequest extends Request
{
    public function getIsPost(): bool
    {
        return true;
    }

    public function getAcceptsJson(): bool
    {
        return true;
    }
}

/**
 * @since 5.38.0
 */
final class CacheClearUser extends User
{
    public function checkPermission(string $permissionName): bool
    {
        return $permissionName === 'smartLinkManager:clearCache';
    }

    public function getId(): ?int
    {
        return 1;
    }

    public function getIsGuest(): bool
    {
        return false;
    }
}
