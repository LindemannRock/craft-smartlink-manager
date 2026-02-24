<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\records;

use craft\db\ActiveRecord;
use craft\records\Site;
use yii\db\ActiveQueryInterface;

/**
 * Smart Link Content Record
 *
 * @property int $id
 * @property int $smartLinkId
 * @property int $siteId
 * @property string $title
 * @property string|null $iosUrl
 * @property string|null $androidUrl
 * @property string|null $huaweiUrl
 * @property string|null $amazonUrl
 * @property string|null $windowsUrl
 * @property string|null $macUrl
 * @property string $fallbackUrl
 * @property string|null $description
 * @property int|null $imageId
 * @property string $imageSize
 * @property SmartLinkRecord $smartLink
 * @property Site $site
 * @since 1.0.0
 */
class SmartLinkContentRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%smartlinkmanager_content}}';
    }

    /**
     * Returns the smart link.
     *
     * @return ActiveQueryInterface
     */
    public function getSmartLink(): ActiveQueryInterface
    {
        return $this->hasOne(SmartLinkRecord::class, ['id' => 'smartLinkId']);
    }

    /**
     * Returns the site.
     *
     * @return ActiveQueryInterface
     */
    public function getSite(): ActiveQueryInterface
    {
        return $this->hasOne(Site::class, ['id' => 'siteId']);
    }
}
