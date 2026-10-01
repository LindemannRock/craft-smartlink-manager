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
use craft\db\Query;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\integrations\IntegrationInterface;
use lindemannrock\smartlinkmanager\integrations\SeomaticIntegration;
use lindemannrock\smartlinkmanager\services\IntegrationService;
use lindemannrock\smartlinkmanager\tests\TestCase;
use nystudio107\seomatic\helpers\MetaValue;
use nystudio107\seomatic\models\MetaScript;
use nystudio107\seomatic\models\MetaScriptContainer;
use nystudio107\seomatic\Seomatic;
use nystudio107\seomatic\services\MetaBundles;
use nystudio107\seomatic\services\MetaContainers;
use nystudio107\seomatic\services\SeoElements;
use nystudio107\seomatic\variables\SeomaticVariable;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @since 5.31.0
 */
#[CoversNothing]
class SeomaticTrackingTemplateTest extends TestCase
{
    #[DataProvider('dataLayerNames')]
    public function testConfiguredDataLayerReceivesAllSelectedEvents(string $configured, string $expected, bool $include): void
    {
        $this->swapPluginComponent('seomatic', 'metaContainers', new MetaContainers());
        Seomatic::$plugin->metaContainers->createMetaContainer(MetaScriptContainer::CONTAINER_TYPE, MetaScriptContainer::CONTAINER_TYPE . 'general');
        $script = Seomatic::$plugin->script->create(['key' => 'googleTagManager', 'vars' => ['dataLayerVariableName' => ['value' => 'dataLayer']]]);
        self::assertInstanceOf(MetaScript::class, $script);
        self::assertSame($script, Seomatic::$plugin->script->get('googleTagManager'));
        $vars = $script->vars;
        $originalInclude = $script->include;
        $environmentName = 'SMARTLINK_TEST_DATA_LAYER';
        $environmentExisted = array_key_exists($environmentName, $_SERVER);
        $environmentValue = $_SERVER[$environmentName] ?? null;
        $_SERVER[$environmentName] = ' envLinkEvents ';
        try {
            $script->vars['dataLayerVariableName']['value'] = $configured;
            $script->include = $include;
            $automatic = $this->executeTracking([
                'dataLayerName' => $expected,
                'search' => '?src=qr',
                'response' => ['autoRedirect' => true, 'goUrl' => 'https://links.example/automatic'],
            ]);
            self::assertSame(['already_queued', 'smart_links_qr_scan', 'smart_links_redirect'], array_column($automatic['events'], 'event'));
            self::assertSame([2100], $automatic['navigationTimes']);
            $manual = $this->executeTracking(['dataLayerName' => $expected, 'search' => '?src=qr&debug=1', 'click' => true]);
            self::assertSame(['already_queued', 'smart_links_qr_scan', 'smart_links_button_click'], array_column($manual['events'], 'event'));
            self::assertSame([300], $manual['navigationTimes']);
            if ($expected !== 'dataLayer') {
                self::assertSame([['event' => 'default_queue_untouched']], $automatic['defaultEvents']);
                self::assertSame([['event' => 'default_queue_untouched']], $manual['defaultEvents']);
            }
        } finally {
            $script->vars = $vars;
            $script->include = $originalInclude;
            if ($environmentExisted) {
                $_SERVER[$environmentName] = $environmentValue;
            } else {
                unset($_SERVER[$environmentName]);
            }
        }
    }

    public static function dataLayerNames(): iterable
    {
        yield 'default' => ['dataLayer', 'dataLayer', true];
        yield 'custom with whitespace' => [' linkEvents ', 'linkEvents', true];
        yield 'environment value with whitespace' => ['$SMARTLINK_TEST_DATA_LAYER', 'envLinkEvents', true];
        yield 'valid unicode identifier' => ['événements', 'événements', true];
        yield 'disabled GTM retains default' => ['linkEvents', 'dataLayer', false];
        yield 'empty setting retains default' => ['', 'dataLayer', true];
    }

