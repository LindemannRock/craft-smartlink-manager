<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Stubs;

use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\services\DeviceDetectionService;

/**
 * Deterministic device detection stub for redirect-controller tests.
 *
 * @since 5.30.0
 */
final class StubDeviceDetectionService extends DeviceDetectionService
{
    public function detectDevice(?string $userAgent = null): DeviceInfo
    {
        $info = new DeviceInfo();
        $info->platform = 'ios';
        $info->deviceType = 'smartphone';
        $info->isMobile = true;
        $info->userAgent = 'Mozilla/5.0 (Test) SmartLinkManagerStub/1.0';
        $info->browser = 'TestBrowser';
        $info->osName = 'TestOS';
        $info->language = 'en';

        return $info;
    }

    public function detectLanguage(): string
    {
        return 'en';
    }

    public function getRedirectUrl(SmartLink $smartLink, DeviceInfo $deviceInfo, ?string $language = null): string
    {
        return $smartLink->iosUrl ?: $smartLink->fallbackUrl;
    }
}
