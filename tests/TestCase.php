<?php

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests;

use Craft;
use lindemannrock\base\testing\IntegrationTestCase;
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
    protected const MARKER = '__smartlink_test_';

    protected SmartLinksService $smartLinks;
    protected AnalyticsService $analytics;

    private int $seedCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smartLinks = SmartLinkManager::$plugin->smartLinks;
        $this->analytics = SmartLinkManager::$plugin->analytics;
        $this->seedCounter = 0;
        $this->purgeTestSmartLinks();
    }

    protected function tearDown(): void
    {
        $this->purgeTestSmartLinks();
        parent::tearDown();
    }

    /**
     * Seed a saved {@see SmartLink} element with a marker slug. Built directly
     * (not via service) so the test pins the slug — `saveSmartLink()` runs the
     * full validation pipeline, which we want, but we still need the marker on
     * the column so `purgeTestSmartLinks()` can find the row by LIKE prefix.
     *
     * Slugs are constrained to `[a-zA-Z0-9_\-]+`; the marker prefix satisfies
     * that pattern so the underscore-friendly form is safe.
     *
     * @param array<string, mixed> $overrides
     */
    protected function seedSmartLink(array $overrides = []): SmartLink
    {
        $this->seedCounter++;
        $marker = self::MARKER . $this->seedCounter . '_' . substr(uniqid('', true), -8);

        $element = new SmartLink();
        $element->title = $overrides['title'] ?? 'Test SmartLink ' . $this->seedCounter;
        $element->slug = $overrides['slug'] ?? $marker;
        $element->fallbackUrl = $overrides['fallbackUrl'] ?? 'https://example.com/fallback';
        $element->iosUrl = $overrides['iosUrl'] ?? null;
        $element->androidUrl = $overrides['androidUrl'] ?? null;
        $element->trackAnalytics = $overrides['trackAnalytics'] ?? true;
        $element->siteId = $overrides['siteId'] ?? Craft::$app->getSites()->getPrimarySite()->id;
        $element->setEnabledForSite($overrides['enabled'] ?? true);

        $this->assertTrue(
            $this->smartLinks->saveSmartLink($element),
            'Seeded smart link must save — errors: ' . json_encode($element->getErrors()),
        );

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
     * DELETE FROM {%smartlinkmanager} WHERE slug LIKE '__smartlink_test_%' —
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
