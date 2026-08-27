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
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\queue\BaseJob;
use craft\queue\Queue;
use craft\web\Request;
use craft\web\Response;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\helpers\RecurringQueueHelper;
use lindemannrock\base\queue\DeferredQueueJob;
use lindemannrock\base\queue\PortableQueueScheduler;
use lindemannrock\smartlinkmanager\controllers\SettingsController;
use lindemannrock\smartlinkmanager\jobs\CleanupAnalyticsJob;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\services\AnalyticsCleanupScheduler;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use yii\queue\Queue as ProxyQueue;
use yii\queue\serializers\JsonSerializer;
use yii\queue\sqs\Queue as SqsQueue;

/**
 * Pins portable analytics-cleanup ownership, migration, and isolation.
 *
 * @since 5.29.0
 */
final class SchedulerPatternTest extends TestCase
{
    private AnalyticsCleanupScheduler $scheduler;
    private ?Queue $originalQueue = null;
    private ?RecordingSmartlinkSqsQueue $sqsProxy = null;
    private ?RecordingUnknownProxyQueue $unknownProxy = null;
    private bool $timePaused = false;
    private ?object $originalRequest = null;
    private ?object $originalResponse = null;
    private ?string $originalRequestMethod = null;
    private ?string $originalTimezone = null;

    /** @var list<string> */
    private array $testTables = [];

