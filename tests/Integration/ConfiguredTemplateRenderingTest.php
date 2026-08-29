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
use craft\helpers\FileHelper;
use craft\web\Request;
use craft\web\TemplateResponseFormatter;
use craft\web\View;
use lindemannrock\smartlinkmanager\controllers\QrCodeController;
use lindemannrock\smartlinkmanager\controllers\RedirectController;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\Stubs\StubDeviceDetectionService;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use yii\web\Response;

/**
 * Protects configured frontend-template rendering through Craft's deferred
 * response formatter.
 *
 * @since 5.38.0
 */
final class ConfiguredTemplateRenderingTest extends TestCase
{
    private ?object $originalRequest = null;
    private ?object $originalResponse = null;
    private ?object $originalView = null;

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
        if ($this->originalView !== null) {
            Craft::$app->set('view', $this->originalView);
            $this->originalView = null;
        }

        parent::tearDown();
    }

    #[DataProvider('environmentTemplateConsumers')]
    public function testDefinedEnvironmentTemplateRendersThroughControllerAndCraftLoader(
        string $setting,
        string $environmentName,
        string $templateName,
        string $consumer,
    ): void {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-configured-template');
        $templatePath = $templatesPath . DIRECTORY_SEPARATOR . $templateName . '.twig';
        FileHelper::createDirectory(dirname($templatePath));
        self::assertNotFalse(file_put_contents($templatePath, "rendered-{$consumer}"));

        $environmentExisted = array_key_exists($environmentName, $_SERVER);
        $environmentValue = $environmentExisted ? $_SERVER[$environmentName] : null;
        $_SERVER[$environmentName] = $templateName;

        try {
            $this->withSiteTemplatesPath($templatesPath, function() use (
                $setting,
                $environmentName,
                $consumer,
                $templatesPath,
            ): void {
                $this->withSettings([
                    $setting => '$' . $environmentName,
                    'enableAnalytics' => false,
                    'enableQrCodeCache' => false,
                    'enabledIntegrations' => [],
                ], function() use ($setting, $environmentName, $consumer, $templatesPath): void {
                    $settings = SmartLinkManager::$plugin->getSettings();
                    self::assertTrue($settings->validate());
                    self::assertSame('$' . $environmentName, $settings->{$setting});

                    $response = match ($consumer) {
                        'redirect' => $this->redirectResponse($this->activeLink()),
                        'qr' => $this->qrDisplayResponse($this->qrLink()),
                        default => throw new \LogicException("Unknown template consumer: {$consumer}"),
                    };

                    Craft::$app->getView()->setTemplatesPath($templatesPath);
                    self::assertSame(TemplateResponseFormatter::FORMAT, $response->format);
                    $this->formatResponse($response);
                    self::assertSame("rendered-{$consumer}", $response->content);
                    self::assertSame('$' . $environmentName, $settings->{$setting});
                });
            });
        } finally {
            $this->restoreServerValue($environmentName, $environmentExisted, $environmentValue);
        }
    }

    /**
     * @return iterable<string, array{setting: string, environmentName: string, templateName: string, consumer: string}>
     */
    public static function environmentTemplateConsumers(): iterable
    {
        yield 'redirect landing page' => [
            'setting' => 'redirectTemplate',
            'environmentName' => 'SMARTLINK_MANAGER_TEST_REDIRECT_TEMPLATE',
            'templateName' => 'configured/redirect',
            'consumer' => 'redirect',
        ];
        yield 'QR display page' => [
            'setting' => 'qrTemplate',
            'environmentName' => 'SMARTLINK_MANAGER_TEST_QR_TEMPLATE',
            'templateName' => 'configured/qr',
            'consumer' => 'qr',
        ];
    }

    #[DataProvider('environmentTemplateVariants')]
    public function testEnvironmentTemplateVariantsKeepSetupAndRuntimeAligned(
        string $setting,
        string $environmentName,
        string $consumer,
        string $variant,
    ): void {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-template-variant');
        $configuredPath = 'configured/' . $consumer . '-' . $variant;
        $filePath = match ($variant) {
            'explicit-html' => $templatesPath . DIRECTORY_SEPARATOR . $configuredPath . '.html',
            'exact-extensionless' => $templatesPath . DIRECTORY_SEPARATOR . $configuredPath,
            'index' => $templatesPath . DIRECTORY_SEPARATOR . $configuredPath . DIRECTORY_SEPARATOR . 'index.twig',
            'site-override' => null,
            default => $templatesPath . DIRECTORY_SEPARATOR . $configuredPath . '.twig',
        };
        if ($variant === 'explicit-html') {
            $configuredPath .= '.html';
        }
        if ($variant === 'site-override') {
            foreach (Craft::$app->getSites()->getAllSites(false) as $site) {
                $sitePath = $templatesPath . DIRECTORY_SEPARATOR . $site->handle
                    . DIRECTORY_SEPARATOR . $configuredPath . '.twig';
                FileHelper::createDirectory(dirname($sitePath));
                self::assertNotFalse(file_put_contents($sitePath, "variant-{$consumer}-{$variant}"));
            }
        } else {
            self::assertIsString($filePath);
            FileHelper::createDirectory(dirname($filePath));
            self::assertNotFalse(file_put_contents($filePath, "variant-{$consumer}-{$variant}"));
        }

        $environmentExisted = array_key_exists($environmentName, $_SERVER);
        $environmentValue = $environmentExisted ? $_SERVER[$environmentName] : null;
        $_SERVER[$environmentName] = $configuredPath;

        try {
            $this->withSiteTemplatesPath($templatesPath, function() use (
                $setting,
                $environmentName,
                $consumer,
                $variant,
                $templatesPath,
            ): void {
                $this->withSettings([
                    $setting => '$' . $environmentName,
                    'enableAnalytics' => false,
                    'enableQrCodeCache' => false,
                    'enabledIntegrations' => [],
                ], function() use ($setting, $environmentName, $consumer, $variant, $templatesPath): void {
                    $settings = SmartLinkManager::$plugin->getSettings();
                    self::assertTrue($settings->validate());
                    self::assertSame('$' . $environmentName, $settings->{$setting});
                    $statuses = array_column(SmartLinkManager::$plugin->setup->templateStatuses($settings), null, 'setting');
                    self::assertTrue($statuses[$setting]['exists']);
                    self::assertSame($_SERVER[$environmentName], $statuses[$setting]['template']);

                    $response = $this->responseForConsumer($consumer);
                    Craft::$app->getView()->setTemplatesPath($templatesPath);
                    $this->formatResponse($response);
                    self::assertSame("variant-{$consumer}-{$variant}", $response->content);
                    self::assertSame('$' . $environmentName, $settings->{$setting});
                });
            });
        } finally {
            $this->restoreServerValue($environmentName, $environmentExisted, $environmentValue);
        }
    }

    /**
     * @return iterable<string, array{setting: string, environmentName: string, consumer: string, variant: string}>
     */
    public static function environmentTemplateVariants(): iterable
    {
        foreach ([
            'redirectTemplate' => ['redirect', 'SMARTLINK_MANAGER_TEST_REDIRECT_VARIANT'],
            'qrTemplate' => ['qr', 'SMARTLINK_MANAGER_TEST_QR_VARIANT'],
        ] as $setting => [$consumer, $environmentName]) {
            foreach (['global', 'site-override', 'explicit-html', 'exact-extensionless', 'index'] as $variant) {
                yield "{$consumer} {$variant}" => [
                    'setting' => $setting,
                    'environmentName' => $environmentName,
                    'consumer' => $consumer,
                    'variant' => $variant,
                ];
            }
        }
    }

    #[DataProvider('templateConsumers')]
    public function testDirectLiteralAndEmptyDefaultSettingsContinueToRender(
        string $setting,
        string $consumer,
        string $defaultTemplate,
    ): void {
        $this->assertConfiguredTemplateRenders(
            $setting,
            $consumer,
            'configured/' . $consumer . '.html',
            'direct-' . $consumer,
        );

        $settings = new Settings([$setting => '']);
        self::assertSame(
            $defaultTemplate,
            $setting === 'redirectTemplate'
                ? $settings->getResolvedRedirectTemplate()
                : $settings->getResolvedQrTemplate(),
        );
    }

    #[DataProvider('templateConsumers')]
    public function testEnvironmentChangesAreResolvedAtEachConsumption(
        string $setting,
        string $consumer,
        string $defaultTemplate,
    ): void {
        $environmentName = 'SMARTLINK_MANAGER_TEST_CHANGING_' . strtoupper($consumer);
        $environmentExisted = array_key_exists($environmentName, $_SERVER);
        $environmentValue = $environmentExisted ? $_SERVER[$environmentName] : null;
        $templatesPath = $this->createTrackedTempDirectory('smartlink-changing-template');
        foreach (['first', 'second'] as $marker) {
            $path = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . $marker . '.twig';
            FileHelper::createDirectory(dirname($path));
            self::assertNotFalse(file_put_contents($path, "{$consumer}-{$marker}"));
        }

        try {
            $this->withSiteTemplatesPath($templatesPath, function() use (
                $setting,
                $consumer,
                $environmentName,
                $templatesPath,
            ): void {
                $this->withSettings([
                    $setting => '$' . $environmentName,
                    'enableAnalytics' => false,
                    'enableQrCodeCache' => false,
                    'enabledIntegrations' => [],
                ], function() use ($consumer, $environmentName, $templatesPath): void {
                    foreach (['first', 'second'] as $marker) {
                        $_SERVER[$environmentName] = 'configured/' . $marker;
                        $response = $this->responseForConsumer($consumer);
                        Craft::$app->getView()->setTemplatesPath($templatesPath);
                        $this->formatResponse($response);
                        self::assertSame("{$consumer}-{$marker}", $response->content);
                    }
                });
            });
        } finally {
            $this->restoreServerValue($environmentName, $environmentExisted, $environmentValue);
        }
    }

    /**
     * @return iterable<string, array{setting: string, consumer: string, defaultTemplate: string}>
     */
    public static function templateConsumers(): iterable
    {
        yield 'redirect landing' => [
            'setting' => 'redirectTemplate',
            'consumer' => 'redirect',
            'defaultTemplate' => 'smartlink-manager/redirect',
        ];
        yield 'QR display' => [
            'setting' => 'qrTemplate',
            'consumer' => 'qr',
            'defaultTemplate' => 'smartlink-manager/qr',
        ];
    }

    private function assertConfiguredTemplateRenders(
        string $setting,
        string $consumer,
        string $templateName,
        string $marker,
    ): void {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-direct-template');
        $templatePath = $templatesPath . DIRECTORY_SEPARATOR . $templateName;
        FileHelper::createDirectory(dirname($templatePath));
        self::assertNotFalse(file_put_contents($templatePath, $marker));

        $this->withSiteTemplatesPath($templatesPath, function() use (
            $setting,
            $consumer,
            $templateName,
            $marker,
            $templatesPath,
        ): void {
            $this->withSettings([
                $setting => $templateName,
                'enableAnalytics' => false,
                'enableQrCodeCache' => false,
                'enabledIntegrations' => [],
            ], function() use ($consumer, $marker, $templatesPath): void {
                $response = $this->responseForConsumer($consumer);
                Craft::$app->getView()->setTemplatesPath($templatesPath);
                $this->formatResponse($response);
                self::assertSame($marker, $response->content);
            });
        });
    }

    private function responseForConsumer(string $consumer): Response
    {
        return match ($consumer) {
            'redirect' => $this->redirectResponse($this->activeLink()),
            'qr' => $this->qrDisplayResponse($this->qrLink()),
            default => throw new \LogicException("Unknown template consumer: {$consumer}"),
        };
    }

    private function activeLink(): SmartLink
    {
        return $this->seedSmartLink([
            'iosUrl' => 'https://example.com/ios',
            'trackAnalytics' => false,
        ]);
    }

    private function qrLink(): SmartLink
    {
        $link = $this->seedSmartLink(['trackAnalytics' => false]);
        $link->qrCodeEnabled = true;
        self::assertTrue(Craft::$app->getElements()->saveElement($link));

        return $link;
    }

    private function redirectResponse(SmartLink $link): Response
    {
        $this->resetWebRuntime();
        $this->swapPluginComponent('smartlink-manager', 'deviceDetection', new StubDeviceDetectionService());

        return (new RedirectController('redirect', SmartLinkManager::$plugin))->actionIndex($link->slug);
    }

    private function qrDisplayResponse(SmartLink $link): Response
    {
        $this->resetWebRuntime();

        return (new QrCodeController('qr-code', SmartLinkManager::$plugin))->actionDisplay($link->slug);
    }

    private function resetWebRuntime(): void
    {
        $this->originalRequest ??= Craft::$app->get('request');
        $this->originalResponse ??= Craft::$app->get('response');
        $this->originalView ??= Craft::$app->get('view');

        Craft::$app->set('request', new ConfiguredTemplateRequest());
        Craft::$app->set('response', new Response());
        Craft::$app->set('view', new View());
    }

    private function formatResponse(Response $response): void
    {
        $outputBufferLevel = ob_get_level();

        try {
            (new TemplateResponseFormatter())->format($response);
        } finally {
            while (ob_get_level() > $outputBufferLevel) {
                ob_end_clean();
            }
        }
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    private function withSiteTemplatesPath(string $templatesPath, callable $callback): mixed
    {
        $originalTemplatesPath = Craft::getAlias('@templates');
        self::assertIsString($originalTemplatesPath);
        Craft::setAlias('@templates', $templatesPath);

        try {
            return $callback();
        } finally {
            Craft::setAlias('@templates', $originalTemplatesPath);
        }
    }

    private function restoreServerValue(string $key, bool $existed, mixed $value): void
    {
        if ($existed) {
            $_SERVER[$key] = $value;
        } else {
            unset($_SERVER[$key]);
        }
    }
}

final class ConfiguredTemplateRequest extends Request
{
    public function getIsConsoleRequest(): bool
    {
        return false;
    }

    public function getIsCpRequest(): bool
    {
        return false;
    }

    public function getParam($name, $defaultValue = null): mixed
    {
        return $defaultValue;
    }

    public function getQueryParam($name, $defaultValue = null): mixed
    {
        return $defaultValue;
    }

    public function getQueryParams(): array
    {
        return [];
    }

    public function getUserIP(int $filterOptions = 0): ?string
    {
        return '203.0.113.42';
    }

    public function getUserAgent(): ?string
    {
        return 'LindemannRock Configured Template Test';
    }

    public function getReferrer(): ?string
    {
        return null;
    }
}
