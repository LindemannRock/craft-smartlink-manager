<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\events;

use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use yii\base\Event;

/**
 * Smart Link Event
 *
 * @property SmartLink $smartLink The smart link element
 * @property DeviceInfo $device The device information
 * @property string $redirectUrl The redirect URL (modifiable)
 * @property array $metadata Additional metadata
 * @since 1.0.0
 */
class SmartLinkEvent extends Event
{
    /**
     * @var SmartLink The smart link element
     * @since 5.0.0
     */
    public SmartLink $smartLink;

    /**
     * @var DeviceInfo The device information
     * @since 5.0.0
     */
    public DeviceInfo $device;

    /**
     * @var string The redirect URL
     * @since 5.0.0
     */
    public string $redirectUrl = '';

    /**
     * @var array Additional metadata
     * @since 5.0.0
     */
    public array $metadata = [];
}
