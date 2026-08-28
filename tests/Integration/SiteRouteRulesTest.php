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
use craft\web\Request;
use lindemannrock\smartlinkmanager\helpers\PublicSiteHelper;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use yii\web\UrlManager;

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

    public function testGeneratedSiteIdentifiersResolveThroughCraftSiteRoutes(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);

        foreach ([
            '{siteHandle}' => $site->handle,
            '{siteId}' => (string)$site->id,
            '{siteUid}' => $site->uid,
        ] as $token => $identifier) {
            $this->withSettings([
                'smartlinkBaseUrl' => "https://smart.example/{$token}",
                'usePrefix' => true,
                'slugPrefix' => 'go',
                'qrPrefix' => 'go/qr',
            ], function() use ($identifier, $link): void {
                self::assertSame(
                    ['smartlink-manager/redirect/index', ['siteHandle' => $identifier, 'slug' => $link->slug]],
                    $this->parseSitePath("{$identifier}/go/{$link->slug}"),
                );
                self::assertSame(
                    ['smartlink-manager/qr-code/generate', ['siteHandle' => $identifier, 'slug' => $link->slug]],
                    $this->parseSitePath("{$identifier}/go/qr/{$link->slug}"),
                );
                self::assertSame(
                    ['smartlink-manager/qr-code/display', ['siteHandle' => $identifier, 'slug' => $link->slug]],
                    $this->parseSitePath("{$identifier}/go/qr/{$link->slug}/view"),
                );
            });
        }
    }

    public function testGeneratedSiteIdentifiersResolveInRootAndCustomPathModes(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);

        foreach (['{siteHandle}' => $site->handle, '{siteId}' => (string)$site->id, '{siteUid}' => $site->uid] as $token => $identifier) {
            foreach ([
                ['baseUrl' => "https://smart.example/{$token}", 'usePrefix' => false, 'redirectPath' => "{$identifier}/{$link->slug}"],
                ['baseUrl' => "https://smart.example/public/{$token}", 'usePrefix' => true, 'redirectPath' => "public/{$identifier}/go/{$link->slug}"],
            ] as $mode) {
                $this->withSettings([
                    'smartlinkBaseUrl' => $mode['baseUrl'],
                    'usePrefix' => $mode['usePrefix'],
                    'slugPrefix' => 'go',
                    'qrPrefix' => 'qr',
                ], function() use ($identifier, $link, $mode): void {
                    self::assertSame(
                        ['smartlink-manager/redirect/index', ['siteHandle' => $identifier, 'slug' => $link->slug]],
                        $this->parseSitePath($mode['redirectPath']),
                    );

                    $qrBase = $mode['usePrefix'] ? "public/{$identifier}/qr" : "{$identifier}/qr";
                    self::assertSame(
                        ['smartlink-manager/qr-code/generate', ['siteHandle' => $identifier, 'slug' => $link->slug]],
                        $this->parseSitePath("{$qrBase}/{$link->slug}"),
                    );
                    self::assertSame(
                        ['smartlink-manager/qr-code/display', ['siteHandle' => $identifier, 'slug' => $link->slug]],
                        $this->parseSitePath("{$qrBase}/{$link->slug}/view"),
                    );
                });
            }
        }
    }

    public function testUnknownSiteIdentifiersDoNotResolveToPluginRoutes(): void
    {
        $link = $this->seedSmartLink();
        $this->withSettings([
            'usePrefix' => true,
            'slugPrefix' => 'go',
            'qrPrefix' => 'qr',
        ], function() use ($link): void {
            foreach (['missing-site', '999999999', '00000000-0000-4000-8000-000000000000'] as $identifier) {
                $parsed = $this->parseSitePath("{$identifier}/go/{$link->slug}");
                self::assertNotSame('smartlink-manager/redirect/index', $parsed[0] ?? null);

                $parsed = $this->parseSitePath("{$identifier}/qr/{$link->slug}");
                self::assertNotSame('smartlink-manager/qr-code/generate', $parsed[0] ?? null);
            }
        });
    }

    public function testCombinedCustomHostAndPathTokensResolveOneConsistentSite(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink(['siteId' => $site->id]);

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://{siteHandle}.smart.example/public/{siteUid}',
            'usePrefix' => true,
            'slugPrefix' => 'go',
            'qrPrefix' => 'qr',
        ], function() use ($link, $site): void {
            self::assertSame(
                ['smartlink-manager/redirect/index', ['slug' => $link->slug]],
                $this->parseSitePath("public/{$site->uid}/go/{$link->slug}"),
            );
            self::assertSame(
                ['smartlink-manager/qr-code/generate', ['slug' => $link->slug]],
                $this->parseSitePath("public/{$site->uid}/qr/{$link->slug}"),
            );
            self::assertSame(
                ['smartlink-manager/qr-code/display', ['slug' => $link->slug]],
                $this->parseSitePath("public/{$site->uid}/qr/{$link->slug}/view"),
            );

            $originalRequest = Craft::$app->getRequest();
            try {
                Craft::$app->set('request', new SiteRouteRequest(
                    "public/{$site->uid}/go/{$link->slug}",
                    $site->handle . '.smart.example',
                ));
                self::assertSame($site->id, PublicSiteHelper::resolveConfiguredRequest()?->id);
            } finally {
                Craft::$app->set('request', $originalRequest);
            }
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

    /** @return array{0: string, 1: array<string, string>}|false */
    private function parseSitePath(string $path): array|false
    {
        $request = new SiteRouteRequest($path);

        $manager = new UrlManager([
            'enablePrettyUrl' => true,
            'rules' => $this->siteUrlRules(),
        ]);
        $parsed = $manager->parseRequest($request);
        if ($parsed === false) {
            return false;
        }

        return [$parsed[0], array_map('strval', $parsed[1])];
    }
}

final class SiteRouteRequest extends Request
{
    public function __construct(
        private readonly string $fixturePath,
        private readonly string $fixtureHost = 'smart.example',
    ) {
        parent::__construct();
    }

    public function getPathInfo(bool $returnRealPathInfo = false): string
    {
        return $this->fixturePath;
    }

    public function getHostName(): string
    {
        return $this->fixtureHost;
    }
}
