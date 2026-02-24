<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services\analytics;

use Craft;
use craft\helpers\App;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use lindemannrock\base\traits\GeoLookupTrait;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\db\Expression;

/**
 * Analytics Tracking Service
 *
 * Click recording, IP privacy, and geo lookup.
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     5.22.0
 */
class AnalyticsTrackingService
{
    use LoggingTrait;
    use GeoLookupTrait;

    public function __construct()
    {
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Track a click on a smart link
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $deviceInfo
     * @param array $metadata
     */
    public function trackClick(SmartLink $smartLink, DeviceInfo $deviceInfo, array $metadata = []): void
    {
        $metadata['ip'] = Craft::$app->request->getUserIP();

        try {
            $this->saveAnalytics(
                $smartLink->id,
                $deviceInfo->toArray(),
                $metadata
            );
        } catch (\Exception $e) {
            $this->logError('Failed to save analytics', ['error' => $e->getMessage()]);
        }

        $this->_incrementClickCount($smartLink);
    }

    /**
     * Save analytics record
     *
     * @param int $linkId
     * @param array $deviceInfo
     * @param array $metadata
     * @return bool
     */
    public function saveAnalytics(int $linkId, array $deviceInfo, array $metadata = []): bool
    {
        $this->logInfo('Saving Smart Link analytics', ['linkId' => $linkId]);

        try {
            $db = Craft::$app->getDb();

            $settings = SmartLinkManager::$plugin->getSettings();
            if ($settings->anonymizeIpAddress && isset($metadata['ip'])) {
                $metadata['ip'] = $this->_anonymizeIp($metadata['ip']);
            }

            $ipHash = null;
            if (isset($metadata['ip'])) {
                try {
                    $ipHash = $this->_hashIpWithSalt($metadata['ip']);
                } catch (\Exception $e) {
                    $this->logError('Failed to hash IP address', ['error' => $e->getMessage()]);
                    $ipHash = null;
                    unset($metadata['ip']);
                }
            }

            $data = [
                'linkId' => $linkId,
                'siteId' => $metadata['siteId'] ?? Craft::$app->getSites()->getCurrentSite()->id,
                'deviceType' => $deviceInfo['deviceType'] ?? $deviceInfo['type'] ?? null,
                'deviceBrand' => $deviceInfo['brand'] ?? null,
                'deviceModel' => $deviceInfo['model'] ?? null,
                'osName' => $deviceInfo['osName'] ?? null,
                'osVersion' => $deviceInfo['osVersion'] ?? null,
                'browser' => $deviceInfo['browser'] ?? null,
                'browserVersion' => $deviceInfo['browserVersion'] ?? null,
                'browserEngine' => $deviceInfo['browserEngine'] ?? null,
                'clientType' => $deviceInfo['clientType'] ?? null,
                'isRobot' => $deviceInfo['isBot'] ?? $deviceInfo['isRobot'] ?? false,
                'isMobileApp' => $deviceInfo['isMobileApp'] ?? false,
                'botName' => $deviceInfo['botName'] ?? null,
                'country' => null,
                'language' => $metadata['language'] ?? null,
                'referrer' => $metadata['referrer'] ?? null,
                'ip' => $ipHash,
                'userAgent' => $deviceInfo['userAgent'] ?? null,
                'metadata' => Json::encode($metadata),
                'dateCreated' => Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ];

            if (SmartLinkManager::$plugin->getSettings()->enableGeoDetection && isset($metadata['ip'])) {
                $location = $this->getLocationFromIp($metadata['ip']);
                if ($location) {
                    $data['country'] = $location['countryCode'];
                    $data['city'] = $location['city'];
                    $data['region'] = $location['region'];
                    $data['timezone'] = $location['timezone'];
                    $data['latitude'] = $location['lat'];
                    $data['longitude'] = $location['lon'];
                }
            }

            unset($metadata['ip']);
            $data['metadata'] = Json::encode($metadata);

            return (bool) $db->createCommand()
                ->insert('{{%smartlinkmanager_analytics}}', $data)
                ->execute();
        } catch (\Exception $e) {
            $context = ['error' => $e->getMessage(), 'linkId' => $linkId];
            if (isset($data)) {
                $context['data'] = $data;
            }
            $this->logError('Failed to save analytics', $context);
            return false;
        }
    }

    /**
     * Get location data from IP address
     *
     * @param string $ip
     * @return array|null
     */
    public function getLocationFromIp(string $ip): ?array
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $this->getDefaultLocation();
        }

