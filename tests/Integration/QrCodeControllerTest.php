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

    public function testPublicGenerationUsesSavedCanonicalStyleDespiteQueryOverrides(): void
    {
        $link = $this->qrLink('smartlink-test-qr-public-error-correction', 'png');
        $link->qrCodeSize = 320;
        $link->qrCodeColor = '#123456';
        $link->qrCodeBgColor = '#F5E6D3';
        $link->qrCodeEyeColor = '#AA2244';
        self::assertTrue(Craft::$app->getElements()->saveElement($link));
        $this->installRequest([
            'size' => 999,
            'color' => '654321',
            'bg' => 'FFFFFF',
            'format' => 'svg',
            'errorCorrection' => 'H',
            'margin' => 1,
            'moduleStyle' => 'dots',
            'eyeStyle' => 'rounded',
            'eyeColor' => '0000FF',
            'logo' => '999',
            'logoSize' => 30,
        ]);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->withSettings([
            'defaultQrErrorCorrection' => 'Q',
            'defaultQrMargin' => 4,
            'qrModuleStyle' => 'square',
            'qrEyeStyle' => 'pointed',
            'enableQrLogo' => false,
        ], function() use ($link, $service): void {
            $this->controller()->actionGenerate($link->slug);

            self::assertSame([
                'size' => 320,
                'color' => '123456',
                'bg' => 'F5E6D3',
                'format' => 'png',
                'errorCorrection' => 'Q',
                'margin' => 4,
                'moduleStyle' => 'square',
                'eyeStyle' => 'pointed',
                'eyeColor' => 'AA2244',
            ], $service->lastOptions);
        });
    }

    public function testGeneratedResponsePreservesMimeAndSignatureAcrossErrorCorrectionLevels(): void
    {
        $this->withSettings(['enableQrCodeCache' => false], function(): void {
            foreach (['L', 'M', 'Q', 'H'] as $errorCorrection) {
                foreach (['png', 'svg'] as $format) {
                    $this->installRequest([
                        'preview' => '1',
                        'url' => 'https://example.com/qr-level-response',
                        'format' => $format,
                        'errorCorrection' => $errorCorrection,
                    ]);
                    $response = $this->controller()->actionGenerate();

                    if ($format === 'svg') {
                        self::assertSame('image/svg+xml', $response->headers->get('Content-Type'));
                        self::assertStringContainsString('<svg', (string)$response->content);
                        self::assertStringContainsString('</svg>', (string)$response->content);
                    } else {
                        self::assertSame('image/png', $response->headers->get('Content-Type'));
                        self::assertStringStartsWith("\x89PNG\r\n\x1a\n", (string)$response->content);
                        self::assertStringEndsWith("\x00\x00\x00\x00IEND\xAE\x42\x60\x82", (string)$response->content);
                    }
                }
            }
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

    public function testAuthenticatedPreviewForwardsErrorCorrection(): void
    {
        $this->installRequest([
            'preview' => '1',
            'url' => 'https://example.com/preview-error-correction',
            'format' => 'svg',
            'errorCorrection' => 'q',
        ]);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);
        $controller = $this->controller();

        $response = $controller->actionGenerate();

        self::assertTrue($controller->loginRequired);
        self::assertSame(['smartLinkManager:editLinks'], $controller->requiredPermissions);
        self::assertSame('q', $service->lastOptions['errorCorrection']);
        self::assertFalse($service->lastOptions['_cache']);
        self::assertSame('private, no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
    }

    public function testAuthenticatedExistingLinkPreviewRequiresEditPermission(): void
    {
        $link = $this->qrLink('smartlink-test-qr-existing-permission', 'png');
        $this->installRequest(['linkId' => $link->id]);
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

    public function testAuthenticatedExistingLinkPreviewUsesUnsavedStyleAtFixedSizeWithoutCaching(): void
    {
        $link = $this->qrLink('smartlink-test-qr-existing-preview', 'png');
        $this->installRequest([
            'linkId' => $link->id,
            'size' => 2048,
            'format' => 'svg',
            'color' => '123456',
            'bg' => 'F5E6D3',
            'errorCorrection' => 'H',
            'margin' => 2,
            'moduleStyle' => 'dots',
            'eyeStyle' => 'rounded',
            'eyeColor' => 'AA2244',
            'logoSize' => 24,
        ]);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);
        $controller = $this->controller();

        $response = $controller->actionGenerate();

        self::assertTrue($controller->loginRequired);
        self::assertSame(150, $service->lastOptions['size']);
        self::assertSame('svg', $service->lastOptions['format']);
        self::assertSame('123456', $service->lastOptions['color']);
        self::assertSame('F5E6D3', $service->lastOptions['bg']);
        self::assertSame('H', $service->lastOptions['errorCorrection']);
        self::assertSame('dots', $service->lastOptions['moduleStyle']);
        self::assertSame('rounded', $service->lastOptions['eyeStyle']);
        self::assertSame('AA2244', $service->lastOptions['eyeColor']);
        self::assertFalse($service->lastOptions['_cache']);
        self::assertSame(4096, $service->lastOptions['_sizeMax']);
        self::assertSame('private, no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
    }

    public function testAuthenticatedDownloadsNormalizeExactSizeAndFilenameTogether(): void
    {
        $link = $this->qrLink('smartlink-test-qr-exact-download', 'png');
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->withSettings([
            'enableQrDownload' => true,
            'qrDownloadFilename' => '{slug}-{size}-{format}',
        ], function() use ($link, $service): void {
            foreach ([
                ['requested' => 99, 'expected' => 100, 'format' => 'png'],
                ['requested' => 100, 'expected' => 100, 'format' => 'svg'],
                ['requested' => 256, 'expected' => 256, 'format' => 'png'],
                ['requested' => 512, 'expected' => 512, 'format' => 'svg'],
                ['requested' => 1000, 'expected' => 1000, 'format' => 'png'],
                ['requested' => 1001, 'expected' => 1001, 'format' => 'svg'],
                ['requested' => 1024, 'expected' => 1024, 'format' => 'png'],
                ['requested' => 2048, 'expected' => 2048, 'format' => 'svg'],
                ['requested' => 4096, 'expected' => 4096, 'format' => 'png'],
                ['requested' => 5000, 'expected' => 4096, 'format' => 'svg'],
            ] as $case) {
                $this->installRequest([
                    'linkId' => $link->id,
                    'download' => '1',
                    'size' => $case['requested'],
                    'format' => $case['format'],
                ]);
                $response = $this->controller()->actionGenerate();

                self::assertSame($case['expected'], $service->lastOptions['size']);
                self::assertSame($case['format'], $service->lastOptions['format']);
                self::assertStringContainsString(
                    "{$link->slug}-{$case['expected']}-{$case['format']}.{$case['format']}",
                    (string)$response->headers->get('Content-Disposition'),
                );
                self::assertSame('private, no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
            }
        });
    }

    public function testAuthenticatedDownloadRespectsDisabledSetting(): void
    {
        $link = $this->qrLink('smartlink-test-qr-disabled-download', 'png');
        $this->installRequest(['linkId' => $link->id, 'download' => '1', 'size' => 512]);
        $this->swapPluginComponent('smartlink-manager', 'qrCode', new ControllerThrowingQrCodeService());

        $this->withSettings(['enableQrDownload' => false], function(): void {
            $this->expectException(NotFoundHttpException::class);
            $this->controller()->actionGenerate();
        });
    }

    public function testMissingAuthenticatedExistingLinkIsNotFound(): void
    {
        $this->installRequest(['linkId' => 2147483647]);
        $this->expectException(NotFoundHttpException::class);

        $this->controller()->actionGenerate();
    }

    public function testTrashedAuthenticatedExistingLinkIsNotFound(): void
    {
        $link = $this->qrLink('smartlink-test-qr-trashed-existing', 'png');
        self::assertTrue(Craft::$app->getElements()->deleteElement($link));
        $this->installRequest(['linkId' => $link->id]);
        $this->expectException(NotFoundHttpException::class);

        $this->controller()->actionGenerate();
    }

    public function testQrDisabledAuthenticatedExistingLinkIsNotFound(): void
    {
        $link = $this->qrLink('smartlink-test-qr-unavailable-existing', 'png');
        $link->qrCodeEnabled = false;
        self::assertTrue(Craft::$app->getElements()->saveElement($link));
        $this->installRequest(['linkId' => $link->id]);
        $this->expectException(NotFoundHttpException::class);

        $this->controller()->actionGenerate();
    }

    public function testAuthenticatedLargePngDownloadHasExactBytesAndFilenameDimension(): void
    {
        $link = $this->qrLink('smartlink-test-qr-real-large-download', 'png');
        $this->installRequest([
            'linkId' => $link->id,
            'download' => '1',
            'size' => 2048,
            'format' => 'png',
        ]);

        $this->withSettings([
            'enableQrCodeCache' => true,
            'enableQrDownload' => true,
            'qrDownloadFilename' => '{slug}-{size}-{format}',
        ], function() use ($link): void {
            $response = $this->controller()->actionGenerate();
            $dimensions = getimagesizefromstring((string)$response->content);

            self::assertIsArray($dimensions);
            self::assertSame(2048, $dimensions[0]);
            self::assertSame(2048, $dimensions[1]);
            self::assertSame('image/png', $dimensions['mime']);
            self::assertStringContainsString(
                "{$link->slug}-2048-png.png",
                (string)$response->headers->get('Content-Disposition'),
            );
            self::assertSame('private, no-store, no-cache, must-revalidate, max-age=0', $response->headers->get('Cache-Control'));
        });
    }

    public function testPublicSlugCannotBeSwitchedIntoAuthenticatedOverrideMode(): void
    {
        $link = $this->qrLink('smartlink-test-qr-mode-confusion', 'png');
        $this->installRequest([
            'linkId' => $link->id,
            'preview' => '1',
            'url' => 'https://attacker.example/override',
            'size' => 4096,
            'format' => 'svg',
        ]);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);
        $controller = $this->controller();

        $response = $controller->actionGenerate($link->slug);

        self::assertFalse($controller->loginRequired);
        self::assertSame($link->qrCodeSize, $service->lastOptions['size']);
        self::assertSame('png', $service->lastOptions['format']);
        self::assertArrayNotHasKey('_cache', $service->lastOptions);
        self::assertSame('public, max-age=86400', $response->headers->get('Cache-Control'));
    }

    public function testNonScalarPublicStyleParametersAreIgnored(): void
    {
        $link = $this->qrLink('smartlink-test-qr-array-query', 'png');
        $this->installRequest([
            'size' => ['4096'],
            'format' => ['svg'],
            'color' => ['FFFFFF'],
            'logo' => ['42'],
        ]);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->controller()->actionGenerate($link->slug);

        self::assertSame($link->qrCodeSize, $service->lastOptions['size']);
        self::assertSame('png', $service->lastOptions['format']);
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
            'errorCorrection' => 'H',
            'download' => '1',
        ]);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->withSettings([
            'enableQrCodeCache' => false,
            'enableQrDownload' => true,
            'defaultQrFormat' => 'png',
            'defaultQrErrorCorrection' => 'M',
            'qrDownloadFilename' => '../{slug}-qr-{size}-{format}',
        ], function() use ($link, $service): void {
            $response = $this->controller()->actionGenerate($link->slug);
            $disposition = (string)$response->headers->get('Content-Disposition');

            self::assertSame('image/png', $response->headers->get('Content-Type'));
            self::assertStringEndsWith('-png.png"', $disposition);
            self::assertStringNotContainsString('../', $disposition);
            self::assertStringContainsString($link->slug, $disposition);
            self::assertSame('M', $service->lastOptions['errorCorrection']);
            self::assertStringContainsString('-' . $link->qrCodeSize . '-png.png"', $disposition);
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
        } catch (\Throwable $e) {
            self::assertInstanceOf(ServerErrorHttpException::class, $e);
            self::assertSame('QR code generation failed.', $e->getMessage());
            self::assertNotEmpty($controller->loggedErrors);
            self::assertSame('Failed to generate QR code', $controller->loggedErrors[0]['message']);
            self::assertSame('png', $controller->loggedErrors[0]['params']['format']);
        }
    }

    public function testDisplayPreservesNormalizedPngTemplatePayload(): void
    {
        $link = $this->qrLink('smartlink-test-qr-display-png', 'png');
        $this->installRequest(['size' => 999, 'format' => 'svg', 'errorCorrection' => 'H']);
        $service = new ControllerRecordingQrCodeService();
        $service->pngOutput = "\x89PNG\r\n\x1a\nfixture";
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $this->withSettings(['defaultQrFormat' => 'png', 'defaultQrErrorCorrection' => 'M'], function() use ($link, $service): void {
            $controller = $this->controller();
            $controller->actionDisplay($link->slug);

            self::assertSame($link->qrCodeSize, $service->lastOptions['size']);
            self::assertSame('png', $service->lastOptions['format']);
            self::assertSame('M', $service->lastOptions['errorCorrection']);
            self::assertSame($link->qrCodeSize, $controller->lastTemplateVariables['size']);
            self::assertSame('png', $controller->lastTemplateVariables['format']);
            self::assertSame(base64_encode($service->pngOutput), $controller->lastTemplateVariables['qrCodeData']);
            self::assertArrayNotHasKey('qrCodeSvg', $controller->lastTemplateVariables);
        });
    }

    public function testDisplayPreservesNormalizedSvgTemplatePayload(): void
    {
        $link = $this->qrLink('smartlink-test-qr-display-svg', 'svg');
        $this->installRequest(['format' => 'svg', 'errorCorrection' => 'H']);
        $service = new ControllerRecordingQrCodeService();
        $this->swapPluginComponent('smartlink-manager', 'qrCode', $service);

        $controller = $this->controller();
        $this->withSettings(['defaultQrErrorCorrection' => 'H'], function() use ($controller, $link): void {
            $controller->actionDisplay($link->slug);
        });

        self::assertSame('svg', $service->lastOptions['format']);
        self::assertSame('H', $service->lastOptions['errorCorrection']);
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
        } catch (\Throwable $e) {
            self::assertInstanceOf(ServerErrorHttpException::class, $e);
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
