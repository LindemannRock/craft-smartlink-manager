<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests;

use Craft;
use lindemannrock\base\testing\IntegrationTestCase;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\services\AnalyticsService;
use lindemannrock\smartlinkmanager\services\SmartLinksService;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Base test case for smartlink-manager integration tests.
 *
 * Extends the shared {@see IntegrationTestCase} for component snapshot/restore
 * and generic Query helpers, and layers plugin-specific shorthand on top:
 *  - direct accessors for `smartLinks` / `analytics` services
 *  - per-test marker prefix + DB purge helpers covering the element table and
 *    its analytics rows (FK CASCADE handles the rest)
 *  - {@see seedSmartLink()} convenience for spinning up a saved element with
 *    a marker slug
 *
 * Subclasses can override `setUp()` for additional fixture work but should
 * call `parent::setUp()` to keep marker-based isolation working.
 *
 * @since 5.28.0
 */
abstract class TestCase extends IntegrationTestCase
{
    /**
     * Marker prefix used for every test-seeded smart link slug. The
     * smartlinkmanager table has a UNIQUE index on `slug` and the analytics
     * table is FK-linked to {{%smartlinkmanager}}.id; deleting the element
     * rows drains the rest via FK CASCADE.
     */
    protected const MARKER = 'smartlink-test-';

    protected SmartLinksService $smartLinks;
    protected AnalyticsService $analytics;

    /**
     * @var list<callable(): void>
     */
    private array $settingsOverrideRestorers = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->smartLinks = SmartLinkManager::$plugin->smartLinks;
        $this->analytics = SmartLinkManager::$plugin->analytics;
        $this->purgeTestSmartLinks();
    }

    protected function tearDown(): void
    {
        try {
            $this->restoreSettingsOverrides();
        } finally {
            parent::tearDown();
        }
    }

    /**
     * Seed a saved {@see SmartLink} element with a marker slug. Built directly
     * (not via service) so the test pins the slug — `saveSmartLink()` runs the
     * full validation pipeline, which we want, but we still need the marker on
     * the column so `purgeTestSmartLinks()` can find the row by LIKE prefix.
     *
     * Slugs are constrained to `[a-zA-Z0-9_\-]+`; base helper underscores are
     * normalized to hyphens to keep the marker in the plugin's usual slug shape.
     *
     * @param array<string, mixed> $overrides
     */
    protected function seedSmartLink(array $overrides = []): SmartLink
    {
        $marker = str_replace('_', '-', $this->nextTestMarker(self::MARKER, 'link'));

        $element = new SmartLink();
        $element->title = $overrides['title'] ?? 'Test SmartLink ' . $marker;
        $element->slug = $overrides['slug'] ?? $marker;
        $element->fallbackUrl = $overrides['fallbackUrl'] ?? 'https://example.com/fallback';
        $element->iosUrl = $overrides['iosUrl'] ?? null;
        $element->androidUrl = $overrides['androidUrl'] ?? null;
        $element->trackAnalytics = $overrides['trackAnalytics'] ?? true;
        $element->postDate = $overrides['postDate'] ?? null;
        $element->dateExpired = $overrides['dateExpired'] ?? null;
        $element->siteId = $overrides['siteId'] ?? Craft::$app->getSites()->getPrimarySite()->id;
        $element->setEnabledForSite($overrides['enabled'] ?? true);

        $this->assertTrue(
            $this->smartLinks->saveSmartLink($element),
            'Seeded smart link must save — errors: ' . json_encode($element->getErrors()),
        );

        if ($element->id !== null) {
            $this->trackElementForCleanup((int) $element->id);
        }

        return $element;
    }

    /**
     * Reload a smart link from the DB and return the persisted `hits` count.
     * Bypasses any in-memory model state the service might hold.
     */
    protected function fetchHitsFromDb(int $id): int
    {
        $row = $this->fetchRow('{{%smartlinkmanager}}', ['id' => $id]);
        $this->assertNotNull($row, "Smart link row {$id} not found.");

        return (int) $row['hits'];
    }

    /**
     * Temporarily override plugin settings on the live settings model.
     *
     * @param array<string, mixed> $overrides
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    protected function withSettings(array $overrides, callable $callback): mixed
    {
        $restore = $this->createSettingsOverride($overrides);

        try {
            return $callback();
        } finally {
            $restore();
        }
    }

    /**
     * Apply a settings override for the rest of the current test.
     *
     * @param array<string, mixed> $overrides
     */
    protected function applySettingsForTest(array $overrides): void
    {
        $this->settingsOverrideRestorers[] = $this->createSettingsOverride($overrides);
    }

    /**
     * Create a scoped override that beats both DB-backed settings and the
     * workspace's config/smartlink-manager.php file.
     *
     * @param array<string, mixed> $overrides
     * @return callable(): void
     */
    private function createSettingsOverride(array $overrides): callable
    {
        $config = Craft::$app->getConfig();
        $previousConfigDir = $config->configDir;
        $originalConfig = $config->getConfigFromFile('smartlink-manager');
        $testConfig = array_merge(is_array($originalConfig) ? $originalConfig : [], $overrides);

        $tempDir = Craft::$app->getPath()->getTempPath()
            . DIRECTORY_SEPARATOR
            . 'smartlink-manager-test-config-' . bin2hex(random_bytes(4));

        if (!is_dir($tempDir) && !mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
            throw new \RuntimeException("Unable to create temporary config directory: {$tempDir}");
        }

        file_put_contents(
            $tempDir . DIRECTORY_SEPARATOR . 'smartlink-manager.php',
            "<?php\nreturn " . var_export($testConfig, true) . ";\n",
        );

        $settings = SmartLinkManager::$plugin->getSettings();
        $previous = [];

        foreach ($overrides as $attribute => $value) {
            $previous[$attribute] = $settings->{$attribute};
            $settings->{$attribute} = $value;
        }

        $config->configDir = $tempDir;
        DateFormatHelper::clearConfigCache('smartlink-manager');

        $restored = false;

        return static function() use (
            $config,
            $previousConfigDir,
            $settings,
            $previous,
            $tempDir,
            &$restored,
        ): void {
            if ($restored) {
                return;
            }

            $config->configDir = $previousConfigDir;

            foreach ($previous as $attribute => $value) {
                $settings->{$attribute} = $value;
            }

            DateFormatHelper::clearConfigCache('smartlink-manager');
            \craft\helpers\FileHelper::removeDirectory($tempDir);
            $restored = true;
        };
    }

    private function restoreSettingsOverrides(): void
    {
        while ($restore = array_pop($this->settingsOverrideRestorers)) {
            $restore();
        }
    }

    /**
     * DELETE FROM {%smartlinkmanager} WHERE slug LIKE 'smartlink-test-%' —
     * the FK CASCADE drains the rest. We route deletion through the elements
     * service so soft-deleted rows in {{%elements}} stay consistent.
     */
    protected function purgeTestSmartLinks(): void
    {
        $ids = (new \craft\db\Query())
            ->from('{{%smartlinkmanager}}')
            ->where(['like', 'slug', self::MARKER . '%', false])
            ->select(['id'])
            ->column();

        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            $element = SmartLink::find()->id((int) $id)->status(null)->one();
            if ($element !== null) {
                Craft::$app->elements->deleteElement($element, true);
            }
        }
    }
}
