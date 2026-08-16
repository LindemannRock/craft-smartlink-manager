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
use craft\base\Component;
use craft\cachecascade\CascadeCache;
use craft\helpers\FileHelper;
use lindemannrock\base\cache\ScopedCache;
use lindemannrock\base\device\DeviceDetection;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\services\CacheStorageService;
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;
use lindemannrock\smartlinkmanager\services\LocalCacheService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use yii\caching\CacheInterface;
use yii\web\HeaderCollection;

require_once dirname(__DIR__) . '/Fixtures/CascadeCache.php';

/**
 * Pins local cache-clearing behavior and implementation boundaries.
 *
 * @since 5.34.0
 */
#[CoversClass(LocalCacheService::class)]
final class LocalCacheServiceTest extends TestCase
{
    private string $originalRuntimePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalRuntimePath = Craft::$app->getRuntimePath();
        Craft::$app->setRuntimePath($this->createTrackedTempDirectory('smartlink-local-cache-'));
    }

    protected function tearDown(): void
    {
        Craft::$app->setRuntimePath($this->originalRuntimePath);
        parent::tearDown();
    }

    public function testFileCacheClearingDeletesOnlyCacheFiles(): void
    {
        $cachePath = PluginHelper::getCachePath(SmartLinkManager::$plugin, 'qr');
        FileHelper::createDirectory($cachePath);

        $cacheFile = $cachePath . 'local-cache-service-test.cache';
        $nestedCacheFile = $cachePath . 'local-cache-service-test-nested.cache';
        $nonCacheFile = $cachePath . 'local-cache-service-test.txt';

        file_put_contents($cacheFile, 'cache');
        file_put_contents($nestedCacheFile, 'cache');
        file_put_contents($nonCacheFile, 'keep');

        try {
            $this->withSettings([
                'cacheStorageMethod' => 'file',
            ], function() use ($cacheFile, $nestedCacheFile, $nonCacheFile): void {
                $cleared = SmartLinkManager::$plugin->localCache->clearQrCache();

                self::assertGreaterThanOrEqual(2, $cleared);
                self::assertFileDoesNotExist($cacheFile);
                self::assertFileDoesNotExist($nestedCacheFile);
                self::assertFileExists($nonCacheFile);
            });
        } finally {
            @unlink($cacheFile);
            @unlink($nestedCacheFile);
            @unlink($nonCacheFile);
        }
    }

    public function testApplicationClearUsesGenerationFamiliesWithoutRedisEnumeration(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $localCacheSource = file_get_contents($pluginRoot . '/src/services/LocalCacheService.php');
        self::assertIsString($localCacheSource);
        self::assertStringContainsString('cacheStorage->clearFamily', $localCacheSource);
        self::assertStringContainsString('deviceDetection->clearCache', $localCacheSource);

        foreach ([
            $pluginRoot . '/src/SmartLinkManager.php',
            $pluginRoot . '/src/controllers/SettingsController.php',
            $pluginRoot . '/src/services/CacheStorageService.php',
            $pluginRoot . '/src/services/DeviceDetectionService.php',
            $pluginRoot . '/src/services/LocalCacheService.php',
            $pluginRoot . '/src/services/QrCodeService.php',
            $pluginRoot . '/src/utilities/SmartLinkManagerUtility.php',
        ] as $sourceFile) {
            $source = file_get_contents($sourceFile);
            self::assertIsString($source);
            foreach (['SADD', 'SMEMBERS', 'SREM', 'SSCAN', 'KEYS', 'SCAN', 'flush(', 'glob('] as $forbiddenOperation) {
                self::assertStringNotContainsString($forbiddenOperation, $source);
            }
        }
    }

    public function testDeviceClearInvalidatesOnlyDeviceFamilyAndResetsRequestLocalDetector(): void
    {
        $originalCache = Craft::$app->getCache();
        $originalRequest = Craft::$app->getRequest();
        self::assertInstanceOf(CacheInterface::class, $originalCache);
        $cache = new CascadeCache();
        Craft::$app->set('cache', $cache);
        Craft::$app->set('request', new CacheDeviceRequest());

        try {
            $this->withSettings([
                'cacheStorageMethod' => 'craft',
                'cacheDeviceDetection' => true,
                'deviceDetectionCacheDuration' => 79,
            ], function() use ($cache): void {
                $service = new DeviceDetectionService();
                SmartLinkManager::$plugin->set('deviceDetection', $service);
                $userAgent = 'SmartlinkCache/1.0';
                self::assertInstanceOf(DeviceInfo::class, $service->detectDevice($userAgent));

                $detector = new \ReflectionProperty(DeviceDetectionService::class, 'deviceDetection');
                self::assertInstanceOf(DeviceDetection::class, $detector->getValue($service));

                $device = new ScopedCache($cache, SmartLinkManager::$plugin->id, CacheStorageService::FAMILY_DEVICE);
                $qr = new ScopedCache($cache, SmartLinkManager::$plugin->id, CacheStorageService::FAMILY_QR);
                $sentinel = new ScopedCache($cache, 'unrelated-plugin', 'sentinel');
                $deviceIdentity = [
                    'legacyPrefix' => PluginHelper::getCacheKeyPrefix(SmartLinkManager::$plugin->id, 'device'),
                    'device' => $userAgent,
                ];
                self::assertTrue($device->set($deviceIdentity, ['platform' => 'ios', 'language' => 'en'], 60));
                self::assertTrue($qr->set('owned', 'qr', 60));
                self::assertTrue($sentinel->set('owned', 'safe', 60));
                self::assertSame('ios', (new DeviceDetectionService())->detectDevice($userAgent)->platform);
                self::assertContains(79, $cache->setDurations);

                self::assertSame(0, SmartLinkManager::$plugin->localCache->clearDeviceCache());
                self::assertNull($detector->getValue($service));
                self::assertTrue($device->get($deviceIdentity)->isMiss());
                self::assertSame('qr', $qr->get('owned')->value);
                self::assertSame('safe', $sentinel->get('owned')->value);
            });
        } finally {
            Craft::$app->set('cache', $originalCache);
            Craft::$app->set('request', $originalRequest);
            SmartLinkManager::$plugin->set('deviceDetection', DeviceDetectionService::class);
        }
    }

    public function testEphemeralUnsuitableCacheDisablesDeviceCachingWithoutResolvingAFilePath(): void
    {
        $originalCache = Craft::$app->getCache();
        self::assertInstanceOf(CacheInterface::class, $originalCache);
        $originalEphemeral = $_SERVER['CRAFT_EPHEMERAL'] ?? null;
        $hadEphemeral = array_key_exists('CRAFT_EPHEMERAL', $_SERVER);
        Craft::$app->set('cache', new \yii\caching\ArrayCache());
        $_SERVER['CRAFT_EPHEMERAL'] = true;

        try {
            $this->withSettings([
                'cacheStorageMethod' => 'file',
                'cacheDeviceDetection' => true,
            ], function(): void {
                $service = new DeviceDetectionService();
                $config = (new \ReflectionMethod($service, 'getDeviceDetectionConfig'))->invoke($service);
                self::assertIsArray($config);
                self::assertFalse($config['cacheEnabled']);
                self::assertSame('file', $config['cacheStorageMethod']);
                self::assertNull($config['cachePath']);
                self::assertDirectoryDoesNotExist(Craft::$app->getRuntimePath() . '/smartlink-manager');
            });
        } finally {
            Craft::$app->set('cache', $originalCache);
            if ($hadEphemeral) {
                $_SERVER['CRAFT_EPHEMERAL'] = $originalEphemeral;
            } else {
                unset($_SERVER['CRAFT_EPHEMERAL']);
            }
        }
    }

    public function testClearAllInvalidatesQrAndDeviceFamiliesWithoutChangingUnrelatedSentinel(): void
    {
        $originalCache = Craft::$app->getCache();
        self::assertInstanceOf(CacheInterface::class, $originalCache);
        $cache = new CascadeCache();
        Craft::$app->set('cache', $cache);

        try {
            $this->withSettings(['cacheStorageMethod' => 'craft'], function() use ($cache): void {
                SmartLinkManager::$plugin->set('deviceDetection', new DeviceDetectionService());
                $qr = new ScopedCache($cache, SmartLinkManager::$plugin->id, CacheStorageService::FAMILY_QR);
                $device = new ScopedCache($cache, SmartLinkManager::$plugin->id, CacheStorageService::FAMILY_DEVICE);
                $sentinel = new ScopedCache($cache, 'unrelated-plugin', 'sentinel');
                self::assertTrue($qr->set('owned', 'qr', 60));
                self::assertTrue($device->set('owned', 'device', 60));
                self::assertTrue($sentinel->set('owned', 'safe', 60));

                $decision = SmartLinkManager::$plugin->cacheStorage->getStorageDecision();
                self::assertSame(0, SmartLinkManager::$plugin->localCache->clearAllCaches($decision));
                self::assertTrue($qr->get('owned')->isMiss());
                self::assertTrue($device->get('owned')->isMiss());
                self::assertSame('safe', $sentinel->get('owned')->value);
            });
        } finally {
            Craft::$app->set('cache', $originalCache);
            SmartLinkManager::$plugin->set('deviceDetection', DeviceDetectionService::class);
        }
    }

    public function testDurableDeviceFileCachePreservesBaseIdentityJsonTtlAndClearBehavior(): void
    {
        $originalRequest = Craft::$app->getRequest();
        $hadEphemeral = array_key_exists('CRAFT_EPHEMERAL', $_SERVER);
        $originalEphemeral = $_SERVER['CRAFT_EPHEMERAL'] ?? null;
        $_SERVER['CRAFT_EPHEMERAL'] = false;
        Craft::$app->set('request', new CacheDeviceRequest());

        try {
            $this->withSettings([
                'cacheStorageMethod' => 'file',
                'cacheDeviceDetection' => true,
                'deviceDetectionCacheDuration' => 60,
            ], function(): void {
                $userAgent = 'SmartlinkDurableDevice/1.0';
                $path = PluginHelper::getCachePath(SmartLinkManager::$plugin, CacheStorageService::FAMILY_DEVICE);
                $file = $path . md5($userAgent) . '.cache';
                $detected = (new DeviceDetectionService())->detectDevice($userAgent);

                self::assertFileExists($file);
                $stored = json_decode((string) file_get_contents($file), true);
                self::assertIsArray($stored);
                self::assertSame($detected->platform, $stored['platform']);
                self::assertSame($detected->language, $stored['language']);
                self::assertSame(1, SmartLinkManager::$plugin->cacheStorage->countFiles(CacheStorageService::FAMILY_DEVICE));
                touch($file, time() - 61);
                $refreshed = (new DeviceDetectionService())->detectDevice($userAgent);
                self::assertSame($detected->platform, $refreshed->platform);
                self::assertGreaterThan(time() - 10, filemtime($file));
                self::assertSame(1, SmartLinkManager::$plugin->localCache->clearDeviceCache());
                self::assertFileDoesNotExist($file);
            });
        } finally {
            Craft::$app->set('request', $originalRequest);
            if ($hadEphemeral) {
                $_SERVER['CRAFT_EPHEMERAL'] = $originalEphemeral;
            } else {
                unset($_SERVER['CRAFT_EPHEMERAL']);
            }
        }
    }

    public function testDeviceCacheIdentityIncludesClientHintsAndPreservesLanguagePlatformAndGeoCallback(): void
    {
        $originalRequest = Craft::$app->getRequest();

        try {
            $this->swapPluginComponent('smartlink-manager', 'analytics', new CacheGeoAnalyticsStub());
            $this->withSettings([
                'cacheStorageMethod' => 'file',
                'cacheDeviceDetection' => true,
                'deviceDetectionCacheDuration' => 60,
                'enableGeoDetection' => true,
            ], function(): void {
                $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148';
                Craft::$app->set('request', new CacheDeviceRequest([
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Sec-CH-UA-Platform' => '"iOS"',
                    'Sec-CH-UA-Model' => '"iPhone"',
                ]));
                $first = (new DeviceDetectionService())->detectDevice($userAgent);
                self::assertSame('ios', $first->platform);
                self::assertSame('en', $first->language);

                Craft::$app->set('request', new CacheDeviceRequest([
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Sec-CH-UA-Platform' => '"iOS"',
                    'Sec-CH-UA-Model' => '"iPad"',
                ]));
                (new DeviceDetectionService())->detectDevice($userAgent);
                self::assertSame(2, SmartLinkManager::$plugin->cacheStorage->countFiles(CacheStorageService::FAMILY_DEVICE));

                $config = (new \ReflectionMethod(DeviceDetectionService::class, 'getDeviceDetectionConfig'))
                    ->invoke(new DeviceDetectionService());
                self::assertIsArray($config);
                self::assertTrue($config['includeLanguage']);
                self::assertTrue($config['includePlatform']);
                self::assertTrue($config['enableGeoDetection']);
                self::assertIsCallable($config['geoLookupCallback']);
                self::assertSame(['countryCode' => 'EG'], $config['geoLookupCallback']('203.0.113.77'));
            });
        } finally {
            Craft::$app->set('request', $originalRequest);
        }
    }

    public function testDeviceRedirectOutcomesRemainPlatformSpecific(): void
    {
        $originalRequest = Craft::$app->getRequest();
        Craft::$app->set('request', new CacheDeviceRequest());
        $link = new SmartLink();
        $link->iosUrl = 'https://example.com/ios';
        $link->androidUrl = 'https://example.com/android';
        $link->huaweiUrl = 'https://example.com/huawei';
        $link->windowsUrl = 'https://example.com/windows';
        $link->macUrl = 'https://example.com/mac';
        $link->fallbackUrl = 'https://example.com/fallback';
        $service = new DeviceDetectionService();

        try {
            foreach ([
                'ios' => $link->iosUrl,
                'android' => $link->androidUrl,
                'huawei' => $link->huaweiUrl,
                'windows' => $link->windowsUrl,
                'macos' => $link->macUrl,
                'other' => '',
            ] as $platform => $expected) {
                self::assertSame($expected, $service->getRedirectUrl($link, new DeviceInfo(['platform' => $platform])));
            }
        } finally {
            Craft::$app->set('request', $originalRequest);
        }
    }
}

final class CacheDeviceRequest extends \craft\console\Request
{
    private HeaderCollection $testHeaders;

    /** @param array<string, string> $headers */
    public function __construct(array $headers = [])
    {
        parent::__construct();
        $this->testHeaders = new HeaderCollection();
        foreach ($headers as $name => $value) {
            $this->testHeaders->set($name, $value);
        }
    }

    public function getQueryParam($name, $defaultValue = null): mixed
    {
        return $defaultValue;
    }

    public function getUserAgent(): ?string
    {
        return $this->testHeaders->get('User-Agent');
    }

    public function getHeaders(): HeaderCollection
    {
        return $this->testHeaders;
    }
}

final class CacheGeoAnalyticsStub extends Component
{
    /** @return array{countryCode: string} */
    public function getLocationFromIp(string $ip): array
    {
        return ['countryCode' => $ip === '203.0.113.77' ? 'EG' : 'US'];
    }
}
