<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins SmartLink slug normalization and duplicate handling.
 *
 * @since 5.29.0
 */
final class SlugUniquenessTest extends TestCase
{
    public function testNewDuplicateSlugAutoSuffixes(): void
    {
        $this->seedSmartLink(['slug' => self::MARKER . 'promo']);

        $smartLink = $this->makeSmartLink('Promo', self::MARKER . 'promo');

        self::assertTrue($this->smartLinks->saveSmartLink($smartLink), implode(', ', $smartLink->getFirstErrors()));
        self::assertSame(self::MARKER . 'promo-1', $smartLink->slug);
    }

    public function testExistingDuplicateSlugRejects(): void
    {
        $this->seedSmartLink(['slug' => self::MARKER . 'promo-one']);
        $smartLink = $this->seedSmartLink(['slug' => self::MARKER . 'promo-two']);

        $smartLink->slug = self::MARKER . 'promo-one';

        self::assertFalse($this->smartLinks->saveSmartLink($smartLink));
        self::assertNotEmpty($smartLink->getErrors('slug'));
    }

    public function testSlugNormalizesToKebabSlug(): void
    {
        $smartLink = $this->makeSmartLink('Promo', 'SmartLink Test Mixed Case');

        self::assertTrue($this->smartLinks->saveSmartLink($smartLink), implode(', ', $smartLink->getFirstErrors()));
        self::assertSame('smartlink-test-mixed-case', $smartLink->slug);
    }

    public function testDuplicateLoopThrowsInsteadOfReusingCollidingHundredthCandidate(): void
    {
        $base = self::MARKER . 'full';
        for ($suffix = 0; $suffix <= 100; $suffix++) {
            $slug = $suffix === 0 ? $base : $base . '-' . $suffix;
            $this->seedSmartLink(['slug' => $slug]);
        }

        $smartLink = $this->makeSmartLink('Full', $base);

        $this->expectException(\RuntimeException::class);
        $this->smartLinks->saveSmartLink($smartLink);
    }

    private function makeSmartLink(string $title, string $slug): SmartLink
    {
        $smartLink = new SmartLink();
        $smartLink->title = $title;
        $smartLink->slug = $slug;
        $smartLink->fallbackUrl = 'https://example.com/fallback';
        $smartLink->trackAnalytics = true;
        $smartLink->siteId = \Craft::$app->getSites()->getPrimarySite()->id;
        $smartLink->setEnabledForSite(true);

        return $smartLink;
    }
}