    public function testPreparedSiteMetadataSelectsItsOwnDataLayerAndPreservesRuntimeOverrides(): void
    {
        $sites = array_slice(Craft::$app->getSites()->getAllSites(), 0, 2);
        self::assertCount(2, $sites);
        $this->swapPluginComponent('seomatic', 'metaContainers', new MetaContainers());
        $this->swapPluginComponent('seomatic', 'metaBundles', new MetaBundles());
        $this->swapPluginComponent('seomatic', 'seoElements', new SeoElements());
        $cache = Craft::$app->getCache();
        $matchedElement = Seomatic::$matchedElement;
        $seomaticVariable = Seomatic::$seomaticVariable;
        $loading = Seomatic::$loadingMetaContainers;
        $language = Seomatic::$language;
        $metaValueState = [MetaValue::$templateObjectVars, MetaValue::$templatePreviewVars, MetaValue::$view];
        $bundleIds = (new Query())->select('id')->from('{{%seomatic_metabundles}}')->column();
        $scripts = [];
        Seomatic::$seomaticVariable = new SeomaticVariable();
        Craft::$app->set('cache', new \yii\caching\DummyCache());
        try {
            foreach ($sites as $index => $site) {
                $bundle = Seomatic::$plugin->metaBundles->getGlobalMetaBundle($site->id);
                $script = $bundle->metaContainers[MetaScriptContainer::CONTAINER_TYPE . 'general']->data['googleTagManager'];
                self::assertInstanceOf(MetaScript::class, $script);
                $scripts[] = [$script, $script->vars, $script->include, $script->environment];
                $script->vars['dataLayerVariableName']['value'] = 'siteEvents' . $index;
                $script->include = true;
                $script->environment = [];
            }
            $this->withSettings(['enableAnalytics' => true, 'enabledIntegrations' => ['seomatic']], function() use ($sites): void {
                foreach ($sites as $index => $site) {
                    $link = $this->seedSmartLink(['siteId' => $site->id]);
                    $integration = new SeomaticIntegration();
                    self::assertTrue($integration->prepareMetadataForSmartLink($link));
                    $script = Seomatic::$plugin->script->get('googleTagManager');
                    self::assertInstanceOf(MetaScript::class, $script);
                    self::assertSame('siteEvents' . $index, $script->vars['dataLayerVariableName']['value']);
                    $result = $this->executeTracking(['dataLayerName' => 'siteEvents' . $index, 'search' => '?src=qr'], [], $link);
                    self::assertSame(['already_queued', 'smart_links_qr_scan'], array_column($result['events'], 'event'));
                    self::assertSame([['event' => 'default_queue_untouched']], $result['defaultEvents']);
                    // SEOmatic allows template-time script variables; rendering must not reload the bundle.
                    $script->vars['dataLayerVariableName']['value'] = 'runtimeEvents' . $index;
                    $override = $this->executeTracking(['dataLayerName' => 'runtimeEvents' . $index, 'search' => '?src=qr'], [], $link);
                    self::assertSame(['already_queued', 'smart_links_qr_scan'], array_column($override['events'], 'event'));
                }
            });
        } finally {
            foreach ($scripts as [$script, $vars, $include, $environment]) {
                $script->vars = $vars;
                $script->include = $include;
                $script->environment = $environment;
            }
            $createdIds = array_diff((new Query())->select('id')->from('{{%seomatic_metabundles}}')->column(), $bundleIds);
            if ($createdIds !== []) {
                Craft::$app->getDb()->createCommand()->delete('{{%seomatic_metabundles}}', ['id' => array_values($createdIds)])->execute();
            }
            Craft::$app->set('cache', $cache);
            Seomatic::$matchedElement = $matchedElement;
            Seomatic::$seomaticVariable = $seomaticVariable;
            Seomatic::$loadingMetaContainers = $loading;
            Seomatic::$language = $language;
            [MetaValue::$templateObjectVars, MetaValue::$templatePreviewVars, MetaValue::$view] = $metaValueState;
        }
    }

    public function testLandingArrivalDoesNotRecordARedirect(): void
    {
        $result = $this->executeTracking();
        self::assertSame(['already_queued'], $result['arrivalEvents']);
        self::assertSame(['already_queued'], array_column($result['events'], 'event'));
        self::assertSame([], $result['navigations']);
    }

