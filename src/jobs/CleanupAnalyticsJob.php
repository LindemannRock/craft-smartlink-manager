<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025-2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\jobs;

use Craft;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\queue\BaseJob;
use lindemannrock\base\traits\QueueTtrTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\queue\RetryableJobInterface;

/**
 * Cleanup old analytics data based on retention settings
 *
 * @since 1.0.0
 */
class CleanupAnalyticsJob extends BaseJob implements RetryableJobInterface
{
    use QueueTtrTrait;
    use LoggingTrait;

    /**
     * @var bool Whether to reschedule after completion
     */
    public bool $reschedule = false;

    /**
     * Stable owner token for the portable recurring cleanup chain.
     *
     * @since 5.38.0
     */
    public string $recurringOwner = '';

    /**
     * @var string|null Next run time display string
     */
    public ?string $nextRunTime = null;

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);

        if ($this->reschedule && !$this->nextRunTime) {
            $settings = SmartLinkManager::$plugin->getSettings();
            $nextRun = SmartLinkManager::$plugin->analyticsCleanupScheduler->nextTarget();
            if ($nextRun !== null) {
                $this->nextRunTime = SmartLinkManager::$plugin->analyticsCleanupScheduler->formatTarget(
                    $nextRun,
                    $settings,
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        $pluginName = SmartLinkManager::$plugin->getSettings()->getDisplayName();
        $description = Craft::t('smartlink-manager', '{pluginName}: Cleaning up old analytics', ['pluginName' => $pluginName]);

        if ($this->nextRunTime) {
            $description .= " ({$this->nextRunTime})";
        }

        return $description;
    }

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $retentionDays = $settings->analyticsRetention;

        // A reserved recurring job must honor the current effective settings.
        if ($this->reschedule && (!$settings->enableAnalytics || $retentionDays <= 0)) {
            return;
        }

        // Manual cleanup remains available while retention is positive.
        if ($retentionDays <= 0) {
            return;
        }

        // Calculate cutoff date
        $cutoffDate = DateTimeHelper::toDateTime("now -$retentionDays days");
        $cutoffDateString = Db::prepareDateForDb($cutoffDate);

        // Get count of records to delete for progress tracking
        $totalRecords = (new Query())
            ->from('{{%smartlinkmanager_analytics}}')
            ->where(['<', 'dateCreated', $cutoffDateString])
            ->count();

        if ($totalRecords === 0) {
            // No records to delete, but still reschedule for next run
            if ($this->reschedule) {
                $this->scheduleNextCleanup();
            }
            return;
        }

        $this->setProgress($queue, 0, Craft::t('smartlink-manager', 'Deleting {count} old analytics records', [
            'count' => $totalRecords,
        ]));

        // Delete in batches to avoid memory issues
        $batchSize = 1000;
        $deleted = 0;

        while (true) {
            // Get batch of old record IDs
            $oldRecordIds = (new Query())
                ->select(['id'])
                ->from('{{%smartlinkmanager_analytics}}')
                ->where(['<', 'dateCreated', $cutoffDateString])
                ->limit($batchSize)
                ->column();

            if (empty($oldRecordIds)) {
                break;
            }

            // Delete batch
            Craft::$app->getDb()->createCommand()
                ->delete('{{%smartlinkmanager_analytics}}', ['id' => $oldRecordIds])
                ->execute();

            $deleted += count($oldRecordIds);

            $this->setProgress($queue, $deleted / $totalRecords, Craft::t('smartlink-manager', 'Deleted {deleted} of {total} records', [
                'deleted' => $deleted,
                'total' => $totalRecords,
            ]));
        }

        $this->logInfo('Cleaned up analytics records', ['deleted' => $deleted, 'retentionDays' => $retentionDays]);

        // Reschedule if needed
        if ($this->reschedule) {
            $this->scheduleNextCleanup();
        }
    }

    /** Schedule the next canonical daily cleanup. */
    private function scheduleNextCleanup(): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $result = SmartLinkManager::$plugin->analyticsCleanupScheduler->scheduleSuccessor($settings);

        $this->logDebug('Scheduled next analytics cleanup', [
            'status' => $result->status,
            'jobId' => $result->jobId,
        ]);
    }
}
