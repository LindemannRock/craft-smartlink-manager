<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services;

use craft\base\Component;
use lindemannrock\base\cache\DisposableCacheStorageDecision;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Clears plugin-owned QR and device caches from local storage.
 *
 * @since 5.34.0
 */
class LocalCacheService extends Component
{
    /**
     * Clear cached QR code entries from the configured local cache backend.
     */
    public function clearQrCache(?DisposableCacheStorageDecision $decision = null): int
    {
        return SmartLinkManager::$plugin->cacheStorage->clearFamily(CacheStorageService::FAMILY_QR, $decision);
    }

    /**
     * Clear cached device entries from the configured local cache backend.
     */
    public function clearDeviceCache(?DisposableCacheStorageDecision $decision = null): int
    {
        return SmartLinkManager::$plugin->deviceDetection->clearCache($decision);
    }

    /**
     * Clear all plugin-owned local cache entries.
     */
    public function clearAllCaches(?DisposableCacheStorageDecision $decision = null): int
    {
        return $this->clearQrCache($decision) + $this->clearDeviceCache($decision);
    }
}
