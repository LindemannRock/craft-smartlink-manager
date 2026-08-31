<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\services;

use Craft;
use craft\db\Query;
use craft\queue\BaseJob;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\RecurringQueueHelper;
use lindemannrock\base\helpers\RecurringQueueResult;
use lindemannrock\base\helpers\ScheduleHelper;
use lindemannrock\base\queue\PortableQueueScheduler;
use lindemannrock\smartlinkmanager\jobs\CleanupAnalyticsJob;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\base\Component;
use yii\db\Expression;

/**
 * Owns SmartLink Manager's portable recurring analytics-cleanup schedule.
 *
 * @since 5.37.4
 */
class AnalyticsCleanupScheduler extends Component
{
    public const PLUGIN_TOKEN = 'smartlinkmanager';
    public const RECURRING_OWNER = 'smartlink-manager:analytics-cleanup:daily';
    public const LIFECYCLE_MUTEX = 'smartlink-manager:analytics-cleanup:schedule';
    public const PORTABLE_MUTEX = 'smartlink-manager:analytics-cleanup:portable';

    public int $mutexTimeout = 5;

    public function ensureScheduled(?Settings $settings = null): RecurringQueueResult
    {
        $settings ??= $this->settings();
        if (!$this->isEnabled($settings)) {
            return new RecurringQueueResult(RecurringQueueResult::STATUS_SKIPPED);
        }

        $target = $this->nextTarget();
        if ($target === null) {
            return new RecurringQueueResult(RecurringQueueResult::STATUS_SKIPPED);
        }

        return $this->withLifecycleLock(
            fn(): RecurringQueueResult => $this->withPortableLock(
                fn(): RecurringQueueResult => $this->ensureAt($target, $settings),
            ),
        );
    }

    public function scheduleSuccessor(?Settings $settings = null): RecurringQueueResult
    {
        return $this->ensureScheduled($settings);
    }

    public function replaceForEffectiveTransition(bool $wasEnabled, Settings $settings): RecurringQueueResult
    {
        if ($wasEnabled === $this->isEnabled($settings)) {
            return new RecurringQueueResult(RecurringQueueResult::STATUS_SKIPPED);
        }

        return $this->replace($settings);
    }

    public function replace(Settings $settings): RecurringQueueResult
    {
        return $this->withLifecycleLock(function() use ($settings): RecurringQueueResult {
            return $this->withPortableLock(function() use ($settings): RecurringQueueResult {
                $this->cancelRows();

                if (!$this->isEnabled($settings)) {
                    return new RecurringQueueResult(RecurringQueueResult::STATUS_SKIPPED);
                }

                $target = $this->nextTarget();
                if ($target === null) {
                    return new RecurringQueueResult(RecurringQueueResult::STATUS_SKIPPED);
                }

                return $this->ensureAt($target, $settings);
            });
        });
    }

    public function cancel(): int
    {
        return $this->withLifecycleLock(
            fn(): int => $this->withPortableLock(fn(): int => $this->cancelRows()),
        );
    }

    public function hasScheduled(): bool
    {
        return RecurringQueueHelper::hasPending(
            self::PLUGIN_TOKEN,
            CleanupAnalyticsJob::class,
            [self::RECURRING_OWNER],
        ) || $this->legacyRows(true) !== [];
    }

    public function isEnabled(Settings $settings): bool
    {
        return $settings->enableAnalytics && $settings->analyticsRetention > 0;
    }

    public function nextTarget(): ?\DateTime
    {
        return ScheduleHelper::calculateNext('daily');
    }

    public function formatTarget(\DateTimeInterface $target, Settings $settings): string
    {
        return DateFormatHelper::formatCompactDatetimeFromSettings(
            \DateTime::createFromInterface($target),
            $settings,
            null,
            false,
            pluginHandle: 'smartlink-manager',
        );
    }

    private function ensureAt(\DateTimeInterface $target, Settings $settings): RecurringQueueResult
    {
        $legacyRows = $this->legacyRows(true);
        if ($legacyRows !== []) {
            $duplicates = array_merge(
                array_slice($legacyRows, 1),
                $this->portableRows(false),
            );

            return new RecurringQueueResult(
                RecurringQueueResult::STATUS_EXISTING,
                (string)$legacyRows[0]['id'],
                $this->deleteRows($duplicates),
            );
        }

        $portableRows = RecurringQueueHelper::hasPending(
            self::PLUGIN_TOKEN,
            CleanupAnalyticsJob::class,
            [self::RECURRING_OWNER],
        ) ? $this->portableRows(true) : [];

        if ($portableRows !== []) {
            return new RecurringQueueResult(
                RecurringQueueResult::STATUS_EXISTING,
                (string)$portableRows[0]['id'],
                $this->deleteRows(array_slice($portableRows, 1)),
            );
        }

        $jobId = PortableQueueScheduler::pushAt(
            job: $this->cleanupJob($target, $settings),
            targetTimestamp: $target->getTimestamp(),
            identityTokens: [
                self::PLUGIN_TOKEN,
                CleanupAnalyticsJob::class,
                self::RECURRING_OWNER,
            ],
            mutexName: self::PORTABLE_MUTEX,
            mutexTimeout: $this->mutexTimeout,
            priority: 1024,
            ttr: 1800,
        );

        if ($jobId === null) {
            throw new \RuntimeException('Portable analytics cleanup scheduling did not create a queue row.');
        }

        return new RecurringQueueResult(RecurringQueueResult::STATUS_CREATED, $jobId);
    }

