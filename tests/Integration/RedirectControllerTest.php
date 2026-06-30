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
use craft\console\Request as ConsoleRequest;
use lindemannrock\smartlinkmanager\controllers\RedirectController;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\Stubs\StubDeviceDetectionService;
use lindemannrock\smartlinkmanager\tests\TestCase;
use yii\web\Response;

/**
 * Pins SmartLink landing-page and action redirect behavior.
 *
 * @since 5.30.0
 */
final class RedirectControllerTest extends TestCase
{
    private ?object $originalRequest = null;
    private ?object $originalResponse = null;

    protected function tearDown(): void
    {
        if ($this->originalRequest !== null) {
            Craft::$app->set('request', $this->originalRequest);
            $this->originalRequest = null;
        }
        if ($this->originalResponse !== null) {
            Craft::$app->set('response', $this->originalResponse);
            $this->originalResponse = null;
        }

        parent::tearDown();
    }

    public function testLandingPageRendersConfiguredTemplate(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new MobileIosDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-render',
            'iosUrl' => 'https://example.com/ios',
        ]);

        $this->withSettings([
            'redirectTemplate' => 'smartlink-manager/redirect',
            'smartlinkBaseUrl' => 'https://smart.example/{siteHandle}',
        ], function() use ($link): void {
            $controller = $this->controller();
            $response = $controller->actionIndex($link->slug);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('rendered:smartlink-manager/redirect', $response->content);
            self::assertSame('no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
            self::assertArrayNotHasKey('eventType', $controller->lastVariables);
            self::assertSame('direct', $controller->lastVariables['source'] ?? null);
            self::assertArrayNotHasKey('goUrl', $controller->lastVariables);
            self::assertArrayNotHasKey('auto', $controller->lastVariables['goUrls'] ?? []);
            self::assertArrayNotHasKey('autoRedirectUrl', $controller->lastVariables);
            $renderedSmartLink = $controller->lastVariables['smartLink'] ?? null;
            self::assertInstanceOf(SmartLink::class, $renderedSmartLink);
            $autoRedirectScript = (string)$renderedSmartLink->renderRedirectScript();
            self::assertStringContainsString('smart.example', $autoRedirectScript);
            self::assertStringContainsString(
                'actions\\/smartlink-manager\\/redirect\\/auto-redirect',
                $autoRedirectScript
            );
            self::assertStringContainsString('slug=' . $link->slug, $autoRedirectScript);
            self::assertStringContainsString('site=en', $autoRedirectScript);
            self::assertStringNotContainsString('\\/en\\/index.php', $autoRedirectScript);
            self::assertStringNotContainsString("smartlink-manager\\/redirect\\/auto\\/{$link->slug}", $autoRedirectScript);
            self::assertStringNotContainsString('src=', $autoRedirectScript);
            self::assertStringContainsString(
                'actions/smartlink-manager/redirect/go',
                (string) ($controller->lastVariables['goUrls']['ios'] ?? '')
            );
            self::assertStringContainsString('platform=ios', (string) ($controller->lastVariables['goUrls']['ios'] ?? ''));
            self::assertArrayNotHasKey('autoRedirect', $controller->lastVariables);
        });
    }

    public function testLandingPageAddsSiteParamWhenConfiguredBaseUrlHasNoSiteToken(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new MobileIosDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-render-shared-base',
            'iosUrl' => 'https://example.com/ios',
        ]);
        $site = Craft::$app->getSites()->getSiteById($link->siteId);
        self::assertNotNull($site);

        $this->withSettings([
            'redirectTemplate' => 'smartlink-manager/redirect',
            'smartlinkBaseUrl' => 'https://smart.example',
        ], function() use ($link, $site): void {
            $controller = $this->controller();
            $response = $controller->actionIndex($link->slug);

            self::assertSame(200, $response->getStatusCode());
            self::assertArrayNotHasKey('goUrl', $controller->lastVariables);
            self::assertArrayNotHasKey('auto', $controller->lastVariables['goUrls'] ?? []);
            self::assertArrayNotHasKey('autoRedirectUrl', $controller->lastVariables);
            $renderedSmartLink = $controller->lastVariables['smartLink'] ?? null;
            self::assertInstanceOf(SmartLink::class, $renderedSmartLink);
            $autoRedirectScript = (string)$renderedSmartLink->renderRedirectScript();
            self::assertStringContainsString('smart.example', $autoRedirectScript);
            self::assertStringContainsString('actions\\/smartlink-manager\\/redirect\\/auto-redirect', $autoRedirectScript);
            self::assertStringContainsString('slug=' . $link->slug, $autoRedirectScript);
            self::assertStringContainsString('site=' . $site->handle, $autoRedirectScript);
        });
    }

    public function testLandingPageDoesNotAutoRedirectMobileWithoutPlatformUrl(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new MobileIosDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-render-no-ios',
            'iosUrl' => null,
            'fallbackUrl' => 'https://example.com/fallback',
        ]);

        $controller = $this->controller();
        $response = $controller->actionIndex($link->slug);

        self::assertSame(200, $response->getStatusCode());
        self::assertArrayNotHasKey('autoRedirect', $controller->lastVariables);
    }

    public function testLandingPageDoesNotAutoRedirectDesktop(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new DesktopDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-render-desktop',
            'iosUrl' => 'https://example.com/ios',
            'fallbackUrl' => 'https://example.com/fallback',
        ]);

        $controller = $this->controller();
        $response = $controller->actionIndex($link->slug);

        self::assertSame(200, $response->getStatusCode());
        self::assertArrayNotHasKey('autoRedirect', $controller->lastVariables);
    }

    public function testLandingPagePreservesQrSourceOnTrackedAutoHop(): void
    {
        $this->installWebHarness(['src' => 'qr']);
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new MobileIosDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-render-qr',
            'iosUrl' => 'https://example.com/ios',
        ]);

        $controller = $this->controller();
        $response = $controller->actionIndex($link->slug);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('qr', $controller->lastVariables['source'] ?? null);
        self::assertArrayNotHasKey('goUrl', $controller->lastVariables);
        self::assertArrayNotHasKey('auto', $controller->lastVariables['goUrls'] ?? []);
        self::assertArrayNotHasKey('autoRedirectUrl', $controller->lastVariables);
        $renderedSmartLink = $controller->lastVariables['smartLink'] ?? null;
        self::assertInstanceOf(SmartLink::class, $renderedSmartLink);
        self::assertStringNotContainsString(
            'src=qr',
            (string)$renderedSmartLink->renderRedirectScript()
        );
    }

    public function testAutoRedirectResolverReturnsTrackedGoUrlForMobile(): void
    {
        $this->installWebHarness(['src' => 'qr']);
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new MobileIosDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-auto-resolver',
            'iosUrl' => 'https://example.com/ios',
        ]);

        $response = $this->controller()->actionAutoRedirect($link->slug);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
        $data = $response->data;
        self::assertIsArray($data);
        self::assertTrue($data['autoRedirect'] ?? false);
        self::assertStringContainsString(
            'actions/smartlink-manager/redirect/go',
            (string) ($data['goUrl'] ?? '')
        );
        self::assertStringContainsString('slug=' . $link->slug, (string) ($data['goUrl'] ?? ''));
        self::assertStringContainsString('platform=auto', (string) ($data['goUrl'] ?? ''));
        self::assertStringContainsString('src=qr', (string) ($data['goUrl'] ?? ''));
    }

    public function testAutoRedirectResolverReturnsFalseForDesktop(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new DesktopDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-auto-resolver-desktop',
            'iosUrl' => 'https://example.com/ios',
        ]);

        $response = $this->controller()->actionAutoRedirect($link->slug);

        self::assertSame(200, $response->getStatusCode());
        $data = $response->data;
        self::assertIsArray($data);
        self::assertFalse($data['autoRedirect'] ?? true);
        self::assertNull($data['goUrl'] ?? null);
    }

    public function testAutoRedirectUsesDeviceDetectedDestinationAndTracksQrSource(): void
    {
        $this->installWebHarness(['src' => 'qr']);
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-auto-go',
            'iosUrl' => 'https://example.com/ios',
            'fallbackUrl' => 'https://example.com/fallback',
        ]);

        $this->withSettings([
            'enableAnalytics' => true,
            'enableGeoDetection' => false,
            'ipHashSalt' => '0123456789abcdef0123456789abcdef',
        ], function() use ($link): void {
            $response = $this->controller()->actionGo($link->slug, 'auto');

            self::assertSame(302, $response->getStatusCode());
            self::assertSame('https://example.com/ios', $response->headers->get('Location'));
            self::assertSame(1, $this->fetchHitsFromDb((int) $link->id));

            $row = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
            self::assertNotNull($row);
            self::assertStringContainsString('"source":"qr"', (string) $row['metadata']);
            self::assertStringContainsString('"clickType":"redirect"', (string) $row['metadata']);
        });
    }

    public function testUnknownSourceFallsBackToDirectAttribution(): void
    {
        $this->installWebHarness(['src' => 'spa']);
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-unknown-source',
            'iosUrl' => 'https://example.com/ios',
            'fallbackUrl' => 'https://example.com/fallback',
        ]);

        $this->withSettings([
            'enableAnalytics' => true,
            'enableGeoDetection' => false,
            'ipHashSalt' => '0123456789abcdef0123456789abcdef',
        ], function() use ($link): void {
            $response = $this->controller()->actionGo($link->slug, 'auto');

            self::assertSame(302, $response->getStatusCode());

            $row = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
            self::assertNotNull($row);
            self::assertStringContainsString('"source":"direct"', (string) $row['metadata']);
        });
    }

    public function testExplicitPlatformRedirectsToMatchingUrl(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-android-go',
            'androidUrl' => 'https://example.com/android',
            'fallbackUrl' => 'https://example.com/fallback',
            'trackAnalytics' => false,
        ]);

        $response = $this->controller()->actionGo($link->slug, 'android');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://example.com/android', $response->headers->get('Location'));
        self::assertSame(0, $this->fetchHitsFromDb((int) $link->id));
    }

    public function testMissingExplicitPlatformUrlFallsBackToFallbackUrl(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-missing-platform',
            'fallbackUrl' => 'https://example.com/fallback',
            'trackAnalytics' => false,
        ]);

        $response = $this->controller()->actionGo($link->slug, 'android');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://example.com/fallback', $response->headers->get('Location'));
    }

    public function testInvalidPlatformDefaultsToAuto(): void
    {
        $this->installWebHarness();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-invalid-platform',
            'iosUrl' => 'https://example.com/ios',
            'fallbackUrl' => 'https://example.com/fallback',
            'trackAnalytics' => false,
        ]);

        $response = $this->controller()->actionGo($link->slug, 'javascript');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://example.com/ios', $response->headers->get('Location'));
    }

    /** @param array<string, mixed> $queryParams */
    private function installWebHarness(array $queryParams = []): void
    {
        if ($this->originalRequest === null) {
            $this->originalRequest = Craft::$app->get('request');
        }
        if ($this->originalResponse === null) {
            $this->originalResponse = Craft::$app->get('response');
        }

        Craft::$app->set('request', new TestConsoleRequest($queryParams));
        Craft::$app->set('response', new \craft\web\Response());
    }

    private function controller(): TestRedirectController
    {
        return new TestRedirectController('redirect', SmartLinkManager::$plugin);
    }
}

