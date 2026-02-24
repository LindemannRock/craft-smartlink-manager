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
     */
    public string $platform = 'other';

    /**
     * @var bool Is mobile device
     */
    public bool $isMobile = false;

    /**
     * @var bool Is tablet
     */
    public bool $isTablet = false;

    /**
     * @var bool Is desktop
     */
    public bool $isDesktop = false;

    /**
     * @var string User agent string
     */
    public string $userAgent = '';

    /**
     * @var string|null Device vendor (Apple, Samsung, Huawei, etc.)
     */
    public ?string $vendor = null;

    /**
     * @var string|null OS version
     */
    public ?string $osVersion = null;

    /**
     * @var string|null Browser name
     */
    public ?string $browser = null;

    /**
     * @var string|null Browser version
     */
    public ?string $browserVersion = null;

    /**
     * @var string|null Detected language
     */
    public ?string $language = null;

    /**
     * @var string|null Country code
     */
    public ?string $country = null;

    /**
     * @var string|null Device model
     */
    public ?string $model = null;

    /**
     * @var string|null Device brand name
     */
    public ?string $brand = null;

    /**
     * @var string|null Device type (smartphone, tablet, desktop, tv, console, etc.)
     */
    public ?string $deviceType = null;

    /**
     * @var bool Is bot/crawler
     */
    public bool $isBot = false;

    /**
     * @var string|null Bot name if detected
     */
    public ?string $botName = null;

    /**
     * @var string|null Operating system name
     */
    public ?string $osName = null;

    /**
     * @var string|null Browser engine
     */
    public ?string $browserEngine = null;

    /**
     * @var bool Is mobile app
     */
    public bool $isMobileApp = false;

    /**
     * @var string|null Client type (browser, mobile app, feed reader, etc.)
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