    public function testQrArrivalAndAutomaticNavigationAreSeparateEvents(): void
    {
        $goUrl = 'https://links.example/7/actions/smartlink-manager/redirect/go/campaign/auto?src=qr';
        $result = $this->executeTracking([
            'search' => '?src=qr',
            'response' => ['autoRedirect' => true, 'goUrl' => $goUrl],
        ]);
        self::assertSame(['already_queued', 'smart_links_qr_scan'], $result['arrivalEvents']);
        self::assertSame($result['arrivalEvents'], $result['beforeTimers']);
        self::assertSame(['smart_links_qr_scan', 'smart_links_redirect', 'navigate'], $result['timeline']);
        self::assertSame([$goUrl], $result['navigations']);
        self::assertSame([100, 2000], $result['timerDelays']);
        self::assertSame('qr', $result['events'][1]['smart_link']['source']);
        self::assertSame('qr', $result['events'][2]['smart_link']['source']);
        self::assertSame('no-store', $result['requests'][0]['options']['cache']);
        self::assertStringContainsString('src=qr', $result['requests'][0]['url']);
    }

    public function testNormalAutomaticNavigationRecordsOneRedirectBeforeLeaving(): void
    {
        $result = $this->executeTracking([
            'response' => ['autoRedirect' => true, 'goUrl' => 'https://links.example/tracked-auto'],
        ]);
        self::assertSame(['already_queued'], $result['beforeTimers']);
        self::assertSame(['smart_links_redirect', 'navigate'], $result['timeline']);
        self::assertSame('direct', $result['events'][1]['smart_link']['source']);
        self::assertSame('auto', $result['events'][1]['smart_link']['platform']);
    }

    #[DataProvider('automaticTrackingSelections')]
    public function testAutomaticTrackingWaitsOnlyForApplicableQueuedEvents(array $enabled, string $search, bool $waits): void
    {
        $goUrl = 'https://links.example/7/actions/smartlink-manager/redirect/go/campaign/auto';
        $result = $this->executeTracking([
            'search' => $search,
            'response' => ['autoRedirect' => true, 'goUrl' => $goUrl],
            'checkpoints' => [99, 100, 2099, 2100, 4100],
        ], ['seomaticTrackingEvents' => $enabled, 'seomaticEventPrefix' => 'custom_campaign']);

        self::assertSame([], $result['checkpoints'][0]['navigations']);
        self::assertSame($waits ? [] : [$goUrl], $result['checkpoints'][1]['navigations']);
        self::assertSame($waits ? [] : [$goUrl], $result['checkpoints'][2]['navigations']);
        self::assertSame([$goUrl], $result['checkpoints'][3]['navigations']);
        self::assertSame([$goUrl], $result['checkpoints'][4]['navigations']);
        self::assertSame($waits ? [100, 2000] : [100], $result['timerDelays']);
        self::assertSame($waits ? [2100] : [100], $result['navigationTimes']);
        $expectedEvents = [];
        if ($search === '?src=qr' && in_array('qr_scan', $enabled, true)) {
            $expectedEvents[] = ['event' => 'custom_campaign_qr_scan', 'time' => 0];
        }
        if (in_array('redirect', $enabled, true)) {
            $expectedEvents[] = ['event' => 'custom_campaign_redirect', 'time' => 100];
        }
        self::assertSame($expectedEvents, $result['eventTimes']);
    }

    public static function automaticTrackingSelections(): iterable
    {
        yield 'redirect selected direct visit' => [['redirect'], '', true];
        yield 'redirect selected QR visit' => [['redirect'], '?src=qr', true];
        yield 'QR-only tagged visit' => [['qr_scan'], '?src=qr', true];
        yield 'QR-only ordinary visit' => [['qr_scan'], '', false];
        yield 'both automatic events' => [['redirect', 'qr_scan'], '?src=qr', true];
        yield 'all events' => [['redirect', 'qr_scan', 'button_click'], '?src=qr', true];
        yield 'manual tracking only' => [['button_click'], '?src=qr', false];
        yield 'all events disabled' => [[], '?src=qr', false];
    }

