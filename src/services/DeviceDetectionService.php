<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services;

use Craft;
use craft\base\Component;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\base\traits\DeviceDetectionTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Device Detection Service
 *
 * @since 1.0.0
 */
class DeviceDetectionService extends Component
{
    use LoggingTrait;
    use DeviceDetectionTrait;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Detect device information from user agent
     *
     * @param string|null $userAgent
     * @return DeviceInfo
     */
    public function detectDevice(?string $userAgent = null): DeviceInfo
    {
        $data = $this->detectDeviceInfo($userAgent);

        /** @var DeviceInfo $deviceInfo */
        $deviceInfo = $this->buildDeviceModel($data, DeviceInfo::class, [
            'userAgent' => 'userAgent',
            'deviceType' => 'deviceType',
            'isMobile' => 'isMobile',
            'isTablet' => 'isTablet',
            'isDesktop' => 'isDesktop',
            'isMobileApp' => 'isMobileApp',
            'brand' => 'deviceBrand',
            'model' => 'deviceModel',
            'osName' => 'osName',
            'osVersion' => 'osVersion',
            'clientType' => 'clientType',
            'browser' => 'browser',
            'browserVersion' => 'browserVersion',
            'browserEngine' => 'browserEngine',
            'isBot' => 'isRobot',
            'botName' => 'botName',
            'platform' => 'platform',
            'vendor' => 'vendor',
        ]);

        $deviceInfo->language = $data['language'] ?? null;

        return $deviceInfo;
    }

    /**
     * Get redirect URL based on device and smart link configuration
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $deviceInfo
     * @param string|null $language
     * @return string
     */
    public function getRedirectUrl(SmartLink $smartLink, DeviceInfo $deviceInfo, ?string $language = null): string
    {
        // Check for localized URLs first
        if ($language && $smartLink->localizedUrls) {
            $localizedUrls = $smartLink->localizedUrls[$language] ?? null;
            if ($localizedUrls) {
                return $this->_getUrlForPlatform($deviceInfo->platform, $localizedUrls, $smartLink);
            }
        }
        
        // Use default URLs
        return $this->_getUrlForPlatform($deviceInfo->platform, [
            'iosUrl' => $smartLink->iosUrl,
            'androidUrl' => $smartLink->androidUrl,
            'huaweiUrl' => $smartLink->huaweiUrl,
            'amazonUrl' => $smartLink->amazonUrl,
            'windowsUrl' => $smartLink->windowsUrl,
            'macUrl' => $smartLink->macUrl,
            'fallbackUrl' => $smartLink->fallbackUrl,
        ], $smartLink);
    }

    /**
     * Detect language from request
     *
     * @return string
     */
    public function detectLanguage(): string
    {
        return $this->detectLanguageFromConfig();
    }

    /**
     * Check if device is mobile
     *
     * @param DeviceInfo $deviceInfo
     * @return bool
     */
    public function isMobileDevice(DeviceInfo $deviceInfo): bool
    {
        return $deviceInfo->isMobile || $deviceInfo->isTablet;
    }

    /**
     * Get app store name for platform
     *
     * @param string $platform
     * @return string
     */
    public function getAppStoreName(string $platform): string
    {
        return match ($platform) {
            'ios' => 'App Store',
            'android' => 'Google Play',
            'huawei' => 'AppGallery',
            'amazon' => 'Amazon Appstore',
            'windows' => 'Microsoft Store',
            default => 'App Store',
        };
    }

    /**
     * Get URL for specific platform
     *
     * @param string $platform
     * @param array $urls
     * @param SmartLink $smartLink
     * @return string
     */
    private function _getUrlForPlatform(string $platform, array $urls, SmartLink $smartLink): string
    {
        switch ($platform) {
            case 'ios':
                return $urls['iosUrl'] ?? '';

            case 'huawei':
                // Try Huawei first, then fallback to Android URL
                return $urls['huaweiUrl'] ?? $urls['androidUrl'] ?? '';

            case 'android':
                // Check if it's Amazon device
                $ua = strtolower(Craft::$app->getRequest()->getUserAgent() ?? '');
                if (strpos($ua, 'kindle') !== false || strpos($ua, 'silk') !== false) {
                    return $urls['amazonUrl'] ?? $urls['androidUrl'] ?? '';
                }
                return $urls['androidUrl'] ?? '';

            case 'windows':
                return $urls['windowsUrl'] ?? '';

            case 'macos':
                return $urls['macUrl'] ?? '';

            default:
                // Unknown platform - return empty, show landing page
                return '';
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDeviceDetectionConfig(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();

        return [
            'cacheEnabled' => (bool) $settings->cacheDeviceDetection,
            'cacheStorageMethod' => $settings->cacheStorageMethod,
            'cacheDuration' => (int) $settings->deviceDetectionCacheDuration,
            'cachePath' => PluginHelper::getCachePath(SmartLinkManager::$plugin, 'device'),
            'cacheKeyPrefix' => PluginHelper::getCacheKeyPrefix(SmartLinkManager::$plugin->id, 'device'),
            'cacheKeySet' => PluginHelper::getCacheKeySet(SmartLinkManager::$plugin->id, 'device'),
            'includeLanguage' => true,
            'includePlatform' => true,
            'languageDetectionMethod' => $settings->languageDetectionMethod ?? 'browser',
            'enableGeoDetection' => (bool) $settings->enableGeoDetection,
            'geoLookupCallback' => function(string $ip): ?array {
                return SmartLinkManager::$plugin->analytics->getLocationFromIp($ip);
            },
        ];
    }
}
