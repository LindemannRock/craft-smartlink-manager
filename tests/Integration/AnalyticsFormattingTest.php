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
use craft\helpers\Json;
use craft\helpers\StringHelper;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Covers analytics display/export formatting that depends on plugin settings.
 *
 * @since 5.30.0
 */
final class AnalyticsFormattingTest extends TestCase
{
    public function testClicksChartLabelsFollowPluginDateSettings(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);
        $now = new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone()));

        $this->insertAnalyticsRow($link->id, $site->id, $now);

        $this->withSettings([
            'monthFormat' => 'long',
            'dateOrder' => 'dmy',
            'dateSeparator' => '.',
        ], function() use ($link, $site, $now): void {
            $data = $this->analytics->getClicksData($link->id, 'today', $site->id);
            $expected = DateFormatHelper::formatDate($now, 'cascade', false, false, 'smartlink-manager');

            self::assertSame([$expected], $data['labels']);
            self::assertSame([1], $data['values']);
            self::assertNotSame([date('M j')], $data['labels'], 'Chart labels must not fall back to PHP date().');
        });
    }

    public function testHourlyPeakLabelFollowsPluginTimeSettings(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);
        $timezone = new \DateTimeZone(Craft::$app->getTimeZone());
        $peakTime = new \DateTime('today 15:15:00', $timezone);

        $this->insertAnalyticsRow($link->id, $site->id, $peakTime);
        $this->insertAnalyticsRow($link->id, $site->id, $peakTime->modify('+10 minutes'));

        $this->withSettings([
            'timeFormat' => '24',
            'showSeconds' => false,
        ], function() use ($link, $site): void {
            $data = $this->analytics->getHourlyAnalytics($link->id, 'today', $site->id);

            self::assertSame(15, $data['peakHour']);
            self::assertSame(2, $data['data'][15]);
            self::assertSame('15:00', $data['peakHourFormatted']);
        });
    }

    public function testExportDataFormatsLandingButtonMetadataAndCustomDomainUrl(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink([
            'title' => 'Exported SmartLink',
            'slug' => 'smartlink-test-export-landing-button',
            'siteId' => $site->id,
        ]);

        $this->insertAnalyticsRow(
            $link->id,
            $site->id,
            new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone())),
            [
                'source' => 'landing',
                'clickType' => 'button',
                'platform' => 'android',
                'buttonUrl' => 'https://example.com/android',
            ],
        );

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://smart.example',
            'usePrefix' => false,
            'slugPrefix' => 'go',
            'enableGeoDetection' => false,
        ], function() use ($link, $site): void {
            $rows = $this->analytics->getExportData($link->id, 'today', $site->id);

            self::assertCount(1, $rows);
            self::assertSame('Exported SmartLink', $rows[0]['name']);
            self::assertSame('https://smart.example/smartlink-test-export-landing-button', $rows[0]['smartLinkUrl']);
            self::assertSame($site->name, $rows[0]['siteName']);
            self::assertSame('Landing', $rows[0]['source']);
            self::assertSame('Button', $rows[0]['clickType']);
            self::assertSame('Android', $rows[0]['platform']);
            self::assertSame('https://example.com/android', $rows[0]['destinationUrl']);
            self::assertSame('Yes', $rows[0]['isRobot']);
            self::assertSame('Blink', $rows[0]['browserEngine']);
            self::assertSame('en', $rows[0]['language']);
            self::assertArrayNotHasKey('country', $rows[0], 'Geo columns should be omitted when geo export is disabled.');
        });
    }

    public function testSmartLinkAnalyticsAggregatesButtonClicksByPlatform(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);
        $now = new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone()));

        $this->insertAnalyticsRow($link->id, $site->id, $now, [
            'clickType' => 'button',
            'platform' => 'ios',
        ]);
        $this->insertAnalyticsRow($link->id, $site->id, $now, [
            'clickType' => 'button',
            'platform' => 'android',
        ]);
        $this->insertAnalyticsRow($link->id, $site->id, $now, [
            'clickType' => 'button',
            'platform' => 'android',
        ]);

        $analytics = $this->analytics->getSmartLinkAnalytics($link->id, 'today', $site->id);

        self::assertSame(3, $analytics['buttonClicks']['total']);
        self::assertSame([
            'android' => 2,
            'ios' => 1,
        ], $analytics['buttonClicks']['byPlatform']);
    }

    public function testExportDataReadsLegacyAndUnusualMetadataWithoutOffsetErrors(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);
        $now = new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone()));

        $this->insertAnalyticsRow($link->id, $site->id, $now, Json::encode([
            'source' => 'landing',
            'clickType' => 'button',
            'platform' => 'ios',
        ]));
        $this->insertAnalyticsRow($link->id, $site->id, $now, null);
        $this->insertAnalyticsRow($link->id, $site->id, $now, '');
        $this->insertAnalyticsRow($link->id, $site->id, $now, 'not-json');

        $rows = $this->analytics->getExportData($link->id, 'today', $site->id);
        $sources = array_column($rows, 'source');
        $clickTypes = array_column($rows, 'clickType');
        sort($sources);
        sort($clickTypes);

        self::assertCount(4, $rows);
        self::assertSame(['Direct', 'Direct', 'Direct', 'Landing'], $sources);
        self::assertSame(['Button', 'Redirect', 'Redirect', 'Redirect'], $clickTypes);
    }

    public function testSiteScopedEnrichmentKeepsLocalizedVariantAndLatestInteractionTogether(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));
        [$siteA, $siteB] = $sites;
        $link = $this->seedSmartLink([
            'siteId' => $siteA->id,
            'title' => 'Site A analytics title',
            'fallbackUrl' => 'https://example.com/site-a',
        ]);

        $siteBLink = SmartLink::find()->id($link->id)->siteId($siteB->id)->status(null)->one();
        self::assertInstanceOf(SmartLink::class, $siteBLink);
        $siteBLink->title = 'Site B analytics title';
        $siteBLink->fallbackUrl = 'https://example.com/site-b';
        self::assertTrue(Craft::$app->getElements()->saveElement($siteBLink));

        $now = new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone()));
        $this->insertAnalyticsRow($link->id, $siteA->id, $now->modify('-1 minute'), [
            'source' => 'direct',
            'clickType' => 'redirect',
            'redirectUrl' => 'https://example.com/latest-site-a',
        ]);
        $this->insertAnalyticsRow($link->id, $siteB->id, $now, [
            'source' => 'direct',
            'clickType' => 'redirect',
            'redirectUrl' => 'https://example.com/latest-site-b',
        ]);

        $topLinks = $this->analytics->getTopLinks('today', 5, $siteA->id);
        self::assertCount(1, $topLinks);
        self::assertSame('Site A analytics title', $topLinks[0]['name']);
        self::assertSame('https://example.com/latest-site-a', $topLinks[0]['lastDestinationUrl']);

        $exportRows = $this->analytics->getExportData($link->id, 'today', $siteB->id);
        self::assertCount(1, $exportRows);
        self::assertSame('Site B analytics title', $exportRows[0]['name']);
    }

    public function testLinksUsedRequiresTheAnalyticsSiteVariantToBeEnabled(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));
        [$siteA, $siteB] = $sites;
        $link = $this->seedSmartLink(['siteId' => $siteA->id]);
        $siteAVariant = SmartLink::find()->id($link->id)->siteId($siteA->id)->status(null)->one();
        $siteBVariant = SmartLink::find()->id($link->id)->siteId($siteB->id)->status(null)->one();
        self::assertInstanceOf(SmartLink::class, $siteAVariant);
        self::assertInstanceOf(SmartLink::class, $siteBVariant);
        $siteAVariant->setEnabledForSite(false);
        self::assertTrue(Craft::$app->getElements()->saveElement($siteAVariant));
        self::assertSame(SmartLink::STATUS_ENABLED, $siteBVariant->getStatus());

        $this->insertAnalyticsRow(
            (int)$link->id,
            $siteA->id,
            new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone())),
        );

        $summary = $this->analytics->getAnalyticsSummary('today', null, $siteA->id);
        self::assertSame(1, $summary['totalClicks']);
        self::assertSame(0, $summary['activeLinks']);
        self::assertSame(0, $summary['linksUsed']);
        self::assertSame([], $summary['topLinks']);
    }

    public function testLatestInteractionTieIsDeterministicWithinEachSite(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);
        $time = new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone()));
        $this->insertAnalyticsRow($link->id, $site->id, $time, [
            'clickType' => 'redirect',
            'redirectUrl' => 'https://example.com/first-tied-interaction',
        ]);
        $this->insertAnalyticsRow($link->id, $site->id, $time, [
            'clickType' => 'redirect',
            'redirectUrl' => 'https://example.com/second-tied-interaction',
        ]);

        $topLinks = $this->analytics->getTopLinks('today', 5, $site->id);

        self::assertCount(1, $topLinks);
        self::assertSame(2, $topLinks[0]['clicks']);
        self::assertSame($site->id, $topLinks[0]['siteId']);
        self::assertSame('https://example.com/second-tied-interaction', $topLinks[0]['lastDestinationUrl']);
    }

    public function testAllSitesTopLinksKeepsOneLocalizedRowPerSite(): void
    {
        $sites = array_values(Craft::$app->getSites()->getAllSites(false));
        self::assertGreaterThanOrEqual(2, count($sites));
        [$siteA, $siteB] = $sites;
        $link = $this->seedSmartLink([
            'siteId' => $siteA->id,
            'title' => 'All sites title A',
        ]);
        $siteBLink = SmartLink::find()->id($link->id)->siteId($siteB->id)->status(null)->one();
        self::assertInstanceOf(SmartLink::class, $siteBLink);
        $siteBLink->title = 'All sites title B';
        self::assertTrue(Craft::$app->getElements()->saveElement($siteBLink));
        $time = new \DateTime('now', new \DateTimeZone(Craft::$app->getTimeZone()));
        $this->insertAnalyticsRow($link->id, $siteA->id, $time, null);
        $this->insertAnalyticsRow($link->id, $siteB->id, $time, null);

        $topLinks = $this->analytics->getTopLinks('today', 5, null);
        $ownedRows = array_values(array_filter(
            $topLinks,
            static fn(array $row): bool => (int)$row['id'] === (int)$link->id,
        ));

        self::assertCount(2, $ownedRows);
        self::assertSame([$siteA->id, $siteB->id], array_column($ownedRows, 'siteId'));
        self::assertSame(['All sites title A', 'All sites title B'], array_column($ownedRows, 'name'));
        self::assertSame(['Unknown', 'Unknown'], array_column($ownedRows, 'lastInteractionType'));
    }

    private function insertAnalyticsRow(int $linkId, int $siteId, \DateTime $dateCreated, mixed $metadata = null): void
    {
        $dateCreated = clone $dateCreated;
        $dateCreated->setTimezone(new \DateTimeZone('UTC'));
        $date = $dateCreated->format('Y-m-d H:i:s');

        Craft::$app->db->createCommand()
            ->insert('{{%smartlinkmanager_analytics}}', [
                'linkId' => $linkId,
                'siteId' => $siteId,
                'deviceType' => 'mobile',
                'osName' => 'Android',
                'browser' => 'Chrome',
                'browserEngine' => 'Blink',
                'language' => 'en',
                'isRobot' => true,
                'metadata' => $metadata,
                'dateCreated' => $date,
                'dateUpdated' => $date,
                'uid' => StringHelper::UUID(),
            ])
            ->execute();
    }
}
