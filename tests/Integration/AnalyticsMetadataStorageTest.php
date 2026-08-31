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
use craft\db\Connection;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use lindemannrock\base\helpers\DbHelper;
use lindemannrock\smartlinkmanager\migrations\m260816_000000_normalize_analytics_metadata;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsMetadata;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use yii\db\ColumnSchema;
use yii\db\JsonExpression;
use yii\db\Schema;

/**
 * Covers native analytics metadata storage and historical normalization.
 *
 * @since 5.37.4
 */
final class AnalyticsMetadataStorageTest extends TestCase
{
    /**
     * @param class-string<ColumnSchema> $columnSchemaClass
     */
    #[DataProvider('nativeJsonColumnSchemas')]
    public function testNativeJsonColumnsEncodeArraysOnceAndPreEncodedStringsTwice(
        string $columnSchemaClass,
        string $dbType,
    ): void {
        $metadata = ['source' => 'qr', 'clickType' => 'redirect'];
        $encoded = Json::encode($metadata);
        $column = new $columnSchemaClass();
        $column->type = Schema::TYPE_JSON;
        $column->dbType = $dbType;

        $native = $column->dbTypecast($metadata);
        $legacy = $column->dbTypecast($encoded);

        self::assertInstanceOf(JsonExpression::class, $native);
        self::assertInstanceOf(JsonExpression::class, $legacy);
        self::assertSame($metadata, $native->getValue());
        self::assertSame($encoded, $legacy->getValue());
        self::assertSame($encoded, Json::encode($native->getValue()));
        self::assertSame(Json::encode($encoded), Json::encode($legacy->getValue()));
    }

    public function testMetadataDecoderPreservesObjectsAndFalseyValuesAndFailsClosed(): void
    {
        $metadata = [
            'source' => 'qr',
            'enabled' => false,
            'count' => 0,
            'label' => '',
        ];

        self::assertSame($metadata, AnalyticsMetadata::decode($metadata));
        self::assertSame($metadata, AnalyticsMetadata::decode(Json::encode($metadata)));
        self::assertSame($metadata, AnalyticsMetadata::decode(Json::encode(Json::encode($metadata))));

        foreach ([null, '', 'not-json', Json::encode(null), Json::encode(false), Json::encode('scalar')] as $value) {
            self::assertSame([], AnalyticsMetadata::decode($value));
        }
    }

    public function testOnlyLegacyOuterJsonStringsAreEligibleForNormalization(): void
    {
        $object = AnalyticsMetadata::unwrapLegacyJsonString(Json::encode(Json::encode(['source' => 'qr'])));
        $list = AnalyticsMetadata::unwrapLegacyJsonString(Json::encode(Json::encode(['ios', 'android'])));

        self::assertInstanceOf(\stdClass::class, $object);
        self::assertSame('qr', $object->source);
        self::assertSame(['ios', 'android'], $list);

        foreach ([
            Json::encode(['source' => 'qr']),
            Json::encode(Json::encode('scalar')),
            Json::encode(Json::encode(null)),
            null,
            '',
            'not-json',
        ] as $value) {
            self::assertNull(AnalyticsMetadata::unwrapLegacyJsonString($value));
        }
    }

    public function testMysqlAndPostgresJsonExtractionUseTheirNativeSyntax(): void
    {
        $originalDb = Craft::$app->getDb();

        try {
            Craft::$app->set('db', new Connection(['dsn' => 'mysql:host=unused;dbname=unused']));
            self::assertSame(
                "JSON_UNQUOTE(JSON_EXTRACT([[metadata]], '$.source'))",
                DbHelper::jsonExtract('metadata', 'source'),
            );

            Craft::$app->set('db', new Connection(['dsn' => 'pgsql:host=unused;dbname=unused']));
            self::assertSame("[[metadata]]->>'source'", DbHelper::jsonExtract('metadata', 'source'));
        } finally {
            Craft::$app->set('db', $originalDb);
        }
    }

