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
use craft\base\ElementInterface;
use craft\base\Model;
use craft\elements\db\ElementQueryInterface;
use craft\events\DefineHtmlEvent;
use craft\models\FieldLayout;
use craft\models\Site;
use DateTime;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\integrations\SeomaticIntegration;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use nystudio107\seomatic\assetbundles\seomatic\SeomaticAsset;
use nystudio107\seomatic\base\SeoElementInterface;
use nystudio107\seomatic\helpers\ArrayHelper;
use nystudio107\seomatic\helpers\Config as ConfigHelper;
use nystudio107\seomatic\helpers\PluginTemplate;
use nystudio107\seomatic\models\MetaBundle;
use nystudio107\seomatic\Seomatic;
use yii\base\Event;

/**
 * SEOmatic adapter for SmartLink elements.
 *
 * SmartLinks are route-backed elements without native Craft URIs. This adapter
 * exposes a synthetic content source and primes SEOmatic against the matched
 * SmartLink in rendered redirect/QR flows.
 *
 * @since 5.31.0
 */
class SeoSmartLink implements SeoElementInterface
{
    public const META_BUNDLE_TYPE = 'smartlink';
    public const SOURCE_ID = 1;
    public const SOURCE_HANDLE = 'smartlinks';
    public const SOURCE_TYPE = 'smartlinks';
    public const CONFIG_FILE_PATH = 'entrymeta/Bundle';

    public static function getMetaBundleType(): string
    {
        return self::META_BUNDLE_TYPE;
    }

    public static function getElementClasses(): array
    {
        return [SmartLink::class];
    }

    public static function getElementRefHandle(): string
    {
        return SmartLink::refHandle() ?? 'smartLink';
    }

    public static function getRequiredPluginHandle(): ?string
    {
        return 'smartlink-manager';
    }

    public static function installEventHandlers(): void
    {
        Event::on(
            SmartLink::class,
            SmartLink::EVENT_DEFINE_SIDEBAR_HTML,
            static function(DefineHtmlEvent $event): void {
                /** @var SmartLink|null $smartLink */
                $smartLink = $event->sender ?? null;

                if (!$smartLink instanceof SmartLink || $smartLink->id === null) {
                    return;
                }

                $seomatic = SmartLinkManager::$plugin->integration->getIntegration('seomatic');

                if (!$seomatic instanceof SeomaticIntegration || !$seomatic->isAvailable() || !$seomatic->isEnabled()) {
                    return;
                }

                Seomatic::$view->registerAssetBundle(SeomaticAsset::class);
                Seomatic::setMatchedElement($smartLink);
                Seomatic::$plugin->metaContainers->previewMetaContainers(
                    self::relativeUri($smartLink) ?? '',
                    $smartLink->siteId,
                    true,
                    true,
                    $smartLink
                );

                if (Seomatic::$settings->displayPreviewSidebar && Seomatic::$matchedElement) {
                    $event->html .= PluginTemplate::renderPluginTemplate('_sidebars/entry-preview.twig');
                }
            }
        );
    }

    public static function sitemapElementsQuery(MetaBundle $metaBundle): ElementQueryInterface
    {
        return SmartLink::find()
            ->siteId($metaBundle->sourceSiteId)
            ->withSyntheticUris()
            ->limit($metaBundle->metaSitemapVars->sitemapLimit);
    }

    public static function sitemapAltElement(
        MetaBundle $metaBundle,
        int $elementId,
        int $siteId,
    ): ?ElementInterface {
        return SmartLink::find()
            ->id($elementId)
            ->siteId($siteId)
            ->limit(1)
            ->one();
    }

    public static function previewUri(string $sourceHandle, $siteId, $typeId = null): ?string
    {
        if ($sourceHandle !== self::SOURCE_HANDLE) {
            return null;
        }

        $element = SmartLink::find()
            ->siteId($siteId)
            ->limit(1)
            ->one();

        return $element instanceof SmartLink ? self::relativeUri($element) : null;
    }

    /**
     * @return FieldLayout[]
     */
    public static function fieldLayouts(string $sourceHandle, $typeId = null): array
    {
        if ($sourceHandle !== self::SOURCE_HANDLE) {
            return [];
        }

        $layout = Craft::$app->getFields()->getLayoutByType(SmartLink::class);

        return $layout instanceof FieldLayout ? [$layout] : [];
    }

    public static function typeMenuFromHandle(string $sourceHandle): array
    {
        return [];
    }

    public static function sourceModelFromId(int $sourceId): ?SmartLinkSeoSource
    {
        return $sourceId === self::SOURCE_ID ? self::sourceModel() : null;
    }

    public static function sourceModelFromHandle(string $sourceHandle): ?SmartLinkSeoSource
    {
        return $sourceHandle === self::SOURCE_HANDLE ? self::sourceModel() : null;
    }

    public static function mostRecentElement(Model $sourceModel, int $sourceSiteId): ?ElementInterface
    {
        return SmartLink::find()
            ->siteId($sourceSiteId)
            ->limit(1)
            ->orderBy(['elements.dateUpdated' => SORT_DESC])
            ->one();
    }

    public static function configFilePath(): string
    {
        return self::CONFIG_FILE_PATH;
    }

    public static function metaBundleConfig(Model $sourceModel): array
    {
        /** @var SmartLinkSeoSource $sourceModel */
        return ArrayHelper::merge(
            ConfigHelper::getConfigFromFile(self::configFilePath()),
            [
                'sourceBundleType' => self::getMetaBundleType(),
                'sourceId' => $sourceModel->id,
                'sourceName' => $sourceModel->getName(),
                'sourceHandle' => $sourceModel->handle,
                'sourceType' => $sourceModel->type,
                'sourceDateUpdated' => new DateTime(),
                'metaGlobalVars' => [
                    'seoTitle' => '{{ seomatic.helper.extractTextFromField(smartLink.title) }}',
                    'canonicalUrl' => '{{ smartLink.url }}',
                    'robots' => 'all',
                ],
                'metaBundleSettings' => [
                    'seoTitleSource' => 'fromField',
                    'seoTitleField' => 'title',
                ],
                'metaSitemapVars' => [
                    'sitemapUrls' => false,
                    'sitemapAssets' => false,
                    'sitemapFiles' => false,
                    'sitemapAltLinks' => false,
                ],
            ]
        );
    }

    public static function sourceIdFromElement(ElementInterface $element): ?int
    {
        return $element instanceof SmartLink ? self::SOURCE_ID : null;
    }

    public static function typeIdFromElement(ElementInterface $element): ?int
    {
        return null;
    }

    public static function sourceHandleFromElement(ElementInterface $element): ?string
    {
        return $element instanceof SmartLink ? self::SOURCE_HANDLE : null;
    }

    public static function createContentMetaBundle(Model $sourceModel): void
    {
        /** @var Site $site */
        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            Seomatic::$plugin->metaBundles->createMetaBundleFromSeoElement(
                self::class,
                $sourceModel,
                $site->id,
                null,
                true
            );
        }
    }

    public static function createAllContentMetaBundles(): void
    {
        self::createContentMetaBundle(self::sourceModel());
    }

    private static function sourceModel(): SmartLinkSeoSource
    {
        return new SmartLinkSeoSource();
    }

    private static function relativeUri(SmartLink $element): ?string
    {
        $url = $element->getUrl();
        if ($url === null) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);

        if (!is_string($path)) {
            return null;
        }

        return trim($path, '/');
    }
}
