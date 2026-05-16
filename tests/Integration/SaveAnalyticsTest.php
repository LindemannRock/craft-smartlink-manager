<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use craft\helpers\Json;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins the contract for {@see \lindemannrock\smartlinkmanager\services\AnalyticsService::saveAnalytics()}
 * (which delegates to {@see \lindemannrock\smartlinkmanager\services\analytics\AnalyticsTrackingService::saveAnalytics()}).
 *
 * `saveAnalytics()` is the IP-aware workhorse beneath `trackClick()`. Tests
 * target it directly because it accepts `metadata['ip']` explicitly, whereas
 * `trackClick()` overrides metadata['ip'] with `Craft::$app->request->getUserIP()`
 * — a method missing from the console Request the test bootstrap uses (see
 * `HitCounterIncrementTest` for the swap pattern when trackClick is needed).
 *
 * Covers:
 *  - happy path: a save writes one row keyed by linkId with the expected
 *    deviceInfo fields, userAgent, referrer, and serialized metadata
 *  - the IP NEVER appears in the stored `metadata` blob (the helper strips
 *    `metadata['ip']` before JSON-encoding — privacy contract)
 *  - `ipHashSalt` controls the stored `ip` column — same IP + same salt →
 *    same hash, deterministic SHA-256
 *  - `anonymizeIpAddress` masks the last IPv4 octet *before* hashing so
 *    `192.168.1.42` and `192.168.1.99` collapse to the same hash
 *  - the unconfigured-salt sentinel `$SMARTLINK_MANAGER_IP_SALT` does NOT
 *    crash the insert; the row lands with `ip = null` instead of writing
 *    an unsalted hash
 *
 * @since 5.28.0
 */
final class SaveAnalyticsTest extends TestCase
{
    private const TEST_SALT = '0123456789abcdef0123456789abcdef';

    /** Track + restore settings mutations across tests. */
    private ?string $savedSalt = null;
    private bool $savedAnonymize = false;
    private bool $savedEnableGeo = false;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $this->savedSalt = $settings->ipHashSalt;
        $this->savedAnonymize = $settings->anonymizeIpAddress;
        $this->savedEnableGeo = $settings->enableGeoDetection;

        $settings->ipHashSalt = self::TEST_SALT;
        $settings->anonymizeIpAddress = false;
        $settings->enableGeoDetection = false;
    }

    protected function tearDown(): void
    {
        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->ipHashSalt = $this->savedSalt;
        $settings->anonymizeIpAddress = $this->savedAnonymize;
        $settings->enableGeoDetection = $this->savedEnableGeo;

        parent::tearDown();
    }

    public function testSaveAnalyticsWritesRowWithDeviceAndMetadataFields(): void
    {
        $link = $this->seedSmartLink();
        $deviceInfo = [
            'deviceType' => 'desktop',
            'browser' => 'TestBrowser',
            'browserVersion' => '99.0',
            'osName' => 'TestOS',
            'osVersion' => '0',
            'userAgent' => 'Mozilla/5.0 (Test) SmartLinkManagerStub/1.0',
        ];
        $metadata = [
            'ip' => '203.0.113.42',
            'referrer' => 'https://example.com/some/page',
            'siteId' => $link->siteId,
            'source' => 'qr',
        ];

        $this->assertTrue($this->analytics->saveAnalytics($link->id, $deviceInfo, $metadata));

        $row = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
        $this->assertNotNull($row, 'saveAnalytics() should persist a single analytics row.');
        $this->assertSame($link->siteId, (int) $row['siteId']);
        $this->assertSame('desktop', $row['deviceType']);
        $this->assertSame('TestBrowser', $row['browser']);
        $this->assertSame('TestOS', $row['osName']);
        $this->assertSame('Mozilla/5.0 (Test) SmartLinkManagerStub/1.0', $row['userAgent']);
        $this->assertSame('https://example.com/some/page', $row['referrer']);

        $stored = Json::decode($row['metadata']);
        $this->assertSame('qr', $stored['source']);
        $this->assertArrayNotHasKey(
            'ip',
            $stored,
            'IP must be stripped from the metadata blob — it only ever belongs in the hashed `ip` column.',
        );
    }

    public function testSaveAnalyticsHashesIpDeterministicallyWithSalt(): void
    {
        $link = $this->seedSmartLink();
        $expectedHash = hash('sha256', '203.0.113.42' . self::TEST_SALT);

        $this->assertTrue($this->analytics->saveAnalytics($link->id, [], ['ip' => '203.0.113.42']));

        $row = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
        $this->assertNotNull($row);
        $this->assertSame($expectedHash, $row['ip'], 'Stored IP must be sha256(ip+salt) — deterministic and salt-bound.');
        $this->assertSame(64, strlen($row['ip']), 'SHA-256 hex output is 64 chars.');
    }

    public function testSaveAnalyticsProducesSameHashForSameIpAcrossCalls(): void
    {
        $link = $this->seedSmartLink();

        $this->analytics->saveAnalytics($link->id, [], ['ip' => '203.0.113.42']);
        $this->analytics->saveAnalytics($link->id, [], ['ip' => '203.0.113.42']);

        $hashes = (new \craft\db\Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $link->id])
            ->select(['ip'])
            ->column();

        $this->assertCount(2, $hashes, 'Two saveAnalytics() calls should produce two analytics rows.');
        $this->assertSame($hashes[0], $hashes[1], 'Same IP + same salt → same hash. Repeat visitors are correlatable.');
    }

    public function testSaveAnalyticsAnonymizesIpv4BeforeHashing(): void
    {
        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->anonymizeIpAddress = true;

        $link = $this->seedSmartLink();
        // Two IPs in the same /24 must collapse to the same anonymised form
        // (192.168.1.0), then hash to the same value.
        $this->analytics->saveAnalytics($link->id, [], ['ip' => '192.168.1.42']);
        $this->analytics->saveAnalytics($link->id, [], ['ip' => '192.168.1.99']);

        $hashes = (new \craft\db\Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['linkId' => $link->id])
            ->select(['ip'])
            ->column();

        $this->assertCount(2, $hashes);
        $expected = hash('sha256', '192.168.1.0' . self::TEST_SALT);
        $this->assertSame($expected, $hashes[0]);
        $this->assertSame($expected, $hashes[1], 'IP anonymisation must run BEFORE hashing, so /24 neighbours share a hash.');
    }

    public function testSaveAnalyticsWithUnconfiguredSaltStillWritesRowButNullsTheIp(): void
    {
        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        // Mirror the production "not configured" sentinel value defined in
        // AnalyticsTrackingService::_hashIpWithSalt() — this string is treated
        // as "no salt set" and trips the hash-error branch in
        // AnalyticsIpHelper::prepare(), which must NOT propagate the exception.
        $settings->ipHashSalt = '$SMARTLINK_MANAGER_IP_SALT';

        $link = $this->seedSmartLink();
        $this->assertTrue(
            $this->analytics->saveAnalytics($link->id, [], ['ip' => '203.0.113.42']),
            'A missing salt must not crash saveAnalytics — the row should still land.',
        );

        $row = $this->fetchRow('{{%smartlinkmanager_analytics}}', ['linkId' => $link->id]);
        $this->assertNotNull($row);
        $this->assertNull($row['ip'], 'Without a salt, the IP must be persisted as null rather than an unsalted hash.');
    }
}
