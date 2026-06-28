<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\integrations\seomatic;

use craft\base\Model;

/**
 * Minimal SEOmatic site settings model for the synthetic SmartLinks source.
 *
 * @since 5.31.0
 */
class SmartLinkSeoSourceSiteSettings extends Model
{
    public int $siteId;
    public bool $hasUrls = true;
    public string $template = '';
}
