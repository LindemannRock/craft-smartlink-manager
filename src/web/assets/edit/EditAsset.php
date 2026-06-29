<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\web\assets\edit;

use craft\web\AssetBundle;

/**
 * SmartLink edit asset bundle.
 *
 * Provides client-side behavior for the SmartLink edit form.
 *
 * @since 5.31.0
 */
class EditAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        $this->sourcePath = __DIR__ . '/dist';

        $this->js = [
            'edit.js',
        ];

        parent::init();
    }
}
