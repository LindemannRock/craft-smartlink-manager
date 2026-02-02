<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\web\Controller;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\Response;

/**
 * Redirect Controller
 * Handles smart link redirects on the frontend
 *
 * @since 1.0.0
 */
class RedirectController extends Controller
{
    use LoggingTrait;

    /**
     * @var array<int|string>|bool|int Allow anonymous access
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Handle smart link landing page display
     *
     * @param string $slug
     * @return Response
     */
    public function actionIndex(string $slug): Response
    {
        // Get current site for multi-site support
        $currentSite = Craft::$app->getSites()->getCurrentSite();

        // Check if SmartLink Manager is enabled for the current site
        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings->isSiteEnabled($currentSite->id)) {
            $this->logInfo('SmartLink Manager disabled for this site', ['siteId' => $currentSite->id, 'slug' => $slug]);
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get the smart link for the current site
        // Get any status to check expired/disabled/pending separately
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($currentSite->id)
            ->status(null)
            ->one();

        if (!$smartLink) {
            $url = Craft::$app->getRequest()->getUrl();

            // Check Redirect Manager for matching redirect (if installed)
            $redirect = $this->handleRedirect404($url, 'smartlink-manager', [
                'type' => 'smart-link-not-found',
                'slug' => $slug,
            ]);

            if ($redirect) {
                $this->logInfo('Smart link 404 handled by Redirect Manager', [
                    'url' => $url,
                    'slug' => $slug,
                    'destination' => $redirect['destinationUrl'],
                ]);

                return $this->redirect($redirect['destinationUrl'], $redirect['statusCode']);
            }

            // Fallback to configured URL
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';

            // Handle relative URLs
            if (strpos($redirectUrl, '://') === false && strpos($redirectUrl, '/') !== 0) {
                $redirectUrl = '/' . $redirectUrl;
            }

            return $this->redirect($redirectUrl);
        }

        // Check if smart link is disabled
        if ($smartLink->getStatus() === SmartLink::STATUS_DISABLED) {
            $this->logInfo('Smart link disabled', ['slug' => $slug]);
            // Redirect to not found
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Check if smart link is expired
        if ($smartLink->getStatus() === SmartLink::STATUS_EXPIRED) {
            $this->logInfo('Smart link expired', ['slug' => $slug]);
            // Redirect to not found
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Check if smart link is pending
        if ($smartLink->getStatus() === SmartLink::STATUS_PENDING) {
            $this->logInfo('Smart link pending', ['slug' => $slug]);
            // Redirect to not found
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get device info and language for template display
        $deviceInfo = SmartLinkManager::$plugin->deviceDetection->detectDevice();
        $language = SmartLinkManager::$plugin->deviceDetection->detectLanguage();

        // Set cache headers - this page can be fully cached
        $response = Craft::$app->getResponse();
        $response->headers->set('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour

        // Render the template - all links will point to action URLs for tracking
        $settings = SmartLinkManager::$plugin->getSettings();
        $template = $settings->redirectTemplate ?: 'smartlink-manager/redirect';

        return $this->renderTemplate($template, [
            'smartLink' => $smartLink,
            'device' => $deviceInfo,
            'language' => $language,
        ]);
    }

    /**
     * Track and redirect to platform URL
     * This endpoint handles all navigation with server-side tracking
     *
     * @param string $slug
     * @param string $platform Platform identifier (ios, android, huawei, amazon, windows, mac, fallback, auto)
     * @return Response
     */
    public function actionGo(string $slug, string $platform = 'auto'): Response
    {
        // Normalize platform parameter to lowercase first
        $platform = strtolower($platform);

        // Validate platform parameter - only allow valid values
        $validPlatforms = ['auto', 'ios', 'android', 'huawei', 'amazon', 'windows', 'mac', 'fallback'];
        if (!in_array($platform, $validPlatforms)) {
            // Invalid platform, default to auto
            $platform = 'auto';
        }

        // Get site from param or fall back to current site
        $siteParam = Craft::$app->getRequest()->getParam('site');
        if ($siteParam) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteParam);
            $siteId = $site ? $site->id : Craft::$app->getSites()->getCurrentSite()->id;
        } else {
            $siteId = Craft::$app->getSites()->getCurrentSite()->id;
        }

        // Check if SmartLink Manager is enabled for the site
        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings->isSiteEnabled($siteId)) {
            $this->logInfo('SmartLink Manager disabled for this site', ['siteId' => $siteId, 'slug' => $slug]);
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get the smart link for the site
        // Get any status to check expired/disabled/pending separately
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($siteId)
            ->status(null)
            ->one();

        if (!$smartLink) {
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Check if smart link is disabled
        if ($smartLink->getStatus() === SmartLink::STATUS_DISABLED) {
            $this->logInfo('Smart link disabled', ['slug' => $slug]);
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Check if smart link is expired
        if ($smartLink->getStatus() === SmartLink::STATUS_EXPIRED) {
            $this->logInfo('Smart link expired', ['slug' => $slug]);
            // Redirect to not found
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Check if smart link is pending
        if ($smartLink->getStatus() === SmartLink::STATUS_PENDING) {
            $this->logInfo('Smart link pending', ['slug' => $slug]);
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get device info for tracking
        $deviceInfo = SmartLinkManager::$plugin->deviceDetection->detectDevice();
        $language = SmartLinkManager::$plugin->deviceDetection->detectLanguage();

        // Get source parameter for QR tracking
        $source = Craft::$app->getRequest()->getParam('src', 'direct');

        // Determine destination URL
        $destinationUrl = null;
        $clickType = 'button';

        if ($platform === 'auto') {
            // Auto-detect platform and redirect (for mobile auto-redirect)
            $destinationUrl = SmartLinkManager::$plugin->deviceDetection->getRedirectUrl(
                $smartLink,
                $deviceInfo,
                $language
            );
            $clickType = 'redirect';
            $platform = $deviceInfo->platform ?? 'unknown';
        } else {
            // Manual platform selection from button click
            $destinationUrl = match ($platform) {
                'ios' => $smartLink->iosUrl,
                'android' => $smartLink->androidUrl,
                'huawei' => $smartLink->huaweiUrl,
                'amazon' => $smartLink->amazonUrl,
                'windows' => $smartLink->windowsUrl,
                'mac' => $smartLink->macUrl,
                default => $smartLink->fallbackUrl, // fallback and any other values
            };
        }

        // Track the click if analytics are enabled
        if ($smartLink->trackAnalytics && SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            // Normalize platform value to match DeviceInfo valid values
            $normalizedPlatform = match ($platform) {
                'mac' => 'macos',
                'fallback' => 'other',
                default => $platform
            };

            // If platform is 'auto', use the detected platform from deviceInfo
            if ($normalizedPlatform === 'auto') {
                $normalizedPlatform = $deviceInfo->platform ?? 'other';
            }

            SmartLinkManager::$plugin->analytics->trackClick(
                $smartLink,
                $deviceInfo,
                [
                    'clickType' => $clickType,
                    'platform' => $normalizedPlatform,
                    'buttonUrl' => $destinationUrl,
                    'referrer' => Craft::$app->request->getReferrer(),
                    'source' => $source,
                    'siteId' => $siteId, // Pass the detected site ID
                    'language' => $language,
                ]
            );

            // Log SEOmatic event tracking for monitoring (actual tracking happens client-side in templates)
            // Client-side JavaScript in redirect.twig/qr.twig pushes events to GTM dataLayer BEFORE redirects
            // Only log if SEOmatic integration is enabled
            $seomatic = SmartLinkManager::$plugin->integration->getIntegration('seomatic');
            if ($seomatic && $seomatic->isAvailable() && $seomatic->isEnabled()) {
                $this->logInfo("SEOmatic client-side tracking: {$clickType} event for '{$smartLink->slug}'", [
                    'event_type' => $clickType === 'redirect' ? 'redirect' : 'button_click',
                    'slug' => $smartLink->slug,
                    'platform' => $normalizedPlatform,
                    'source' => $source,
                    'destination' => $destinationUrl,
                ]);
            }
        }

        // Redirect to destination
        if ($destinationUrl) {
            return $this->redirect($destinationUrl, 302);
        }

        // No destination URL available, redirect to fallback
        return $this->redirect($smartLink->fallbackUrl, 302);
    }

    /**
     * Get fresh device detection (for cached pages)
     * This endpoint is never cached and provides real-time device info
     *
     * @return Response
     */
    public function actionRefreshCsrf(): Response
    {
        // Prevent caching of this response
        $this->response->setNoCacheHeaders();

        // Detect device using the plugin's device detection service
        $deviceInfo = SmartLinkManager::$plugin->deviceDetection->detectDevice();

        return $this->asJson([
            'csrfToken' => Craft::$app->request->getCsrfToken(),
            'isMobile' => $deviceInfo->isMobile ?? false,
            'platform' => $deviceInfo->platform ?? 'unknown',
        ]);
    }

    /**
     * Handle 404 through Redirect Manager if available
     *
     * @param string $url The URL that wasn't found
     * @param string $source Source identifier (e.g., 'smartlink-manager')
     * @param array $context Additional context data
     * @return array|null Redirect data or null if no redirect found
     */
    private function handleRedirect404(string $url, string $source, array $context = []): ?array
    {
        // Use the integration to check availability and enabled status
        $integration = SmartLinkManager::$plugin->integration->getIntegration('redirect-manager');
        if (!$integration || !$integration->isAvailable() || !$integration->isEnabled()) {
            return null;
        }

        try {
            // Get Redirect Manager plugin instance
            $redirectManager = PluginHelper::getPlugin('redirect-manager');
            if (!$redirectManager instanceof \lindemannrock\redirectmanager\RedirectManager) {
                return null;
            }

            // Add source to context
            $context['source'] = $source;

            // Call the service method to handle external 404
            return $redirectManager->redirects->handleExternal404($url, $context);
        } catch (\Throwable $e) {
            $this->logError('Failed to check Redirect Manager for 404', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
