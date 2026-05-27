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
