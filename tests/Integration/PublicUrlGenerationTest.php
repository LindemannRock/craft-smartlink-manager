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
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins public SmartLink and QR URL generation for prefix and custom-domain
 * setups.
 *
 * @since 5.30.0
 */
final class PublicUrlGenerationTest extends TestCase
{
    public function testRedirectUrlUsesConfiguredPrefixByDefault(): void
    {
        $link = $this->seedSmartLink(['slug' => 'smartlink-test-url-prefix']);

        $this->withSettings([
            'smartlinkBaseUrl' => null,
            'usePrefix' => true,
            'slugPrefix' => 'go',
        ], function() use ($link): void {
            self::assertSame('/go/smartlink-test-url-prefix', (string) parse_url($link->getRedirectUrl(), PHP_URL_PATH));
        });
    }

    public function testRedirectUrlCanOmitPrefix(): void
    {
        $link = $this->seedSmartLink(['slug' => 'smartlink-test-url-root']);

        $this->withSettings([
            'smartlinkBaseUrl' => null,
            'usePrefix' => false,
            'slugPrefix' => 'go',
        ], function() use ($link): void {
            self::assertSame('/smartlink-test-url-root', (string) parse_url($link->getRedirectUrl(), PHP_URL_PATH));
        });
    }

    public function testRedirectUrlUsesCustomDomainWithAndWithoutPrefix(): void
    {
        $link = $this->seedSmartLink(['slug' => 'smartlink-test-custom-domain']);

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://smart.example',
            'usePrefix' => true,
            'slugPrefix' => 'go',
        ], function() use ($link): void {
            self::assertSame('https://smart.example/go/smartlink-test-custom-domain', $link->getRedirectUrl());
        });

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://smart.example',
            'usePrefix' => false,
            'slugPrefix' => 'go',
        ], function() use ($link): void {
            self::assertSame('https://smart.example/smartlink-test-custom-domain', $link->getRedirectUrl());
        });
    }

    public function testCustomDomainExpandsSiteTokens(): void
    {
        $site = Craft::$app->getSites()->getPrimarySite();
        $link = $this->seedSmartLink([
            'slug' => 'smartlink-test-token-domain',
            'siteId' => $site->id,
        ]);

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://smart.example/{siteHandle}/{siteId}/{siteUid}',
            'usePrefix' => true,
            'slugPrefix' => 'go',
        ], function() use ($link, $site): void {
            self::assertSame(
                "https://smart.example/{$site->handle}/{$site->id}/{$site->uid}/go/smartlink-test-token-domain",
                $link->getRedirectUrl(),
            );
        });
    }

    public function testQrUrlsUsePublicCustomDomainAndDownloadParameter(): void
    {
        $link = $this->seedSmartLink(['slug' => 'smartlink-test-qr-domain']);

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://smart.example',
            'qrPrefix' => 'go/qr',
        ], function() use ($link): void {
            $imageUrl = $link->getQrCodeUrl();
            self::assertStringStartsWith('https://smart.example/go/qr/smartlink-test-qr-domain?', $imageUrl);
            self::assertStringContainsString('format=', $imageUrl);

            $downloadUrl = $link->getQrCodeUrl(['format' => 'png', 'size' => 512, 'download' => 1]);
            self::assertStringStartsWith('https://smart.example/go/qr/smartlink-test-qr-domain?', $downloadUrl);
            self::assertStringContainsString('download=1', $downloadUrl);
            self::assertStringNotContainsString('/actions/', $downloadUrl);

            $displayUrl = $link->getQrCodeDisplayUrl();
            self::assertStringStartsWith('https://smart.example/go/qr/smartlink-test-qr-domain/view?', $displayUrl);
        });
    }

    public function testQrUrlsSupportStandaloneQrPrefix(): void
    {
        $link = $this->seedSmartLink(['slug' => 'smartlink-test-qr-standalone']);

        $this->withSettings([
            'smartlinkBaseUrl' => 'https://smart.example',
            'qrPrefix' => 'qr',
        ], function() use ($link): void {
            self::assertStringStartsWith('https://smart.example/qr/smartlink-test-qr-standalone?', $link->getQrCodeUrl());
            self::assertStringStartsWith('https://smart.example/qr/smartlink-test-qr-standalone/view?', $link->getQrCodeDisplayUrl());
        });
    }
}
