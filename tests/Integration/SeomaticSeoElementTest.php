<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\integrations\seomatic\SeoSmartLink;
use lindemannrock\smartlinkmanager\integrations\seomatic\SmartLinkSeoSource;
use lindemannrock\smartlinkmanager\tests\TestCase;
use nystudio107\seomatic\models\MetaBundle;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.31.0
 */
#[CoversClass(SeoSmartLink::class)]
#[CoversClass(SmartLinkSeoSource::class)]
class SeomaticSeoElementTest extends TestCase
{
    public function testSmartLinkSeoElementMapsSyntheticContentSource(): void
    {
        $source = SeoSmartLink::sourceModelFromHandle(SeoSmartLink::SOURCE_HANDLE);

        $this->assertInstanceOf(SmartLinkSeoSource::class, $source);
        $this->assertSame('smartlink', SeoSmartLink::getMetaBundleType());
        $this->assertSame(['lindemannrock\smartlinkmanager\elements\SmartLink'], SeoSmartLink::getElementClasses());
        $this->assertSame('smartLink', SeoSmartLink::getElementRefHandle());
        $this->assertSame('smartlink-manager', SeoSmartLink::getRequiredPluginHandle());
        $this->assertSame(SeoSmartLink::SOURCE_ID, $source->id);
        $this->assertSame(SeoSmartLink::SOURCE_HANDLE, $source->handle);
        $this->assertSame(SeoSmartLink::SOURCE_TYPE, $source->type);
        $this->assertNotEmpty($source->getSiteSettings());
    }

    public function testMetaBundleConfigUsesSmartLinkSourceAndDisablesSitemaps(): void
    {
        $source = SeoSmartLink::sourceModelFromId(SeoSmartLink::SOURCE_ID);
        $this->assertInstanceOf(SmartLinkSeoSource::class, $source);

        $config = SeoSmartLink::metaBundleConfig($source);

        $this->assertSame('smartlink', $config['sourceBundleType']);
        $this->assertSame(SeoSmartLink::SOURCE_ID, $config['sourceId']);
        $this->assertSame(SeoSmartLink::SOURCE_HANDLE, $config['sourceHandle']);
        $this->assertSame(SeoSmartLink::SOURCE_TYPE, $config['sourceType']);
        $this->assertSame('{{ seomatic.helper.extractTextFromField(smartLink.title) }}', $config['metaGlobalVars']['seoTitle']);
        $this->assertSame('{{ smartLink.url }}', $config['metaGlobalVars']['canonicalUrl']);
        $this->assertSame('all', $config['metaGlobalVars']['robots']);
        $this->assertSame('fromField', $config['metaBundleSettings']['seoTitleSource']);
        $this->assertSame('title', $config['metaBundleSettings']['seoTitleField']);
        $this->assertFalse($config['metaSitemapVars']['sitemapUrls']);
        $this->assertFalse($config['metaSitemapVars']['sitemapAssets']);
    }

    public function testSmartLinkElementMapsBackToSyntheticSource(): void
    {
        $smartLink = $this->seedSmartLink(['postDate' => new \DateTime('-1 hour')]);

        $this->assertSame(SeoSmartLink::SOURCE_ID, SeoSmartLink::sourceIdFromElement($smartLink));
        $this->assertNull(SeoSmartLink::typeIdFromElement($smartLink));
        $this->assertSame(SeoSmartLink::SOURCE_HANDLE, SeoSmartLink::sourceHandleFromElement($smartLink));
        $this->assertStringEndsWith('/' . $smartLink->slug, $smartLink->getUrl());
        $this->assertNotNull(SeoSmartLink::previewUri(SeoSmartLink::SOURCE_HANDLE, $smartLink->siteId));
    }

    public function testSitemapElementsQueryReturnsSmartLinksForContentSeoCount(): void
    {
        $smartLink = $this->seedSmartLink(['postDate' => new \DateTime('-1 hour')]);
        $source = SeoSmartLink::sourceModelFromId(SeoSmartLink::SOURCE_ID);
        $this->assertInstanceOf(SmartLinkSeoSource::class, $source);

        $config = SeoSmartLink::metaBundleConfig($source);
        $config['sourceSiteId'] = $smartLink->siteId;
        $metaBundle = MetaBundle::create($config);

        $this->assertNotNull($metaBundle);
        $this->assertFalse($metaBundle->metaSitemapVars->sitemapUrls);
        $query = SeoSmartLink::sitemapElementsQuery($metaBundle);
        $this->assertGreaterThanOrEqual(1, $query->count());

        $sitemapElement = SeoSmartLink::sitemapElementsQuery($metaBundle)
            ->slug($smartLink->slug)
            ->one();
        $this->assertNotNull($sitemapElement);
        $this->assertSame($smartLink->getUrl(), $sitemapElement->uri);
    }
}