    #[DataProvider('manualChoicesDuringAutomaticNavigation')]
    public function testManualChoicePreventsAutomaticNavigationFromOverridingIt(int $clickAt, bool $trackButton): void
    {
        $buttonUrl = 'https://links.example/actions/smartlink-manager/redirect/go/campaign/android';
        $enabled = ['redirect'];
        if ($trackButton) {
            $enabled[] = 'button_click';
        }
        $result = $this->executeTracking([
            'response' => ['autoRedirect' => true, 'goUrl' => 'https://links.example/automatic-ios'],
            'buttonUrl' => $buttonUrl,
            'clickAt' => $clickAt,
            'checkpoints' => [$clickAt + 299, $clickAt + 300, 4100],
        ], ['seomaticTrackingEvents' => $enabled]);
        self::assertSame([$buttonUrl], $result['navigations']);
        self::assertSame([$clickAt + ($trackButton ? 300 : 0)], $result['navigationTimes']);
        self::assertSame($trackButton ? [] : [$buttonUrl], $result['checkpoints'][0]['navigations']);
        self::assertSame([$buttonUrl], $result['checkpoints'][1]['navigations']);
        self::assertSame([$buttonUrl], $result['checkpoints'][2]['navigations']);
        $expected = ['already_queued'];
        if ($clickAt > 100) {
            $expected[] = 'smart_links_redirect';
        }
        if ($trackButton) {
            $expected[] = 'smart_links_button_click';
        }
        self::assertSame($expected, array_column($result['events'], 'event'));
        self::assertSame($trackButton, $result['clickPrevented']);
    }

    public static function manualChoicesDuringAutomaticNavigation(): iterable
    {
        yield 'tracked button before automatic initiation' => [50, true];
        yield 'tracked button during grace' => [500, true];
        yield 'untracked button before automatic initiation' => [50, false];
        yield 'untracked button during grace' => [500, false];
    }

    public function testPausedQrLandingRecordsOnlyArrivalAndDoesNotResolveOrNavigate(): void
    {
        $result = $this->executeTracking(['search' => '?src=qr&debug=1']);
        self::assertSame(['already_queued', 'smart_links_qr_scan'], array_column($result['events'], 'event'));
        self::assertSame([], $result['requests']);
        self::assertSame([], $result['navigations']);
        self::assertSame([], $result['timerDelays']);
    }

    #[DataProvider('nonNavigatingResponses')]
    public function testResolverFailuresAndNoDestinationDoNotRecordRedirect(array $input): void
    {
        $result = $this->executeTracking($input);
        self::assertSame(['already_queued'], array_column($result['events'], 'event'));
        self::assertSame([], $result['navigations']);
        self::assertSame([], $result['timerDelays']);
    }

    public static function nonNavigatingResponses(): iterable
    {
        yield 'desktop/no automatic target' => [['response' => ['autoRedirect' => false, 'goUrl' => null]]];
        yield 'missing destination' => [['response' => ['autoRedirect' => true, 'goUrl' => null]]];
        yield 'HTTP failure' => [['httpOk' => false]];
        yield 'network failure' => [['fetchFailure' => true]];
    }

    #[DataProvider('eventSelections')]
    public function testEventSwitchesIndependentlyControlArrivalNavigationAndClicks(array $enabled): void
    {
        $settings = ['seomaticTrackingEvents' => $enabled];
        $result = $this->executeTracking([
            'search' => '?src=qr',
            'response' => ['autoRedirect' => true, 'goUrl' => 'https://links.example/tracked-auto'],
        ], $settings);
        $expected = ['already_queued'];
        if (in_array('qr_scan', $enabled, true)) {
            $expected[] = 'smart_links_qr_scan';
        }
        if (in_array('redirect', $enabled, true)) {
            $expected[] = 'smart_links_redirect';
        }
        self::assertSame($expected, array_column($result['events'], 'event'));
        self::assertSame(['https://links.example/tracked-auto'], $result['navigations']);

        $click = $this->executeTracking(['click' => true], $settings);
        $buttonEnabled = in_array('button_click', $enabled, true);
        self::assertSame($buttonEnabled ? ['already_queued', 'smart_links_button_click'] : ['already_queued'], array_column($click['events'], 'event'));
        self::assertSame($buttonEnabled, $click['clickPrevented']);
        self::assertSame($buttonEnabled, $click['clickListener']);
        self::assertSame($buttonEnabled ? [300] : [], $click['timerDelays']);
        self::assertCount(1, $click['navigations']);
    }

