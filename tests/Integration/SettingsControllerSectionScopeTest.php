<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\base\helpers\SettingsPostHelper;
use lindemannrock\smartlinkmanager\controllers\SettingsController;
use lindemannrock\smartlinkmanager\models\Settings;
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

    public function testStaleDefaultQrLogoIdNormalizesToNull(): void
    {
        $controller = new SettingsController('settings', SmartLinkManager::$plugin);
        $method = new \ReflectionMethod($controller, 'normalizeDefaultQrLogoId');
        $settings = new Settings();

        self::assertNull($method->invoke($controller, 54042, $settings));
    }

    public function testEmptyDefaultQrLogoElementSelectPayloadDoesNotAddIntegerError(): void
    {
        $controller = new SettingsController('settings', SmartLinkManager::$plugin);
        $method = new \ReflectionMethod($controller, 'normalizeDefaultQrLogoId');
        $settings = new Settings();

        $result = SettingsPostHelper::apply(
            model: $settings,
            postedValues: ['defaultQrLogoId' => ['']],
            allowedAttributes: ['defaultQrLogoId'],
            adapters: [
                'defaultQrLogoId' => fn(mixed $value): ?int => $method->invoke($controller, $value, $settings),
            ],
        );

        self::assertFalse($result->hasErrors);
        self::assertSame([], $settings->getErrors('defaultQrLogoId'));
        self::assertNull($settings->defaultQrLogoId);
    }

    public function testQrSettingsTemplateDoesNotPassNullLogoElement(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/templates/settings/qr-code.twig');
        self::assertIsString($source);

        self::assertStringContainsString('{% set selectedLogo = settings.defaultQrLogoId ? craft.assets.id(settings.defaultQrLogoId).one() : null %}', $source);
        self::assertStringContainsString('elements: selectedLogo ? [selectedLogo] : [],', $source);
        self::assertStringNotContainsString('elements: settings.defaultQrLogoId ? [craft.assets.id(settings.defaultQrLogoId).one()] : [],', $source);
    }

    public function testSetupCompleteInfoBoxUsesConfiguredPluginName(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/templates/setup.twig');
        self::assertIsString($source);

        self::assertStringContainsString('{% set smartlinkFullNameHtml = smartlinkHelper.fullName|e %}', $source);
        self::assertStringContainsString('smartlinkFullNameHtml: smartlinkFullNameHtml,', $source);
        self::assertStringContainsString("'{pluginName} is ready to create public smart links and QR landing pages.'|t('smartlink-manager', {", $source);
        self::assertStringContainsString('pluginName: smartlinkFullNameHtml', $source);
        self::assertStringNotContainsString("'SmartLink Manager is ready to create public smart links and QR landing pages.'|t('smartlink-manager')", $source);
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

    public function testInstructionPlaceholdersEscapeConfiguredPluginName(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $files = [
            '/src/templates/settings/general.twig',
            '/src/templates/settings/behavior.twig',
            '/src/templates/settings/analytics.twig',
            '/src/templates/_components/fields/SmartLinkField/settings.twig',
            '/src/templates/smartlinks/_partials/fields.twig',
        ];

        foreach ($files as $file) {
            $source = file_get_contents($pluginRoot . $file);
            self::assertIsString($source);
            self::assertDoesNotMatchRegularExpression('/instructions:.*smartlinkHelper\\.(?:lowerDisplayName|pluralLowerDisplayName|fullName|displayName)/', $source, $file);
        }
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

    public function testRawInfoBoxMessagesEscapeDynamicPlaceholders(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $files = [
            '/src/templates/settings/cache.twig' => [
                'contains' => [
                    '{% set smartlinkCacheBasePathHtml = smartlinkHelper.cacheBasePath|e %}',
                    'path: smartlinkCacheBasePathHtml',
                ],
                'notContains' => [
                    'path: smartlinkHelper.cacheBasePath',
                ],
            ],
            '/src/templates/settings/integrations.twig' => [
                'contains' => [
                    '{% set smartlinkLowerNameHtml = smartlinkHelper.lowerDisplayName|e %}',
                    '{% set smartlinkPluralLowerNameHtml = smartlinkHelper.pluralLowerDisplayName|e %}',
                    '{% set seomaticPluginNameHtml = seomaticPluginName|e %}',
                    '{% set rmPluginNameHtml = rmPluginName|e %}',
                    'pluginName: seomaticPluginNameHtml',
                    'pluginName: smartlinkPluralLowerNameHtml',
                    'pluginName: smartlinkLowerNameHtml',
                    'rmPluginName: rmPluginNameHtml',
                ],
                'notContains' => [
                    'message: \'<strong>\' ~ "Note"|t(\'smartlink-manager\') ~ \':</strong> \' ~ "No tracking scripts are currently configured in {pluginName}. Events will be queued but not sent until you configure GTM or Google Analytics in {pluginName}."|t(\'smartlink-manager\', { pluginName: seomaticPluginName })',
                    ' ~ "View and manage all redirects ({pluginName} + regular pages) in one place"|t(\'smartlink-manager\', {pluginName: smartlinkHelper.pluralLowerDisplayName}) ~ ',
                    ' ~ "See how many people try to access deleted or changed {pluginName}, their devices, browsers, and countries"|t(\'smartlink-manager\', {pluginName: smartlinkHelper.pluralLowerDisplayName}) ~ ',
                    ' ~ "Redirects persist even if {pluginName} is deleted, preventing broken links permanently"|t(\'smartlink-manager\', {pluginName: smartlinkHelper.lowerDisplayName}) ~ ',
                    ' ~ "{rmPluginName} shows which plugin created each redirect for better organization"|t(\'smartlink-manager\', {rmPluginName: rmPluginName}) ~ ',
                ],
            ],
            '/src/templates/utilities/index.twig' => [
                'contains' => [
                    '{% set selectedSiteLabelHtml = selectedSiteLabel|e %}',
                    '~ selectedSiteLabelHtml',
                ],
                'notContains' => [
                    '~ selectedSiteLabel,',
                ],
            ],
        ];

        foreach ($files as $file => $expectations) {
            $source = file_get_contents($pluginRoot . $file);
            self::assertIsString($source);

            foreach ($expectations['contains'] as $expected) {
                self::assertStringContainsString($expected, $source, $file);
            }

            foreach ($expectations['notContains'] as $unexpected) {
                self::assertStringNotContainsString($unexpected, $source, $file);
            }
        }
    }
}
