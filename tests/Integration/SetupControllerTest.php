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
use craft\models\Site;
use craft\services\Sites;
use lindemannrock\smartlinkmanager\console\controllers\SetupController;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use yii\console\ExitCode;

/**
 * Covers global starter-template installation behavior.
 *
 * @since 5.38.0
 */
final class SetupControllerTest extends TestCase
{
    #[DataProvider('configuredTemplateProvider')]
    public function testCopyCommandUsesExactConfiguredGlobalDestination(
        string $templateKey,
        string $setting,
        string $configuredPath,
        string $expectedDestination,
        string $unexpectedDestination,
    ): void {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-destination');

        $this->withSettings([$setting => $configuredPath], function() use (
            $expectedDestination,
            $templateKey,
            $templatesPath,
            $unexpectedDestination,
        ): void {
            $exitCode = $this->withSiteTemplatesPath(
                $templatesPath,
                fn(): int => $this->runCopyCommand($templateKey),
            );

            self::assertSame(ExitCode::OK, $exitCode);
            self::assertFileExists($templatesPath . DIRECTORY_SEPARATOR . $expectedDestination);
            self::assertFileDoesNotExist($templatesPath . DIRECTORY_SEPARATOR . $unexpectedDestination);
            self::assertSame(
                file_get_contents($this->bundledTemplatePath($templateKey)),
                file_get_contents($templatesPath . DIRECTORY_SEPARATOR . $expectedDestination),
            );
        });
    }

    /**
     * @return iterable<string, array{templateKey: string, setting: string, configuredPath: string, expectedDestination: string, unexpectedDestination: string}>
     */
    public static function configuredTemplateProvider(): iterable
    {
        yield 'redirect explicit html' => [
            'templateKey' => 'redirect',
            'setting' => 'redirectTemplate',
            'configuredPath' => 'configured/redirect.html',
            'expectedDestination' => 'configured/redirect.html',
            'unexpectedDestination' => 'configured/redirect.html.twig',
        ];
        yield 'QR explicit twig' => [
            'templateKey' => 'qr',
            'setting' => 'qrTemplate',
            'configuredPath' => 'configured/qr.twig',
            'expectedDestination' => 'configured/qr.twig',
            'unexpectedDestination' => 'configured/qr.twig.twig',
        ];
        yield 'redirect extensionless' => [
            'templateKey' => 'redirect',
            'setting' => 'redirectTemplate',
            'configuredPath' => 'configured/redirect',
            'expectedDestination' => 'configured/redirect.twig',
            'unexpectedDestination' => 'configured/redirect',
        ];
        yield 'QR extensionless' => [
            'templateKey' => 'qr',
            'setting' => 'qrTemplate',
            'configuredPath' => 'configured/qr',
            'expectedDestination' => 'configured/qr.twig',
            'unexpectedDestination' => 'configured/qr',
        ];
    }

    #[DataProvider('templateSettings')]
    public function testCopyCommandInstallsOneGlobalFallbackWhenAnySiteIsMissing(
        string $templateKey,
        string $setting,
    ): void {
        [$firstSite, $secondSite] = $this->twoEnabledSites();
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-fallback');
        $overridePath = $templatesPath . DIRECTORY_SEPARATOR . $firstSite->handle
            . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . $templateKey . '.twig';
        FileHelper::createDirectory(dirname($overridePath));
        self::assertNotFalse(file_put_contents($overridePath, 'site override'));
        $overrideMode = fileperms($overridePath);

        $this->withSettings([
            'enabledSites' => [(int)$firstSite->id, (int)$secondSite->id],
            $setting => 'configured/' . $templateKey,
        ], function() use ($firstSite, $secondSite, $templateKey, $templatesPath, $overridePath, $overrideMode): void {
            $exitCode = $this->withCraftSites(
                [$firstSite, $secondSite],
                fn(): int => $this->withSiteTemplatesPath(
                    $templatesPath,
                    fn(): int => $this->runCopyCommand($templateKey),
                ),
            );

            self::assertSame(ExitCode::OK, $exitCode);
            self::assertSame('site override', file_get_contents($overridePath));
            self::assertSame($overrideMode, fileperms($overridePath));
            self::assertFileDoesNotExist(
                $templatesPath . DIRECTORY_SEPARATOR . $secondSite->handle
                    . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . $templateKey . '.twig',
            );
            self::assertSame(
                file_get_contents($this->bundledTemplatePath($templateKey)),
                file_get_contents($templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . $templateKey . '.twig'),
            );
        });
    }