    public static function eventSelections(): iterable
    {
        $events = ['redirect', 'button_click', 'qr_scan'];
        for ($mask = 0; $mask < 8; $mask++) {
            $enabled = [];
            foreach ($events as $index => $event) {
                if ($mask & (1 << $index)) {
                    $enabled[] = $event;
                }
            }
            yield implode('+', $enabled) ?: 'none' => [$enabled];
        }
    }

    #[DataProvider('trackedButtonUrls')]
    public function testManualClicksPreserveCustomPrefixSourceAndTrackedPlatform(string $url, string $platform): void
    {
        $result = $this->executeTracking([
            'search' => '?src=qr&debug=1',
            'click' => true,
            'buttonUrl' => $url,
        ], ['seomaticEventPrefix' => 'custom_campaign']);
        self::assertSame(['already_queued', 'custom_campaign_qr_scan', 'custom_campaign_button_click'], array_column($result['events'], 'event'));
        self::assertSame($platform, $result['events'][2]['smart_link']['platform']);
        self::assertSame('qr', $result['events'][2]['smart_link']['source']);
        self::assertSame("Campaign 'quoted' & mobile", $result['events'][2]['smart_link']['title']);
        self::assertSame([$url], $result['navigations']);
    }

    public static function trackedButtonUrls(): iterable
    {
        yield 'custom domain and path route' => ['https://links.example/ar/actions/smartlink-manager/redirect/go/campaign/android?src=qr', 'android'];
        yield 'query route' => ['https://links.example/index.php?action=smartlink-manager/redirect/go&slug=campaign&platform=ios', 'ios'];
        yield 'missing platform' => ['https://links.example/actions/smartlink-manager/redirect/go?slug=campaign', 'unknown'];
    }

    public function testDisabledIntegrationLeavesNavigationAndButtonsUnchanged(): void
    {
        $result = $this->executeTracking([
            'search' => '?src=qr',
            'response' => ['autoRedirect' => true, 'goUrl' => 'https://links.example/tracked-auto'],
            'click' => true,
        ], ['enabledIntegrations' => []]);
        self::assertSame(['already_queued'], array_column($result['events'], 'event'));
        self::assertFalse($result['clickPrevented']);
        self::assertFalse($result['clickListener']);
        self::assertCount(2, $result['navigations']);
        self::assertSame([100], $result['timerDelays']);
    }

    public function testDisabledAnalyticsSuppressesIntegrationEvents(): void
    {
        $result = $this->executeTracking(['search' => '?src=qr', 'click' => true], ['enableAnalytics' => false]);
        self::assertSame(['already_queued'], array_column($result['events'], 'event'));
        self::assertFalse($result['clickPrevented']);
    }

    public function testUnavailableSeomaticDoesNotBlockAutomaticNavigation(): void
    {
        $this->swapPluginComponent('smartlink-manager', 'integration', new class() extends IntegrationService {
            public function getIntegration(string $handle): ?IntegrationInterface
            {
                return new class() extends SeomaticIntegration {
                    public function isAvailable(): bool
                    {
                        return false;
                    }
                };
            }
        });
        $result = $this->executeTracking([
            'search' => '?src=qr',
            'response' => ['autoRedirect' => true, 'goUrl' => 'https://links.example/tracked-auto'],
        ]);
        self::assertSame(['already_queued'], array_column($result['events'], 'event'));
        self::assertSame(['https://links.example/tracked-auto'], $result['navigations']);
        self::assertFalse($result['clickListener']);
        self::assertSame([100], $result['timerDelays']);
        self::assertSame([100], $result['navigationTimes']);
    }

    public function testLegacyQrDisplayHelpersRemainCallableWithoutEmittingEvents(): void
    {
        $this->withSettings(['enableAnalytics' => true, 'enabledIntegrations' => ['seomatic']], function(): void {
            $link = new SmartLink();
            self::assertNull($link->renderQrSeomaticTracking());
            self::assertNull($link->renderSeomaticTracking('qr_scan'));
            self::assertNull($link->renderSeomaticTracking());
        });
    }

