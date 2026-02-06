<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Analytics Record
 *
 * @property int $id
 * @property int $linkId
 * @property int|null $siteId
 * @property string|null $deviceType
 * @property string|null $deviceBrand
 * @property string|null $deviceModel
 * @property string|null $osName
 * @property string|null $osVersion
 * @property string|null $browser
 * @property string|null $browserVersion
 * @property string|null $browserEngine
 * @property string|null $clientType
 * @property bool $isRobot
 * @property bool $isMobileApp
 * @property string|null $botName
 * @property string|null $country
 * @property string|null $city
 * @property string|null $region
 * @property string|null $timezone
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $language
 * @property string|null $referrer
 * @property string|null $ip
 * @property string|null $userAgent
 * @property string|null $metadata
 * @property string $dateCreated
 * @property string $dateUpdated
 * @property string $uid
 * @property SmartLinkRecord $smartLink
 * @since 1.0.0
 */
class AnalyticsRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%smartlinkmanager_analytics}}';
    }

    /**
     * Returns the analytics record's smart link.
     *
     * @return ActiveQueryInterface
     * @since 1.0.0
     */
    public function getSmartLink(): ActiveQueryInterface
    {
        return $this->hasOne(SmartLinkRecord::class, ['id' => 'linkId']);
    }
}
