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
use craft\console\Application as ConsoleApplication;
use craft\console\Request;
use craft\console\User as ConsoleUser;
use craft\db\Query;
use craft\helpers\Db;
use craft\web\Response;
use craft\web\Session;
use lindemannrock\smartlinkmanager\controllers\ImportExportController;
use lindemannrock\smartlinkmanager\controllers\SmartlinksController;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use lindemannrock\smartlinkmanager\variables\SmartLinkManagerVariable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Covers effective QR-size defaults at supported SmartLink creation boundaries.
 *
 * @since 5.38.0
 */
#[CoversClass(SmartlinksController::class)]
#[CoversClass(ImportExportController::class)]
#[CoversClass(SmartLinkManagerVariable::class)]
final class DefaultQrSizeInitializationTest extends TestCase
{
    private mixed $originalRequest = null;
    private mixed $originalResponse = null;

    protected function tearDown(): void
    {
        if ($this->originalRequest !== null) {
            Craft::$app->set('request', $this->originalRequest);
        }
        if ($this->originalResponse !== null) {
            Craft::$app->set('response', $this->originalResponse);
        }

        parent::tearDown();
    }

    #[DataProvider('supportedDefaultSizes')]
    public function testControlPanelCreationAndSaveFallbackUseEffectiveDefault(int $defaultSize): void
    {
        $this->withSettings(['defaultQrSize' => $defaultSize], function() use ($defaultSize): void {
            $this->installRequest();
            $editController = new DefaultQrSizeSmartlinksController('smartlinks', SmartLinkManager::$plugin);
            $editController->actionEdit();

            self::assertInstanceOf(SmartLink::class, $editController->renderedVariables['smartLink'] ?? null);
            self::assertSame($defaultSize, $editController->renderedVariables['smartLink']->qrCodeSize);

            $slug = str_replace('_', '-', $this->nextTestMarker(self::MARKER, 'cp-default'));
            $this->installRequest([
                'siteId' => Craft::$app->getSites()->getPrimarySite()->id,
                'title' => 'Control Panel default QR size',
                'slug' => $slug,
                'fallbackUrl' => 'https://example.com/' . $slug,
                'qrCodeEnabled' => '1',
                'enabled' => '1',
            ]);

            try {
                $saveController = new DefaultQrSizeSmartlinksController('smartlinks', SmartLinkManager::$plugin);
                $response = $saveController->actionSave();
                self::assertInstanceOf(Response::class, $response);
            } finally {
                $saved = SmartLink::find()->slug($slug)->site('*')->status(null)->one();
                if ($saved?->id !== null) {
                    $this->trackElementForCleanup((int)$saved->id);
                }
            }

            $saved = SmartLink::find()->slug($slug)->site('*')->status(null)->one();
            self::assertInstanceOf(SmartLink::class, $saved);
            self::assertSame($defaultSize, $saved->qrCodeSize);
        });
    }

    public function testImportPersistenceUsesEffectiveDefaultWhenSizeIsAbsent(): void
    {
        $this->withSettings(['defaultQrSize' => 1000], function(): void {
            $smartLink = $this->persistImportRow(null, 'missing-size');

            self::assertSame(1000, $smartLink->qrCodeSize);
            self::assertSame(1000, $this->persistedQrSize($smartLink));
        });
    }

