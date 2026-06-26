<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\gql\resolvers;

use Craft;
use craft\gql\base\Resolver;
use GraphQL\Type\Definition\ResolveInfo;
use lindemannrock\base\helpers\GqlHelper;
use lindemannrock\base\helpers\UrlSafetyHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * GraphQL resolver for SmartLink Manager smart links.
 *
 * @since 5.30.0
 */
class SmartLinkResolver extends Resolver
{
    /**
     * Resolve a smart link slug through SmartLink Manager.
     *
     * @inheritdoc
     */
    public static function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $siteId = self::resolveRequestedSiteId($arguments);
        if ($siteId === null) {
            return null;
        }

        $slug = trim((string)($arguments['slug'] ?? ''));
        if ($slug === '') {
            return null;
        }

        $smartLink = self::findSmartLink($slug, $siteId);
        if ($smartLink === null || !self::isUsable($smartLink)) {
            return null;
        }

        [$destinationUrl, $platform, $clickType] = self::resolveDestination($smartLink, (string)($arguments['platform'] ?? 'auto'));

        if ($destinationUrl === null || $destinationUrl === '') {
            $destinationUrl = $smartLink->fallbackUrl;
        }

        if ($destinationUrl === null || $destinationUrl === '') {
            return null;
        }

        $destinationUrl = UrlSafetyHelper::sanitizeRedirectUrl($destinationUrl);

        if ($smartLink->trackAnalytics && SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            self::trackResolution($smartLink, $destinationUrl, $platform, $clickType, $siteId, 'graphql');
        }

        return self::toArray($smartLink, $destinationUrl, $platform, $clickType);
    }

    /**
     * List enabled smart links for the requested site.
     *
     * @inheritdoc
     */
    public static function resolveAll(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $siteId = self::resolveRequestedSiteId($arguments);
        if ($siteId === null) {
            return [];
        }

        $query = SmartLink::find()
            ->siteId($siteId)
            ->status(null)
            ->orderBy(['elements.dateCreated' => SORT_DESC]);

        $limit = $arguments['limit'] ?? null;
        if (is_numeric($limit) && (int)$limit > 0) {
            $query->limit(min((int)$limit, 500));
        }

        return array_map(
            static fn(SmartLink $smartLink): array => self::toArray($smartLink),
            array_filter($query->all(), static fn(SmartLink $smartLink): bool => self::isUsable($smartLink)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function toArray(
        SmartLink $smartLink,
        ?string $resolvedDestinationUrl = null,
        ?string $resolvedPlatform = null,
        ?string $clickType = null,
    ): array {
        return [
            'id' => $smartLink->id,
            'siteId' => $smartLink->siteId,
            'title' => $smartLink->title,
            'slug' => $smartLink->slug,
            'description' => $smartLink->description,
            'url' => $smartLink->getUrl(),
            'redirectUrl' => $smartLink->getRedirectUrl(),
            'qrCodeUrl' => $smartLink->getQrCodeUrl(),
            'resolvedDestinationUrl' => $resolvedDestinationUrl,
            'resolvedPlatform' => $resolvedPlatform,
            'clickType' => $clickType,
            'fallbackUrl' => $smartLink->fallbackUrl,
            'iosUrl' => $smartLink->iosUrl,
            'androidUrl' => $smartLink->androidUrl,
            'huaweiUrl' => $smartLink->huaweiUrl,
            'amazonUrl' => $smartLink->amazonUrl,
            'windowsUrl' => $smartLink->windowsUrl,
            'macUrl' => $smartLink->macUrl,
            'status' => $smartLink->getStatus(),
            'enabled' => $smartLink->enabled,
            'trackAnalytics' => $smartLink->trackAnalytics,
            'hideTitle' => $smartLink->hideTitle,
            'languageDetection' => $smartLink->languageDetection,
            'hits' => $smartLink->hits,
            'dateExpired' => $smartLink->dateExpired?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Resolve site arguments using the current site as fallback.
     *
     * @param array<string, mixed> $arguments
     * @return int|null
     */
    private static function resolveRequestedSiteId(array $arguments): ?int
    {
        return GqlHelper::resolveSiteId(
            $arguments,
            Craft::$app->getSites()->getCurrentSite()->id,
        );
    }

    private static function findSmartLink(string $slug, int $siteId): ?SmartLink
    {
        $smartLink = SmartLinkManager::$plugin->smartLinks->getSmartLinkBySlug($slug, $siteId);

        return $smartLink ?? SmartLinkManager::$plugin->smartLinks->getSmartLinkBySlug($slug, null);
    }

    private static function isUsable(SmartLink $smartLink): bool
    {
        $settings = SmartLinkManager::$plugin->getSettings();

        if (!$settings->isSiteEnabled($smartLink->siteId)) {
            return false;
        }

        return !in_array($smartLink->getStatus(), [
            SmartLink::STATUS_DISABLED,
            SmartLink::STATUS_PENDING,
            SmartLink::STATUS_EXPIRED,
        ], true);
    }

    /**
     * @return array{0: string|null, 1: string, 2: string}
     */
    private static function resolveDestination(SmartLink $smartLink, string $platform): array
    {
        $platform = strtolower(trim($platform)) ?: 'auto';
        $deviceInfo = SmartLinkManager::$plugin->deviceDetection->detectDevice();
        $language = SmartLinkManager::$plugin->deviceDetection->detectLanguage();

        if ($platform === 'auto') {
            return [
                SmartLinkManager::$plugin->deviceDetection->getRedirectUrl($smartLink, $deviceInfo, $language),
                $deviceInfo->platform ?? 'unknown',
                'redirect',
            ];
        }

        return [
            match ($platform) {
                'ios' => $smartLink->iosUrl,
                'android' => $smartLink->androidUrl,
                'huawei' => $smartLink->huaweiUrl,
                'amazon' => $smartLink->amazonUrl,
                'windows' => $smartLink->windowsUrl,
                'mac' => $smartLink->macUrl,
                default => $smartLink->fallbackUrl,
            },
            $platform,
            'button',
        ];
    }

    private static function trackResolution(
        SmartLink $smartLink,
        string $destinationUrl,
        string $platform,
        string $clickType,
        int $siteId,
        string $source,
    ): void {
        $deviceInfo = SmartLinkManager::$plugin->deviceDetection->detectDevice();
        $language = SmartLinkManager::$plugin->deviceDetection->detectLanguage();

        $normalizedPlatform = match ($platform) {
            'mac' => 'macos',
            'fallback' => 'other',
            default => $platform,
        };

        SmartLinkManager::$plugin->analytics->trackClick(
            $smartLink,
            $deviceInfo,
            [
                'clickType' => $clickType,
                'platform' => $normalizedPlatform,
                'buttonUrl' => $destinationUrl,
                'referrer' => Craft::$app->request->getReferrer(),
                'source' => $source !== '' ? $source : 'graphql',
                'siteId' => $siteId,
                'language' => $language,
            ],
        );
    }
}
