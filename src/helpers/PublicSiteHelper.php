<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\helpers;

use Craft;
use craft\helpers\App;
use craft\models\Site;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Resolves site identifiers used by public SmartLink routes.
 *
 * @since 5.37.4
 */
class PublicSiteHelper
{
    public static function resolveIdentifier(?string $identifier): ?Site
    {
        $identifier = trim((string)$identifier);
        if ($identifier === '') {
            return null;
        }

        $sites = Craft::$app->getSites()->getAllSites(false);

        if (ctype_digit($identifier)) {
            $siteId = (int)$identifier;
            foreach ($sites as $site) {
                if ($site->id === $siteId) {
                    return $site;
                }
            }
        }

        foreach ($sites as $site) {
            if ($site->uid === $identifier) {
                return $site;
            }
        }

        foreach ($sites as $site) {
            if ($site->handle === $identifier) {
                return $site;
            }
        }

        return null;
    }

    public static function resolveConfiguredRequest(): ?Site
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $baseUrl = trim((string)App::parseEnv($settings->smartlinkBaseUrl ?? ''));
        $rawHost = parse_url($baseUrl, PHP_URL_HOST);
        $rawPath = trim((string)parse_url($baseUrl, PHP_URL_PATH), '/');
        if (!is_string($rawHost) || !self::containsSiteToken($baseUrl)) {
            return null;
        }

        $request = Craft::$app->getRequest();
        if (!$request instanceof \yii\web\Request) {
            return null;
        }

        $requestHost = strtolower($request->getHostName());
        $requestPath = trim($request->getPathInfo(), '/');
        foreach (Craft::$app->getSites()->getAllSites(false) as $site) {
            $expandedBaseUrl = $settings->buildPublicUrl('', $site->id);
            $expandedHost = parse_url($expandedBaseUrl, PHP_URL_HOST);
            $expandedPath = trim((string)parse_url($expandedBaseUrl, PHP_URL_PATH), '/');
            $pathMatches = !self::containsSiteToken($rawPath)
                || $requestPath === $expandedPath
                || str_starts_with($requestPath, $expandedPath . '/');

            if (is_string($expandedHost)
                && strtolower($expandedHost) === $requestHost
                && $pathMatches
            ) {
                return $site;
            }
        }

        return null;
    }

    private static function containsSiteToken(string $value): bool
    {
        return str_contains($value, '{siteHandle}')
            || str_contains($value, '{siteId}')
            || str_contains($value, '{siteUid}');
    }
}
