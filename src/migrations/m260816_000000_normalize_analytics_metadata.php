<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\migrations;

use craft\db\Migration;
use craft\db\Query;
use lindemannrock\smartlinkmanager\services\analytics\AnalyticsMetadata;

/**
 * Normalizes analytics metadata strings created after the native JSON migration.
 *
 * @since 5.38.0
 */
class m260816_000000_normalize_analytics_metadata extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $normalized = 0;
        $table = $this->analyticsTableName();

        foreach ((new Query())
            ->select(['id', 'metadata'])
            ->from($table)
            ->where(['not', ['metadata' => null]])
            ->batch(500, $this->db) as $batch) {
            foreach ($batch as $row) {
                $metadata = AnalyticsMetadata::unwrapLegacyJsonString($row['metadata']);
                if ($metadata === null) {
                    continue;
                }

                $this->db->createCommand()
                    ->update($table, ['metadata' => $metadata], ['id' => $row['id']], [], false)
                    ->execute();
                $normalized++;
            }
        }

        echo "    > normalized {$normalized} analytics metadata row(s)\n";

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "    > analytics metadata normalization cannot be reverted safely\n";

        return false;
    }

    protected function analyticsTableName(): string
    {
        return '{{%smartlinkmanager_analytics}}';
    }
}