    private function executeTracking(array $input = [], array $settings = [], ?SmartLink $link = null): array
    {
        return $this->withSettings(array_merge([
            'enableAnalytics' => true,
            'enabledIntegrations' => ['seomatic'],
            'seomaticEventPrefix' => 'smart_links',
            'seomaticTrackingEvents' => ['redirect', 'button_click', 'qr_scan'],
        ], $settings), function() use ($input, $link): array {
            $link ??= new SmartLink(['slug' => 'campaign', 'title' => "Campaign 'quoted' & mobile"]);
            $link->setAutoRedirectScriptUrl('https://links.example/7/actions/smartlink-manager/redirect/auto/campaign?site=ar');
            $html = (string)$link->renderRedirectSeomaticTracking() . (string)$link->renderRedirectScript(true);
            self::assertNotSame('', $html);
            $process = new \Symfony\Component\Process\Process(['node', dirname(__DIR__) . '/js/run-seomatic-tracking.mjs']);
            $process->setInput(json_encode(array_merge($input, ['html' => $html]), JSON_THROW_ON_ERROR));
            $process->setTimeout(10);
            try {
                $process->run();
                self::assertTrue($process->isSuccessful(), $process->getErrorOutput());
                return json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);
            } finally {
                if ($process->isRunning()) {
                    $process->stop(0);
                }
            }
        });
    }

    public function testRedirectTemplatesOwnTrackedAutoNavigation(): void
    {
        $pluginTemplate = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/redirect.twig');
        $redirectScriptTemplate = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/_frontend/redirect.twig');

        $this->assertStringContainsString('smartLink.renderRedirectScript()', $pluginTemplate);
        $this->assertStringContainsString('smartLink.renderRedirectSeomaticTracking()', $pluginTemplate);
        $this->assertStringContainsString('{{ goUrls.ios }}', $pluginTemplate);
        $this->assertStringContainsString('{{ goUrls.fallback }}', $pluginTemplate);
        $this->assertStringNotContainsString('{{ goUrls.auto }}', $pluginTemplate);
        $this->assertStringNotContainsString('{% if autoRedirect %}', $pluginTemplate);
        $this->assertStringNotContainsString('var goUrl = {{ goUrl|json_encode|raw }};', $pluginTemplate);
        $this->assertStringNotContainsString('window.location.replace(goUrl);', $pluginTemplate);
        $this->assertStringNotContainsString('fetch(resolverUrl.toString(), {', $pluginTemplate);
        $this->assertStringNotContainsString("actionUrl('smartlink-manager/redirect/go'", $pluginTemplate);
        $this->assertStringNotContainsString("smartLink.renderSeomaticTracking(eventType ?? 'redirect')", $pluginTemplate);
        $this->assertStringNotContainsString('renderRedirectSeomaticTracking is defined', $pluginTemplate);
        $this->assertStringNotContainsString('DEBUG MODE', $pluginTemplate);
        $this->assertStringNotContainsString('debugMode', $pluginTemplate);

        $this->assertStringContainsString('var resolverUrl = new URL({{ autoRedirectUrl|json_encode|raw }}, window.location.href);', $redirectScriptTemplate);
        $this->assertStringContainsString('fetch(resolverUrl.toString(), {', $redirectScriptTemplate);
        $this->assertStringContainsString('cache: \'no-store\'', $redirectScriptTemplate);
        $this->assertStringContainsString('window.location.replace(data.goUrl);', $redirectScriptTemplate);
        $this->assertStringContainsString('{% if skipDebugRedirect %}', $redirectScriptTemplate);
    }

    public function testQrDisplayTemplateContainsNoScanTracking(): void
    {
        $template = (string)file_get_contents(dirname(__DIR__, 2) . '/src/templates/qr.twig');

        $this->assertStringNotContainsString('smartLink.renderQrSeomaticTracking()', $template);
        $this->assertStringNotContainsString("smartLink.renderSeomaticTracking('qr_scan')", $template);
        $this->assertStringNotContainsString('renderQrSeomaticTracking is defined', $template);
        $this->assertStringNotContainsString('DEBUG MODE', $template);
        $this->assertStringNotContainsString('debugMode', $template);
        $this->assertStringContainsString('{{ qrCodeSvg|raw }}', $template);
        $this->assertStringContainsString('data:image/png;base64,{{ qrCodeData }}', $template);
        $this->assertStringContainsString('smartLink.getQrCodeUrl({ download: 1 })', $template);
        $this->assertStringNotContainsString('smartLink.getQrCodeUrl({ size:', $template);
    }
}
