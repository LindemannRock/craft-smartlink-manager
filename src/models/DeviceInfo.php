<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\models;

use craft\base\Model;

/**
 * Device Info Model
 *
 * @since 1.0.0
 */
class DeviceInfo extends Model
{
    /**
     * @var string Detected platform (ios, android, huawei, windows, macos, linux, other)
     * @since 1.0.0
     */
    public string $platform = 'other';

    /**
     * @var bool Is mobile device
     * @since 1.0.0
     */
    public bool $isMobile = false;

    /**
     * @var bool Is tablet
     * @since 1.0.0
     */
    public bool $isTablet = false;

    /**
     * @var bool Is desktop
     * @since 1.0.0
     */
    public bool $isDesktop = false;

    /**
     * @var string User agent string
     * @since 1.0.0
     */
    public string $userAgent = '';

    /**
     * @var string|null Device vendor (Apple, Samsung, Huawei, etc.)
     * @since 1.0.0
     */
    public ?string $vendor = null;

    /**
     * @var string|null OS version
     * @since 1.0.0
     */
    public ?string $osVersion = null;

    /**
     * @var string|null Browser name
     * @since 1.0.0
     */
    public ?string $browser = null;

    /**
     * @var string|null Browser version
     * @since 1.0.0
     */
    public ?string $browserVersion = null;

    /**
     * @var string|null Detected language
     * @since 1.0.0
     */
    public ?string $language = null;

    /**
     * @var string|null Country code
     * @since 1.0.0
     */
    public ?string $country = null;

    /**
     * @var string|null Device model
     * @since 1.0.0
     */
    public ?string $model = null;

    /**
     * @var string|null Device brand name
     * @since 1.0.0
     */
    public ?string $brand = null;

    /**
     * @var string|null Device type (smartphone, tablet, desktop, tv, console, etc.)
     * @since 1.0.0
     */
    public ?string $deviceType = null;

    /**
     * @var bool Is bot/crawler
     * @since 1.0.0
     */
    public bool $isBot = false;

    /**
     * @var string|null Bot name if detected
     * @since 1.0.0
     */
    public ?string $botName = null;

    /**
     * @var string|null Operating system name
     * @since 1.0.0
     */
    public ?string $osName = null;

    /**
     * @var string|null Browser engine
     * @since 1.0.0
     */
    public ?string $browserEngine = null;

    /**
     * @var bool Is mobile app
     * @since 1.0.0
     */
    public bool $isMobileApp = false;

    /**
     * @var string|null Client type (browser, mobile app, feed reader, etc.)
     * @since 1.0.0
     */
    public ?string $clientType = null;

    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        return [
            [['platform', 'userAgent'], 'required'],
            [['platform'], 'in', 'range' => ['ios', 'android', 'huawei', 'windows', 'macos', 'linux', 'other']],
            [['isMobile', 'isTablet', 'isDesktop', 'isBot', 'isMobileApp'], 'boolean'],
            [['vendor', 'osVersion', 'browser', 'browserVersion', 'language', 'country', 'model', 'brand', 'deviceType', 'botName', 'osName', 'browserEngine', 'clientType'], 'string'],
            [['language'], 'string', 'length' => 2],
            [['country'], 'string', 'length' => 2],
        ];
    }

    /**
     * Convert to array for JSON serialization
     *
     * @return array
     * @since 5.0.0
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true): array
    {
        // Use the deviceType from Matomo or fallback to basic detection
        $type = $this->deviceType ?: 'desktop';
        if (!$this->deviceType) {
            if ($this->isMobile) {
                $type = 'mobile';
            } elseif ($this->isTablet) {
                $type = 'tablet';
            }
        }
        
        return [
            'platform' => $this->platform,
            'type' => $type,
            'deviceType' => $this->deviceType,
            'name' => $this->vendor ?: $this->brand,
            'isMobile' => $this->isMobile,
            'isTablet' => $this->isTablet,
            'isDesktop' => $this->isDesktop,
            'isBot' => $this->isBot,
            'isMobileApp' => $this->isMobileApp,
            'vendor' => $this->vendor,
            'brand' => $this->brand,
            'model' => $this->model,
            'osName' => $this->osName,
            'osVersion' => $this->osVersion,
            'browser' => $this->browser,
            'browserVersion' => $this->browserVersion,
            'browserEngine' => $this->browserEngine,
            'clientType' => $this->clientType,
            'language' => $this->language,
            'country' => $this->country,
            'userAgent' => $this->userAgent,
            'botName' => $this->botName,
        ];
    }
}
