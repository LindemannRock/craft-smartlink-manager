<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\GqlEntityRegistry;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use lindemannrock\base\helpers\GqlHelper;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\gql\resolvers\SmartLinkResolver;

/**
 * GraphQL object type for SmartLink Manager smart links.
 *
 * @since 5.30.0
 */
class SmartLinkType extends ObjectType
{
    public static function getType(): Type
    {
        $typeName = self::getName();
        if ($type = GqlEntityRegistry::getEntity($typeName)) {
            return $type;
        }

        return GqlEntityRegistry::createEntity($typeName, new self([
            'name' => $typeName,
            'fields' => self::class . '::getFieldDefinitions',
            'description' => 'A SmartLink Manager smart link.',
        ]));
    }

    public static function getName(): string
    {
        return 'SmartLinkManagerSmartLink';
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function getFieldDefinitions(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int(),
                'description' => 'The smart link ID.',
            ],
            'site' => [
                'name' => 'site',
                'type' => Type::string(),
                'description' => 'The site handle.',
            ],
            'siteId' => [
                'name' => 'siteId',
                'type' => Type::int(),
                'description' => 'The site ID.',
            ],
            'title' => [
                'name' => 'title',
                'type' => Type::string(),
                'description' => 'The smart link title.',
            ],
            'slug' => [
                'name' => 'slug',
                'type' => Type::string(),
                'description' => 'The public smart link slug.',
            ],
            'description' => [
                'name' => 'description',
                'type' => Type::string(),
                'description' => 'The smart link description.',
            ],
            'url' => [
                'name' => 'url',
                'type' => Type::string(),
                'description' => 'The public smart link URL.',
            ],
            'redirectUrl' => [
                'name' => 'redirectUrl',
                'type' => Type::string(),
                'description' => 'The public redirect URL.',
            ],
            'qrCodeUrl' => [
                'name' => 'qrCodeUrl',
                'type' => Type::string(),
                'description' => 'The public QR code URL.',
            ],
            'resolvedDestinationUrl' => [
                'name' => 'resolvedDestinationUrl',
                'type' => Type::string(),
                'description' => 'The destination URL resolved for the request.',
            ],
            'resolvedPlatform' => [
                'name' => 'resolvedPlatform',
                'type' => Type::string(),
                'description' => 'The platform used for the resolved destination.',
            ],
            'clickType' => [
                'name' => 'clickType',
                'type' => Type::string(),
                'description' => 'Whether the resolution was automatic or button-style.',
            ],
            'fallbackUrl' => [
                'name' => 'fallbackUrl',
                'type' => Type::string(),
                'description' => 'The fallback destination URL.',
            ],
            'iosUrl' => [
                'name' => 'iosUrl',
                'type' => Type::string(),
                'description' => 'The iOS destination URL.',
            ],
            'androidUrl' => [
                'name' => 'androidUrl',
                'type' => Type::string(),
                'description' => 'The Android destination URL.',
            ],
            'huaweiUrl' => [
                'name' => 'huaweiUrl',
                'type' => Type::string(),
                'description' => 'The Huawei destination URL.',
            ],
            'amazonUrl' => [
                'name' => 'amazonUrl',
                'type' => Type::string(),
                'description' => 'The Amazon destination URL.',
            ],
            'windowsUrl' => [
                'name' => 'windowsUrl',
                'type' => Type::string(),
                'description' => 'The Windows destination URL.',
            ],
            'macUrl' => [
                'name' => 'macUrl',
                'type' => Type::string(),
                'description' => 'The macOS destination URL.',
            ],
            'status' => [
                'name' => 'status',
                'type' => Type::string(),
                'description' => 'The smart link status.',
            ],
            'enabled' => [
                'name' => 'enabled',
                'type' => Type::boolean(),
                'description' => 'Whether the smart link is enabled for the site.',
            ],
            'trackAnalytics' => [
                'name' => 'trackAnalytics',
                'type' => Type::boolean(),
                'description' => 'Whether analytics tracking is enabled for the smart link.',
            ],
            'hideTitle' => [
                'name' => 'hideTitle',
                'type' => Type::boolean(),
                'description' => 'Whether the public redirect page hides the title.',
            ],
            'hits' => [
                'name' => 'hits',
                'type' => Type::int(),
                'description' => 'The number of tracked smart link hits.',
            ],
            'dateExpired' => [
                'name' => 'dateExpired',
                'type' => Type::string(),
                'description' => 'The expiry datetime.',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function resolve(mixed $source, array $arguments, mixed $context, ResolveInfo $resolveInfo): mixed
    {
        $fieldName = $resolveInfo->fieldName;

        if ($source instanceof SmartLink) {
            $source = SmartLinkResolver::toArray($source);
        }

        if (is_array($source)) {
            if ($fieldName === 'site') {
                return GqlHelper::siteHandle(isset($source['siteId']) ? (int)$source['siteId'] : null);
            }

            return GqlHelper::nullIfEmptyString($source[$fieldName] ?? null);
        }

        return parent::resolve($source, $arguments, $context, $resolveInfo);
    }
}
