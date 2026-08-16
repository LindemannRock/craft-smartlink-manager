<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services\analytics;

/**
 * Normalizes analytics metadata read from native JSON columns.
 *
 * @since 5.38.0
 */
final class AnalyticsMetadata
{
    /**
     * Decode current JSON objects and legacy double-encoded JSON objects.
     *
     * @return array<string|int, mixed>
     */
    public static function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = self::decodeJson($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded)) {
            $decoded = self::decodeJson($decoded, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * Return the inner object/array only when the value is an outer JSON string.
     *
     * @return array<mixed>|\stdClass|null
     */
    public static function unwrapLegacyJsonString(mixed $value): array|\stdClass|null
    {
        $decoded = self::decodeJson($value, false);
        if (!is_string($decoded)) {
            return null;
        }

        $inner = self::decodeJson($decoded, false);

        return is_array($inner) || $inner instanceof \stdClass ? $inner : null;
    }

    private static function decodeJson(mixed $value, bool $associative): mixed
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return json_decode($value, $associative, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }
    }
}