    /**
     * @return iterable<string, array{templateKey: string, setting: string}>
     */
    public static function templateSettings(): iterable
    {
        yield 'redirect template' => ['templateKey' => 'redirect', 'setting' => 'redirectTemplate'];
        yield 'QR template' => ['templateKey' => 'qr', 'setting' => 'qrTemplate'];
    }

    #[DataProvider('nonCopyableCommandProvider')]
    public function testCopyCommandRejectsPathsThatReadinessRejects(
        string $templateKey,
        string $setting,
        string $environmentState,
        bool $traversal,
    ): void {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-rejected');
        $environmentName = 'SMARTLINK_MANAGER_TEST_COPY_' . strtoupper($templateKey);
        $outsideName = 'smartlink-copy-outside-' . bin2hex(random_bytes(4));
        $configuredPath = $traversal ? '../' . $outsideName : '$' . $environmentName;
        $outsidePath = dirname($templatesPath) . DIRECTORY_SEPARATOR . $outsideName . '.twig';
        $neighbor = $templatesPath . DIRECTORY_SEPARATOR . 'neighbor.txt';
        self::assertNotFalse(file_put_contents($neighbor, "neighbor\0bytes"));
        self::assertTrue(chmod($neighbor, 0640));
        $neighborBytes = file_get_contents($neighbor);
        $neighborMode = fileperms($neighbor);
        $serverState = [array_key_exists($environmentName, $_SERVER), $_SERVER[$environmentName] ?? null];
        $envState = [array_key_exists($environmentName, $_ENV), $_ENV[$environmentName] ?? null];
        $processState = getenv($environmentName);
        $environment = $environmentState === 'undefined'
            ? [$environmentName => null]
            : [$environmentName => ''];

        [$exitCode, $stdout, $stderr] = $this->runConfiguredCopyCommand(
            [$setting => $configuredPath],
            $templatesPath,
            $templateKey,
            $environment,
        );

        self::assertSame(ExitCode::UNSPECIFIED_ERROR, $exitCode, $stdout . $stderr);
        self::assertStringContainsString('configured template path cannot be copied', $stderr);
        self::assertStringContainsString('0 copied, 0 skipped, 1 failed', $stdout);
        self::assertStringNotContainsString('Copied ', $stdout);
        self::assertStringNotContainsString('Overwrite it?', $stdout . $stderr);
        self::assertFileDoesNotExist($templatesPath . DIRECTORY_SEPARATOR . '.twig');
        self::assertFileDoesNotExist($outsidePath);
        self::assertSame([$neighbor], glob($templatesPath . DIRECTORY_SEPARATOR . '*'));
        self::assertSame($neighborBytes, file_get_contents($neighbor));
        self::assertSame($neighborMode, fileperms($neighbor));
        self::assertSame($serverState[0], array_key_exists($environmentName, $_SERVER));
        self::assertSame($serverState[1], $_SERVER[$environmentName] ?? null);
        self::assertSame($envState[0], array_key_exists($environmentName, $_ENV));
        self::assertSame($envState[1], $_ENV[$environmentName] ?? null);
        self::assertSame($processState, getenv($environmentName));
    }

    /**
     * @return iterable<string, array{templateKey: string, setting: string, environmentState: string, traversal: bool}>
     */
    public static function nonCopyableCommandProvider(): iterable
    {
        yield 'redirect undefined environment value' => [
            'templateKey' => 'redirect',
            'setting' => 'redirectTemplate',
            'environmentState' => 'undefined',
            'traversal' => false,
        ];
        yield 'QR empty environment value' => [
            'templateKey' => 'qr',
            'setting' => 'qrTemplate',
            'environmentState' => 'empty',
            'traversal' => false,
        ];
        yield 'redirect traversal path' => [
            'templateKey' => 'redirect',
            'setting' => 'redirectTemplate',
            'environmentState' => 'undefined',
            'traversal' => true,
        ];
        yield 'QR traversal path' => [
            'templateKey' => 'qr',
            'setting' => 'qrTemplate',
            'environmentState' => 'undefined',
            'traversal' => true,
        ];
    }

