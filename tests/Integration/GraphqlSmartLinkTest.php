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
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use lindemannrock\smartlinkmanager\tests\Stubs\StubDeviceDetectionService;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->savedRequest = Craft::$app->getRequest();
        Craft::$app->set('request', new StubConsoleRequest(userIp: '203.0.113.42'));

        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());

        $this->applySettingsForTest([
            'ipHashSalt' => self::TEST_SALT,
            'enableAnalytics' => true,
            'enableGeoDetection' => false,
            'smartlinkBaseUrl' => 'https://smart.example/{siteHandle}',
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->savedRequest !== null) {
            Craft::$app->set('request', $this->savedRequest);
        }

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
        self::assertArrayNotHasKey('source', $queries['smartlinkManagerResolveSmartLink']['args']);
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
        self::assertSame('graphql', $metadata['source']);
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

    public function testListQueryAppliesDefaultLimitWhenLimitIsOmitted(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();

        for ($i = 0; $i < 101; $i++) {
            $this->seedSmartLink(['siteId' => $site->id]);
        }

        $results = SmartLinkResolver::resolveAll(
            null,
            ['siteId' => $site->id],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertCount(100, $results);
    }

    #[DataProvider('invalidExplicitSiteProvider')]
    public function testInvalidExplicitSiteReturnsNoResultsWithoutSideEffects(array $siteArguments): void
    {
        $link = $this->seedSmartLink();
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $result = SmartLinkResolver::resolve(
            null,
            ['slug' => $link->slug, ...$siteArguments],
            null,
            $this->createMock(ResolveInfo::class),
        );

        $results = SmartLinkResolver::resolveAll(
            null,
            $siteArguments,
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertNull($result);
        self::assertSame([], $results);
        self::assertSame(0, $this->fetchHitsFromDb((int)$link->id));
        self::assertSame(0, $this->countRows('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]));
    }

    /** @return array<string, array{0: array<string, int|string>}> */
    public static function invalidExplicitSiteProvider(): array
    {
        return [
            'zero site ID' => [['siteId' => 0]],
            'negative site ID' => [['siteId' => -1]],
            'nonexistent positive site ID' => [['siteId' => 2147483647]],
            'invalid nonempty site handle' => [['site' => '__missing_site__']],
        ];
    }

    public function testNonemptySiteHandleTakesPrecedenceOverInvalidSiteId(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $arguments = ['site' => $site->handle, 'siteId' => 0];
        $result = SmartLinkResolver::resolve(
            null,
            ['slug' => $link->slug, ...$arguments],
            null,
            $this->createMock(ResolveInfo::class),
        );
        $results = SmartLinkResolver::resolveAll(
            null,
            $arguments,
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertIsArray($result);
        self::assertSame($site->id, $result['siteId']);
        self::assertContains($link->id, array_column($results, 'id'));
    }

    public function testExplicitSiteMissDoesNotResolveAnotherSiteOrRecordAnalytics(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));
        [$sourceSite, $requestedSite] = $sites;

        $link = $this->seedSmartLink([
            'siteId' => $sourceSite->id,
            'fallbackUrl' => 'https://example.com/source-site',
        ]);
        $service = new GraphqlExactSiteSmartLinksService($link, $requestedSite->id);
        $this->swapPluginComponent('smartlink-manager', 'smartLinks', $service);
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $result = SmartLinkResolver::resolve(
            null,
            ['slug' => $link->slug, 'siteId' => $requestedSite->id],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertNull($result);
        self::assertSame([[$link->slug, $requestedSite->id]], $service->lookups);
        self::assertSame(0, $this->fetchHitsFromDb((int)$link->id));
        self::assertSame(0, $this->countRows('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]));
    }

    #[DataProvider('implicitSiteProvider')]
    public function testResolveWithoutExplicitSitePreservesCurrentSiteFallbackSemantics(array $siteArguments): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));
        $currentSite = Craft::$app->getSites()->getCurrentSite();
        $sourceSite = $sites[1];
        $link = $this->seedSmartLink([
            'siteId' => $sourceSite->id,
            'fallbackUrl' => 'https://example.com/default-site-fallback',
        ]);
        $sourceVariant = SmartLink::find()->id($link->id)->siteId($sourceSite->id)->status(null)->one();
        self::assertInstanceOf(SmartLink::class, $sourceVariant);
        $service = new GraphqlExactSiteSmartLinksService($sourceVariant, $currentSite->id);
        $this->swapPluginComponent('smartlink-manager', 'smartLinks', $service);
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $listResults = SmartLinkResolver::resolveAll(
            null,
            $siteArguments,
            null,
            $this->createMock(ResolveInfo::class),
        );
        self::assertContains($link->id, array_column($listResults, 'id'));
        self::assertSame(0, $this->fetchHitsFromDb((int)$link->id));

        $result = SmartLinkResolver::resolve(
            null,
            ['slug' => $link->slug, ...$siteArguments],
            null,
            $this->createMock(ResolveInfo::class),
        );

        self::assertIsArray($result);
        self::assertSame($sourceSite->id, $result['siteId']);
        self::assertSame([[$link->slug, $currentSite->id], [$link->slug, null]], $service->lookups);
        $analytics = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
        self::assertNotNull($analytics);
        self::assertSame($sourceSite->id, (int)$analytics['siteId']);
    }

    /** @return array<string, array{0: array<string, mixed>}> */
    public static function implicitSiteProvider(): array
    {
        return [
            'arguments omitted' => [[]],
            'site handle is null' => [['site' => null]],
            'site ID is null' => [['siteId' => null]],
        ];
    }

    public function testExplicitSiteRejectsUnavailableOrPluginDisabledLinkWithoutAnalytics(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));
        [$enabledSite, $disabledSite] = $sites;
        $pending = $this->seedSmartLink([
            'siteId' => $enabledSite->id,
            'postDate' => new \DateTime('+1 day'),
        ]);
        $disabledSiteLink = $this->seedSmartLink(['siteId' => $disabledSite->id]);
        Craft::$app->set('request', new StubWebRequest(userIp: '203.0.113.42'));

        $this->withSettings(['enabledSites' => [$enabledSite->id]], function() use ($disabledSite, $disabledSiteLink, $enabledSite, $pending): void {
            foreach ([
                [$pending, ['siteId' => $enabledSite->id]],
                [$disabledSiteLink, ['site' => $disabledSite->handle]],
            ] as [$link, $siteArguments]) {
                $result = SmartLinkResolver::resolve(
                    null,
                    ['slug' => $link->slug, ...$siteArguments],
                    null,
                    $this->createMock(ResolveInfo::class),
                );

                self::assertNull($result);
                self::assertSame(0, $this->fetchHitsFromDb((int)$link->id));
                self::assertSame(0, $this->countRows('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]));
            }
        });
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

final class GraphqlExactSiteSmartLinksService extends SmartLinksService
{
    /** @var list<array{0: string, 1: int|null}> */
    public array $lookups = [];

    public function __construct(
        private readonly SmartLink $sourceLink,
        private readonly int $requestedSiteId,
        array $config = [],
    ) {
        parent::__construct($config);
    }

    public function getSmartLinkBySlug(string $slug, ?int $siteId = null): ?SmartLink
    {
        $this->lookups[] = [$slug, $siteId];

        if ($siteId === $this->requestedSiteId) {
            return null;
        }

        return $siteId === null ? $this->sourceLink : null;
    }
}
