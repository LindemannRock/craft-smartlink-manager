<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins environment-variable resolution for the 404 redirect URL setting.
 *
 * Regression: `notFoundRedirectUrl` accepted `$ENV_VAR` syntax (UrlOrPathValidator)
 * but was never resolved at redirect time, so the literal `$VAR` string reached the
 * redirect. `getResolvedNotFoundRedirectUrl()` now resolves it via `App::parseEnv()`.
 *
 * @since 5.29.0
 */
final class NotFoundRedirectUrlResolutionTest extends TestCase
{
    public function testResolvesEnvironmentVariable(): void
    {
        $_SERVER['SLM_TEST_NOTFOUND_URL'] = 'https://example.com/404';
        putenv('SLM_TEST_NOTFOUND_URL=https://example.com/404');

        try {
            $settings = new Settings();
            $settings->notFoundRedirectUrl = '$SLM_TEST_NOTFOUND_URL';
            self::assertSame('https://example.com/404', $settings->getResolvedNotFoundRedirectUrl());
        } finally {
            unset($_SERVER['SLM_TEST_NOTFOUND_URL']);
            putenv('SLM_TEST_NOTFOUND_URL');
        }
    }

    public function testPassesThroughLiteralPathsAndUrls(): void
    {
        $settings = new Settings();

        $settings->notFoundRedirectUrl = '/landing';
        self::assertSame('/landing', $settings->getResolvedNotFoundRedirectUrl());

        $settings->notFoundRedirectUrl = 'https://example.com/x';
        self::assertSame('https://example.com/x', $settings->getResolvedNotFoundRedirectUrl());
    }

    public function testFallsBackToRootWhenEmpty(): void
    {
        $settings = new Settings();
        $settings->notFoundRedirectUrl = '';
        self::assertSame('/', $settings->getResolvedNotFoundRedirectUrl());
    }
}
