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
use craft\helpers\Json;
use GraphQL\Type\Definition\ResolveInfo;
use lindemannrock\base\testing\StubConsoleRequest;
use lindemannrock\base\testing\StubWebRequest;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\gql\queries\SmartLinkQuery;
use lindemannrock\smartlinkmanager\gql\resolvers\SmartLinkResolver;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\Stubs\StubDeviceDetectionService;
use lindemannrock\smartlinkmanager\tests\TestCase;
use yii\base\Request as YiiRequest;

/**
 * Covers SmartLink Manager's GraphQL resolver contract.
 *
 * @since 5.30.0
 */
final class GraphqlSmartLinkTest extends TestCase
{
    private const TEST_SALT = '0123456789abcdef0123456789abcdef';

    private ?YiiRequest $savedRequest = null;

    private ?string $savedSalt = null;

    private bool $savedEnableAnalytics = true;

    private bool $savedEnableGeo = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->savedRequest = Craft::$app->getRequest();
        Craft::$app->set('request', new StubConsoleRequest(userIp: '203.0.113.42'));

        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());

        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $this->savedSalt = $settings->ipHashSalt;
        $this->savedEnableAnalytics = $settings->enableAnalytics;
        $this->savedEnableGeo = $settings->enableGeoDetection;

        $settings->ipHashSalt = self::TEST_SALT;
        $settings->enableAnalytics = true;
        $settings->enableGeoDetection = false;
    }

    protected function tearDown(): void
    {
        if ($this->savedRequest !== null) {
            Craft::$app->set('request', $this->savedRequest);
        }

        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->ipHashSalt = $this->savedSalt;
        $settings->enableAnalytics = $this->savedEnableAnalytics;
        $settings->enableGeoDetection = $this->savedEnableGeo;

        parent::tearDown();
    }

    public function testQueryDefinitionsExposeResolveAndListQueriesWithoutTokenCheck(): void
    {
        $queries = SmartLinkQuery::getQueries(false);

        self::assertArrayHasKey('smartlinkManagerResolveSmartLink', $queries);
        self::assertArrayHasKey('smartlinkManagerSmartLinks', $queries);
        self::assertArrayHasKey('slug', $queries['smartlinkManagerResolveSmartLink']['args']);
        self::assertArrayHasKey('site', $queries['smartlinkManagerResolveSmartLink']['args']);
        self::assertArrayHasKey('siteId', $queries['smartlinkManagerResolveSmartLink']['args']);
        self::assertArrayHasKey('platform', $queries['smartlinkManagerResolveSmartLink']['args']);
        self::assertArrayHasKey('source', $queries['smartlinkManagerResolveSmartLink']['args']);
    }

    public function testQueryDefinitionsAreSchemaPermissionGated(): void
    {
        self::assertSame([], SmartLinkQuery::getQueries());
    }

    public function testResolveQueryMatchesSmartLinkAndRecordsAnalytics(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink([
            'fallbackUrl' => 'https://example.com/fallback',
            'iosUrl' => 'https://apps.apple.com/example',
            'siteId' => $site->id,
        ]);
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $result = SmartLinkResolver::resolve(
            null,
            [
                'slug' => $link->slug,
                'site' => $site->handle,
            ],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertIsArray($result);
        self::assertSame($link->id, $result['id']);
        self::assertSame('https://apps.apple.com/example', $result['resolvedDestinationUrl']);
        self::assertSame('ios', $result['resolvedPlatform']);
        self::assertSame('redirect', $result['clickType']);
        self::assertSame(1, (int)$result['hits']);
        self::assertSame(1, $this->fetchHitsFromDb((int)$link->id));

        $analytics = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
        self::assertNotNull($analytics, 'GraphQL resolution must record analytics.');
        self::assertSame($site->id, (int)$analytics['siteId']);
        self::assertNotEmpty($analytics['metadata']);
        $metadata = Json::decode($analytics['metadata']);
        self::assertSame('graphql', $metadata['source']);
        self::assertSame('redirect', $metadata['clickType']);
        self::assertSame('ios', $metadata['platform']);
    }

    public function testResolveQuerySupportsExplicitPlatform(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink([
            'fallbackUrl' => 'https://example.com/fallback',
            'androidUrl' => 'https://play.google.com/example',
            'siteId' => $site->id,
        ]);
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $result = SmartLinkResolver::resolve(
            null,
            [
                'slug' => $link->slug,
                'siteId' => $site->id,
                'platform' => 'android',
                'source' => 'spa',
            ],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertIsArray($result);
        self::assertSame('https://play.google.com/example', $result['resolvedDestinationUrl']);
        self::assertSame('android', $result['resolvedPlatform']);
        self::assertSame('button', $result['clickType']);

        $analytics = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
        self::assertNotNull($analytics);
        $metadata = Json::decode($analytics['metadata']);
        self::assertSame('spa', $metadata['source']);
        self::assertSame('button', $metadata['clickType']);
        self::assertSame('android', $metadata['platform']);
    }

    public function testAutoResolveFallsBackWhenDetectedPlatformHasNoUrl(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink([
            'fallbackUrl' => 'https://example.com/fallback',
            'iosUrl' => 'https://apps.apple.com/example',
            'macUrl' => null,
            'siteId' => $site->id,
        ]);
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new GraphqlSmartLinkDesktopDeviceDetectionService());
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $result = SmartLinkResolver::resolve(
            null,
            [
                'slug' => $link->slug,
                'site' => $site->handle,
            ],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertIsArray($result);
        self::assertSame('https://example.com/fallback', $result['resolvedDestinationUrl']);
        self::assertSame('macos', $result['resolvedPlatform']);
        self::assertSame('redirect', $result['clickType']);
        self::assertSame(1, $this->fetchHitsFromDb((int)$link->id));
    }

    public function testListQueryIsReadOnly(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);

        $results = SmartLinkResolver::resolveAll(
            null,
            ['siteId' => $site->id],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertIsArray($results);
        $ids = array_map(static fn(array $row): int => (int)$row['id'], $results);
        self::assertContains($link->id, $ids);
        self::assertSame(0, $this->fetchHitsFromDb((int)$link->id));
        self::assertSame(0, $this->countRows('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]));
    }

    public function testInvalidExplicitSiteDoesNotFallBack(): void
    {
        $this->seedSmartLink();

        $result = SmartLinkResolver::resolveAll(
            null,
            ['site' => '__missing_site__'],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertSame([], $result);
    }
}

/**
 * Device detector that mimics a desktop/macOS request with no configured Mac URL.
 *
 * @internal
 */
final class GraphqlSmartLinkDesktopDeviceDetectionService extends DeviceDetectionService
{
    public function detectDevice(?string $userAgent = null): DeviceInfo
    {
        $info = new DeviceInfo();
        $info->platform = 'macos';
        $info->deviceType = 'desktop';
        $info->isDesktop = true;
        $info->userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X) SmartLinkManagerGraphqlTest/1.0';
        $info->browser = 'TestBrowser';
        $info->osName = 'macOS';
        $info->language = 'en';

        return $info;
    }

    public function detectLanguage(): string
    {
        return 'en';
    }

    public function getRedirectUrl(SmartLink $smartLink, DeviceInfo $deviceInfo, ?string $language = null): string
    {
        return '';
    }
}
