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
        $this->setLoggingHandle('smartlink-manager');
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

        $deviceInfo = new DeviceInfo();
        $deviceInfo->userAgent = $data['userAgent'] ?? null;
        $deviceInfo->deviceType = $data['deviceType'] ?? null;
        $deviceInfo->isMobile = $data['isMobile'] ?? null;
        $deviceInfo->isTablet = $data['isTablet'] ?? null;
        $deviceInfo->isDesktop = $data['isDesktop'] ?? null;
        $deviceInfo->isMobileApp = $data['isMobileApp'] ?? null;
        $deviceInfo->brand = $data['deviceBrand'] ?? null;
        $deviceInfo->model = $data['deviceModel'] ?? null;
        $deviceInfo->osName = $data['osName'] ?? null;
        $deviceInfo->osVersion = $data['osVersion'] ?? null;
        $deviceInfo->clientType = $data['clientType'] ?? null;
        $deviceInfo->browser = $data['browser'] ?? null;
        $deviceInfo->browserVersion = $data['browserVersion'] ?? null;
        $deviceInfo->browserEngine = $data['browserEngine'] ?? null;
        $deviceInfo->isBot = (bool)($data['isRobot'] ?? false);
        $deviceInfo->botName = $data['botName'] ?? null;
        $deviceInfo->platform = $data['platform'] ?? null;
        $deviceInfo->vendor = $data['vendor'] ?? null;

        // Apply SmartLink-specific language detection (supports browser/ip/both)
        $deviceInfo->language = $this->detectLanguage();

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
        $settings = SmartLinkManager::$plugin->getSettings();
        $request = Craft::$app->getRequest();
        $detectedLang = null;
        
        // Always check URL parameter first (highest priority)
        $langParam = $request->getQueryParam('lang') ?? $request->getQueryParam('locale');
        if ($langParam) {
            $detectedLang = substr($langParam, 0, 2);
        }
        
        // Apply detection method from settings
        if (!$detectedLang) {
            switch ($settings->languageDetectionMethod) {
                case 'browser':
                    $detectedLang = $this->_detectFromBrowser();
                    break;
                    
                case 'ip':
                    if ($settings->enableGeoDetection) {
                        $detectedLang = $this->_detectFromIp();
                    }
                    break;
                    
                case 'both':
                    // Try browser first, then IP
                    $detectedLang = $this->_detectFromBrowser();
                    if (!$detectedLang && $settings->enableGeoDetection) {
                        $detectedLang = $this->_detectFromIp();
                    }
                    break;
            }
        }
        
        // Default to site language if nothing detected
        if (!$detectedLang) {
            $detectedLang = substr(Craft::$app->language, 0, 2);
        }
        
        // Validate against site languages
        $supportedLanguages = [];
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $supportedLanguages[] = substr($site->language, 0, 2);
        }
        $supportedLanguages = array_unique($supportedLanguages);
        
        if (!in_array($detectedLang, $supportedLanguages)) {
            // Default to primary site language
            $detectedLang = substr(Craft::$app->getSites()->getPrimarySite()->language, 0, 2);
        }
        
        return $detectedLang;
    }
    
    /**
     * Detect language from browser headers
     */
    private function _detectFromBrowser(): ?string
    {
        $acceptLanguage = Craft::$app->getRequest()->getHeaders()->get('Accept-Language');
        if ($acceptLanguage) {
            // Parse Accept-Language header
            $languages = [];
            $parts = explode(',', $acceptLanguage);
            foreach ($parts as $part) {
                $lang = explode(';', $part);
                $code = substr(trim($lang[0]), 0, 2);
                $quality = isset($lang[1]) ? (float) str_replace('q=', '', $lang[1]) : 1.0;
                $languages[$code] = $quality;
            }
            
            // Sort by quality
            arsort($languages);
            return array_key_first($languages);
        }
        
        return null;
    }
    
    /**
     * Detect language from IP geolocation
     */
    private function _detectFromIp(): ?string
    {
        // Get IP address
        $ip = Craft::$app->getRequest()->getUserIP();
        if (!$ip) {
            return null;
        }
        
        // Get location from analytics service
        $location = SmartLinkManager::$plugin->analytics->getLocationFromIp($ip);
        if ($location && isset($location['countryCode'])) {
            // Map common country codes to languages
            $countryToLang = [
                'SA' => 'ar', // Saudi Arabia
                'AE' => 'ar', // UAE
                'KW' => 'ar', // Kuwait
                'QA' => 'ar', // Qatar
                'BH' => 'ar', // Bahrain
                'OM' => 'ar', // Oman
                'EG' => 'ar', // Egypt
                'JO' => 'ar', // Jordan
                'LB' => 'ar', // Lebanon
                'IQ' => 'ar', // Iraq
                'SY' => 'ar', // Syria
                'YE' => 'ar', // Yemen
                'LY' => 'ar', // Libya
                'TN' => 'ar', // Tunisia
                'DZ' => 'ar', // Algeria
                'MA' => 'ar', // Morocco
                'US' => 'en', // United States
                'GB' => 'en', // United Kingdom
                'CA' => 'en', // Canada
                'AU' => 'en', // Australia
                'NZ' => 'en', // New Zealand
                'IE' => 'en', // Ireland
                // Add more mappings as needed
            ];
            
            return $countryToLang[$location['countryCode']] ?? null;
        }
        
        return null;
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
            'cacheKeyPrefix' => 'smartlinks:device:',
            'cacheKeySet' => 'smartlinkmanager-device-keys',
            'includeLanguage' => false,
            'includePlatform' => true,
        ];
    }
}
