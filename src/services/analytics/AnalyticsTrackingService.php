<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services\analytics;

use Craft;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use lindemannrock\base\helpers\AnalyticsIpHelper;
use lindemannrock\base\helpers\GeoHelper;
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
        $this->logDebug('Saving Smart Link analytics', ['linkId' => $linkId]);

        try {
            $db = Craft::$app->getDb();

            $settings = SmartLinkManager::$plugin->getSettings();
            $ipState = AnalyticsIpHelper::prepare(
                $metadata['ip'] ?? null,
                $settings->anonymizeIpAddress,
                $settings->enableGeoDetection,
                fn(string $ip): string => $this->_hashIpWithSalt($ip),
            );

            if ($ipState['hashError'] !== null) {
                $this->logError('Failed to hash IP address', ['error' => $ipState['hashError']->getMessage()]);
            }

            $ipHash = $ipState['hashedIp'];
            $geoLookupIp = $ipState['geoLookupIp'];
            $siteId = $metadata['siteId'] ?? Craft::$app->getSites()->getCurrentSite()->id;
            $referrer = $metadata['referrer'] ?? null;
            unset($metadata['ip']);

            $data = [
                'linkId' => $linkId,
                'siteId' => $siteId,
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
                'botCategory' => $deviceInfo['botCategory'] ?? null,
                'botUrl' => $deviceInfo['botUrl'] ?? null,
                'botProducerName' => $deviceInfo['botProducerName'] ?? null,
                'botProducerUrl' => $deviceInfo['botProducerUrl'] ?? null,
                'isSystemAgent' => $deviceInfo['isSystemAgent'] ?? false,
                'trafficType' => $deviceInfo['trafficType'] ?? 'human',
                'country' => null,
                'language' => $deviceInfo['language'] ?? null,
                'referrer' => $referrer,
                'ip' => $ipHash,
                'userAgent' => $deviceInfo['userAgent'] ?? null,
                'metadata' => $metadata,
                'dateCreated' => Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ];

            if ($geoLookupIp) {
                $location = $this->getLocationFromIp($geoLookupIp);
                if ($location) {
                    $data['country'] = $location['countryCode'];
                    $data['city'] = $location['city'];
                    $data['region'] = $location['region'];
                    $data['timezone'] = $location['timezone'];
                    $data['latitude'] = $location['lat'];
                    $data['longitude'] = $location['lon'];
                }
            }

            $data = array_intersect_key($data, array_flip($db->getTableSchema('{{%smartlinkmanager_analytics}}')?->columnNames ?? []));

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
        $geoData = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false
            ? $this->getDefaultLocation()
            : $this->lookupGeoIp($ip, $this->getGeoConfig());

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
            'logCategory' => SmartLinkManager::$plugin->id,
        ];
    }

    /**
     * Get default location for private/local IPs
     *
     * @return array<string, mixed>|null
     */
    private function getDefaultLocation(): ?array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $defaultCountry = $settings->defaultCountry;
        $defaultCity = $settings->defaultCity;

        if (!$defaultCountry || !$defaultCity) {
            return null;
        }

        $location = GeoHelper::getDefaultLocation($defaultCountry, $defaultCity);

        if ($location !== null) {
            return $location;
        }

        Craft::warning('Configured default analytics location was not found; leaving local/private IP geo fields empty. | ' . json_encode([
            'configuredCountry' => $defaultCountry,
            'configuredCity' => $defaultCity,
        ]), SmartLinkManager::$plugin->id);

        return null;
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