    private function cleanupJob(\DateTimeInterface $target, Settings $settings): CleanupAnalyticsJob
    {
        return new CleanupAnalyticsJob([
            'reschedule' => true,
            'recurringOwner' => self::RECURRING_OWNER,
            'nextRunTime' => $this->formatTarget($target, $settings),
        ]);
    }

    private function cancelRows(): int
    {
        return $this->deleteRows(array_merge(
            $this->portableRows(false),
            $this->legacyRows(false),
        ));
    }

    /**
     * @return list<array{id: int|string, job: string, timePushed: int|string, delay: int|string, priority: int|string}>
     */
    private function portableRows(bool $pendingOnly): array
    {
        return $this->queueRows(
            $this->familyQuery()->andWhere(['like', 'job', self::RECURRING_OWNER]),
            $pendingOnly,
        );
    }

    /**
     * @return list<array{id: int|string, job: string, timePushed: int|string, delay: int|string, priority: int|string}>
     */
    private function legacyRows(bool $pendingOnly): array
    {
        $rows = $this->queueRows(
            $this->familyQuery()->andWhere(['not like', 'job', self::RECURRING_OWNER]),
            $pendingOnly,
        );

        return array_values(array_filter(
            $rows,
            fn(array $row): bool => $this->isLegacyRecurringPayload((string)$row['job']),
        ));
    }

    private function familyQuery(): Query
    {
        return (new Query())
            ->from('{{%queue}}')
            ->where(['like', 'job', self::PLUGIN_TOKEN])
            ->andWhere(['like', 'job', $this->jobClassToken(CleanupAnalyticsJob::class)]);
    }

    /**
     * @return list<array{id: int|string, job: string, timePushed: int|string, delay: int|string, priority: int|string}>
     */
    private function queueRows(Query $query, bool $pendingOnly): array
    {
        if ($pendingOnly) {
            $query
                ->andWhere(['fail' => false])
                ->andWhere(['timeUpdated' => null]);
        }

        /** @var list<array{id: int|string, job: string, timePushed: int|string, delay: int|string, priority: int|string}> $rows */
        $rows = $query
            ->select(['id', 'job', 'timePushed', 'delay', 'priority'])
            ->orderBy(new Expression('[[timePushed]] + [[delay]] ASC'))
            ->addOrderBy(['priority' => SORT_ASC, 'id' => SORT_ASC])
            ->all();

        return $rows;
    }

    private function isLegacyRecurringPayload(string $payload): bool
    {
        if (
            !str_contains($payload, self::PLUGIN_TOKEN)
            || str_contains($payload, self::RECURRING_OWNER)
        ) {
            return false;
        }

        $json = json_decode($payload, true);
        if (is_array($json)) {
            return $this->jsonContainsLegacyJob($json);
        }

        return preg_match(
            '/O:\\d+:"' . preg_quote(CleanupAnalyticsJob::class, '/') . '":\\d+:\\{/',
            $payload,
        ) === 1 && str_contains($payload, 's:10:"reschedule";b:1;');
    }

    /** @param array<array-key, mixed> $value */
    private function jsonContainsLegacyJob(array $value): bool
    {
        if (
            ($value['class'] ?? null) === CleanupAnalyticsJob::class
            && ($value['reschedule'] ?? null) === true
        ) {
            return true;
        }

        foreach ($value as $child) {
            if (is_array($child) && $this->jsonContainsLegacyJob($child)) {
                return true;
            }
        }

        return false;
    }

    /** @param class-string<BaseJob> $jobClass */
    private function jobClassToken(string $jobClass): string
    {
        $parts = explode('\\', $jobClass);

        return end($parts) ?: $jobClass;
    }

    /** @param list<array{id: int|string}> $rows */
    private function deleteRows(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        return Craft::$app->getDb()->createCommand()
            ->delete('{{%queue}}', [
                'id' => array_map(static fn(array $row): string => (string)$row['id'], $rows),
            ])
            ->execute();
    }

    private function withLifecycleLock(callable $callback): mixed
    {
        return $this->withLock(self::LIFECYCLE_MUTEX, 'analytics cleanup lifecycle', $callback);
    }

    private function withPortableLock(callable $callback): mixed
    {
        return $this->withLock(self::PORTABLE_MUTEX, 'portable analytics cleanup', $callback);
    }

    private function withLock(string $mutexName, string $label, callable $callback): mixed
    {
        $mutex = Craft::$app->getMutex();
        if (!$mutex->acquire($mutexName, $this->mutexTimeout)) {
            throw new \RuntimeException("Unable to acquire the {$label} lock.");
        }

        try {
            return $callback();
        } finally {
            $mutex->release($mutexName);
        }
    }

    private function settings(): Settings
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings instanceof Settings) {
            throw new \RuntimeException('SmartLink Manager settings are unavailable.');
        }

        return $settings;
    }
}
