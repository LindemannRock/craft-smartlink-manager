<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 */

namespace lindemannrock\smartlinkmanager\records;

use craft\db\ActiveRecord;

/**
 * Import history record
 *
 * @property int $id
 * @property int|null $userId
 * @property string|null $filename
 * @property int|null $filesize
 * @property int $imported
 * @property int $failed
 * @property \DateTime $dateCreated
 * @property \DateTime $dateUpdated
 * @property string $uid
 */
class ImportHistoryRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%smartlinkmanager_import_history}}';
    }
}
