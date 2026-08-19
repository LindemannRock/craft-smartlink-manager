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
use craft\elements\Asset;
use craft\models\Volume;
use craft\web\Request;
use craft\web\Response;
use lindemannrock\smartlinkmanager\controllers\QrCodeController;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\services\QrCodeService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @since 5.38.0
 */
#[CoversClass(QrCodeController::class)]
final class QrCodeControllerTest extends TestCase
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

    public function testPublicGenerateReturnsMatchingPngMimeAndSignature(): void
    {
        $link = $this->qrLink('smartlink-test-qr-public-png', 'png');
        $this->installRequest();

        $this->withSettings(['enableQrCodeCache' => false], function() use ($link): void {
            $response = $this->controller()->actionGenerate($link->slug);

            self::assertSame('image/png', $response->headers->get('Content-Type'));
            self::assertStringStartsWith("\x89PNG\r\n\x1a\n", (string)$response->content);
            self::assertSame('public, max-age=86400', $response->headers->get('Cache-Control'));
        });
    }

    public function testPublicGenerateReturnsMatchingSvgMimeAndSignature(): void
    {
        $link = $this->qrLink('smartlink-test-qr-public-svg', 'svg');
        $this->installRequest(['format' => 'svg']);

        $this->withSettings(['enableQrCodeCache' => false], function() use ($link): void {
            $response = $this->controller()->actionGenerate($link->slug);

            self::assertSame('image/svg+xml', $response->headers->get('Content-Type'));
            self::assertStringContainsString('<svg', (string)$response->content);
            self::assertStringContainsString('</svg>', (string)$response->content);
        });
    }

    public function testAuthenticatedPreviewRequiresEditPermission(): void
    {
        $this->installRequest([
            'preview' => '1',
            'url' => 'https://example.com/preview',
        ]);
        $controller = $this->controller();
        $controller->denyPermission = true;

        $this->expectException(ForbiddenHttpException::class);
        try {
            $controller->actionGenerate();
        } finally {
            self::assertTrue($controller->loginRequired);
            self::assertSame(['smartLinkManager:editLinks'], $controller->requiredPermissions);
        }
    }

    public function testAuthenticatedPreviewReturnsMatchingPngOutput(): void
    {
        $this->installRequest([
            'preview' => '1',
            'url' => 'https://example.com/preview',
            'format' => 'png',
            'size' => 180,
        ]);

        $this->withSettings(['enableQrCodeCache' => false], function(): void {
            $controller = $this->controller();
            $response = $controller->actionGenerate();

            self::assertTrue($controller->loginRequired);
            self::assertSame(['smartLinkManager:editLinks'], $controller->requiredPermissions);
            self::assertSame('image/png', $response->headers->get('Content-Type'));
            self::assertStringStartsWith("\x89PNG\r\n\x1a\n", (string)$response->content);
        });
    }

    public function testPreviewLogoAcceptsOnlyPermittedConfiguredVolumes(): void
    {
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->withSettings([
            'enableQrCodeCache' => false,
            'qrLogoVolumeUid' => 'qr-volume',
            'imageVolumeUid' => 'image-volume',
        ], function() use ($service): void {
            $permittedUser = $this->createTestUser('smartlink-test-qr-preview-permitted');
            $permittedUser->admin = true;
            self::assertTrue(Craft::$app->getElements()->saveElement($permittedUser, false));
            $this->actingAs($permittedUser);

            foreach ([
                ['uid' => 'qr-volume', 'expected' => '42'],
                ['uid' => 'image-volume', 'expected' => '42'],
                ['uid' => 'other-volume', 'expected' => null],
            ] as $case) {
                $this->installRequest([
                    'preview' => '1',
                    'url' => 'https://example.com/preview-logo',
                    'format' => 'png',
                    'logo' => '42',
                ]);
                $controller = $this->controller();
                $controller->previewLogoAsset = new ControllerLogoAsset($case['uid']);
                $controller->actionGenerate();

                self::assertSame($case['expected'], $service->lastOptions['logo'] ?? null);
            }

            $restrictedUser = $this->createTestUser('smartlink-test-qr-preview-restricted');
            $this->actingAs($restrictedUser);
            $this->installRequest([
                'preview' => '1',
                'url' => 'https://example.com/preview-logo-restricted',
                'format' => 'png',
                'logo' => '42',
            ]);
            $controller = $this->controller();
            $controller->previewLogoAsset = new ControllerLogoAsset('qr-volume');
            $controller->actionGenerate();
            self::assertArrayNotHasKey('logo', $service->lastOptions);
        });
    }

    public function testDownloadUsesNormalizedFormatAndSafeFilename(): void
    {
        $link = $this->qrLink('smartlink-test-qr-download', 'png');
        $this->installRequest([
            'format' => 'invalid',
            'download' => '1',
        ]);

        $this->withSettings([
            'enableQrCodeCache' => false,
            'enableQrDownload' => true,
            'defaultQrFormat' => 'png',
            'qrDownloadFilename' => '../{slug}-qr-{size}-{format}',
        ], function() use ($link): void {
            $response = $this->controller()->actionGenerate($link->slug);
            $disposition = (string)$response->headers->get('Content-Disposition');

            self::assertSame('image/png', $response->headers->get('Content-Type'));
            self::assertStringEndsWith('-png.png"', $disposition);
            self::assertStringNotContainsString('../', $disposition);
            self::assertStringContainsString($link->slug, $disposition);
        });
    }

    public function testMissingLinkRemainsNotFound(): void
    {
        $this->installRequest();
        $this->expectException(NotFoundHttpException::class);

        $this->controller()->actionGenerate('smartlink-test-qr-does-not-exist');
    }

    public function testRendererFailureReturnsServerErrorAndIsLogged(): void
    {
        $link = $this->qrLink('smartlink-test-qr-renderer-failure', 'png');
        $this->installRequest();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', new ControllerThrowingQrCodeService());
        $controller = $this->controller();

        try {
            $controller->actionGenerate($link->slug);
            self::fail('Renderer failure should return a server error.');
        } catch (ServerErrorHttpException $e) {
            self::assertSame('QR code generation failed.', $e->getMessage());
            self::assertNotEmpty($controller->loggedErrors);
            self::assertSame('Failed to generate QR code', $controller->loggedErrors[0]['message']);
            self::assertSame('png', $controller->loggedErrors[0]['params']['format']);
        }
    }

    public function testDisplayPreservesNormalizedPngTemplatePayload(): void
    {
        $link = $this->qrLink('smartlink-test-qr-display-png', 'png');
        $this->installRequest(['format' => 'invalid']);
        $service = new ControllerRecordingQrCodeService();
        $service->pngOutput = "\x89PNG\r\n\x1a\nfixture";
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->withSettings(['defaultQrFormat' => 'png'], function() use ($link, $service): void {
            $controller = $this->controller();
            $controller->actionDisplay($link->slug);

            self::assertSame('png', $service->lastOptions['format']);
            self::assertSame('png', $controller->lastTemplateVariables['format']);
            self::assertSame(base64_encode($service->pngOutput), $controller->lastTemplateVariables['qrCodeData']);
            self::assertArrayNotHasKey('qrCodeSvg', $controller->lastTemplateVariables);
        });
    }

    public function testDisplayPreservesNormalizedSvgTemplatePayload(): void
    {
        $link = $this->qrLink('smartlink-test-qr-display-svg', 'svg');
        $this->installRequest(['format' => 'svg']);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $controller = $this->controller();
        $controller->actionDisplay($link->slug);

        self::assertSame('svg', $service->lastOptions['format']);
        self::assertSame('svg', $controller->lastTemplateVariables['format']);
        self::assertSame($service->svgOutput, $controller->lastTemplateVariables['qrCodeSvg']);
        self::assertArrayNotHasKey('qrCodeData', $controller->lastTemplateVariables);
    }

    public function testDisplayRendererFailureReturnsServerErrorAndIsLogged(): void
    {
        $link = $this->qrLink('smartlink-test-qr-display-failure', 'png');
        $this->installRequest();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', new ControllerThrowingQrCodeService());
        $controller = $this->controller();

        try {
            $controller->actionDisplay($link->slug);
            self::fail('Display renderer failure should return a server error.');
        } catch (ServerErrorHttpException $e) {
            self::assertSame('QR code generation failed.', $e->getMessage());
            self::assertNotEmpty($controller->loggedErrors);
            self::assertSame('Failed to generate QR code', $controller->loggedErrors[0]['message']);
            self::assertSame('png', $controller->loggedErrors[0]['params']['format']);
        }
    }

    private function qrLink(string $slug, string $format): SmartLink
    {
        $link = $this->seedSmartLink([
            'slug' => $slug,
            'fallbackUrl' => 'https://example.com/' . $slug,
        ]);
        $link->qrCodeEnabled = true;
        $link->qrCodeFormat = $format;
        self::assertTrue(Craft::$app->getElements()->saveElement($link));

        return $link;
    }

    /** @param array<string, mixed> $queryParams */
    private function installRequest(array $queryParams = []): void
    {
        if ($this->originalRequest === null) {
            $this->originalRequest = Craft::$app->get('request');
        }
        if ($this->originalResponse === null) {
            $this->originalResponse = Craft::$app->get('response');
        }

        Craft::$app->set('request', new QrControllerRequest($queryParams));
        Craft::$app->set('response', new Response());
    }

    private function controller(): TestQrCodeController
    {
        return new TestQrCodeController('qr-code', SmartLinkManager::$plugin);
    }
}

