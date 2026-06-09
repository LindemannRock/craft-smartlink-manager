<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\controllers\ImportExportController;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Import URL validation must accept only absolute http(s) URLs for the store and
 * fallback fields — matching the CP form's UrlValidator — and reject every other
 * scheme (mailto:, ftp:, custom app schemes, and executable schemes).
 *
 * @since 5.29.3
 */
#[CoversClass(ImportExportController::class)]
final class ImportUrlValidationTest extends TestCase
{
    private function isValidUrl(string $value): bool
    {
        $controller = new ImportExportController('import-export', SmartLinkManager::$plugin);
        $method = new \ReflectionMethod($controller, 'isValidUrl');

        return (bool) $method->invoke($controller, $value);
    }

    public function testRejectsExecutableSchemes(): void
    {
        self::assertFalse($this->isValidUrl('javascript:alert(1)'));
        self::assertFalse($this->isValidUrl('javascript://%0aalert(1)'));
        self::assertFalse($this->isValidUrl('data:text/html,<script>alert(1)</script>'));
        self::assertFalse($this->isValidUrl('vbscript:msgbox(1)'));
        self::assertFalse($this->isValidUrl('file:///etc/passwd'));
        // Obfuscated variants the browser would still resolve to javascript:.
        self::assertFalse($this->isValidUrl('  javascript:alert(1)'));
        self::assertFalse($this->isValidUrl("java\tscript:alert(1)"));
    }

    public function testAcceptsHttpStoreUrls(): void
    {
        self::assertTrue($this->isValidUrl('https://apps.apple.com/app/id000000000'));
        self::assertTrue($this->isValidUrl('https://play.google.com/store/apps/details?id=com.example'));
        self::assertTrue($this->isValidUrl('http://example.com/path'));
    }

    public function testRejectsNonHttpSchemes(): void
    {
        // The store/fallback fields are http(s) only — custom app schemes and
        // contact/file schemes are not valid store URLs.
        self::assertFalse($this->isValidUrl('myapp://open/profile'));
        self::assertFalse($this->isValidUrl('fb://profile/33138223345'));
        self::assertFalse($this->isValidUrl('mailto:test@example.com'));
        self::assertFalse($this->isValidUrl('ftp://example.com/file'));
        self::assertFalse($this->isValidUrl('tel:+15551234567'));
        // Bare domain (no scheme) is also rejected.
        self::assertFalse($this->isValidUrl('example.com'));
    }

    public function testRejectsEmptyValue(): void
    {
        self::assertFalse($this->isValidUrl(''));
    }
}
