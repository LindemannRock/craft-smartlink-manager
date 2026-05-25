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
use DateTime;
use DateTimeZone;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use lindemannrock\smartlinkmanager\tests\TestCase;

/**
 * Pins SmartLink element-index date columns to the plugin date-format cascade.
 *
 * @since 5.28.0
 */
final class ElementIndexDateFormattingTest extends TestCase
{
    public function testDateColumnsUseSmartlinkManagerCascadeSettings(): void
    {
        /** @var Settings $settings */
        $settings = SmartLinkManager::$plugin->getSettings();
        $previous = [
            'timeFormat' => $settings->timeFormat,
            'monthFormat' => $settings->monthFormat,
            'dateOrder' => $settings->dateOrder,
            'dateSeparator' => $settings->dateSeparator,
            'showSeconds' => $settings->showSeconds,
        ];

        $settings->timeFormat = '12';
        $settings->monthFormat = 'long';
        $settings->dateOrder = 'mdy';
        $settings->dateSeparator = '/';
        $settings->showSeconds = true;
        DateFormatHelper::clearConfigCache();

        try {
            $smartLink = $this->seedSmartLink();
            $smartLink->dateExpired = new DateTime('2026-01-07 14:30:25', new DateTimeZone(Craft::$app->getTimeZone()));

            $expiryHtml = $smartLink->getAttributeHtml('dateExpired');
            $createdHtml = $smartLink->getAttributeHtml('dateCreated');

            $this->assertStringContainsString('January 7, 2026', $expiryHtml);
            $this->assertStringContainsString('2:30:25 PM', $expiryHtml);
            $this->assertMatchesRegularExpression('/>[A-Z][a-z]+ \d{1,2}, 20\d{2}</', $createdHtml);
        } finally {
            $settings->timeFormat = $previous['timeFormat'];
            $settings->monthFormat = $previous['monthFormat'];
            $settings->dateOrder = $previous['dateOrder'];
            $settings->dateSeparator = $previous['dateSeparator'];
            $settings->showSeconds = $previous['showSeconds'];
            DateFormatHelper::clearConfigCache();
        }
    }
}