    public function testImportPreviewAndFinalWriteAgreeForSupportedSizeInputs(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));

        $cases = [
            'omitted' => [null, 1000, (int)$sites[0]->id],
            'empty' => ['', 1000, (int)$sites[0]->id],
            'whitespace' => ['   ', 1000, (int)$sites[0]->id],
            'minimum' => ['100', 100, (int)$sites[0]->id],
            'standard' => ['256', 256, (int)$sites[0]->id],
            'maximum' => ['1000', 1000, (int)$sites[1]->id],
            'nonnumeric' => ['invalid', 100, (int)$sites[1]->id],
            'below-minimum' => ['99', 100, (int)$sites[1]->id],
            'above-maximum' => ['1001', 1000, (int)$sites[1]->id],
        ];
        $fixture = $this->importFixture($cases, 'supported-inputs');
        $filename = $fixture['filename'];

        $this->installRequest(['mapping' => [
            0 => 'slug',
            1 => 'fallbackUrl',
            2 => 'qrCodeSize',
            3 => 'siteId',
        ]]);

        try {
            $this->withSettings([
                'defaultQrSize' => 1000,
                'enabledSites' => [(int)$sites[0]->id, (int)$sites[1]->id],
            ], function() use ($fixture): void {
                $this->withImportSession($fixture['importData'], function() use ($fixture): void {
                    $controller = new DefaultQrSizeImportExportController('import-export', SmartLinkManager::$plugin);
                    $controller->actionPreview();

                    self::assertSame($fixture['expectedSizes'], $this->sizesBySlug($controller->renderedVariables['validRows'] ?? []));
                    self::assertSame([
                        'totalRows' => count($fixture['expectedSizes']),
                        'validRows' => count($fixture['expectedSizes']),
                        'duplicates' => 0,
                        'errors' => 0,
                    ], $controller->renderedVariables['summary'] ?? null);

                    $this->withSettings(['defaultQrSize' => 100], function() use ($controller): void {
                        $controller->actionImport();
                    });
                    self::assertSame($fixture['expectedSizes'], $this->persistedSizesBySlug(array_keys($fixture['expectedSizes'])));
                    self::assertSame($fixture['expectedSiteIds'], $this->persistedSiteIdsBySlug($fixture['expectedSiteIds']));
                });
            });
        } finally {
            $this->cleanupImportedFixture(array_keys($fixture['expectedSizes']), $filename);
        }
    }

    public function testUnmappedImportSizeUsesTheSameDefaultInPreviewAndFinalWrite(): void
    {
        $siteId = (int)Craft::$app->getSites()->getCurrentSite()->id;
        $fixture = $this->importFixture([
            'unmapped' => ['640', 1000, $siteId],
        ], 'unmapped-size');
        $filename = $fixture['filename'];
        $this->installRequest(['mapping' => [
            0 => 'slug',
            1 => 'fallbackUrl',
            3 => 'siteId',
        ]]);

        try {
            $this->withSettings([
                'defaultQrSize' => 1000,
                'enabledSites' => [$siteId],
            ], function() use ($fixture): void {
                $this->withImportSession($fixture['importData'], function() use ($fixture): void {
                    $controller = new DefaultQrSizeImportExportController('import-export', SmartLinkManager::$plugin);
                    $controller->actionPreview();

                    self::assertSame($fixture['expectedSizes'], $this->sizesBySlug($controller->renderedVariables['validRows'] ?? []));
                    $controller->actionImport();
                    self::assertSame($fixture['expectedSizes'], $this->persistedSizesBySlug(array_keys($fixture['expectedSizes'])));
                });
            });
        } finally {
            $this->cleanupImportedFixture(array_keys($fixture['expectedSizes']), $filename);
        }
    }

    #[DataProvider('absentImportSizes')]
    public function testImportPreviewAndPersistenceResolveTheSameDefault(mixed $value): void
    {
        $this->withSettings(['defaultQrSize' => 1000], function() use ($value): void {
            $previewSize = $this->resolveImportQrCodeSize($value);
            $smartLink = $this->persistImportRow($value, 'absent-size');

            self::assertSame(1000, $previewSize);
            self::assertSame($previewSize, $smartLink->qrCodeSize);
            self::assertSame($previewSize, $this->persistedQrSize($smartLink));
        });
    }

    /** @return iterable<string, array{value: mixed}> */
    public static function absentImportSizes(): iterable
    {
        yield 'column omitted' => ['value' => null];
        yield 'column unmapped' => ['value' => null];
        yield 'mapped value empty' => ['value' => ''];
        yield 'mapped value whitespace only' => ['value' => '   '];
    }

    #[DataProvider('explicitImportSizes')]
    public function testExplicitImportSizeWinsOverEffectiveDefault(int $size): void
    {
        $this->withSettings(['defaultQrSize' => $size === 1000 ? 100 : 1000], function() use ($size): void {
            $previewSize = $this->resolveImportQrCodeSize($size);
            $smartLink = $this->persistImportRow($size, 'explicit-size');

            self::assertSame($size, $previewSize);
            self::assertSame($size, $this->persistedQrSize($smartLink));
        });
    }

    /** @return iterable<string, array{size: int}> */
    public static function explicitImportSizes(): iterable
    {
        yield 'minimum' => ['size' => 100];
        yield 'standard' => ['size' => 256];
        yield 'maximum' => ['size' => 1000];
    }

    #[DataProvider('normalizedImportSizes')]
    public function testInvalidAndOutOfRangeImportSizesKeepExistingNormalization(mixed $value, int $expected): void
    {
        $this->withSettings(['defaultQrSize' => 700], function() use ($expected, $value): void {
            $previewSize = $this->resolveImportQrCodeSize($value);
            $smartLink = $this->persistImportRow($value, 'normalized-size');

            self::assertSame($expected, $previewSize);
            self::assertSame($expected, $this->persistedQrSize($smartLink));
        });
    }

    /** @return iterable<string, array{value: mixed, expected: int}> */
    public static function normalizedImportSizes(): iterable
    {
        yield 'nonnumeric' => ['value' => 'invalid', 'expected' => 100];
        yield 'below minimum' => ['value' => 99, 'expected' => 100];
        yield 'above maximum' => ['value' => 1001, 'expected' => 1000];
    }

    public function testMultipleImportRowsAndSitesKeepIndependentResolvedSizes(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));

        $this->withSettings([
            'defaultQrSize' => 1000,
            'enabledSites' => [(int)$sites[0]->id, (int)$sites[1]->id],
        ], function() use ($sites): void {
            $defaulted = $this->persistImportRow(null, 'first-site', (int)$sites[0]->id);
            $explicit = $this->persistImportRow(256, 'second-site', (int)$sites[1]->id);

            self::assertSame((int)$sites[0]->id, $defaulted->siteId);
            self::assertSame(1000, $this->persistedQrSize($defaulted));
            self::assertSame((int)$sites[1]->id, $explicit->siteId);
            self::assertSame(256, $this->persistedQrSize($explicit));
        });
    }

    #[DataProvider('supportedDefaultSizes')]
    public function testFactorySeedsEffectiveDefaultBeforeCallerConfiguration(int $defaultSize): void
    {
        $this->withSettings(['defaultQrSize' => $defaultSize], function() use ($defaultSize): void {
            $before = $this->countRows('{{%smartlinkmanager}}');
            $variable = new SmartLinkManagerVariable();

            $empty = $variable->create();
            $configured = $variable->create(['title' => 'Unsaved factory SmartLink']);
            $explicit = $variable->create(['qrCodeSize' => $defaultSize === 1000 ? 100 : 1000]);

            self::assertSame($defaultSize, $empty->qrCodeSize);
            self::assertSame($defaultSize, $configured->qrCodeSize);
            self::assertSame($defaultSize === 1000 ? 100 : 1000, $explicit->qrCodeSize);
            self::assertNull($empty->id);
            self::assertNull($configured->id);
            self::assertNull($explicit->id);
            self::assertSame($before, $this->countRows('{{%smartlinkmanager}}'));
        });
    }

    /** @return iterable<string, array{defaultSize: int}> */
    public static function supportedDefaultSizes(): iterable
    {
        yield 'minimum' => ['defaultSize' => 100];
        yield 'standard' => ['defaultSize' => 256];
        yield 'maximum' => ['defaultSize' => 1000];
    }

    private function persistImportRow(mixed $qrCodeSize, string $kind, ?int $siteId = null): SmartLink
    {
        $slug = str_replace('_', '-', $this->nextTestMarker(self::MARKER, $kind));
        $smartLink = new SmartLink();
        $smartLink->siteId = $siteId ?? Craft::$app->getSites()->getPrimarySite()->id;
        $row = [
            'slug' => $slug,
            'title' => 'Imported QR size ' . $slug,
            'fallbackUrl' => 'https://example.com/' . $slug,
            'qrCodeSize' => $qrCodeSize,
        ];

        $controller = new ImportExportController('import-export', SmartLinkManager::$plugin);
        $method = new \ReflectionMethod($controller, 'applyRowToSmartLink');
        $method->invoke($controller, $smartLink, $row, true);
        $smartLink->setEnabledForSite(true);

        self::assertTrue(
            $this->smartLinks->saveSmartLink($smartLink),
            'Imported SmartLink must save: ' . json_encode($smartLink->getErrors()),
        );
        self::assertNotNull($smartLink->id);
        $this->trackElementForCleanup((int)$smartLink->id);

        return $smartLink;
    }

    private function resolveImportQrCodeSize(mixed $value): int
    {
        $controller = new ImportExportController('import-export', SmartLinkManager::$plugin);
        $method = new \ReflectionMethod($controller, 'resolveImportQrCodeSize');
        $resolved = $method->invoke($controller, $value);
        self::assertIsInt($resolved);

        return $resolved;
    }

    private function persistedQrSize(SmartLink $smartLink): int
    {
        self::assertNotNull($smartLink->id);
        $row = $this->fetchRow('{{%smartlinkmanager}}', ['id' => $smartLink->id]);
        self::assertNotNull($row);

        return (int)$row['qrCodeSize'];
    }

    /**
     * @param array<string, array{0: mixed, 1: int, 2: int}> $cases
     * @return array{
     *   filename: string,
     *   importData: array{headers: list<string>, allRows: list<array<int, mixed>>, rowCount: int, filename: string, filesize: int},
     *   expectedSizes: array<string, int>,
     *   expectedSiteIds: array<string, int>
     * }
     */
    private function importFixture(array $cases, string $suffix): array
    {
        $rows = [];
        $expectedSizes = [];
        $expectedSiteIds = [];

        foreach ($cases as $case => [$size, $expectedSize, $siteId]) {
            $slug = str_replace('_', '-', $this->nextTestMarker(self::MARKER, $suffix . '-' . $case));
            $rows[] = [$slug, 'https://example.com/' . $slug, $size, $siteId];
            $expectedSizes[$slug] = $expectedSize;
            $expectedSiteIds[$slug] = $siteId;
        }

        $filename = str_replace('_', '-', $this->nextTestMarker(self::MARKER, $suffix)) . '.csv';

        return [
            'filename' => $filename,
            'importData' => [
                'headers' => ['slug', 'fallbackUrl', 'qrCodeSize', 'siteId'],
                'allRows' => $rows,
                'rowCount' => count($rows),
                'filename' => $filename,
                'filesize' => 321,
            ],
            'expectedSizes' => $expectedSizes,
            'expectedSiteIds' => $expectedSiteIds,
        ];
    }

    /** @param list<array<string, mixed>> $rows @return array<string, int> */
    private function sizesBySlug(array $rows): array
    {
        $sizes = [];
        foreach ($rows as $row) {
            $sizes[(string)$row['slug']] = (int)$row['qrCodeSize'];
        }

        return $sizes;
    }

    /** @param list<string> $slugs @return array<string, int> */
    private function persistedSizesBySlug(array $slugs): array
    {
        $rows = (new Query())
            ->select(['slug', 'qrCodeSize'])
            ->from('{{%smartlinkmanager}}')
            ->where(['slug' => $slugs])
            ->orderBy(['id' => SORT_ASC])
            ->all();

        return $this->sizesBySlug($rows);
    }

    /** @param array<string, int> $expectedSiteIds @return array<string, int> */
    private function persistedSiteIdsBySlug(array $expectedSiteIds): array
    {
        $siteIds = [];
        foreach ($expectedSiteIds as $slug => $siteId) {
            $element = SmartLink::find()->slug($slug)->siteId($siteId)->status(null)->one();
            self::assertInstanceOf(SmartLink::class, $element);
            $siteIds[$slug] = (int)$element->siteId;
        }

        return $siteIds;
    }

    /**
     * @param array<string, mixed> $importData
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    private function withImportSession(array $importData, callable $callback): mixed
    {
        $originalApplication = Craft::$app;
        $session = new DefaultQrSizeSession();
        $application = new DefaultQrSizeApplication($originalApplication, $session, new DefaultQrSizeImportUser());
        Craft::$app = $application;
        \Yii::$app = $application;
        $session->set('smartlink-import', $importData);

        try {
            return $callback();
        } finally {
            Craft::$app = $originalApplication;
            \Yii::$app = $originalApplication;
        }
    }

    /** @param list<string> $slugs */
    private function cleanupImportedFixture(array $slugs, string $filename): void
    {
        $ids = (new Query())
            ->select(['id'])
            ->from('{{%smartlinkmanager}}')
            ->where(['slug' => $slugs])
            ->column();
        foreach ($ids as $id) {
            $element = SmartLink::find()->id((int)$id)->site('*')->status(null)->trashed(null)->one();
            if ($element !== null) {
                Craft::$app->getElements()->deleteElement($element, true);
            }
        }

        Db::delete('{{%smartlinkmanager_import_history}}', ['filename' => $filename]);
    }

    /** @param array<string, mixed> $bodyParams */
    private function installRequest(array $bodyParams = []): void
    {
        if ($this->originalRequest === null) {
            $this->originalRequest = Craft::$app->get('request');
        }
        if ($this->originalResponse === null) {
            $this->originalResponse = Craft::$app->get('response');
        }

        Craft::$app->set('request', new DefaultQrSizeRequest($bodyParams));
        Craft::$app->set('response', new Response());
    }
}

