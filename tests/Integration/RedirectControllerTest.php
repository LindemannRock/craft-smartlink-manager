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
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());
        $link = $this->seedSmartLink(['slug' => 'smartlink-test-render']);

        $this->withSettings([
            'redirectTemplate' => 'smartlink-manager/redirect',
        ], function() use ($link): void {
            $response = $this->controller()->actionIndex($link->slug);

            self::assertSame(200, $response->getStatusCode());
            self::assertSame('rendered:smartlink-manager/redirect', $response->content);
            self::assertSame('no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
        });
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
    /**
     * @param array<string, mixed> $variables
     */
    public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): Response
    {
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
