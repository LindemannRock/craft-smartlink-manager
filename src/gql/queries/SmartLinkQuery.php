<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\gql\queries;

use craft\gql\base\Query;
use GraphQL\Type\Definition\Type;
use lindemannrock\base\helpers\GqlHelper;
use lindemannrock\smartlinkmanager\gql\resolvers\SmartLinkResolver;
use lindemannrock\smartlinkmanager\gql\types\SmartLinkType;

/**
 * GraphQL queries for SmartLink Manager.
 *
 * @since 5.30.0
 */
class SmartLinkQuery extends Query
{
    /**
     * @inheritdoc
     */
    public static function getQueries(bool $checkToken = true): array
    {
        if ($checkToken && !GqlHelper::canQuery('smartlinkManager.all')) {
            return [];
        }

        return [
            'smartlinkManagerResolveSmartLink' => [
                'type' => SmartLinkType::getType(),
                'args' => [
                    'slug' => [
                        'name' => 'slug',
                        'type' => Type::nonNull(Type::string()),
                        'description' => 'The smart link slug to resolve.',
                    ],
                    'site' => [
                        'name' => 'site',
                        'type' => Type::string(),
                        'description' => 'The site handle to resolve against.',
                    ],
                    'siteId' => [
                        'name' => 'siteId',
                        'type' => Type::int(),
                        'description' => 'The site ID to resolve against.',
                    ],
                    'platform' => [
                        'name' => 'platform',
                        'type' => Type::string(),
                        'description' => 'The platform to resolve for. Defaults to auto detection.',
                    ],
                    'source' => [
                        'name' => 'source',
                        'type' => Type::string(),
                        'description' => 'Optional analytics source label. Defaults to graphql.',
                    ],
                ],
                'resolve' => SmartLinkResolver::class . '::resolve',
                'description' => 'Resolves a smart link and records hits/analytics like a real smart link request.',
            ],
            'smartlinkManagerSmartLinks' => [
                'type' => Type::listOf(SmartLinkType::getType()),
                'args' => [
                    'site' => [
                        'name' => 'site',
                        'type' => Type::string(),
                        'description' => 'The site handle to list smart links for.',
                    ],
                    'siteId' => [
                        'name' => 'siteId',
                        'type' => Type::int(),
                        'description' => 'The site ID to list smart links for.',
                    ],
                    'limit' => [
                        'name' => 'limit',
                        'type' => Type::int(),
                        'description' => 'The maximum number of smart links to return.',
                    ],
                ],
                'resolve' => SmartLinkResolver::class . '::resolveAll',
                'description' => 'Lists enabled smart links for a site. This query is read-only.',
            ],
        ];
    }
}
