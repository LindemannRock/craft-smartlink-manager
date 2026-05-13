<?php

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use Craft;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\base\testing\StubConsoleRequest;
use lindemannrock\smartlinkmanager\tests\TestCase;
use yii\base\Request as YiiRequest;

/**
 * Pins the atomic-SQL hit-count increment in
 * {@see \lindemannrock\smartlinkmanager\services\analytics\AnalyticsTrackingService::_incrementClickCount()},
 * invoked from every call to {@see \lindemannrock\smartlinkmanager\services\AnalyticsService::trackClick()}.
 *
 * The increment uses `[[hits]] + 1` (an SQL expression). The naïve form
 * `['hits' => $link->hits + 1]` would read the stale in-memory value, so two
 * concurrent requests could both write `n + 1` and lose a count. This test
 * pins the atomic shape so a regression to the stale form would fail in CI.
 *
 * `trackClick()` reaches for `Craft::$app->request->getUserIP()` — a method
 * present on `craft\web\Request` but not on `craft\console\Request`. The
 * integration bootstrap initialises Craft as a console application, so we
 * swap a {@see StubConsoleRequest} into the app's `request` component for the
 * duration of each test. The IP returned doesn't matter for these assertions;
 * we only need `getUserIP()` to exist.
 *
 * @since 5.28.0
 */
final class HitCounterIncrementTest extends TestCase
{
    private const TEST_SALT = '0123456789abcdef0123456789abcdef';

    private ?YiiRequest $savedRequest = null;
    private ?string $savedSalt = null;
    private bool $savedEnableGeo = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Capture and swap the global request. Restored in tearDown so other
        // tests run against the real console request.
        $this->savedRequest = Craft::$app->getRequest();
        Craft::$app->set('request', new StubConsoleRequest());

        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $this->savedSalt = $settings->ipHashSalt;
        $this->savedEnableGeo = $settings->enableGeoDetection;
        $settings->ipHashSalt = self::TEST_SALT;
        $settings->enableGeoDetection = false;
    }

    protected function tearDown(): void
    {
        if ($this->savedRequest !== null) {
            Craft::$app->set('request', $this->savedRequest);
        }

        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->ipHashSalt = $this->savedSalt;
        $settings->enableGeoDetection = $this->savedEnableGeo;

        parent::tearDown();
    }

    public function testTrackClickIncrementsHitsInDatabase(): void
    {
        $link = $this->seedSmartLink();
        $this->assertSame(0, $this->fetchHitsFromDb($link->id));

        $this->analytics->trackClick($link, $this->makeDeviceInfo());

        $this->assertSame(1, $this->fetchHitsFromDb($link->id));
    }

    public function testMultipleTrackClickCallsAccumulate(): void
    {
        $link = $this->seedSmartLink();

        for ($i = 0; $i < 5; $i++) {
            $this->analytics->trackClick($link, $this->makeDeviceInfo());
        }

        $this->assertSame(5, $this->fetchHitsFromDb($link->id));
    }

    public function testTrackClickUsesAtomicSqlAndDoesNotClobberConcurrentWrites(): void
    {
        $link = $this->seedSmartLink();
        $this->assertSame(0, $link->hits, 'Seeded model starts at 0 hits.');

        // Simulate a concurrent request advancing the DB column while the
        // current in-memory model still believes `hits = 0`. The atomic
        // `[[hits]] + 1` SQL expression must read the *current* DB value,
        // not the stale in-memory one.
        Craft::$app->getDb()
            ->createCommand()
            ->update('{{%smartlinkmanager}}', ['hits' => 100], ['id' => $link->id])
            ->execute();

        $this->assertSame(0, $link->hits);
        $this->assertSame(100, $this->fetchHitsFromDb($link->id));

        $this->analytics->trackClick($link, $this->makeDeviceInfo());

        // If the increment regressed to `$link->hits + 1`, the DB would now
        // hold `1` (clobbering the concurrent +100). The atomic expression
        // instead reads from disk and lands on 101.
        $this->assertSame(
            101,
            $this->fetchHitsFromDb($link->id),
            'Atomic SQL expression must read fresh value from disk.',
        );
    }

    private function makeDeviceInfo(): DeviceInfo
    {
        $info = new DeviceInfo();
        $info->platform = 'other';
        $info->deviceType = 'desktop';
        $info->browser = 'TestBrowser';
        $info->userAgent = 'Mozilla/5.0 (Test) SmartLinkManagerStub/1.0';

        return $info;
    }
}