final class TestQrCodeController extends QrCodeController
{
    public bool $loginRequired = false;
    public bool $denyPermission = false;
    public ?Asset $previewLogoAsset = null;

    /** @var array<string, mixed> */
    public array $lastTemplateVariables = [];

    /** @var list<string> */
    public array $requiredPermissions = [];

    /** @var list<array{message: string, params: array<string, mixed>}> */
    public array $loggedErrors = [];

    public function requireLogin(): void
    {
        $this->loginRequired = true;
    }

    public function requirePermission(string $permissionName): void
    {
        $this->requiredPermissions[] = $permissionName;
        if ($this->denyPermission) {
            throw new ForbiddenHttpException('Fixture permission denied.');
        }
    }

    /** @param array<string, mixed> $params */
    protected function logError(string $message, array $params = []): void
    {
        $this->loggedErrors[] = ['message' => $message, 'params' => $params];
    }

    /** @param array<string, mixed> $variables */
    public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): Response
    {
        $this->lastTemplateVariables = $variables;
        $response = Craft::$app->getResponse();
        $response->content = 'rendered:' . $template;

        return $response;
    }

    protected function resolvePreviewLogoAsset(mixed $logoId): ?Asset
    {
        return $this->previewLogoAsset;
    }

}

final class QrControllerRequest extends Request
{
    /** @param array<string, mixed> $fixtureQueryParams */
    public function __construct(private readonly array $fixtureQueryParams)
    {
        parent::__construct();
    }

    public function getQueryParams(): array
    {
        return $this->fixtureQueryParams;
    }

    public function getQueryParam($name, $defaultValue = null): mixed
    {
        return $this->fixtureQueryParams[$name] ?? $defaultValue;
    }

    public function getIsAjax(): bool
    {
        return false;
    }
}

final class ControllerThrowingQrCodeService extends QrCodeService
{
    public function generateQrCode(string $url, array $options = []): string
    {
        throw new \RuntimeException('Fixture renderer failure details.');
    }
}

final class ControllerRecordingQrCodeService extends QrCodeService
{
    public string $pngOutput = "\x89PNG\r\n\x1a\nfixture";
    public string $svgOutput = '<svg>fixture</svg>';

    /** @var array<string, mixed> */
    public array $lastOptions = [];

    public function generateQrCode(string $url, array $options = []): string
    {
        $this->lastOptions = $options;

        return ($options['format'] ?? 'png') === 'svg' ? $this->svgOutput : $this->pngOutput;
    }
}

final class ControllerLogoAsset extends Asset
{
    public function __construct(private readonly string $fixtureVolumeUid)
    {
        parent::__construct();
    }

    public function getVolume(): Volume
    {
        return new Volume(['uid' => $this->fixtureVolumeUid]);
    }
}