    /** @var list<string> */
    private array $pendingShadowTables = [];

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->isolateResources();
            $this->scheduler = new AnalyticsCleanupScheduler();
            $this->swapPluginComponent('smartlink-manager', 'analyticsCleanupScheduler', $this->scheduler);
        } catch (\Throwable $exception) {
            $this->cleanupIsolatedResources();
            parent::tearDown();

            throw $exception;
        }
    }

    protected function tearDown(): void
    {
        try {
            $this->cleanupIsolatedResources();
        } finally {
            parent::tearDown();
        }
    }

    public function testApprovedBaseClassesLoadFromTheResolvedApprovedSourceWithRequiredApis(): void
    {
        $baseSource = $this->baseSourceRoot() . DIRECTORY_SEPARATOR;
        foreach ([
            RecurringQueueHelper::class => ['hasPending'],
            PortableQueueScheduler::class => ['pushAt', 'continue'],
            DeferredQueueJob::class => ['execute'],
        ] as $class => $methods) {
            $reflection = new ReflectionClass($class);
            $filename = $reflection->getFileName();
            self::assertIsString($filename);
            self::assertStringStartsWith($baseSource, realpath($filename) ?: '');

            foreach ($methods as $method) {
                self::assertTrue($reflection->hasMethod($method), "{$class}::{$method} must be available.");
            }
        }
    }

    public function testRecurringIdentityAndMutexesAreStableAndDistinct(): void
    {
        self::assertSame('smartlinkmanager', AnalyticsCleanupScheduler::PLUGIN_TOKEN);
        self::assertSame('smartlink-manager:analytics-cleanup:daily', AnalyticsCleanupScheduler::RECURRING_OWNER);
        self::assertSame('smartlink-manager:analytics-cleanup:schedule', AnalyticsCleanupScheduler::LIFECYCLE_MUTEX);
        self::assertSame('smartlink-manager:analytics-cleanup:portable', AnalyticsCleanupScheduler::PORTABLE_MUTEX);
        self::assertNotSame(AnalyticsCleanupScheduler::LIFECYCLE_MUTEX, AnalyticsCleanupScheduler::PORTABLE_MUTEX);
    }

    public function testInitialScheduleUsesTheExactNextMidnightDescriptionPriorityAndTtr(): void
    {
        $start = $this->pauseNearMidnight();
        $settings = $this->settings(true, 30);
        $target = $this->scheduler->nextTarget();
        self::assertNotNull($target);

        $result = $this->scheduler->ensureScheduled($settings);
        $row = $this->onlyOwnerRow();
        $job = $this->unserializeRow($row);
        $formatted = $this->scheduler->formatTarget($target, $settings);

        self::assertTrue($result->wasCreated());
        self::assertSame($target->getTimestamp(), (int)$row['timePushed'] + (int)$row['delay']);
        self::assertGreaterThan($start, $target->getTimestamp());
        self::assertSame('00:00:00', $target->format('H:i:s'));
        self::assertSame(1024, (int)$row['priority']);
        self::assertSame(1800, (int)$row['ttr']);
        self::assertInstanceOf(CleanupAnalyticsJob::class, $job);
        self::assertSame(AnalyticsCleanupScheduler::RECURRING_OWNER, $job->recurringOwner);
        self::assertSame($formatted, $job->nextRunTime);
        self::assertSame(
            $settings->getDisplayName() . ': Cleaning up old analytics (' . $formatted . ')',
            (string)$row['description'],
        );
        self::assertFalse($job->canRetry(1, new RuntimeException('test')));
        self::assertSame(1800, $job->getTtr());
    }

    #[DataProvider('portableBoundaryProvider')]
    public function testSqsDelayBoundaryIsInclusive(int $delay, string $expectedClass): void
    {
        $this->installQueue(new RecordingSmartlinkSqsQueue());
        $this->pauseAt(1_800_000_000);

        PortableQueueScheduler::push(
            job: $this->ownedJob(),
            delay: $delay,
            identityTokens: $this->identityTokens(),
            mutexName: AnalyticsCleanupScheduler::PORTABLE_MUTEX,
            priority: 1024,
            ttr: 1800,
        );

        self::assertInstanceOf($expectedClass, $this->unserializeRow($this->onlyOwnerRow()));
        self::assertSame([min(900, $delay)], $this->sqsDelays());
    }

    /** @return iterable<string, array{int, class-string}> */
    public static function portableBoundaryProvider(): iterable
    {
        yield '900 seconds is final' => [900, CleanupAnalyticsJob::class];
        yield '901 seconds starts a handoff' => [901, DeferredQueueJob::class];
    }

    public function testDailySqsScheduleUsesBoundedHandoffsWithoutEarlyCleanup(): void
    {
        $queue = $this->installQueue(new RecordingSmartlinkSqsQueue());
        $start = $this->pauseNearMidnight();
        $settings = $this->settings(true, 30);
        $analyticsId = $this->insertOldAnalyticsRow();

        $this->scheduler->ensureScheduled($settings);
        $target = $this->scheduler->nextTarget();
        self::assertNotNull($target);

        for ($hop = 1; $hop <= 2; $hop++) {
            $row = $this->onlyOwnerRow();
            self::assertInstanceOf(DeferredQueueJob::class, $this->unserializeRow($row));
            $this->pauseAt($start + ($hop * 900));
            self::assertTrue($queue->executeJob((string)$row['id']));
            self::assertTrue($this->analyticsRowExists($analyticsId));
        }

        $row = $this->onlyOwnerRow();
        self::assertInstanceOf(DeferredQueueJob::class, $this->unserializeRow($row));
        $this->pauseAt($target->getTimestamp() - 300);
        self::assertTrue($queue->executeJob((string)$row['id']));
        $consumer = $this->onlyOwnerRow();

        self::assertInstanceOf(CleanupAnalyticsJob::class, $this->unserializeRow($consumer));
        self::assertSame(300, (int)$consumer['delay']);
        self::assertTrue($this->analyticsRowExists($analyticsId));
        self::assertSame([900, 900, 900, 300], $this->sqsDelays());
        self::assertLessThanOrEqual(900, max($this->sqsDelays()));
    }

    public function testLateHandoffQueuesTheConsumerWithZeroDelay(): void
    {
        $queue = $this->installQueue(new RecordingSmartlinkSqsQueue());
        $this->pauseAt(1_800_000_000);
        PortableQueueScheduler::push(
            $this->ownedJob(),
            901,
            $this->identityTokens(),
            AnalyticsCleanupScheduler::PORTABLE_MUTEX,
            priority: 1024,
            ttr: 1800,
        );
        $row = $this->onlyOwnerRow();

        $this->pauseAt(1_800_001_000);
        self::assertTrue($queue->executeJob((string)$row['id']));
        $consumer = $this->onlyOwnerRow();
        self::assertInstanceOf(CleanupAnalyticsJob::class, $this->unserializeRow($consumer));
        self::assertSame(0, (int)$consumer['delay']);
        self::assertSame([900, 0], $this->sqsDelays());
    }

    public function testLocalAndUnknownQueuesRetainTheFullNativeDelay(): void
    {
        $this->pauseAt(1_800_000_000);
        PortableQueueScheduler::push(
            $this->ownedJob(),
            901,
            $this->identityTokens(),
            AnalyticsCleanupScheduler::PORTABLE_MUTEX,
            priority: 1024,
            ttr: 1800,
        );
        self::assertInstanceOf(CleanupAnalyticsJob::class, $this->unserializeRow($this->onlyOwnerRow()));
        self::assertSame(901, (int)$this->onlyOwnerRow()['delay']);

        $this->deleteQueueRows();
        $this->installQueue(new RecordingUnknownProxyQueue());
        PortableQueueScheduler::push(
            $this->ownedJob(),
            901,
            $this->identityTokens(),
            AnalyticsCleanupScheduler::PORTABLE_MUTEX,
            priority: 1024,
            ttr: 1800,
        );
        self::assertInstanceOf(CleanupAnalyticsJob::class, $this->unserializeRow($this->onlyOwnerRow()));
        self::assertSame([901], $this->unknownDelays());
    }

    public function testBootstrapReplayAndExecutingConsumerCreateExactlyOneSuccessor(): void
    {
        $this->applySettingsForTest(['enableAnalytics' => true, 'analyticsRetention' => 30]);
        $settings = $this->settings(true, 30);
        $first = $this->scheduler->ensureScheduled($settings);
        $second = $this->scheduler->ensureScheduled($settings);
        self::assertSame($first->jobId, $second->jobId);
        self::assertSame(1, $this->countPendingOwnerRows());

        self::assertNotNull($first->jobId);
        $this->markExecuting($first->jobId);
        $running = $this->ownedJob();
        $this->invokePrivate($running, 'scheduleNextCleanup');
        $this->invokePrivate($running, 'scheduleNextCleanup');

        self::assertSame(2, $this->countOwnerRows());
        self::assertSame(1, $this->countPendingOwnerRows());
    }

    public function testPhpJsonAndNestedLegacyRowsRetainTheEarliestAndCollapseDuplicates(): void
    {
        $settings = $this->settings(true, 30);
        $late = $this->pushLegacy(true, false, true, 600);
        $early = $this->pushLegacy(true, true, true, 300);

        self::assertTrue($this->scheduler->hasScheduled());
        $result = $this->scheduler->ensureScheduled($settings);

        self::assertSame($early, $result->jobId);
        self::assertSame(1, $result->duplicatesDeleted);
        self::assertFalse($this->rowExists($late));
        self::assertTrue($this->rowExists($early));
        self::assertSame(0, $this->countOwnerRows());
    }

    public function testLegacyChainWinsOverNewOwnerAndFailedLegacyDoesNotBlockRecovery(): void
    {
        $settings = $this->settings(true, 30);
        $owner = $this->scheduler->ensureScheduled($settings)->jobId;
        self::assertNotNull($owner);
        $legacy = $this->pushLegacy(true, false, false, 100);

        $retained = $this->scheduler->ensureScheduled($settings);
        self::assertSame($legacy, $retained->jobId);
        self::assertFalse($this->rowExists($owner));

        $this->deleteQueueRows();
        $failed = $this->pushLegacy(true, true, false, 100);
        $this->setQueueState($failed, fail: true);
        $recovery = $this->scheduler->ensureScheduled($settings);
        self::assertTrue($recovery->wasCreated());
        self::assertTrue($this->rowExists($failed));
        self::assertSame(1, $this->countPendingOwnerRows());
    }

    public function testCancellationRemovesAllOwnedAndLegacyStatesButPreservesManualAndUnrelatedRows(): void
    {
        $this->installQueue(new RecordingSmartlinkSqsQueue());
        $this->pauseNearMidnight();
        $settings = $this->settings(true, 30);

        $failedHandoff = $this->scheduler->ensureScheduled($settings)->jobId;
        self::assertNotNull($failedHandoff);
        $this->setQueueState($failedHandoff, fail: true);
        $reservedHandoff = $this->scheduler->ensureScheduled($settings)->jobId;
        self::assertNotNull($reservedHandoff);
        $this->setQueueState($reservedHandoff, reserved: true);
        self::assertNotNull($this->scheduler->ensureScheduled($settings)->jobId);

        $failedFinal = $this->pushOwnedFinal();
        $this->setQueueState($failedFinal, fail: true);
        $reservedFinal = $this->pushOwnedFinal();
        $this->setQueueState($reservedFinal, reserved: true);
        $this->pushOwnedFinal();

        $legacyFailed = $this->pushLegacy(true, false, false);
        $this->setQueueState($legacyFailed, fail: true);
        $legacyReserved = $this->pushLegacy(true, true, false);
        $this->setQueueState($legacyReserved, reserved: true);
        $legacyPending = $this->pushLegacy(true, false, false);
        $manual = $this->pushLegacy(false, false, false);
        $unrelated = (string)Craft::$app->getQueue()->push(new ForeignCleanupAnalyticsJob());

        self::assertGreaterThanOrEqual(9, $this->scheduler->cancel());
        foreach ([$failedHandoff, $reservedHandoff, $failedFinal, $reservedFinal, $legacyFailed, $legacyReserved, $legacyPending] as $id) {
            self::assertFalse($this->rowExists($id));
        }
        self::assertSame(0, $this->countOwnerRows());
        self::assertTrue($this->rowExists($manual));
        self::assertTrue($this->rowExists($unrelated));
    }

    public function testCancelledReservedHandoffCannotResurrectTheChain(): void
    {
        $queue = $this->installQueue(new RecordingSmartlinkSqsQueue());
        $this->pauseNearMidnight();
        $id = $this->scheduler->ensureScheduled($this->settings(true, 30))->jobId;
        self::assertNotNull($id);
        $handoff = $this->unserializeRow($this->onlyOwnerRow());
        self::assertInstanceOf(DeferredQueueJob::class, $handoff);
        $this->markExecuting($id);

        self::assertSame(1, $this->scheduler->cancel());
        $handoff->execute($queue);
        self::assertSame(0, $this->countOwnerRows());
    }

    public function testDisabledScheduledJobSkipsCleanupWhileManualJobRemainsUsable(): void
    {
        $this->applySettingsForTest(['enableAnalytics' => false, 'analyticsRetention' => 30]);
        $analyticsId = $this->insertOldAnalyticsRow();
        $queue = Craft::$app->getQueue();

        $this->ownedJob()->execute($queue);
        self::assertTrue($this->analyticsRowExists($analyticsId));
        self::assertSame(0, $this->countOwnerRows());

        (new CleanupAnalyticsJob(['reschedule' => false]))->execute($queue);
        self::assertFalse($this->analyticsRowExists($analyticsId));
        self::assertSame(0, $this->countOwnerRows());
    }

    public function testEnableDisableReenableAndRetentionOnlyChangesDoNotChurn(): void
    {
        $enabled = $this->settings(true, 30);
        $first = $this->scheduler->ensureScheduled($enabled);
        self::assertNotNull($first->jobId);

        $enabled->analyticsRetention = 90;
        self::assertTrue($this->scheduler->replaceForEffectiveTransition(true, $enabled)->wasSkipped());
        self::assertTrue($this->rowExists($first->jobId));

        $enabled->enableAnalytics = false;
        self::assertTrue($this->scheduler->replaceForEffectiveTransition(true, $enabled)->wasSkipped());
        self::assertSame(0, $this->countOwnerRows());

        $enabled->enableAnalytics = true;
        $reenabled = $this->scheduler->replaceForEffectiveTransition(false, $enabled);
        self::assertTrue($reenabled->wasCreated());
        self::assertSame(1, $this->countPendingOwnerRows());
    }

    public function testConfigOverridesKeepTheEffectiveChainDuringAConflictingSettingsPost(): void
    {
        $this->applySettingsForTest(['enableAnalytics' => true, 'analyticsRetention' => 30]);
        $effective = PluginHelper::applyConfigOverridesToSettings(Settings::loadFromDatabase(), 'smartlink-manager');
        $jobId = $this->scheduler->ensureScheduled($effective)->jobId;
        self::assertNotNull($jobId);

        $this->withSettingsPost(['enableAnalytics' => false, 'analyticsRetention' => 0]);
        $user = $this->createTestUser('smartlink-scheduler-settings-', ['admin' => true]);
        $this->grantPermissions($user, ['accessCp', 'smartLinkManager:manageSettings']);
        $this->actingAs($user);

        try {
            (new SettingsController('settings', SmartLinkManager::$plugin))->actionSave();
        } catch (MissingComponentException $exception) {
            self::assertSame('Session does not exist in a console request.', $exception->getMessage());
        }

        self::assertTrue($this->rowExists($jobId));
        self::assertSame(1, $this->countPendingOwnerRows());
    }

    public function testPushMutexAndCancellationFailuresRemainObservable(): void
    {
        $settings = $this->settings(true, 30);
        $this->scheduler->mutexTimeout = 0;
        $mutex = Craft::$app->getMutex();
        self::assertTrue($mutex->acquire(AnalyticsCleanupScheduler::LIFECYCLE_MUTEX, 0));

        try {
            $this->scheduler->ensureScheduled($settings);
            self::fail('Lifecycle mutex contention must propagate.');
        } catch (RuntimeException $exception) {
            self::assertSame('Unable to acquire the analytics cleanup lifecycle lock.', $exception->getMessage());
        } finally {
            $mutex->release(AnalyticsCleanupScheduler::LIFECYCLE_MUTEX);
        }

        $proxy = new RecordingSmartlinkSqsQueue();
        $proxy->failPushes = true;
        $this->installQueue($proxy);
        try {
            $this->scheduler->ensureScheduled($settings);
            self::fail('Proxy push failure must propagate.');
        } catch (RuntimeException $exception) {
            self::assertSame('forced Smartlink recurring proxy failure', $exception->getMessage());
        }
        self::assertSame(1, $this->countOwnerRows());

        $this->scheduler->mutexTimeout = 0;
        self::assertTrue($mutex->acquire(AnalyticsCleanupScheduler::LIFECYCLE_MUTEX, 0));
        try {
            $this->scheduler->cancel();
            self::fail('Cancellation lock failure must propagate.');
        } catch (RuntimeException $exception) {
            self::assertSame('Unable to acquire the analytics cleanup lifecycle lock.', $exception->getMessage());
        } finally {
            $mutex->release(AnalyticsCleanupScheduler::LIFECYCLE_MUTEX);
        }
    }

    public function testQueueIsolationRestoresTheExactComponentAndDropsTheExactTemporaryTable(): void
    {
        $permanentQueue = $this->originalQueue;
        self::assertInstanceOf(Queue::class, $permanentQueue);
        self::assertNotSame($permanentQueue, Craft::$app->getQueue());
        Craft::$app->getDb()->createCommand()->addColumn('{{%queue}}', 'smartlinkIsolationMarker', 'integer NULL')->execute();

        $this->cleanupIsolatedResources();
        self::assertSame($permanentQueue, Craft::$app->getQueue());

        $this->isolateResources();
        self::assertNotSame($permanentQueue, Craft::$app->getQueue());
        self::assertNull(Craft::$app->getDb()->getSchema()->getTableSchema('{{%queue}}', true)?->getColumn('smartlinkIsolationMarker'));
    }

    public function testRuntimeHasNoPrivateCloudDependencyOrInfrastructureInspection(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/src/services/AnalyticsCleanupScheduler.php');
        $composer = file_get_contents(dirname(__DIR__, 2) . '/composer.json');
        self::assertIsString($source);
        self::assertIsString($composer);
        self::assertStringNotContainsString('craft\\cloud', $source);
        self::assertStringNotContainsString('craftcms/cloud', $composer);
        self::assertStringNotContainsString('proxyQueue', $source);
        self::assertStringContainsString('RecurringQueueHelper::hasPending', $source);
        self::assertStringContainsString('PortableQueueScheduler::pushAt', $source);
    }

    private function isolateResources(): void
    {
        $queue = Craft::$app->getQueue();
        if (!$queue instanceof Queue) {
            throw new RuntimeException('Scheduler integration tests require Craft\'s database queue.');
        }

        $this->originalQueue = $queue;
        $this->isolateTable($queue->tableName, false);
        $this->isolateTable('{{%smartlinkmanager_analytics}}', false);
        $this->isolateTable('{{%smartlinkmanager_settings}}', true);

        Craft::$app->set('queue', new Queue([
            'db' => Craft::$app->getDb(),
            'mutex' => $queue->mutex,
            'tableName' => $queue->tableName,
            'channel' => $queue->channel,
            'mutexTimeout' => $queue->mutexTimeout,
        ]));
    }

    private function isolateTable(string $tableName, bool $copyRows): void
    {
        $db = Craft::$app->getDb();
        if ($db->getDriverName() !== 'mysql') {
            throw new RuntimeException('Scheduler integration isolation currently requires MySQL.');
        }

        $raw = $db->getSchema()->getRawTableName($tableName);
        $shadow = $raw . '_smartlink_test_' . bin2hex(random_bytes(8));
        $this->pendingShadowTables[] = $shadow;
        $db->createCommand(sprintf(
            'CREATE TEMPORARY TABLE %s LIKE %s',
            $db->quoteTableName($shadow),
            $db->quoteTableName($raw),
        ))->execute();
        if ($copyRows) {
            $db->createCommand(sprintf(
                'INSERT INTO %s SELECT * FROM %s',
                $db->quoteTableName($shadow),
                $db->quoteTableName($raw),
            ))->execute();
        }
        $db->createCommand(sprintf(
            'ALTER TABLE %s RENAME TO %s',
            $db->quoteTableName($shadow),
            $db->quoteTableName($raw),
        ))->execute();
        array_pop($this->pendingShadowTables);
        $this->testTables[] = $raw;
    }

    private function cleanupIsolatedResources(): void
    {
        if ($this->timePaused) {
            DateTimeHelper::resume();
            $this->timePaused = false;
        }
        if ($this->originalTimezone !== null) {
            Craft::$app->setTimeZone($this->originalTimezone);
            $this->originalTimezone = null;
        }
        $this->restoreSettingsRequest();

        if ($this->originalQueue !== null) {
            Craft::$app->set('queue', $this->originalQueue);
            $this->originalQueue = null;
        }

        $db = Craft::$app->getDb();
        foreach (array_reverse(array_merge($this->testTables, $this->pendingShadowTables)) as $table) {
            $db->createCommand('DROP TEMPORARY TABLE IF EXISTS ' . $db->quoteTableName($table))->execute();
        }
        $this->testTables = [];
        $this->pendingShadowTables = [];
        $this->sqsProxy = null;
        $this->unknownProxy = null;
    }

    private function settings(bool $enabled, int $retention): Settings
    {
        $settings = Settings::loadFromDatabase();
        $settings->enableAnalytics = $enabled;
        $settings->analyticsRetention = $retention;

        return $settings;
    }

    private function pauseNearMidnight(): int
    {
        $this->originalTimezone ??= Craft::$app->getTimeZone();
        $now = new \DateTimeImmutable();
        $bestTimezone = $this->originalTimezone;
        $longestDelay = 0;

        foreach (\DateTimeZone::listIdentifiers() as $identifier) {
            $localNow = $now->setTimezone(new \DateTimeZone($identifier));
            $nextMidnight = $localNow->modify('tomorrow')->setTime(0, 0);
            $delay = $nextMidnight->getTimestamp() - $now->getTimestamp();
            if ($delay > $longestDelay) {
                $longestDelay = $delay;
                $bestTimezone = $identifier;
            }
        }

        Craft::$app->setTimeZone($bestTimezone);
        $timestamp = DateFormatHelper::now()->getTimestamp();
        $this->pauseAt($timestamp);

        return $timestamp;
    }

    private function pauseAt(int $timestamp): void
    {
        if ($this->timePaused) {
            DateTimeHelper::resume();
        }
        DateTimeHelper::pause(new \DateTime("@$timestamp"));
        $this->timePaused = true;
    }

    private function ownedJob(): CleanupAnalyticsJob
    {
        return new CleanupAnalyticsJob([
            'reschedule' => true,
            'recurringOwner' => AnalyticsCleanupScheduler::RECURRING_OWNER,
        ]);
    }

    /** @return list<string> */
    private function identityTokens(): array
    {
        return [
            AnalyticsCleanupScheduler::PLUGIN_TOKEN,
            CleanupAnalyticsJob::class,
            AnalyticsCleanupScheduler::RECURRING_OWNER,
        ];
    }

    private function installQueue(ProxyQueue $proxy): Queue
    {
        $current = Craft::$app->getQueue();
        self::assertInstanceOf(Queue::class, $current);
        $this->sqsProxy = $proxy instanceof RecordingSmartlinkSqsQueue ? $proxy : null;
        $this->unknownProxy = $proxy instanceof RecordingUnknownProxyQueue ? $proxy : null;
        $queue = new Queue([
            'db' => Craft::$app->getDb(),
            'mutex' => Craft::$app->getMutex(),
            'tableName' => $current->tableName,
            'channel' => $current->channel,
            'mutexTimeout' => $current->mutexTimeout,
            'proxyQueue' => $proxy,
        ]);
        Craft::$app->set('queue', $queue);

        return $queue;
    }

    /** @return array<string, mixed> */
    private function onlyOwnerRow(): array
    {
        $rows = $this->ownerQuery()->orderBy(['id' => SORT_ASC])->all();
        self::assertCount(1, $rows);

        return $rows[0];
    }

    private function ownerQuery(): Query
    {
        return (new Query())
            ->from('{{%queue}}')
            ->where(['like', 'job', AnalyticsCleanupScheduler::RECURRING_OWNER]);
    }

    private function countOwnerRows(): int
    {
        return (int)$this->ownerQuery()->count();
    }

    private function countPendingOwnerRows(): int
    {
        return (int)$this->ownerQuery()->andWhere(['fail' => false, 'timeUpdated' => null])->count();
    }

    /** @param array<string, mixed> $row */
    private function unserializeRow(array $row): object
    {
        $queue = Craft::$app->getQueue();
        self::assertInstanceOf(Queue::class, $queue);
        $job = $queue->serializer->unserialize((string)$row['job']);
        self::assertIsObject($job);

        return $job;
    }

    private function pushLegacy(bool $reschedule, bool $json, bool $nested, int $delay = 300): string
    {
        $job = new CleanupAnalyticsJob(['reschedule' => $reschedule]);
        $queuedJob = $nested ? new DeferredQueueJob([
            'job' => $job,
            'targetTimestamp' => DateTimeHelper::currentTimeStamp() + $delay,
            'identityTokens' => [AnalyticsCleanupScheduler::PLUGIN_TOKEN, CleanupAnalyticsJob::class],
            'mutexName' => AnalyticsCleanupScheduler::PORTABLE_MUTEX,
            'mutexTimeout' => 5,
            'priority' => 1024,
            'ttr' => 1800,
            'chainId' => bin2hex(random_bytes(8)),
        ]) : $job;
        $id = Craft::$app->getQueue()->delay($delay)->push($queuedJob);
        self::assertNotNull($id);

        if ($json) {
            Craft::$app->getDb()->createCommand()
                ->update('{{%queue}}', ['job' => (new JsonSerializer())->serialize($queuedJob)], ['id' => $id])
                ->execute();
        }

        return $id;
    }

    private function pushOwnedFinal(): string
    {
        $id = Craft::$app->getQueue()->push($this->ownedJob());
        self::assertNotNull($id);

        return $id;
    }

    private function markExecuting(string $id): void
    {
        $this->setQueueState($id, reserved: true);
        $queue = Craft::$app->getQueue();
        self::assertInstanceOf(Queue::class, $queue);
        $property = new \ReflectionProperty(Queue::class, '_executingJobId');
        $property->setValue($queue, $id);
    }

    private function setQueueState(string $id, bool $fail = false, bool $reserved = false): void
    {
        Craft::$app->getDb()->createCommand()->update('{{%queue}}', [
            'fail' => $fail,
            'timeUpdated' => $reserved ? DateTimeHelper::currentTimeStamp() : null,
        ], ['id' => $id])->execute();
    }

    private function rowExists(string $id): bool
    {
        return (new Query())->from('{{%queue}}')->where(['id' => $id])->exists();
    }

    private function deleteQueueRows(): void
    {
        Craft::$app->getDb()->createCommand()->delete('{{%queue}}')->execute();
    }

    private function insertOldAnalyticsRow(): string
    {
        $uid = 'smartlink-scheduler-' . bin2hex(random_bytes(8));
        Craft::$app->getDb()->createCommand()->insert('{{%smartlinkmanager_analytics}}', [
            'linkId' => 1,
            'trafficType' => 'human',
            'dateCreated' => '2020-01-01 00:00:00',
            'dateUpdated' => '2020-01-01 00:00:00',
            'uid' => $uid,
        ])->execute();

        return $uid;
    }

    private function analyticsRowExists(string $uid): bool
    {
        return (new Query())->from('{{%smartlinkmanager_analytics}}')->where(['uid' => $uid])->exists();
    }

    /** @return list<int> */
    private function sqsDelays(): array
    {
        return $this->sqsProxy === null ? [] : array_column($this->sqsProxy->pushes, 'delay');
    }

    /** @return list<int> */
    private function unknownDelays(): array
    {
        return $this->unknownProxy === null ? [] : array_column($this->unknownProxy->pushes, 'delay');
    }

    private function invokePrivate(object $object, string $method): void
    {
        (new ReflectionMethod($object, $method))->invoke($object);
    }

    /** @param array<string, mixed> $settings */
    private function withSettingsPost(array $settings): void
    {
        $this->originalRequest ??= Craft::$app->getRequest();
        $this->originalResponse ??= Craft::$app->getResponse();
        $this->originalRequestMethod ??= $_SERVER['REQUEST_METHOD'] ?? 'GET';
        Craft::$app->set('request', new Request([
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'bodyParams' => ['section' => 'analytics', 'settings' => $settings],
        ]));
        Craft::$app->set('response', new Response());
        $_SERVER['REQUEST_METHOD'] = 'POST';
    }

    private function restoreSettingsRequest(): void
    {
        if ($this->originalRequest !== null) {
            Craft::$app->set('request', $this->originalRequest);
            $this->originalRequest = null;
        }
        if ($this->originalResponse !== null) {
            Craft::$app->set('response', $this->originalResponse);
            $this->originalResponse = null;
        }
        if ($this->originalRequestMethod !== null) {
            $_SERVER['REQUEST_METHOD'] = $this->originalRequestMethod;
            $this->originalRequestMethod = null;
        }
    }
}

