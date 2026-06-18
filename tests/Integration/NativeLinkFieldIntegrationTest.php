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
use lindemannrock\smartlinkmanager\integrations\SmartLinkType;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins the Craft native Link field integration for SmartLink elements.
 *
 * @since 5.30.0
 */
final class NativeLinkFieldIntegrationTest extends TestCase
{
    public function testNativeLinkTypeFormatsAndResolvesSmartLinkValue(): void
    {
        $smartLink = $this->seedSmartLink([
            'title' => 'Native Link SmartLink',
            'slug' => str_replace('_', '-', $this->nextTestMarker(self::MARKER, 'native-link')),
            'fallbackUrl' => 'https://example.com/native-link',
        ]);
        $type = new SmartLinkType();

        $expectedValue = sprintf('{smartLink:%s@%s:url}', $smartLink->id, $smartLink->siteId);

        self::assertSame($expectedValue, $type->value($smartLink));
        self::assertSame($expectedValue, $type->normalizeValue($smartLink));

        $resolved = $type->element($expectedValue);
        self::assertInstanceOf(SmartLink::class, $resolved);
        self::assertSame($smartLink->id, $resolved->id);

        self::assertSame($resolved->getUrl(), $type->renderValue($expectedValue));
        self::assertSame($resolved->title, $type->linkLabel($expectedValue));
        self::assertFalse($type->isValueEmpty($expectedValue));
    }

    public function testNativeLinkTypeValidationRejectsMalformedValues(): void
    {
        $type = new SmartLinkType();
        $error = null;

        self::assertFalse($type->validateValue('not-a-smartlink-token', $error));
        self::assertIsString($error);
        self::assertStringContainsString('Invalid', $error);
        self::assertTrue($type->isValueEmpty('not-a-smartlink-token'));
    }

    public function testNativeLinkTypeGraphqlFieldIdMatchesSmartLinkRefHandle(): void
    {
        self::assertSame('smartLink', SmartLink::refHandle());
        self::assertSame(SmartLink::refHandle(), SmartLinkType::id());
        self::assertSame('SmartLinkManagerSmartLink', SmartLinkType::elementGqlType()->name);
    }
}
