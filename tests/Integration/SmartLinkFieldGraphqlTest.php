<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use Craft;
use craft\base\Element;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\fields\SmartLinkField;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins SmartLink field GraphQL output for entries/elements.
 *
 * @since 5.30.0
 */
final class SmartLinkFieldGraphqlTest extends TestCase
{
    public function testFieldGraphqlResolvesSingleSmartLinkObject(): void
    {
        $smartLink = $this->seedSmartLink([
            'title' => 'Field GraphQL SmartLink',
            'slug' => str_replace('_', '-', $this->nextTestMarker(self::MARKER, 'field-one')),
            'fallbackUrl' => 'https://example.com/field-one',
        ]);
        $field = new SmartLinkField([
            'handle' => 'smartLink',
            'allowMultiple' => false,
        ]);
        $element = $this->fieldElement([$smartLink->id]);

        $definition = $field->getContentGqlType();
        self::assertIsArray($definition);
        self::assertSame('smartLink', $definition['name']);
        self::assertSame('SmartLinkManagerSmartLink', $definition['type']->name);
        self::assertIsCallable($definition['resolve']);

        $resolved = $definition['resolve']($element);

        self::assertIsArray($resolved);
        self::assertSame($smartLink->id, $resolved['id']);
        self::assertSame($smartLink->slug, $resolved['slug']);
        self::assertSame('https://example.com/field-one', $resolved['fallbackUrl']);
        self::assertArrayHasKey('url', $resolved);
        self::assertArrayHasKey('qrCodeUrl', $resolved);
    }

    public function testFieldGraphqlResolvesMultipleSmartLinkObjects(): void
    {
        $first = $this->seedSmartLink([
            'slug' => str_replace('_', '-', $this->nextTestMarker(self::MARKER, 'field-first')),
            'fallbackUrl' => 'https://example.com/field-first',
        ]);
        $second = $this->seedSmartLink([
            'slug' => str_replace('_', '-', $this->nextTestMarker(self::MARKER, 'field-second')),
            'fallbackUrl' => 'https://example.com/field-second',
        ]);
        $field = new SmartLinkField([
            'handle' => 'smartLinks',
            'allowMultiple' => true,
        ]);
        $element = $this->fieldElement([$first->id, $second->id]);

        $definition = $field->getContentGqlType();
        self::assertIsArray($definition);
        self::assertSame('smartLinks', $definition['name']);
        self::assertSame('SmartLinkManagerSmartLink', $definition['type']->getWrappedType()->name);

        $resolved = $definition['resolve']($element);

        self::assertIsArray($resolved);
        self::assertCount(2, $resolved);
        $ids = array_column($resolved, 'id');
        sort($ids);
        $expectedIds = [$first->id, $second->id];
        sort($expectedIds);
        self::assertSame($expectedIds, $ids);
    }

    /**
     * @param array<int|null> $ids
     */
    private function fieldElement(array $ids): SmartLinkFieldGraphqlTestElement
    {
        $element = new SmartLinkFieldGraphqlTestElement();
        $element->id = 123456;
        $element->siteId = Craft::$app->getSites()->getPrimarySite()->id;
        $element->selectedSmartLinkIds = array_values(array_filter($ids));

        return $element;
    }
}

/**
 * Minimal saved-element stand-in for exercising SmartLinkField GraphQL output.
 *
 * @internal
 */
final class SmartLinkFieldGraphqlTestElement extends Element
{
    /**
     * @var array<int>
     */
    public array $selectedSmartLinkIds = [];

    public static function displayName(): string
    {
        return 'SmartLink field GraphQL test element';
    }

    public function getFieldValue(string $fieldHandle): mixed
    {
        return SmartLink::find()
            ->id($this->selectedSmartLinkIds)
            ->siteId($this->siteId)
            ->status(null);
    }
}
