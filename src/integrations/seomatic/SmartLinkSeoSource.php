<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\integrations\seomatic;

use Craft;
use craft\base\Model;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Synthetic SEOmatic source model for route-backed SmartLink elements.
 *
 * @since 5.31.0
 */
class SmartLinkSeoSource extends Model
{
    public int $id = SeoSmartLink::SOURCE_ID;
    public string $handle = SeoSmartLink::SOURCE_HANDLE;
    public string $type = SeoSmartLink::SOURCE_TYPE;

    /**
     * @return array<int, SmartLinkSeoSourceSiteSettings>
     */
    public function getSiteSettings(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $siteSettings = [];

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $siteSettings[$site->id] = new SmartLinkSeoSourceSiteSettings([
                'siteId' => $site->id,
                'hasUrls' => $settings->isSiteEnabled((int) $site->id),
            ]);
        }

        return $siteSettings;
    }

    public function getName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getPluralDisplayName();
    }
}
