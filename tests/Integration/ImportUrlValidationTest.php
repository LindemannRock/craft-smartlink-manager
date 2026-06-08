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
 * Import URL validation must reject executable schemes while still accepting
 * the custom app deep links that smart links legitimately route to.
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

    public function testAcceptsHttpAndAppDeepLinks(): void
    {
        self::assertTrue($this->isValidUrl('https://example.com'));
        self::assertTrue($this->isValidUrl('http://example.com/path'));
        // Custom app deep links are the whole point of the platform URL fields.
        self::assertTrue($this->isValidUrl('myapp://open/profile'));
        self::assertTrue($this->isValidUrl('fb://profile/33138223345'));
    }

    public function testRejectsEmptyValue(): void
    {
        self::assertFalse($this->isValidUrl(''));
    }
}
