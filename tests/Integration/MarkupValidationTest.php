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
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * The element must reject dangerous HTML/script markup in title/description on
 * validation, so the CP add/edit form is guarded the same as CSV import.
 *
 * @since 5.29.3
 */
#[CoversClass(SmartLink::class)]
final class MarkupValidationTest extends TestCase
{
    public function testRejectsMarkupInTitle(): void
    {
        $element = new SmartLink();
        $element->title = '<script>alert(1)</script>';

        self::assertFalse($element->validate(['title']));
        self::assertTrue($element->hasErrors('title'));
    }

    public function testRejectsMarkupInDescription(): void
    {
        $element = new SmartLink();
        $element->description = '<img src=x onerror="alert(1)">';

        self::assertFalse($element->validate(['description']));
        self::assertTrue($element->hasErrors('description'));
    }

    public function testAllowsCleanText(): void
    {
        $element = new SmartLink();
        $element->title = 'Summer Sale 2026';
        $element->description = 'Big discounts on everything';

        self::assertTrue($element->validate(['title']));
        self::assertTrue($element->validate(['description']));
        self::assertFalse($element->hasErrors('title'));
        self::assertFalse($element->hasErrors('description'));
    }

    public function testAllowsLoneAngleBracketInText(): void
    {
        // Precise denylist must not trip on a benign `<` — this is why we reject
        // dangerous patterns rather than stripping tags.
        $element = new SmartLink();
        $element->title = 'price < $5 today';

        self::assertTrue($element->validate(['title']));
        self::assertFalse($element->hasErrors('title'));
    }
}