    public function testMigrationNormalizesOnlyEligibleRowsAndIsSafeToRepeat(): void
    {
        $db = Craft::$app->getDb();
        $table = 'smartlinkmanager_metadata_test_' . bin2hex(random_bytes(4));
        $setup = new class(['db' => $db]) extends Migration {
            public function safeUp(): bool
            {
                return true;
            }

            public function safeDown(): bool
            {
                return true;
            }
        };

        ob_start();
        try {
            $setup->createTable($table, [
                'id' => $setup->primaryKey(),
                'linkId' => $setup->integer()->notNull(),
                'metadata' => $setup->json()->null(),
                'marker' => $setup->string()->notNull(),
                'dateUpdated' => $setup->dateTime()->notNull(),
            ]);
        } finally {
            ob_end_clean();
        }

        $dateUpdated = '2026-08-16 12:00:00';
        $rows = [
            'proper-object' => ['source' => 'direct', 'clickType' => 'redirect'],
            'legacy-object' => Json::encode(['source' => 'qr', 'clickType' => 'button']),
            'legacy-list' => Json::encode(['ios', 'android']),
            'legacy-empty-object' => Json::encode(new \stdClass()),
            'empty-string' => '',
            'malformed-inner' => 'not-json',
            'scalar-inner' => Json::encode('scalar'),
            'null' => null,
        ];

        try {
            foreach ($rows as $marker => $metadata) {
                $db->createCommand()->insert($table, [
                    'linkId' => 42,
                    'metadata' => $metadata,
                    'marker' => $marker,
                    'dateUpdated' => $dateUpdated,
                ])->execute();
            }

            $before = $this->rowsByMarker($table);
            $migration = new class($table, ['db' => $db]) extends m260816_000000_normalize_analytics_metadata {
                /** @param array<string, mixed> $config */
                public function __construct(private readonly string $table, array $config = [])
                {
                    parent::__construct($config);
                }

                protected function analyticsTableName(): string
                {
                    return $this->table;
                }
            };

            ob_start();
            try {
                self::assertTrue($migration->safeUp());
                $firstOutput = (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }

            $afterFirst = $this->rowsByMarker($table);
            self::assertStringContainsString('normalized 3 analytics metadata row(s)', $firstOutput);
            self::assertSame($before['proper-object']['metadata'], $afterFirst['proper-object']['metadata']);
            self::assertSame(['source' => 'qr', 'clickType' => 'button'], AnalyticsMetadata::decode($afterFirst['legacy-object']['metadata']));
            self::assertSame(['ios', 'android'], AnalyticsMetadata::decode($afterFirst['legacy-list']['metadata']));
            self::assertSame([], AnalyticsMetadata::decode($afterFirst['legacy-empty-object']['metadata']));
            self::assertNull(AnalyticsMetadata::unwrapLegacyJsonString($afterFirst['legacy-object']['metadata']));

            foreach (['empty-string', 'malformed-inner', 'scalar-inner', 'null'] as $marker) {
                self::assertSame($before[$marker]['metadata'], $afterFirst[$marker]['metadata'], $marker);
            }

            foreach ($before as $marker => $row) {
                self::assertSame($row['linkId'], $afterFirst[$marker]['linkId'], $marker);
                self::assertSame($row['marker'], $afterFirst[$marker]['marker'], $marker);
                self::assertSame($row['dateUpdated'], $afterFirst[$marker]['dateUpdated'], $marker);
            }

            self::assertSame(1, (int) (new Query())
                ->from($table)
                ->where([DbHelper::jsonExtract('metadata', 'source') => 'qr'])
                ->count());

            ob_start();
            try {
                self::assertTrue($migration->safeUp());
                $secondOutput = (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
            $afterSecond = $this->rowsByMarker($table);

            self::assertStringContainsString('normalized 0 analytics metadata row(s)', $secondOutput);
            self::assertSame($afterFirst, $afterSecond);

            ob_start();
            try {
                self::assertFalse($migration->safeDown());
            } finally {
                ob_end_clean();
            }
            self::assertSame($afterSecond, $this->rowsByMarker($table));
        } finally {
            $this->dropTableIfExists($table);
        }
    }

    /**
     * @return iterable<string, array{class-string<ColumnSchema>, string}>
     */
    public static function nativeJsonColumnSchemas(): iterable
    {
        yield 'MySQL' => [\yii\db\mysql\ColumnSchema::class, Schema::TYPE_JSON];
        yield 'PostgreSQL' => [\yii\db\pgsql\ColumnSchema::class, \yii\db\pgsql\Schema::TYPE_JSONB];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function rowsByMarker(string $table): array
    {
        return (new Query())
            ->from($table)
            ->orderBy(['id' => SORT_ASC])
            ->indexBy('marker')
            ->all();
    }

    private function dropTableIfExists(string $table): void
    {
        $db = Craft::$app->getDb();
        if ($db->tableExists($table)) {
            $db->createCommand()->dropTable($table)->execute();
        }
    }
}
