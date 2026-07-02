<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\controllers\SettingsController;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.29.0
 */
#[CoversClass(SettingsController::class)]
final class SettingsControllerSectionScopeTest extends TestCase
{
    public function testSettingsSectionsMatchRenderedFormScopes(): void
    {
        $controller = new SettingsController('settings', SmartLinkManager::$plugin);
        $method = new \ReflectionMethod($controller, '_validationAttributesForSection');

        $expected = [
            'general' => [
                'pluginName',
                'enabledSites',
                'usePrefix',
                'slugPrefix',
                'qrPrefix',
                'smartlinkBaseUrl',
                'redirectTemplate',
                'qrTemplate',
                'imageVolumeUid',
                'logLevel',
            ],
            'analytics' => [
                'enableAnalytics',
                'enableGeoDetection',
                'geoProvider',
                'geoApiKey',
                'anonymizeIpAddress',
                'analyticsRetention',
            ],
            'integrations' => [
                'enabledIntegrations',
                'seomaticTrackingEvents',
                'seomaticEventPrefix',
                'redirectManagerEvents',
            ],
            'qr-code' => [
                'defaultQrSize',
                'defaultQrFormat',
                'defaultQrColor',
                'defaultQrBgColor',
                'defaultQrMargin',
                'qrModuleStyle',
                'qrEyeStyle',
                'qrEyeColor',
                'enableQrLogo',
                'qrLogoVolumeUid',
                'defaultQrLogoId',
                'qrLogoSize',
                'defaultQrErrorCorrection',
                'enableQrDownload',
                'qrDownloadFilename',
            ],
            'behavior' => [
                'notFoundRedirectUrl',
            ],
            'interface' => [
                'itemsPerPage',
                'timeFormat',
                'monthFormat',
                'dateOrder',
                'dateSeparator',
                'showSeconds',
                'defaultDateRange',
                'exportsCsv',
                'exportsJson',
                'exportsExcel',
            ],
            'cache' => [
                'cacheStorageMethod',
                'enableQrCodeCache',
                'qrCodeCacheDuration',
                'cacheDeviceDetection',
                'deviceDetectionCacheDuration',
            ],
        ];

        foreach ($expected as $section => $attributes) {
            self::assertSame($attributes, $method->invoke($controller, $section), "Unexpected {$section} settings scope.");
        }
    }

    public function testRecentClicksDestinationTitleUsesAttributeEscaping(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $source = file_get_contents($pluginRoot . '/src/web/assets/analytics/src/analytics.js');
        $dist = file_get_contents($pluginRoot . '/src/web/assets/analytics/dist/analytics.js');
        self::assertIsString($source);
        self::assertIsString($dist);

        self::assertStringContainsString('function escAttr(str)', $source);
        self::assertStringContainsString('title="\' + escAttr(destUrl)', $source);
        self::assertStringNotContainsString('title="\' + esc(destUrl)', $source);
        self::assertStringContainsString('&quot;', $dist);
        self::assertStringContainsString('&#039;', $dist);
    }

    public function testRawSettingsInfoBoxesEscapeConfiguredPluginName(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $general = file_get_contents($pluginRoot . '/src/templates/settings/general.twig');
        $integrations = file_get_contents($pluginRoot . '/src/templates/settings/integrations.twig');
        self::assertIsString($general);
        self::assertIsString($integrations);

        self::assertStringContainsString('{% set smartlinkFullNameHtml = smartlinkHelper.fullName|e %}', $general);
        self::assertStringContainsString('{% set smartlinkDisplayNameHtml = smartlinkHelper.displayName|e %}', $general);
        self::assertStringContainsString('smartName: smartlinkFullNameHtml', $general);
        self::assertStringContainsString('singularName: smartlinkDisplayNameHtml', $general);
        self::assertStringNotContainsString('smartName: smartlinkHelper.fullName', $general);
        self::assertStringNotContainsString('message: "URL Prefix is disabled. {singularName} URLs will be generated as root paths like <code>/your-link</code>."|t(\'smartlink-manager\', {singularName: smartlinkHelper.displayName})|raw', $general);
        self::assertStringNotContainsString('singularName: smartlinkHelper.displayName, siteHandle:', $general);

        self::assertStringContainsString('{% set smartlinkFullNameHtml = smartlinkHelper.fullName|e %}', $integrations);
        self::assertStringContainsString('pluginName: smartlinkFullNameHtml', $integrations);
        self::assertStringContainsString('~ smartlinkFullNameHtml ~', $integrations);
        self::assertStringNotContainsString("|t('smartlink-manager', {pluginName: smartlinkHelper.fullName}) ~", $integrations);
        self::assertStringNotContainsString('~ smartlinkHelper.fullName ~', $integrations);
    }
}
