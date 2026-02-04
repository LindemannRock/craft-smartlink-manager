<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\console\controllers;

use craft\console\Controller;
use craft\helpers\Console;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Demo Controller for testing
 *
 * @since 1.0.0
 */
class DemoController extends Controller
{
    /**
     * Add a demo QR code click
     *
     * @param int|null $id Smart link ID
     * @return int
     * @since 1.0.0
     */
    public function actionAddQrClick($id = null): int
    {
        if ($id) {
            $smartLink = SmartLink::find()->id($id)->one();
        } else {
            $smartLink = SmartLink::find()->one();
        }
        
        if (!$smartLink) {
            $this->stderr("No smart links found!\n", Console::FG_RED);
            return self::EXIT_CODE_ERROR;
        }
        
        $this->stdout("Adding demo QR code click for: {$smartLink->title}\n", Console::FG_YELLOW);
        
        // Create device info for a typical QR scan from iPhone
        $deviceInfo = new \lindemannrock\smartlinkmanager\models\DeviceInfo([
            'deviceType' => 'smartphone',
            'brand' => 'Apple',
            'model' => 'iPhone 14',
            'platform' => 'ios',
            'osName' => 'iOS',
            'osVersion' => '17.0',
            'browser' => 'Mobile Safari',
            'browserVersion' => '17.0',
            'isMobile' => true,
            'isTablet' => false,
            'isDesktop' => false,
            'isBot' => false,
            'userAgent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15',
        ]);
        
        // Save analytics directly
        SmartLinkManager::$plugin->analytics->saveAnalytics(
            $smartLink->id,
            $deviceInfo->toArray(),
            [
                'redirectUrl' => $smartLink->getRedirectUrl(),
                'language' => 'en',
                'referrer' => null,
                'source' => 'qr', // Mark as QR code
                'ip' => '192.168.1.100',
            ]
        );
        
        $this->stdout("✅ Demo QR code click added successfully!\n", Console::FG_GREEN);
        $this->stdout("Go to SmartLink Manager > {$smartLink->title} > Analytics tab to see it\n");
        
        return self::EXIT_CODE_NORMAL;
    }
}