final class DefaultQrSizeSmartlinksController extends SmartlinksController
{
    /** @var array<string, mixed> */
    public array $renderedVariables = [];

    public function requirePermission(string $permissionName): void
    {
        // The test isolates default propagation; permission behavior has its own family.
    }

    /** @param array<string, mixed> $params */
    protected function logError(string $message, array $params = []): void
    {
        if ($message === 'Smart link save error') {
            throw new \RuntimeException((string)($params['error'] ?? $message));
        }
    }

    /** @param array<string, mixed> $variables */
    public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): Response
    {
        $this->renderedVariables = $variables;
        $response = Craft::$app->getResponse();
        $response->content = 'rendered:' . $template;

        return $response;
    }
}

final class DefaultQrSizeImportExportController extends ImportExportController
{
    /** @var array<string, mixed> */
    public array $renderedVariables = [];

    /** @param array<string, mixed> $variables */
    public function renderTemplate(string $template, array $variables = [], ?string $templateMode = null): Response
    {
        $this->renderedVariables = $variables;

        return Craft::$app->getResponse();
    }
}

final class DefaultQrSizeRequest extends Request
{
    /** @param array<string, mixed> $bodyParams */
    public function __construct(private readonly array $bodyParams)
    {
        parent::__construct();
    }

    public function getBodyParam($name, $defaultValue = null): mixed
    {
        return $this->bodyParams[$name] ?? $defaultValue;
    }

