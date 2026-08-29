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
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\FileHelper;
use craft\models\Site;
use craft\services\Sites;
use craft\web\View;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\services\SetupService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use yii\base\Event;

/**
 * Pins setup-readiness detection.
 *
 * Covers the template-existence resolution (which must match how Craft resolves
 * the frontend templates at render time — any configured extension plus
 * directory-style index templates) and the IP salt readiness gate (which must
 * mirror the runtime hash gate).
 *
 * @since 5.27.0
 */
final class SetupServiceTest extends TestCase
{
    private SetupService $setup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setup = SmartLinkManager::$plugin->setup;
    }

    public function testTemplateStatusDetectsExtensionAndIndexVariants(): void
    {
        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();
        $dir = 'smartlink-test-setup-' . bin2hex(random_bytes(4));
        $absDir = $templatesPath . DIRECTORY_SEPARATOR . $dir;
        FileHelper::createDirectory($absDir);

        try {
            // Direct .twig file: templates/<dir>/redirect.twig
            file_put_contents($absDir . DIRECTORY_SEPARATOR . 'redirect.twig', '{# test #}');
            // Direct .html file: templates/<dir>/alt.html
            file_put_contents($absDir . DIRECTORY_SEPARATOR . 'alt.html', '<!-- test -->');
            // Directory-style index template: templates/<dir>/qr/index.twig
            FileHelper::createDirectory($absDir . DIRECTORY_SEPARATOR . 'qr');
            file_put_contents($absDir . DIRECTORY_SEPARATOR . 'qr' . DIRECTORY_SEPARATOR . 'index.twig', '{# test #}');

            $this->withSettings([
                'redirectTemplate' => $dir . '/redirect',
                'qrTemplate' => $dir . '/qr',
            ], function(): void {
                $settings = SmartLinkManager::$plugin->getSettings();
                $bySetting = $this->indexBySetting($this->setup->templateStatuses($settings));

                self::assertTrue(
                    $bySetting['redirectTemplate']['exists'],
                    'A .twig template must be detected as present.',
                );
                self::assertTrue(
                    $bySetting['qrTemplate']['exists'],
                    'A directory-style index.twig template must be detected as present.',
                );
            });

            if (in_array('html', Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions, true)) {
                $this->withSettings([
                    'redirectTemplate' => $dir . '/alt',
                ], function(): void {
                    $settings = SmartLinkManager::$plugin->getSettings();
                    $bySetting = $this->indexBySetting($this->setup->templateStatuses($settings));

                    self::assertTrue(
                        $bySetting['redirectTemplate']['exists'],
                        'A .html template must be detected when html is a configured template extension.',
                    );
                });
            }
        } finally {
            FileHelper::removeDirectory($absDir);
        }
    }

    public function testTemplateStatusReportsMissingTemplate(): void
    {
        $dir = 'smartlink-test-setup-missing-' . bin2hex(random_bytes(4));

        $this->withSettings([
            'redirectTemplate' => $dir . '/redirect',
        ], function(): void {
            $settings = SmartLinkManager::$plugin->getSettings();
            $bySetting = $this->indexBySetting($this->setup->templateStatuses($settings));

            self::assertFalse(
                $bySetting['redirectTemplate']['exists'],
                'A template with no matching file must be reported as missing.',
            );
        });
    }

    public function testExplicitAndExtensionlessPathsMatchCraftResolution(): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-exact');
        FileHelper::createDirectory($templatesPath . DIRECTORY_SEPARATOR . 'configured');
        self::assertNotFalse(file_put_contents(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.html',
            '<!-- redirect -->',
        ));
        self::assertNotFalse(file_put_contents(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'qr',
            'extensionless qr',
        ));

        $statuses = $this->withSiteTemplatesPath(
            $templatesPath,
            fn(): array => $this->setup->templateStatuses(new Settings([
                'redirectTemplate' => 'configured/redirect.html',
                'qrTemplate' => 'configured/qr',
            ])),
        );
        $bySetting = $this->indexBySetting($statuses);

        self::assertTrue($bySetting['redirectTemplate']['exists']);
        self::assertSame('templates/configured/redirect.html', $bySetting['redirectTemplate']['destination']);
        self::assertTrue($bySetting['redirectTemplate']['destinationExists']);
        self::assertTrue($bySetting['qrTemplate']['exists']);
        self::assertSame('templates/configured/qr.twig', $bySetting['qrTemplate']['destination']);
        self::assertFalse($bySetting['qrTemplate']['destinationExists']);
    }

    #[DataProvider('templateSettings')]
    public function testExplicitTwigIsNotSatisfiedBySameStemHtml(string $setting): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-explicit-twig');
        FileHelper::createDirectory($templatesPath . DIRECTORY_SEPARATOR . 'configured');
        self::assertNotFalse(file_put_contents(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'template.html',
            '<!-- same stem -->',
        ));

        $statuses = $this->withSiteTemplatesPath(
            $templatesPath,
            fn(): array => $this->setup->templateStatuses(new Settings([
                $setting => 'configured/template.twig',
            ])),
        );
        $bySetting = $this->indexBySetting($statuses);

        self::assertFalse($bySetting[$setting]['exists']);
        self::assertSame('templates/configured/template.twig', $bySetting[$setting]['destination']);
        self::assertFalse($bySetting[$setting]['destinationExists']);
        self::assertSame(
            '<!-- same stem -->',
            file_get_contents($templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'template.html'),
        );
    }

    /**
     * @return iterable<string, array{setting: string}>
     */
    public static function templateSettings(): iterable
    {
        yield 'redirect template' => ['setting' => 'redirectTemplate'];
        yield 'QR template' => ['setting' => 'qrTemplate'];
    }

    public function testEveryEnabledSiteMustResolveEachTemplate(): void
    {
        [$firstSite, $secondSite] = $this->twoSites();
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-sites');
        $settings = new Settings([
            'enabledSites' => [(int)$firstSite->id, (int)$secondSite->id],
            'redirectTemplate' => 'configured/redirect',
            'qrTemplate' => 'configured/qr',
        ]);

        foreach ([$firstSite, $secondSite] as $site) {
            foreach (['redirect', 'qr'] as $template) {
                $path = $templatesPath . DIRECTORY_SEPARATOR . $site->handle
                    . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . $template . '.twig';
                FileHelper::createDirectory(dirname($path));
                self::assertNotFalse(file_put_contents($path, "{$site->handle}-{$template}"));
            }
        }

        $this->withCraftSites([$firstSite, $secondSite], function() use ($firstSite, $secondSite, $settings, $templatesPath): void {
            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            self::assertTrue($this->indexBySetting($statuses)['redirectTemplate']['exists']);
            self::assertTrue($this->indexBySetting($statuses)['qrTemplate']['exists']);

            $secondRedirect = $templatesPath . DIRECTORY_SEPARATOR . $secondSite->handle
                . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig';
            self::assertTrue(unlink($secondRedirect));
            $globalRedirect = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig';
            FileHelper::createDirectory(dirname($globalRedirect));
            self::assertNotFalse(file_put_contents($globalRedirect, 'global redirect'));

            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            self::assertTrue(
                $this->indexBySetting($statuses)['redirectTemplate']['exists'],
                'One site override plus the global fallback must satisfy every enabled site.',
            );

            self::assertTrue(unlink($globalRedirect));
            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            self::assertFalse(
                $this->indexBySetting($statuses)['redirectTemplate']['exists'],
                'A cached path from the first site must not leak into the missing second site.',
            );

            $pluginScopedSettings = clone $settings;
            $pluginScopedSettings->enabledSites = [(int)$firstSite->id];
            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($pluginScopedSettings),
            );
            self::assertTrue(
                $this->indexBySetting($statuses)['redirectTemplate']['exists'],
                'A site disabled for SmartLink Manager must not block readiness.',
            );
        });
    }

    public function testCraftDisabledAndEmptySiteScopesAreExcludedSafely(): void
    {
        [$enabledSite, $disabledSite] = $this->twoSites();
        $disabledSite->enabled = false;
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-disabled-sites');
        $override = $templatesPath . DIRECTORY_SEPARATOR . $enabledSite->handle
            . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig';
        FileHelper::createDirectory(dirname($override));
        self::assertNotFalse(file_put_contents($override, 'enabled'));
        $settings = new Settings([
            'enabledSites' => [(int)$enabledSite->id, (int)$disabledSite->id],
            'redirectTemplate' => 'configured/redirect',
        ]);

        $this->withCraftSites([$enabledSite], function() use ($settings, $templatesPath): void {
            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            self::assertTrue(
                $this->indexBySetting($statuses)['redirectTemplate']['exists'],
                'A Craft-disabled site must not participate in readiness.',
            );
        });

        $this->withCraftSites([], function() use ($settings, $templatesPath): void {
            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            self::assertFalse($this->indexBySetting($statuses)['redirectTemplate']['exists']);
            self::assertFalse($this->setup->getStatus($settings)['templatesReady']);
        }, $enabledSite);
    }

    #[DataProvider('templateReadinessStateProvider')]
    public function testTemplateChecksRestoreCallerState(bool $ready, bool $serverValuesPresent): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-state');
        if ($ready) {
            FileHelper::createDirectory($templatesPath . DIRECTORY_SEPARATOR . 'configured');
            self::assertNotFalse(file_put_contents(
                $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig',
                'redirect',
            ));
            self::assertNotFalse(file_put_contents(
                $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'qr.twig',
                'qr',
            ));
        }

        $runnerState = $this->captureCallerSiteState();
        $runnerViewMode = Craft::$app->getView()->getTemplateMode();
        try {
            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
            if ($serverValuesPresent) {
                $_SERVER['CRAFT_SITE'] = "sentinel\0site";
                $_SERVER['CRAFT_SITE_UPPER'] = "SENTINEL\0UPPER";
            } else {
                unset($_SERVER['CRAFT_SITE'], $_SERVER['CRAFT_SITE_UPPER']);
            }
            $expectedState = $this->captureCallerSiteState();
            $expectedView = Craft::$app->getView();
            $expectedViewMode = $expectedView->getTemplateMode();

            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses(new Settings([
                    'redirectTemplate' => 'configured/redirect',
                    'qrTemplate' => 'configured/qr',
                ])),
            );
            foreach ($statuses as $status) {
                self::assertSame($ready, $status['exists']);
            }
            $this->assertCallerSiteState($expectedState);
            self::assertSame($expectedView, Craft::$app->getView());
            self::assertSame($expectedViewMode, Craft::$app->getView()->getTemplateMode());
        } finally {
            Craft::$app->getView()->setTemplateMode($runnerViewMode);
            $this->restoreCallerSiteState($runnerState);
        }
    }

    /**
     * @return iterable<string, array{ready: bool, serverValuesPresent: bool}>
     */
    public static function templateReadinessStateProvider(): iterable
    {
        yield 'success with exact server values' => ['ready' => true, 'serverValuesPresent' => true];
        yield 'missing with exact server values' => ['ready' => false, 'serverValuesPresent' => true];
        yield 'success with absent server values' => ['ready' => true, 'serverValuesPresent' => false];
        yield 'missing with absent server values' => ['ready' => false, 'serverValuesPresent' => false];
    }

    public function testTemplateResolutionExceptionRestoresCallerState(): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-exception');
        $runnerState = $this->captureCallerSiteState();
        $runnerViewMode = Craft::$app->getView()->getTemplateMode();
        $handler = static function(RegisterTemplateRootsEvent $event): void {
            throw new RuntimeException('smartlink-template-resolution-failure');
        };
        Event::on(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, $handler);

        try {
            $_SERVER['CRAFT_SITE'] = "exception\0site";
            $_SERVER['CRAFT_SITE_UPPER'] = "EXCEPTION\0UPPER";
            Craft::$app->getView()->setTemplateMode(View::TEMPLATE_MODE_CP);
            $expectedState = $this->captureCallerSiteState();
            $expectedView = Craft::$app->getView();
            $expectedViewMode = $expectedView->getTemplateMode();

            try {
                $this->withSiteTemplatesPath(
                    $templatesPath,
                    fn(): array => $this->setup->templateStatuses(new Settings([
                        'redirectTemplate' => 'configured/redirect',
                    ])),
                );
                self::fail('The resolver exception must propagate.');
            } catch (RuntimeException $exception) {
                self::assertSame('smartlink-template-resolution-failure', $exception->getMessage());
            }

            $this->assertCallerSiteState($expectedState);
            self::assertSame($expectedView, Craft::$app->getView());
            self::assertSame($expectedViewMode, Craft::$app->getView()->getTemplateMode());
        } finally {
            Event::off(View::class, View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS, $handler);
            Craft::$app->getView()->setTemplateMode($runnerViewMode);
            $this->restoreCallerSiteState($runnerState);
        }
    }

    public function testOriginallyUnsetCurrentSiteAndServerValuesRemainUnset(): void
    {
        [$site] = $this->twoSites();
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-unset-site');
        FileHelper::createDirectory($templatesPath . DIRECTORY_SEPARATOR . 'configured');
        self::assertNotFalse(file_put_contents(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig',
            'redirect',
        ));
        $originalLanguage = Craft::$app->language;
        $craftSiteExisted = array_key_exists('CRAFT_SITE', $_SERVER);
        $craftSite = $craftSiteExisted ? $_SERVER['CRAFT_SITE'] : null;
        $craftSiteUpperExisted = array_key_exists('CRAFT_SITE_UPPER', $_SERVER);
        $craftSiteUpper = $craftSiteUpperExisted ? $_SERVER['CRAFT_SITE_UPPER'] : null;

        try {
            unset($_SERVER['CRAFT_SITE'], $_SERVER['CRAFT_SITE_UPPER']);
            $this->withCraftSites([$site], function() use ($site, $templatesPath): void {
                self::assertFalse(Craft::$app->getSites()->getHasCurrentSite());
                $statuses = $this->withSiteTemplatesPath(
                    $templatesPath,
                    fn(): array => $this->setup->templateStatuses(new Settings([
                        'enabledSites' => [(int)$site->id],
                        'redirectTemplate' => 'configured/redirect',
                    ])),
                );
                self::assertTrue($this->indexBySetting($statuses)['redirectTemplate']['exists']);
                self::assertFalse(Craft::$app->getSites()->getHasCurrentSite());
                self::assertArrayNotHasKey('CRAFT_SITE', $_SERVER);
                self::assertArrayNotHasKey('CRAFT_SITE_UPPER', $_SERVER);
            }, null, false);
            self::assertSame($originalLanguage, Craft::$app->language);
        } finally {
            $this->restoreServerValue('CRAFT_SITE', $craftSiteExisted, $craftSite);
            $this->restoreServerValue('CRAFT_SITE_UPPER', $craftSiteUpperExisted, $craftSiteUpper);
            Craft::$app->language = $originalLanguage;
        }
    }

    public function testEnvironmentBackedTemplatesUseEffectiveValuesWithoutMutatingRawSettings(): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-setup-env');
        FileHelper::createDirectory($templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'qr');
        self::assertNotFalse(file_put_contents(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.html',
            'redirect',
        ));
        self::assertNotFalse(file_put_contents(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'qr' . DIRECTORY_SEPARATOR . 'index.twig',
            'qr',
        ));
        $environment = [
            'SMARTLINK_MANAGER_TEST_SETUP_REDIRECT' => 'configured/redirect.html',
            'SMARTLINK_MANAGER_TEST_SETUP_QR' => 'configured/qr',
        ];
        $original = [];
        foreach ($environment as $name => $value) {
            $original[$name] = [array_key_exists($name, $_SERVER), $_SERVER[$name] ?? null];
            $_SERVER[$name] = $value;
        }

        try {
            $settings = new Settings([
                'redirectTemplate' => '$SMARTLINK_MANAGER_TEST_SETUP_REDIRECT',
                'qrTemplate' => '$SMARTLINK_MANAGER_TEST_SETUP_QR',
            ]);
            $statuses = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            $bySetting = $this->indexBySetting($statuses);

            self::assertSame('configured/redirect.html', $bySetting['redirectTemplate']['template']);
            self::assertTrue($bySetting['redirectTemplate']['exists']);
            self::assertSame('configured/qr', $bySetting['qrTemplate']['template']);
            self::assertTrue($bySetting['qrTemplate']['exists']);
            self::assertSame('$SMARTLINK_MANAGER_TEST_SETUP_REDIRECT', $settings->redirectTemplate);
            self::assertSame('$SMARTLINK_MANAGER_TEST_SETUP_QR', $settings->qrTemplate);

            $_SERVER['SMARTLINK_MANAGER_TEST_SETUP_REDIRECT'] = 'configured/missing';
            $changed = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): array => $this->setup->templateStatuses($settings),
            );
            self::assertSame('configured/missing', $this->indexBySetting($changed)['redirectTemplate']['template']);
            self::assertFalse($this->indexBySetting($changed)['redirectTemplate']['exists']);
        } finally {
            foreach ($original as $name => [$existed, $value]) {
                $this->restoreServerValue($name, $existed, $value);
            }
        }
    }

    #[DataProvider('nonCopyableTemplateProvider')]
    public function testReadinessRejectedTemplatePathsAreNotCopyable(
        string $setting,
        string $configuredTemplate,
        ?string $environmentName,
        ?string $environmentValue,
    ): void {
        $serverExisted = $environmentName !== null && array_key_exists($environmentName, $_SERVER);
        $serverValue = $serverExisted && $environmentName !== null ? $_SERVER[$environmentName] : null;

        try {
            if ($environmentName !== null) {
                if ($environmentValue === null) {
                    unset($_SERVER[$environmentName]);
                } else {
                    $_SERVER[$environmentName] = $environmentValue;
                }
            }

            $settings = new Settings([$setting => $configuredTemplate]);
            $status = $this->indexBySetting($this->setup->templateStatuses($settings))[$setting];

            if ($environmentName === null) {
                self::assertSame('../outside-template', $status['template']);
            } else {
                self::assertSame('', $status['template']);
            }
            self::assertFalse($status['exists']);
            self::assertFalse($status['copyable']);
            self::assertSame('', $status['destination']);
            self::assertSame('', $status['destinationDir']);
            self::assertFalse($status['destinationDirExists']);
            self::assertFalse($status['destinationExists']);
            self::assertSame($configuredTemplate, $settings->{$setting});
        } finally {
            if ($environmentName !== null) {
                $this->restoreServerValue($environmentName, $serverExisted, $serverValue);
            }
        }
    }

    /**
     * @return iterable<string, array{setting: string, configuredTemplate: string, environmentName: string|null, environmentValue: string|null}>
     */
    public static function nonCopyableTemplateProvider(): iterable
    {
        foreach ([
            'redirect' => 'redirectTemplate',
            'QR' => 'qrTemplate',
        ] as $label => $setting) {
            $undefinedName = 'SMARTLINK_MANAGER_TEST_UNDEFINED_' . strtoupper($label);
            yield $label . ' undefined environment value' => [
                'setting' => $setting,
                'configuredTemplate' => '$' . $undefinedName,
                'environmentName' => $undefinedName,
                'environmentValue' => null,
            ];

            $emptyName = 'SMARTLINK_MANAGER_TEST_EMPTY_' . strtoupper($label);
            yield $label . ' empty environment value' => [
                'setting' => $setting,
                'configuredTemplate' => '$' . $emptyName,
                'environmentName' => $emptyName,
                'environmentValue' => '',
            ];

            yield $label . ' traversal path' => [
                'setting' => $setting,
                'configuredTemplate' => '../outside-template',
                'environmentName' => null,
                'environmentValue' => null,
            ];
        }
    }

    public function testIpSaltConfiguredWhenSaltPresent(): void
    {
        $this->withSettings([
            'ipHashSalt' => str_repeat('a', 40),
        ], function(): void {
            self::assertTrue(
                $this->setup->isIpSaltConfigured(SmartLinkManager::$plugin->getSettings()),
                'A real salt value must count as configured.',
            );
        });
    }

    public function testIpSaltNotConfiguredWhenEmpty(): void
    {
        $this->withSettings([
            'ipHashSalt' => '',
        ], function(): void {
            self::assertFalse(
                $this->setup->isIpSaltConfigured(SmartLinkManager::$plugin->getSettings()),
                'An empty salt must not count as configured.',
            );
        });
    }

    public function testIpSaltNotConfiguredForUnresolvedPlaceholder(): void
    {
        $this->withSettings([
            'ipHashSalt' => '$SMARTLINK_MANAGER_IP_SALT',
        ], function(): void {
            self::assertFalse(
                $this->setup->isIpSaltConfigured(SmartLinkManager::$plugin->getSettings()),
                'The unresolved default env placeholder must not count as configured.',
            );
        });
    }

    /**
     * @param array<int, array{setting: string, template: string, destination: string, destinationDir: string, destinationDirExists: bool, destinationExists: bool, exists: bool, copyable: bool}> $statuses
     * @return array<string, array{setting: string, template: string, destination: string, destinationDir: string, destinationDirExists: bool, destinationExists: bool, exists: bool, copyable: bool}>
     */
    private function indexBySetting(array $statuses): array
    {
        $indexed = [];
        foreach ($statuses as $status) {
            $indexed[$status['setting']] = $status;
        }

        return $indexed;
    }

    /**
     * @return array{0: Site, 1: Site}
     */
    private function twoSites(): array
    {
        $currentSite = Craft::$app->getSites()->getCurrentSite();
        self::assertNotNull($currentSite->id);
        self::assertNotNull($currentSite->handle);

        $firstSite = clone $currentSite;
        $firstSite->handle = 'smartlinkAlpha' . bin2hex(random_bytes(2));
        $firstSite->language = 'de-DE';
        $secondSite = new Site([
            'id' => (int)$currentSite->id + 2000000,
            'uid' => 'smartlink-second-site-' . bin2hex(random_bytes(4)),
            'handle' => 'smartlinkBeta' . bin2hex(random_bytes(2)),
            'name' => 'SmartLink Test Second Site',
            'language' => 'fr-FR',
            'enabled' => true,
        ]);

        return [$firstSite, $secondSite];
    }

    /**
     * @template T
     * @param list<Site> $sites
     * @param callable(): T $callback
     * @return T
     */
    private function withCraftSites(
        array $sites,
        callable $callback,
        ?Site $fallbackSite = null,
        bool $hasCurrentSite = true,
    ): mixed {
        $originalSites = Craft::$app->getSites();
        $testSites = new class() extends Sites {
            public ?Site $currentSite = null;

            /** @var list<Site> */
            public array $sites = [];

            public function getAllSites(?bool $withDisabled = null): array
            {
                return $this->sites;
            }

            public function getHasCurrentSite(): bool
            {
                return $this->currentSite !== null;
            }

            public function getCurrentSite(): Site
            {
                if ($this->currentSite === null) {
                    throw new RuntimeException('No current test site is set.');
                }

                return $this->currentSite;
            }

            public function setCurrentSite(mixed $site): void
            {
                if ($site !== null && !$site instanceof Site) {
                    throw new RuntimeException('The setup readiness test only supports Site objects or null.');
                }

                $this->currentSite = $site;
            }
        };
        $testSites->currentSite = $hasCurrentSite ? ($sites[0] ?? $fallbackSite) : null;
        $testSites->sites = $sites;
        Craft::$app->set('sites', $testSites);

        try {
            return $callback();
        } finally {
            Craft::$app->set('sites', $originalSites);
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

    /**
     * @return array{site: Site, language: string, craftSiteExisted: bool, craftSite: mixed, craftSiteUpperExisted: bool, craftSiteUpper: mixed}
     */
    private function captureCallerSiteState(): array
    {
        $craftSiteExisted = array_key_exists('CRAFT_SITE', $_SERVER);
        $craftSiteUpperExisted = array_key_exists('CRAFT_SITE_UPPER', $_SERVER);

        return [
            'site' => Craft::$app->getSites()->getCurrentSite(),
            'language' => Craft::$app->language,
            'craftSiteExisted' => $craftSiteExisted,
            'craftSite' => $craftSiteExisted ? $_SERVER['CRAFT_SITE'] : null,
            'craftSiteUpperExisted' => $craftSiteUpperExisted,
            'craftSiteUpper' => $craftSiteUpperExisted ? $_SERVER['CRAFT_SITE_UPPER'] : null,
        ];
    }

    /**
     * @param array{site: Site, language: string, craftSiteExisted: bool, craftSite: mixed, craftSiteUpperExisted: bool, craftSiteUpper: mixed} $state
     */
    private function assertCallerSiteState(array $state): void
    {
        self::assertSame($state['site'], Craft::$app->getSites()->getCurrentSite());
        self::assertSame($state['language'], Craft::$app->language);
        if ($state['craftSiteExisted']) {
            self::assertArrayHasKey('CRAFT_SITE', $_SERVER);
            self::assertSame($state['craftSite'], $_SERVER['CRAFT_SITE']);
        } else {
            self::assertArrayNotHasKey('CRAFT_SITE', $_SERVER);
        }
        if ($state['craftSiteUpperExisted']) {
            self::assertArrayHasKey('CRAFT_SITE_UPPER', $_SERVER);
            self::assertSame($state['craftSiteUpper'], $_SERVER['CRAFT_SITE_UPPER']);
        } else {
            self::assertArrayNotHasKey('CRAFT_SITE_UPPER', $_SERVER);
        }
    }

    /**
     * @param array{site: Site, language: string, craftSiteExisted: bool, craftSite: mixed, craftSiteUpperExisted: bool, craftSiteUpper: mixed} $state
     */
    private function restoreCallerSiteState(array $state): void
    {
        try {
            Craft::$app->getSites()->setCurrentSite($state['site']);
        } finally {
            Craft::$app->language = $state['language'];
            $this->restoreServerValue('CRAFT_SITE', $state['craftSiteExisted'], $state['craftSite']);
            $this->restoreServerValue('CRAFT_SITE_UPPER', $state['craftSiteUpperExisted'], $state['craftSiteUpper']);
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
