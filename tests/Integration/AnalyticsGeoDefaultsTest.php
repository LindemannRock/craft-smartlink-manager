<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Integration;

use craft\helpers\App;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins local/private IP geo fallback behavior.
 *
 * @since 5.31.0
 */
final class AnalyticsGeoDefaultsTest extends TestCase
{
    public function testPrivateIpHasNoGeoLocationWithoutExplicitDefaults(): void
    {
        $this->withoutDefaultLocationEnv(function(): void {
            $this->withSettings([
                'defaultCountry' => null,
                'defaultCity' => null,
            ], function(): void {
                self::assertNull(
                    $this->analytics->getLocationFromIp('127.0.0.1'),
                    'Private/local IPs must not synthesize a default geo location unless both defaults are configured.',
                );
            });
        });
    }

    public function testPrivateIpUsesExplicitSupportedDefaults(): void
    {
        $this->withSettings([
            'defaultCountry' => 'US',
            'defaultCity' => 'New York',
        ], function(): void {
            $location = $this->analytics->getLocationFromIp('192.168.1.42');

            self::assertIsArray($location);
            self::assertSame('US', $location['countryCode']);
            self::assertSame('New York', $location['city']);
        });
    }

    public function testPrivateIpHasNoGeoLocationForUnsupportedDefaults(): void
    {
        $this->withSettings([
            'defaultCountry' => 'ZZ',
            'defaultCity' => 'Missing City',
        ], function(): void {
            self::assertNull(
                $this->analytics->getLocationFromIp('10.0.0.10'),
                'Unsupported local/private IP geo defaults should leave geo fields empty instead of falling back to Dubai.',
            );
        });
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    private function withoutDefaultLocationEnv(callable $callback): mixed
    {
        $countryServer = $_SERVER['SMARTLINK_MANAGER_DEFAULT_COUNTRY'] ?? null;
        $cityServer = $_SERVER['SMARTLINK_MANAGER_DEFAULT_CITY'] ?? null;
        $countryEnv = $_ENV['SMARTLINK_MANAGER_DEFAULT_COUNTRY'] ?? null;
        $cityEnv = $_ENV['SMARTLINK_MANAGER_DEFAULT_CITY'] ?? null;
        $countryEffective = App::env('SMARTLINK_MANAGER_DEFAULT_COUNTRY');
        $cityEffective = App::env('SMARTLINK_MANAGER_DEFAULT_CITY');

        unset(
            $_SERVER['SMARTLINK_MANAGER_DEFAULT_COUNTRY'],
            $_SERVER['SMARTLINK_MANAGER_DEFAULT_CITY'],
            $_ENV['SMARTLINK_MANAGER_DEFAULT_COUNTRY'],
            $_ENV['SMARTLINK_MANAGER_DEFAULT_CITY'],
        );
        putenv('SMARTLINK_MANAGER_DEFAULT_COUNTRY');
        putenv('SMARTLINK_MANAGER_DEFAULT_CITY');

        try {
            return $callback();
        } finally {
            $this->restoreEnvValue('SMARTLINK_MANAGER_DEFAULT_COUNTRY', $countryServer, $countryEnv, $countryEffective);
            $this->restoreEnvValue('SMARTLINK_MANAGER_DEFAULT_CITY', $cityServer, $cityEnv, $cityEffective);
        }
    }

    private function restoreEnvValue(string $name, ?string $serverValue, ?string $envValue, mixed $effectiveValue): void
    {
        if ($serverValue !== null) {
            $_SERVER[$name] = $serverValue;
        }

        if ($envValue !== null) {
            $_ENV[$name] = $envValue;
        }

        if (is_string($effectiveValue)) {
            putenv($name . '=' . $effectiveValue);
        } else {
            putenv($name);
        }
    }
}
