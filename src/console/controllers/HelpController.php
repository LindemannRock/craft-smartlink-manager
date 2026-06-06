<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\console\controllers;

use lindemannrock\base\console\controllers\AbstractHelpController;

/**
 * Console help for SmartLink Manager commands.
 *
 * @since 5.29.0
 */
final class HelpController extends AbstractHelpController
{
    /**
     * @inheritdoc
     */
    protected function helpManifest(): array
    {
        return [
            'title' => 'SmartLink Manager',
            'pluginHandle' => 'smartlink-manager',
            'commandPrefixes' => [
                'php craft',
                'ddev craft',
            ],
            'summary' => 'Use these commands to generate the IP hash salt used for privacy-safe SmartLink analytics and add demo QR click data during development.',
            'common' => [
                'security/generate-salt',
                'demo/add-qr-click',
            ],
            'groups' => [
                [
                    'name' => 'security',
                    'label' => 'Security',
                    'description' => 'Generate privacy and analytics secrets.',
                    'commands' => [
                        [
                            'path' => 'security/generate-salt',
                            'summary' => 'Generate the IP hash salt.',
                            'description' => 'Generate a secure SMARTLINK_MANAGER_IP_SALT value and add it to the project .env file when possible.',
                            'examples' => [
                                'smartlink-manager/security/generate-salt',
                            ],
                            'notes' => [
                                'Run this before analytics data starts accumulating.',
                                'Use the same salt across environments if you need unique-visitor analytics continuity.',
                                'Changing an existing salt resets unique-visitor tracking because future hashes will no longer match old analytics rows.',
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'demo',
                    'label' => 'Demo Data',
                    'description' => 'Add development-only analytics samples.',
                    'commands' => [
                        [
                            'path' => 'demo/add-qr-click',
                            'summary' => 'Add a demo QR click.',
                            'description' => 'Create one simulated iPhone QR scan analytics row for a SmartLink. Use this only in development or test environments.',
                            'usageOptions' => '[--id=<smart-link-id>]',
                            'options' => [
                                [
                                    'name' => '--id',
                                    'description' => 'Optional SmartLink ID. Omit to use the first SmartLink found.',
                                ],
                            ],
                            'examples' => [
                                'smartlink-manager/demo/add-qr-click',
                                'smartlink-manager/demo/add-qr-click --id=42',
                            ],
                            'notes' => [
                                'This writes analytics data. Do not run it against production analytics unless you intentionally want a demo row.',
                                'Native Craft help also exposes the backing option as --smart-link-id.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
