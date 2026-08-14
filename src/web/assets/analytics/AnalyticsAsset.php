<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\web\assets\analytics;

use craft\web\AssetBundle;

/**
 * SmartLink Analytics Asset Bundle
 *
 * Provides SmartLink Manager analytics wiring for cp-analytics pages.
 *
 * @since 5.20.0
 */
class AnalyticsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = '@lindemannrock/smartlinkmanager/web/assets/analytics/dist';

        $this->depends = [
            \lindemannrock\base\web\assets\analytics\AnalyticsAsset::class,
        ];

        $this->js = [
            'analytics.js',
        ];

        parent::init();
    }
}
