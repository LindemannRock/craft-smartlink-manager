<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;

/**
 * Smart Link Record
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property bool $trackAnalytics
 * @property bool $qrCodeEnabled
 * @property int|null $qrCodeSize
 * @property string|null $qrCodeColor
 * @property string|null $qrCodeBgColor
 * @property string|null $qrCodeEyeColor
 * @property string|null $qrCodeFormat
 * @property int|null $qrLogoId
 * @property bool $hideTitle
 * @property bool|null $languageDetection
 * @property int $hits
 * @property string|null $metadata
 * @property int|null $authorId
 * @property string|null $postDate
 * @property string|null $dateExpired
 * @property string $dateCreated
 * @property string $dateUpdated
 * @property string $uid
 * @property Element $element
 * @since 1.0.0
 */
class SmartLinkRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%smartlinkmanager}}';
    }

    /**
     * Returns the smart link's element.
     *
     * @return ActiveQueryInterface
     * @since 1.0.0
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }
}
