<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\models\Site;
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
    public function actionIndex(string $slug, ?string $siteHandle = null): Response
    {
        $site = $this->resolveSite($siteHandle);
        if (!$site) {
            $this->logWarning('Invalid site handle for smart link request', ['slug' => $slug, 'siteHandle' => $siteHandle]);
            return $this->redirectToNotFound();
        }

        // Check if SmartLink Manager is enabled for the current site
        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings->isSiteEnabled($site->id)) {
            $this->logInfo('SmartLink Manager disabled for this site', ['siteId' => $site->id, 'slug' => $slug]);
            return $this->redirectToNotFound();
        }

        // Get the smart link for the current site first.
        // If not found, fallback across sites because custom-domain requests can
        // resolve to a different current site than the link's saved site.
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($site->id)
            ->status(null)
            ->one();

        if (!$smartLink) {
            $smartLink = SmartLink::find()
                ->slug($slug)
                ->site('*')
                ->status(null)
                ->one();
        }

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
            return $this->redirectToNotFound();
        }

        // Validate against the link's actual site, not only the request-resolved site.
        if (!$settings->isSiteEnabled($smartLink->siteId)) {
            $this->logInfo('SmartLink Manager disabled for smart link site', [
                'siteId' => $smartLink->siteId,
                'slug' => $slug,
            ]);
            return $this->redirectToNotFound();
        }

        // Check if smart link is disabled
        if ($smartLink->getStatus() === SmartLink::STATUS_DISABLED) {
            $this->logInfo('Smart link disabled', ['slug' => $slug]);
            return $this->redirectToNotFound();
        }

        // Check if smart link is expired
        if ($smartLink->getStatus() === SmartLink::STATUS_EXPIRED) {
            $this->logInfo('Smart link expired', ['slug' => $slug]);
            return $this->redirectToNotFound();
        }

        // Check if smart link is pending
        if ($smartLink->getStatus() === SmartLink::STATUS_PENDING) {
            $this->logInfo('Smart link pending', ['slug' => $slug]);
            return $this->redirectToNotFound();
        }

        // Get device info and language for template display
        $deviceInfo = SmartLinkManager::$plugin->deviceDetection->detectDevice();
        $language = SmartLinkManager::$plugin->deviceDetection->detectLanguage();

        $response = Craft::$app->getResponse();
        $this->applyNoStoreHeaders($response);

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
    public function actionGo(string $slug, string $platform = 'auto', ?string $siteHandle = null): Response
    {
        // Normalize platform parameter to lowercase first
        $platform = strtolower($platform);

        // Validate platform parameter - only allow valid values
        $validPlatforms = ['auto', 'ios', 'android', 'huawei', 'amazon', 'windows', 'mac', 'fallback'];
        if (!in_array($platform, $validPlatforms, true)) {
            // Invalid platform, default to auto
            $platform = 'auto';
        }

        // Resolve site from route first, then from query param, then fallback to current
        $site = null;
        if ($siteHandle) {
            $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);
        }
        if (!$site) {
            $siteParam = Craft::$app->getRequest()->getParam('site');
            if ($siteParam) {
                $site = Craft::$app->getSites()->getSiteByHandle((string) $siteParam);
            }
        }
        if (!$site) {
            $site = Craft::$app->getSites()->getCurrentSite();
        }
        $siteId = $site->id;

        // Check if SmartLink Manager is enabled for the site
        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings->isSiteEnabled($siteId)) {
            $this->logInfo('SmartLink Manager disabled for this site', ['siteId' => $siteId, 'slug' => $slug]);
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get the smart link for the resolved site first.
        // If not found, fallback across sites (custom-domain/current-site mismatch).
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($siteId)
            ->status(null)
            ->one();

        if (!$smartLink) {
            $smartLink = SmartLink::find()
                ->slug($slug)
                ->site('*')
                ->status(null)
                ->one();
        }

        if (!$smartLink) {
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Validate against the smart link's actual site.
        if (!$settings->isSiteEnabled($smartLink->siteId)) {
            $this->logInfo('SmartLink Manager disabled for smart link site', [
                'siteId' => $smartLink->siteId,
                'slug' => $slug,
            ]);
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

        $shouldTrack = $smartLink->trackAnalytics && SmartLinkManager::$plugin->getSettings()->enableAnalytics;

        // Track the click if analytics are enabled
        if ($shouldTrack) {
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
            $response = $this->redirect($this->_sanitizeUrl($destinationUrl), 302);
            if ($shouldTrack) {
                $this->applyNoStoreHeaders($response);
            }
            return $response;
        }

        // No destination URL available, redirect to fallback
        $response = $this->redirect($this->_sanitizeUrl($smartLink->fallbackUrl), 302);
        if ($shouldTrack) {
            $this->applyNoStoreHeaders($response);
        }
        return $response;
    }

    /**
     * Resolve request site from route handle (if provided), otherwise use current site.
     */
    private function resolveSite(?string $siteHandle): ?Site
    {
        if ($siteHandle) {
            return Craft::$app->getSites()->getSiteByHandle($siteHandle);
        }

        return Craft::$app->getSites()->getCurrentSite();
    }

    /**
     * Redirect to configured not-found destination.
     */
    private function redirectToNotFound(): Response
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $redirectUrl = $settings->notFoundRedirectUrl ?: '/';

        if (strpos($redirectUrl, '://') === false && strpos($redirectUrl, '/') !== 0) {
            $redirectUrl = '/' . $redirectUrl;
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Get fresh device detection (for cached pages)
     * This endpoint is never cached and provides real-time device info
     *
     * @return Response
     * @since 1.7.0
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

    private function applyNoStoreHeaders(Response $response): void
    {
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
    }

    /**
     * Sanitize a URL to prevent XSS via dangerous schemes.
     *
     * Only allows http://, https://, and relative paths (starting with /).
     * Rejects javascript:, data:, vbscript:, and other dangerous schemes.
     *
     * @param string $url
     * @return string Sanitized URL, or '/' if scheme is disallowed
     */
    private function _sanitizeUrl(string $url): string
    {
        $url = trim($url);

        // Allow relative URLs
        if (str_starts_with($url, '/')) {
            return $url;
        }

        // Allow http and https
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        // Reject everything else (javascript:, data:, vbscript:, etc.)
        $this->logWarning('Blocked unsafe URL scheme', ['url' => $url]);
        return '/';
    }
}