    public function testAllTemplateCopyFailsWhenOneTemplateIsNotCopyable(): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-all-rejected');
        $environmentName = 'SMARTLINK_MANAGER_TEST_COPY_ALL_EMPTY';
        $qrDestination = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'qr.twig';
        FileHelper::createDirectory(dirname($qrDestination));
        self::assertNotFalse(file_put_contents($qrDestination, 'existing qr'));
        self::assertTrue(chmod($qrDestination, 0640));
        $qrMode = fileperms($qrDestination);

        [$exitCode, $stdout, $stderr] = $this->runConfiguredCopyCommand(
            [
                'redirectTemplate' => '$' . $environmentName,
                'qrTemplate' => 'configured/qr.twig',
            ],
            $templatesPath,
            null,
            [$environmentName => ''],
        );

        self::assertSame(ExitCode::UNSPECIFIED_ERROR, $exitCode, $stdout . $stderr);
        self::assertStringContainsString('configured template path cannot be copied', $stderr);
        self::assertStringContainsString('destination already exists (templates/configured/qr.twig)', $stdout);
        self::assertStringContainsString('0 copied, 1 skipped, 1 failed', $stdout);
        self::assertStringNotContainsString('Copied ', $stdout);
        self::assertStringNotContainsString('Overwrite it?', $stdout . $stderr);
        self::assertFileDoesNotExist($templatesPath . DIRECTORY_SEPARATOR . '.twig');
        self::assertSame('existing qr', file_get_contents($qrDestination));
        self::assertSame($qrMode, fileperms($qrDestination));
    }

    public function testCopyCommandSkipsWhenEverySiteHasAnOverride(): void
    {
        [$firstSite, $secondSite] = $this->twoEnabledSites();
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-overrides');
        $paths = [];
        foreach ([$firstSite, $secondSite] as $site) {
            $path = $templatesPath . DIRECTORY_SEPARATOR . $site->handle
                . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig';
            FileHelper::createDirectory(dirname($path));
            self::assertNotFalse(file_put_contents($path, 'override-' . $site->handle));
            $paths[$path] = [file_get_contents($path), fileperms($path)];
        }

        $this->withSettings([
            'enabledSites' => [(int)$firstSite->id, (int)$secondSite->id],
            'redirectTemplate' => 'configured/redirect',
        ], function() use ($firstSite, $secondSite, $paths, $templatesPath): void {
            $exitCode = $this->withCraftSites(
                [$firstSite, $secondSite],
                fn(): int => $this->withSiteTemplatesPath(
                    $templatesPath,
                    fn(): int => $this->runCopyCommand('redirect', true),
                ),
            );

            self::assertSame(ExitCode::OK, $exitCode);
            self::assertFileDoesNotExist(
                $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig',
            );
            foreach ($paths as $path => [$contents, $mode]) {
                self::assertSame($contents, file_get_contents($path));
                self::assertSame($mode, fileperms($path));
            }
        });
    }

    #[DataProvider('globallyResolvedWithoutDestinationProvider')]
    public function testCopyCommandTruthfullySkipsWhenTemplateResolvesWithoutExactDestination(
        string $resolutionPath,
    ): void {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-resolved');
        $config = ['redirectTemplate' => 'configured/redirect'];
        $resolvedFiles = [];
        if (str_contains($resolutionPath, '{siteHandle}')) {
            $sites = Craft::$app->getSites()->getAllSites(false);
            $config['enabledSites'] = array_map(static fn(Site $site): int => (int)$site->id, $sites);
            foreach ($sites as $site) {
                $relativePath = str_replace('{siteHandle}', $site->handle, $resolutionPath);
                $resolvedFiles[] = $templatesPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            }
        } else {
            $resolvedFiles[] = $templatesPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $resolutionPath);
        }
        foreach ($resolvedFiles as $resolvedFile) {
            FileHelper::createDirectory(dirname($resolvedFile));
            self::assertNotFalse(file_put_contents($resolvedFile, 'resolved template'));
            self::assertTrue(chmod($resolvedFile, 0640));
        }

        [$exitCode, $stdout, $stderr] = $this->runConfiguredCopyCommand(
            $config,
            $templatesPath,
            'redirect',
        );

        self::assertSame(ExitCode::OK, $exitCode, $stderr);
        self::assertStringContainsString('template already resolves for every enabled site', $stdout);
        self::assertStringNotContainsString('destination already exists', $stdout);
        self::assertStringNotContainsString('Overwrite it?', $stdout . $stderr);
        self::assertFileDoesNotExist(
            $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig',
        );
        foreach ($resolvedFiles as $resolvedFile) {
            self::assertSame('resolved template', file_get_contents($resolvedFile));
            self::assertSame(0640, fileperms($resolvedFile) & 0777);
        }
    }

    /**
     * @return iterable<string, array{resolutionPath: string}>
     */
    public static function globallyResolvedWithoutDestinationProvider(): iterable
    {
        yield 'site-handle override' => [
            'resolutionPath' => '{siteHandle}/configured/redirect.twig',
        ];
        yield 'global index template' => [
            'resolutionPath' => 'configured/redirect/index.twig',
        ];
    }

    public function testOverwriteReplacesOnlyExactGlobalDestination(): void
    {
        [$firstSite, $secondSite] = $this->twoEnabledSites();
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-overwrite');
        $globalDestination = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig';
        $sameStem = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.html';
        $unrelated = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'other.twig';
        FileHelper::createDirectory(dirname($globalDestination));
        self::assertNotFalse(file_put_contents($globalDestination, 'old global'));
        self::assertNotFalse(file_put_contents($sameStem, 'same stem'));
        self::assertNotFalse(file_put_contents($unrelated, 'unrelated'));
        $protected = [
            $sameStem => [file_get_contents($sameStem), fileperms($sameStem)],
            $unrelated => [file_get_contents($unrelated), fileperms($unrelated)],
        ];
        foreach ([$firstSite, $secondSite] as $site) {
            $path = $templatesPath . DIRECTORY_SEPARATOR . $site->handle
                . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'redirect.twig';
            FileHelper::createDirectory(dirname($path));
            self::assertNotFalse(file_put_contents($path, 'protected-' . $site->handle));
            $protected[$path] = [file_get_contents($path), fileperms($path)];
        }

        $this->withSettings([
            'enabledSites' => [(int)$firstSite->id, (int)$secondSite->id],
            'redirectTemplate' => 'configured/redirect.twig',
        ], function() use ($firstSite, $secondSite, $globalDestination, $protected, $templatesPath): void {
            $exitCode = $this->withCraftSites(
                [$firstSite, $secondSite],
                fn(): int => $this->withSiteTemplatesPath(
                    $templatesPath,
                    fn(): int => $this->runCopyCommand('redirect', true),
                ),
            );

            self::assertSame(ExitCode::OK, $exitCode);
            self::assertSame(file_get_contents($this->bundledTemplatePath('redirect')), file_get_contents($globalDestination));
            foreach ($protected as $path => [$contents, $mode]) {
                self::assertSame($contents, file_get_contents($path));
                self::assertSame($mode, fileperms($path));
            }
        });
    }

    public function testInteractiveCancellationPreservesExistingDestination(): void
    {
        $templatesPath = $this->createTrackedTempDirectory('smartlink-copy-cancel');
        $configPath = $this->createTrackedTempDirectory('smartlink-copy-cancel-config');
        FileHelper::copyDirectory(Craft::$app->getConfig()->configDir, $configPath);
        self::assertNotFalse(file_put_contents(
            $configPath . DIRECTORY_SEPARATOR . 'smartlink-manager.php',
            "<?php\nreturn ['qrTemplate' => 'configured/qr'];\n",
        ));
        $destination = $templatesPath . DIRECTORY_SEPARATOR . 'configured' . DIRECTORY_SEPARATOR . 'qr.twig';
        FileHelper::createDirectory(dirname($destination));
        self::assertNotFalse(file_put_contents($destination, 'keep this exact file'));
        $mode = fileperms($destination);

        [$exitCode, $stdout, $stderr] = $this->runInteractiveCopyCommand($configPath, $templatesPath, "n\n");

        self::assertSame(ExitCode::OK, $exitCode, $stderr);
        self::assertStringContainsString('Overwrite it?', $stdout);
        self::assertStringContainsString('0 copied, 1 skipped, 0 failed', $stdout);
        self::assertSame('keep this exact file', file_get_contents($destination));
        self::assertSame($mode, fileperms($destination));
    }

    private function runCopyCommand(string $template, bool $overwrite = false): int
    {
        $controller = new SetupController('setup', SmartLinkManager::$plugin);
        $controller->interactive = false;
        $controller->template = $template;
        $controller->overwrite = $overwrite;

        return $controller->actionCopyTemplates();
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, string|null> $environmentOverrides
     * @return array{0: int, 1: string, 2: string}
     */
    private function runConfiguredCopyCommand(
        array $config,
        string $templatesPath,
        ?string $template,
        array $environmentOverrides = [],
    ): array {
        $configPath = $this->createTrackedTempDirectory('smartlink-copy-config');
        FileHelper::copyDirectory(Craft::$app->getConfig()->configDir, $configPath);
        self::assertNotFalse(file_put_contents(
            $configPath . DIRECTORY_SEPARATOR . 'smartlink-manager.php',
            "<?php\nreturn " . var_export($config, true) . ";\n",
        ));
        $projectRoot = Craft::getAlias('@root');
        self::assertIsString($projectRoot);
        $command = [
            PHP_BINARY,
            $projectRoot . DIRECTORY_SEPARATOR . 'craft',
            '--configPath=' . $configPath,
            '--templatesPath=' . $templatesPath,
            'smartlink-manager/setup/copy-templates',
            '--interactive=1',
        ];
        if ($template !== null) {
            $command[] = '--template=' . $template;
        }
        $environment = getenv();
        self::assertIsArray($environment);
        foreach ($environmentOverrides as $name => $value) {
            if ($value === null) {
                unset($environment[$name]);
            } else {
                $environment[$name] = $value;
            }
        }

        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $projectRoot,
            $environment,
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Unable to start the configured setup command test.');
        }

        try {
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            return [$exitCode, $stdout, $stderr];
        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
        }
    }

    private function bundledTemplatePath(string $template): string
    {
        $projectRoot = Craft::getAlias('@root');
        self::assertIsString($projectRoot);

        return $projectRoot . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'lindemannrock'
            . DIRECTORY_SEPARATOR . 'craft-smartlink-manager' . DIRECTORY_SEPARATOR . 'src'
            . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $template . '.twig';
    }

    /**
     * @return array{0: int, 1: string, 2: string}
     */
    private function runInteractiveCopyCommand(
        string $configPath,
        string $templatesPath,
        string $input,
    ): array {
        $projectRoot = Craft::getAlias('@root');
        self::assertIsString($projectRoot);
        $process = proc_open(
            [
                PHP_BINARY,
                $projectRoot . DIRECTORY_SEPARATOR . 'craft',
                '--configPath=' . $configPath,
                '--templatesPath=' . $templatesPath,
                'smartlink-manager/setup/copy-templates',
                '--template=qr',
                '--interactive=1',
            ],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $projectRoot,
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Unable to start the interactive setup command test.');
        }

        try {
            fwrite($pipes[0], $input);
            fclose($pipes[0]);
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);

            return [$exitCode, $stdout, $stderr];
        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }
        }
    }

    /**
     * @return array{0: Site, 1: Site}
     */
    private function twoEnabledSites(): array
    {
        $currentSite = Craft::$app->getSites()->getCurrentSite();
        self::assertNotNull($currentSite->id);
        self::assertNotNull($currentSite->handle);
        $firstSite = clone $currentSite;
        $firstSite->handle = 'smartlinkCopyAlpha' . bin2hex(random_bytes(2));
        $firstSite->language = 'de-DE';
        $secondSite = new Site([
            'id' => (int)$currentSite->id + 3000000,
            'uid' => 'smartlink-copy-second-' . bin2hex(random_bytes(4)),
            'handle' => 'smartlinkCopyBeta' . bin2hex(random_bytes(2)),
            'name' => 'SmartLink Copy Second Site',
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
    private function withCraftSites(array $sites, callable $callback): mixed
    {
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
                    throw new RuntimeException('No current setup command test site is set.');
                }

                return $this->currentSite;
            }

            public function setCurrentSite(mixed $site): void
            {
                if ($site !== null && !$site instanceof Site) {
                    throw new RuntimeException('The setup command test only supports Site objects or null.');
                }

                $this->currentSite = $site;
            }
        };
        $testSites->currentSite = $sites[0];
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
}
