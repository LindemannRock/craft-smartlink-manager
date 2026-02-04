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
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $iosUrl
 * @property string|null $androidUrl
 * @property string|null $huaweiUrl
 * @property string|null $amazonUrl
 * @property string|null $windowsUrl
 * @property string|null $macUrl
 * @property string $fallbackUrl
 * @property bool $trackAnalytics
 * @property bool $qrCodeEnabled
 * @property int $qrCodeSize
 * @property string|null $qrCodeColor
 * @property string|null $qrCodeBgColor
 * @property array|null $languageDetection
 * @property array|null $localizedUrls
 * @property string|null $metadata
 * @property string|null $title
 * @property int|null $authorId
 * @property string|null $postDate
 * @property string|null $dateExpired
 * @property bool $hideTitle
 * @property string|null $qrCodeFormat
 * @property string|null $qrCodeEyeColor
 * @property int|null $qrLogoId
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