final class RecordingSmartlinkSqsQueue extends SqsQueue
{
    /** @var list<array{delay: int, priority: mixed, ttr: int}> */
    public array $pushes = [];

    public bool $failPushes = false;

    protected function pushMessage($message, $ttr, $delay, $priority): string
    {
        if ($this->failPushes) {
            throw new RuntimeException('forced Smartlink recurring proxy failure');
        }

        $this->pushes[] = ['delay' => (int)$delay, 'priority' => $priority, 'ttr' => (int)$ttr];

        return 'smartlink-recurring-sqs-' . count($this->pushes);
    }
}

final class RecordingUnknownProxyQueue extends ProxyQueue
{
    /** @var list<array{delay: int, priority: mixed, ttr: int}> */
    public array $pushes = [];

    public function status($id): int
    {
        return self::STATUS_WAITING;
    }

    protected function pushMessage($message, $ttr, $delay, $priority): string
    {
        $this->pushes[] = ['delay' => (int)$delay, 'priority' => $priority, 'ttr' => (int)$ttr];

        return 'smartlink-recurring-unknown-' . count($this->pushes);
    }
}

final class ForeignCleanupAnalyticsJob extends BaseJob
{
    public function execute($queue): void
    {
    }

    protected function defaultDescription(): ?string
    {
        return 'Foreign analytics cleanup';
    }
}