    public function getValidatedBodyParam(string $name): ?string
    {
        $value = $this->bodyParams[$name] ?? null;

        return is_scalar($value) ? (string)$value : null;
    }

    public function getQueryParam($name, $defaultValue = null): mixed
    {
        return $defaultValue;
    }

    public function getIsPost(): bool
    {
        return true;
    }

    public function getAcceptsJson(): bool
    {
        return true;
    }

    public function getIsAjax(): bool
    {
        return false;
    }

    public function getIsCpRequest(): bool
    {
        return false;
    }
}

final class DefaultQrSizeApplication extends ConsoleApplication
{
    public function __construct(
        private readonly ConsoleApplication $application,
        private readonly DefaultQrSizeSession $session,
        private readonly DefaultQrSizeImportUser $user,
    ) {
    }

    public function getSession(): DefaultQrSizeSession
    {
        return $this->session;
    }

    public function get($id, $throwException = true): ?object
    {
        if ($id === 'session') {
            return $this->session;
        }
        if ($id === 'user') {
            return $this->user;
        }

        return $this->application->get($id, $throwException);
    }

    public function has($id, $checkInstance = false): bool
    {
        return in_array($id, ['session', 'user'], true) || $this->application->has($id, $checkInstance);
    }
}

final class DefaultQrSizeImportUser extends ConsoleUser
{
    public function checkPermission(string $permissionName): bool
    {
        return true;
    }

    public function getId(): ?int
    {
        return 1;
    }

    public function getIsGuest(): bool
    {
        return false;
    }
}

final class DefaultQrSizeSession extends Session
{
    /** @var array<string, mixed> */
    private array $values = [];

    public function has($key): bool
    {
        return array_key_exists((string)$key, $this->values);
    }

    public function get($key, $defaultValue = null): mixed
    {
        return $this->values[(string)$key] ?? $defaultValue;
    }

    public function set($key, $value): void
    {
        $this->values[(string)$key] = $value;
    }

    public function remove($key): mixed
    {
        $key = (string)$key;
        $value = $this->values[$key] ?? null;
        unset($this->values[$key]);

        return $value;
    }

    public function setNotice(string $message, array $settings = []): void
    {
        $this->set('notice', $message);
    }

    public function setError(string $message, array $settings = []): void
    {
        $this->set('error', $message);
    }
}
