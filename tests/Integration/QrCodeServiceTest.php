<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use lindemannrock\smartlinkmanager\services\QrCodeService;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @since 5.28.0
 */
#[CoversClass(QrCodeService::class)]
class QrCodeServiceTest extends TestCase
{
    public function testGeneratesStyledSvgQrCode(): void
    {
        $qrCode = $this->generateWithoutCache([
            'format' => 'svg',
            'size' => 180,
            'margin' => 2,
            'color' => '1A73E8',
            'bg' => 'FFFFFF',
            'eyeColor' => '111111',
            'moduleStyle' => 'dots',
            'eyeStyle' => 'rounded',
        ]);

        $this->assertStringContainsString('<svg', $qrCode);
        $this->assertStringContainsString('</svg>', $qrCode);
    }

    public function testGeneratesSvgDataUrl(): void
    {
        $dataUrl = $this->generateDataUrlWithoutCache([
            'format' => 'svg',
            'size' => 160,
        ]);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUrl);

        $encoded = substr($dataUrl, strlen('data:image/svg+xml;base64,'));
        $decoded = base64_decode($encoded, true);

        $this->assertIsString($decoded);
        $this->assertStringContainsString('<svg', $decoded);
    }

    public function testGeneratesPngQrCodeWhenImagickIsAvailable(): void
    {
        if (!class_exists(\Imagick::class) || !class_exists(ImagickImageBackEnd::class)) {
            $this->markTestSkipped('Imagick is not available.');
        }

        $qrCode = $this->generateWithoutCache([
            'format' => 'png',
            'size' => 160,
            'margin' => 2,
        ]);

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $qrCode);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function generateWithoutCache(array $options): string
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $originalCacheSetting = $settings->enableQrCodeCache;
        $settings->enableQrCodeCache = false;

        try {
            return SmartLinkManager::$plugin->qrCode->generateQrCode('https://example.com/qr-test', $options);
        } finally {
            $settings->enableQrCodeCache = $originalCacheSetting;
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function generateDataUrlWithoutCache(array $options): string
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $originalCacheSetting = $settings->enableQrCodeCache;
        $settings->enableQrCodeCache = false;

        try {
            return SmartLinkManager::$plugin->qrCode->generateQrCodeDataUrl('https://example.com/qr-test-data-url', $options);
        } finally {
            $settings->enableQrCodeCache = $originalCacheSetting;
        }
    }
}
