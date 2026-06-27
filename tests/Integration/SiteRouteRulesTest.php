<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins SmartLink site route registration for prefixed/root URL modes.
 *
 * @since 5.30.0
 */
final class SiteRouteRulesTest extends TestCase
{
    public function testPrefixedRoutesAreRegisteredWhenPrefixIsEnabled(): void
    {
        $this->withSettings([
            'usePrefix' => true,
            'slugPrefix' => 'go',
            'qrPrefix' => 'go/qr',
        ], function(): void {
            $rules = $this->siteUrlRules();

            self::assertArrayHasKey('smartlink-manager/redirect/go/<slug:[a-zA-Z0-9\\-\\_]+>/<platform:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayHasKey('<siteHandle:[^>]+>/smartlink-manager/redirect/go/<slug:[a-zA-Z0-9\\-\\_]+>/<platform:[a-zA-Z0-9\\-\\_]+>', $this->normalizedSiteHandleRules($rules));
            self::assertArrayHasKey('go/<slug:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayNotHasKey('<slug:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayHasKey('go/qr/<slug:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayHasKey('go/qr/<slug:[a-zA-Z0-9\\-\\_]+>/view', $rules);
        });
    }

    public function testRootRoutesAreRegisteredWhenPrefixIsDisabled(): void
    {
        $this->withSettings([
            'usePrefix' => false,
            'slugPrefix' => 'go',
            'qrPrefix' => 'qr',
        ], function(): void {
            $rules = $this->siteUrlRules();

            self::assertArrayHasKey('smartlink-manager/redirect/go/<slug:[a-zA-Z0-9\\-\\_]+>/<platform:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayHasKey('<siteHandle:[^>]+>/smartlink-manager/redirect/go/<slug:[a-zA-Z0-9\\-\\_]+>/<platform:[a-zA-Z0-9\\-\\_]+>', $this->normalizedSiteHandleRules($rules));
            self::assertArrayHasKey('<slug:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayNotHasKey('go/<slug:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayHasKey('qr/<slug:[a-zA-Z0-9\\-\\_]+>', $rules);
            self::assertArrayHasKey('qr/<slug:[a-zA-Z0-9\\-\\_]+>/view', $rules);
        });
    }

    public function testPluginRoutesRemainAfterExistingSiteRoutes(): void
    {
        $this->withSettings([
            'usePrefix' => false,
            'slugPrefix' => 'go',
            'qrPrefix' => 'qr',
        ], function(): void {
            $eventRules = [
                'api' => 'graphql/api',
                'about' => 'site/about',
            ];
            $merged = array_merge($eventRules, $this->siteUrlRules());
            $keys = array_keys($merged);

            $apiIndex = array_search('api', $keys, true);
            $rootIndex = array_search('<slug:[a-zA-Z0-9\\-\\_]+>', $keys, true);

            self::assertIsInt($apiIndex);
            self::assertIsInt($rootIndex);
            self::assertLessThan($rootIndex, $apiIndex);
            self::assertSame('graphql/api', $merged['api']);
        });
    }

    /** @return array<string, string> */
    private function siteUrlRules(): array
    {
        $method = new \ReflectionMethod(SmartLinkManager::$plugin, 'getSiteUrlRules');
        $method->setAccessible(true);

        /** @var array<string, string> */
        return $method->invoke(SmartLinkManager::$plugin);
    }

    /**
     * @param array<string, string> $rules
     * @return array<string, string>
     */
    private function normalizedSiteHandleRules(array $rules): array
    {
        $normalized = [];
        foreach ($rules as $pattern => $route) {
            $normalized[(string) preg_replace('/<siteHandle:[^>]+>/', '<siteHandle:[^>]+>', $pattern)] = $route;
        }

        return $normalized;
    }
}
