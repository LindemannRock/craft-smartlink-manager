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
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\ScheduleHelper;
use lindemannrock\smartlinkmanager\jobs\CleanupAnalyticsJob;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use ReflectionMethod;

/**
 * Pins SmartLink Manager's scheduler-pattern integration with base helpers.
 *
 * @since 5.29.0
 */
final class SchedulerPatternTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteSmartLinkManagerQueueRows();
    }

    protected function tearDown(): void
    {
        $this->deleteSmartLinkManagerQueueRows();
        parent::tearDown();
    }

    public function testAnalyticsCleanupReschedulesWhenExistingCleanupRowExists(): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->enableAnalytics = true;
        $settings->analyticsRetention = 30;

        Craft::$app->getQueue()->delay(300)->push(new CleanupAnalyticsJob([
            'reschedule' => true,
        ]));
        $this->assertSame(1, $this->countQueueRows('CleanupAnalyticsJob'));

        $job = new CleanupAnalyticsJob([
            'reschedule' => true,
        ]);
        $this->invokePrivate($job, 'scheduleNextCleanup');

        $this->assertSame(2, $this->countQueueRows('CleanupAnalyticsJob'));
    }

    public function testAnalyticsCleanupBootstrapDoesNotDuplicateExistingDelayedCleanupRow(): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->enableAnalytics = true;
        $settings->analyticsRetention = 30;

        Craft::$app->getQueue()->delay(300)->push(new CleanupAnalyticsJob([
            'reschedule' => true,
        ]));
        $this->assertSame(1, $this->countQueueRows('CleanupAnalyticsJob'));

        $this->invokePrivate(SmartLinkManager::$plugin, 'scheduleAnalyticsCleanup');

        $this->assertSame(1, $this->countQueueRows('CleanupAnalyticsJob'));
    }

    public function testAnalyticsCleanupBootstrapUsesCanonicalDailyRun(): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->enableAnalytics = true;
        $settings->analyticsRetention = 30;

        $this->invokePrivate(SmartLinkManager::$plugin, 'scheduleAnalyticsCleanup');

        $this->assertSame(1, $this->countQueueRows('CleanupAnalyticsJob'));

        $row = $this->latestQueueRow('CleanupAnalyticsJob');
        self::assertIsArray($row);
        self::assertStringContainsString($this->expectedDailyRunTime(), (string) $row['description']);
    }

    public function testAnalyticsCleanupBootstrapCollapsesDuplicatePendingRows(): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $settings->enableAnalytics = true;
        $settings->analyticsRetention = 30;

        Craft::$app->getQueue()->delay(300)->push(new CleanupAnalyticsJob([
            'reschedule' => true,
        ]));
        Craft::$app->getQueue()->delay(300)->push(new CleanupAnalyticsJob([
            'reschedule' => true,
        ]));
        $this->assertSame(2, $this->countQueueRows('CleanupAnalyticsJob'));

        $this->invokePrivate(SmartLinkManager::$plugin, 'scheduleAnalyticsCleanup');

        $this->assertSame(1, $this->countQueueRows('CleanupAnalyticsJob'));
    }

    private function invokePrivate(object $object, string $method): void
    {
        $reflection = new ReflectionMethod($object, $method);
        $reflection->invoke($object);
    }

    private function countQueueRows(string $jobClass): int
    {
        return (int) (new \craft\db\Query())
            ->from('{{%queue}}')
            ->where(['like', 'job', 'smartlinkmanager'])
            ->andWhere(['like', 'job', $jobClass])
            ->count();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function latestQueueRow(string $jobClass): ?array
    {
        $row = (new \craft\db\Query())
            ->from('{{%queue}}')
            ->where(['like', 'job', 'smartlinkmanager'])
            ->andWhere(['like', 'job', $jobClass])
            ->select(['id', 'description'])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $row !== false ? $row : null;
    }

    private function expectedDailyRunTime(): string
    {
        $nextRun = ScheduleHelper::calculateNext('daily');
        self::assertNotNull($nextRun);

        return DateFormatHelper::formatCompactDatetimeFromSettings(
            $nextRun,
            SmartLinkManager::$plugin->getSettings(),
            false,
            false,
        );
    }

    private function deleteSmartLinkManagerQueueRows(): void
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}', [
                'and',
                ['like', 'job', 'smartlinkmanager'],
                ['like', 'job', 'CleanupAnalyticsJob'],
            ])
            ->execute();
    }
}
