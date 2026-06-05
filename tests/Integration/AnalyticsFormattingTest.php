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
            self::assertArrayNotHasKey('country', $rows[0], 'Geo columns should be omitted when geo export is disabled.');
        });
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function insertAnalyticsRow(int $linkId, int $siteId, \DateTime $dateCreated, array $metadata = []): void
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
                'metadata' => !empty($metadata) ? Json::encode($metadata) : null,
                'dateCreated' => $date,
                'dateUpdated' => $date,
                'uid' => StringHelper::UUID(),
            ])
            ->execute();
    }
}