        $geoData = $this->lookupGeoIp($ip, $this->getGeoConfig());

        if ($geoData === null) {
            return null;
        }

        return [
            'countryCode' => $geoData['countryCode'] ?? null,
            'country' => $geoData['country'] ?? null,
            'city' => $geoData['city'] ?? null,
            'region' => $geoData['region'] ?? null,
            'timezone' => $geoData['timezone'] ?? null,
            'lat' => $geoData['latitude'] ?? null,
            'lon' => $geoData['longitude'] ?? null,
        ];
    }

    /**
     * Get country from IP address (backward compatibility)
     *
     * @param string $ip
     * @return string|null
     */
    public function getCountryFromIp(string $ip): ?string
    {
        $location = $this->getLocationFromIp($ip);
        return $location ? $location['countryCode'] : null;
    }

    /**
     * Get geo config from plugin settings
     *
     * @return array<string, mixed>
     */
    protected function getGeoConfig(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();

        return [
            'provider' => $settings->geoProvider ?? 'ip-api.com',
            'apiKey' => $settings->geoApiKey ?? null,
        ];
    }

    /**
     * Get default location for private/local IPs
     *
     * @return array<string, mixed>
     */
    private function getDefaultLocation(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $defaultCountry = $settings->defaultCountry ?: (App::env('SMARTLINK_MANAGER_DEFAULT_COUNTRY') ?: 'AE');
        $defaultCity = $settings->defaultCity ?: (App::env('SMARTLINK_MANAGER_DEFAULT_CITY') ?: 'Dubai');

        $locations = [
            'US' => [
                'New York' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'New York', 'region' => 'New York', 'timezone' => 'America/New_York', 'lat' => 40.7128, 'lon' => -74.0060],
                'Los Angeles' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'Los Angeles', 'region' => 'California', 'timezone' => 'America/Los_Angeles', 'lat' => 34.0522, 'lon' => -118.2437],
                'Chicago' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'Chicago', 'region' => 'Illinois', 'timezone' => 'America/Chicago', 'lat' => 41.8781, 'lon' => -87.6298],
                'San Francisco' => ['countryCode' => 'US', 'country' => 'United States', 'city' => 'San Francisco', 'region' => 'California', 'timezone' => 'America/Los_Angeles', 'lat' => 37.7749, 'lon' => -122.4194],
            ],
            'GB' => [
                'London' => ['countryCode' => 'GB', 'country' => 'United Kingdom', 'city' => 'London', 'region' => 'England', 'timezone' => 'Europe/London', 'lat' => 51.5074, 'lon' => -0.1278],
                'Manchester' => ['countryCode' => 'GB', 'country' => 'United Kingdom', 'city' => 'Manchester', 'region' => 'England', 'timezone' => 'Europe/London', 'lat' => 53.4808, 'lon' => -2.2426],
            ],
            'AE' => [
                'Dubai' => ['countryCode' => 'AE', 'country' => 'United Arab Emirates', 'city' => 'Dubai', 'region' => 'Dubai', 'timezone' => 'Asia/Dubai', 'lat' => 25.2048, 'lon' => 55.2708],
                'Abu Dhabi' => ['countryCode' => 'AE', 'country' => 'United Arab Emirates', 'city' => 'Abu Dhabi', 'region' => 'Abu Dhabi', 'timezone' => 'Asia/Dubai', 'lat' => 24.4539, 'lon' => 54.3773],
            ],
            'SA' => [
                'Riyadh' => ['countryCode' => 'SA', 'country' => 'Saudi Arabia', 'city' => 'Riyadh', 'region' => 'Riyadh Province', 'timezone' => 'Asia/Riyadh', 'lat' => 24.7136, 'lon' => 46.6753],
                'Jeddah' => ['countryCode' => 'SA', 'country' => 'Saudi Arabia', 'city' => 'Jeddah', 'region' => 'Makkah Province', 'timezone' => 'Asia/Riyadh', 'lat' => 21.5433, 'lon' => 39.1728],
            ],
            'DE' => [
                'Berlin' => ['countryCode' => 'DE', 'country' => 'Germany', 'city' => 'Berlin', 'region' => 'Berlin', 'timezone' => 'Europe/Berlin', 'lat' => 52.5200, 'lon' => 13.4050],
                'Munich' => ['countryCode' => 'DE', 'country' => 'Germany', 'city' => 'Munich', 'region' => 'Bavaria', 'timezone' => 'Europe/Berlin', 'lat' => 48.1351, 'lon' => 11.5820],
            ],
            'FR' => [
                'Paris' => ['countryCode' => 'FR', 'country' => 'France', 'city' => 'Paris', 'region' => 'Ile-de-France', 'timezone' => 'Europe/Paris', 'lat' => 48.8566, 'lon' => 2.3522],
            ],
            'CA' => [
                'Toronto' => ['countryCode' => 'CA', 'country' => 'Canada', 'city' => 'Toronto', 'region' => 'Ontario', 'timezone' => 'America/Toronto', 'lat' => 43.6532, 'lon' => -79.3832],
                'Vancouver' => ['countryCode' => 'CA', 'country' => 'Canada', 'city' => 'Vancouver', 'region' => 'British Columbia', 'timezone' => 'America/Vancouver', 'lat' => 49.2827, 'lon' => -123.1207],
            ],
            'AU' => [
                'Sydney' => ['countryCode' => 'AU', 'country' => 'Australia', 'city' => 'Sydney', 'region' => 'New South Wales', 'timezone' => 'Australia/Sydney', 'lat' => -33.8688, 'lon' => 151.2093],
                'Melbourne' => ['countryCode' => 'AU', 'country' => 'Australia', 'city' => 'Melbourne', 'region' => 'Victoria', 'timezone' => 'Australia/Melbourne', 'lat' => -37.8136, 'lon' => 144.9631],
            ],
            'JP' => [
                'Tokyo' => ['countryCode' => 'JP', 'country' => 'Japan', 'city' => 'Tokyo', 'region' => 'Tokyo', 'timezone' => 'Asia/Tokyo', 'lat' => 35.6762, 'lon' => 139.6503],
            ],
            'SG' => [
                'Singapore' => ['countryCode' => 'SG', 'country' => 'Singapore', 'city' => 'Singapore', 'region' => 'Singapore', 'timezone' => 'Asia/Singapore', 'lat' => 1.3521, 'lon' => 103.8198],
            ],
            'IN' => [
                'Mumbai' => ['countryCode' => 'IN', 'country' => 'India', 'city' => 'Mumbai', 'region' => 'Maharashtra', 'timezone' => 'Asia/Kolkata', 'lat' => 19.0760, 'lon' => 72.8777],
                'Delhi' => ['countryCode' => 'IN', 'country' => 'India', 'city' => 'Delhi', 'region' => 'Delhi', 'timezone' => 'Asia/Kolkata', 'lat' => 28.7041, 'lon' => 77.1025],
            ],
        ];

        if (isset($locations[$defaultCountry][$defaultCity])) {
            return $locations[$defaultCountry][$defaultCity];
        }

        return $locations['AE']['Dubai'];
    }

    /**
     * Increment click count in smart link metadata
     *
     * @param SmartLink $smartLink
     */
    private function _incrementClickCount(SmartLink $smartLink): void
    {
        Craft::$app->db->createCommand()
            ->update('{{%smartlinkmanager}}', [
                'hits' => new Expression('[[hits]] + 1'),
            ], ['id' => $smartLink->id])
            ->execute();

        $smartLink->hits++;
    }

    /**
     * Anonymize IP address for privacy
     * IPv4: Masks last octet (192.168.1.123 -> 192.168.1.0)
     * IPv6: Masks last 80 bits (keeps first 48 bits)
     *
     * @param string $ip
     * @return string
     */
    private function _anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return preg_replace('/\.\d+$/', '.0', $ip);
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $binary = inet_pton($ip);
            if ($binary === false) {
                return $ip;
            }

            $anonymized = substr($binary, 0, 6) . str_repeat("\0", 10);

            $result = inet_ntop($anonymized);
            return $result !== false ? $result : $ip;
        }

        return $ip;
    }

    /**
     * Hash IP address with salt for privacy
     *
     * @param string $ip
     * @return string
     * @throws \Exception
     */
    private function _hashIpWithSalt(string $ip): string
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $salt = $settings->ipHashSalt;

        if (!$salt || $salt === '$SMARTLINK_MANAGER_IP_SALT' || trim($salt) === '') {
            $this->logError('IP hash salt not configured - analytics tracking disabled', [
                'ip' => 'hidden',
                'saltValue' => $salt ?? 'NULL',
            ]);
            throw new \Exception('IP hash salt not configured. Run: php craft smartlink-manager/security/generate-salt');
        }

        return hash('sha256', $ip . $salt);
    }
}