final class TestRedirectController extends RedirectController
{
    /** @var array<string, mixed> */
    public array $lastVariables = [];

    /**
     * @param array<string, mixed> $variables
     */
    public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): Response
    {
        $this->lastVariables = $variables;

        $response = Craft::$app->getResponse();
        $response->setStatusCode(200);
        $response->content = "rendered:{$template}";

        return $response;
    }
}

final class TestConsoleRequest extends ConsoleRequest
{
    /** @param array<string, mixed> $queryParams */
    public function __construct(private array $queryParams = [], array $config = [])
    {
        parent::__construct($config);
    }

    public function getParam($name, $defaultValue = null): mixed
    {
        return $this->queryParams[$name] ?? $defaultValue;
    }

    /** @return array<string, mixed> */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    public function getIsAjax(): bool
    {
        return false;
    }

    public function getUserIP(): ?string
    {
        return '203.0.113.42';
    }

    public function getUserAgent(): ?string
    {
        return 'Mozilla/5.0 (Test) SmartLinkManagerStub/1.0';
    }

    public function getReferrer(): ?string
    {
        return 'https://example.com/referrer';
    }
}

final class DesktopDeviceDetectionService extends DeviceDetectionService
{
    public function detectDevice(?string $userAgent = null): DeviceInfo
    {
        $info = new DeviceInfo();
        $info->platform = 'macos';
        $info->deviceType = 'desktop';
        $info->isDesktop = true;
        $info->userAgent = 'Mozilla/5.0 (Test) SmartLinkManagerDesktopStub/1.0';
        $info->browser = 'TestBrowser';
        $info->osName = 'TestOS';
        $info->language = 'en';

        return $info;
    }

    public function detectLanguage(): string
    {
        return 'en';
    }

    public function getRedirectUrl(SmartLink $smartLink, DeviceInfo $deviceInfo, ?string $language = null): string
    {
        return $smartLink->macUrl ?: $smartLink->fallbackUrl;
    }
}

final class MobileIosDeviceDetectionService extends DeviceDetectionService
{
    public function detectDevice(?string $userAgent = null): DeviceInfo
    {
        $info = new DeviceInfo();
        $info->platform = 'ios';
        $info->deviceType = 'smartphone';
        $info->isMobile = true;
        $info->userAgent = 'Mozilla/5.0 (Test) SmartLinkManagerMobileIosStub/1.0';
        $info->browser = 'TestBrowser';
        $info->osName = 'TestOS';
        $info->language = 'en';

        return $info;
    }

    public function detectLanguage(): string
    {
        return 'en';
    }
}
