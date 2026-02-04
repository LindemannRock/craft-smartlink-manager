<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\integrations;

use Craft;
use craft\helpers\App;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use nystudio107\seomatic\Seomatic;
use yii\base\Event;

/**
 * SEOmatic Integration
 *
 * Integrates SmartLink Manager with SEOmatic's tracking scripts
 * Pushes click events to Google Tag Manager data layer and Google Analytics
 *
 * @since 1.1.0
 */
class SeomaticIntegration extends BaseIntegration
{
    /**
     * @var array Events queued for next page render
     */
    private array $queuedEvents = [];

    /**
     * @var bool Whether event listeners have been registered
     */
    private bool $listenersRegistered = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->handle = 'seomatic';
        $this->name = 'SEOmatic';

        // Set logging handle for LoggingTrait
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Check if SEOmatic plugin is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isPluginInstalled('seomatic');
    }

    /**
     * Push an event to SEOmatic's data layer
     *
     * @param string $eventType
     * @param array $data
     * @return bool
     */
    public function pushEvent(string $eventType, array $data): bool
    {
        // Pre-flight checks
        if (!$this->isAvailable()) {
            $this->logDebug('SEOmatic plugin not available');
            return false;
        }

        if (!$this->isEnabled()) {
            $this->logDebug('SEOmatic integration not enabled');
            return false;
        }

        if (!$this->shouldTrackEvent($eventType)) {
            $this->logDebug("Event type '{$eventType}' not configured for tracking");
            return false;
        }

        if (!$this->validateEventData($eventType, $data)) {
            return false;
        }

        try {
            // Format event data
            $formattedData = $this->formatEventData($eventType, $data);

            // Register event listener if not already done
            $this->registerEventListener();

            // Queue the event
            $this->queuedEvents[] = $formattedData;

            // Try to inject immediately if scripts are available
            $this->injectDataLayerEvent($formattedData);

            $this->logInfo("Event '{$eventType}' queued successfully", [
                'event' => $formattedData['event'],
                'slug' => $data['slug'] ?? null,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->logError('Failed to push event', [
                'eventType' => $eventType,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    /**
     * Inject data layer event into SEOmatic scripts
     *
     * @param array $eventData
     * @return bool
     */
    private function injectDataLayerEvent(array $eventData): bool
    {
        if (!class_exists(Seomatic::class) || !isset(Seomatic::$plugin)) {
            return false;
        }

        try {
            // Access SEOmatic's script service
            $scriptService = Seomatic::$plugin->script ?? null;
            if (!$scriptService) {
                $this->logDebug('SEOmatic script service not available');
                return false;
            }

            // Try to inject into Google Tag Manager
            $gtmScript = $scriptService->get('googleTagManager');
            if ($gtmScript && $gtmScript->include) {
                // Initialize dataLayer if not exists
                if (!is_array($gtmScript->dataLayer)) {
                    $gtmScript->dataLayer = [];
                }

                // Add event to data layer
                $gtmScript->dataLayer[] = $eventData;

                $this->logDebug('Event injected into GTM data layer', [
                    'event' => $eventData['event'],
                ]);
                return true;
            }

            // Try to inject into gtag.js (Google Analytics)
            $gtagScript = $scriptService->get('gtag');
            if ($gtagScript && $gtagScript->include) {
                // Initialize dataLayer if not exists
                if (!is_array($gtagScript->dataLayer)) {
                    $gtagScript->dataLayer = [];
                }

                // Add event to data layer
                $gtagScript->dataLayer[] = $eventData;

                $this->logDebug('Event injected into gtag data layer', [
                    'event' => $eventData['event'],
                ]);
                return true;
            }

            $this->logDebug('No active tracking scripts found in SEOmatic');
            return false;
        } catch (\Throwable $e) {
            $this->logError('Failed to inject data layer event', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return false;
        }
    }

    /**
     * Register event listener for dynamic meta injection
     * This ensures events are available when pages are rendered
     */
    private function registerEventListener(): void
    {
        if ($this->listenersRegistered) {
            return;
        }

        $dynamicMetaClass = 'nystudio107\seomatic\helpers\DynamicMeta';
        if (!class_exists($dynamicMetaClass)) {
            return;
        }

        Event::on(
            $dynamicMetaClass,
            'addDynamicMeta',
            function($event) {
                $this->onAddDynamicMeta($event);
            }
        );

        $this->listenersRegistered = true;
        $this->logDebug('Registered SEOmatic event listeners');
    }

    /**
     * Handle SEOmatic's AddDynamicMeta event
     * Inject queued events into the data layer
     *
     * @param mixed $event
     */
    private function onAddDynamicMeta($event): void
    {
        if (empty($this->queuedEvents)) {
            return;
        }

        try {
            foreach ($this->queuedEvents as $eventData) {
                $this->injectDataLayerEvent($eventData);
            }

            $this->logDebug('Injected queued events', ['count' => count($this->queuedEvents)]);
        } catch (\Throwable $e) {
            $this->logError('Error in AddDynamicMeta handler', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Get integration status and configuration details
     * Checks ALL sites for tracking scripts
     *
     * @return array
     */
    public function getStatus(): array
    {
        $status = [
            'available' => $this->isAvailable(),
            'enabled' => $this->isEnabled(),
            'scripts' => [],
            'configuration' => [],
        ];

        if (!$this->isAvailable()) {
            return $status;
        }

        try {
            // Get all sites
            $sites = Craft::$app->sites->getAllSites();
            $scriptsFound = [];

            // Check each site for tracking scripts
            if (class_exists(Seomatic::class) && isset(Seomatic::$plugin)) {
                $currentSiteId = Craft::$app->sites->getCurrentSite()->id;

                foreach ($sites as $site) {
                    // Temporarily switch to this site
                    Craft::$app->sites->setCurrentSite($site);

                    // Load SEOmatic meta containers for this specific site
                    // This ensures we get site-specific configuration, not cached/global values
                    try {
                        if (isset(Seomatic::$plugin->metaContainers)) {
                            Seomatic::$plugin->metaContainers->loadMetaContainers('', $site->id);
                        }
                    } catch (\Throwable $e) {
                        // Silently continue if we can't load meta containers for this site
                    }

                    $scriptService = Seomatic::$plugin->script ?? null;
                    if (!$scriptService) {
                        continue;
                    }

                    // Check Google Tag Manager
                    $gtmScript = $scriptService->get('googleTagManager');

                    // Only include if GTM is enabled AND has an actual GTM ID configured
                    if ($gtmScript && $gtmScript->include) {
                        // Try both possible keys for GTM ID
                        $gtmId = $gtmScript->vars['googleTagManagerId']['value'] ??
                                $gtmScript->vars['googleTagManagerContainerId']['value'] ??
                                null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($gtmId)) {
                            // Only parse if it's an environment variable reference
                            if (strpos($gtmId, '$') !== false) {
                                $gtmId = App::env($gtmId);
                            }
                            $gtmId = trim($gtmId);
                        }

                        // Only add if there's an actual GTM ID (not empty)
                        if (!empty($gtmId)) {
                            if (!isset($scriptsFound['googleTagManager'])) {
                                $scriptsFound['googleTagManager'] = [
                                    'active' => true,
                                    'name' => 'Google Tag Manager',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['googleTagManager']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $gtmId,
                            ];
                        }
                    }

                    // Check Google Analytics (gtag.js)
                    $gtagScript = $scriptService->get('gtag');

                    // Check if the script is actually configured for THIS specific site
                    $shouldInclude = false;
                    $measurementId = null;

                    if ($gtagScript && $gtagScript->include) {
                        // Correct key is 'googleAnalyticsId', not 'googleAnalyticsMeasurementId'
                        $measurementId = $gtagScript->vars['googleAnalyticsId']['value'] ?? null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($measurementId)) {
                            if (strpos($measurementId, '$') !== false) {
                                $measurementId = App::env($measurementId);
                            }
                            $measurementId = trim($measurementId);
                        }

                        // Only include if there's an actual measurement ID configured (not empty)
                        $shouldInclude = !empty($measurementId);
                    }

                    if ($shouldInclude) {
                        if (!isset($scriptsFound['gtag'])) {
                            $scriptsFound['gtag'] = [
                                'active' => true,
                                'name' => 'Google Analytics 4',
                                'sites' => [],
                            ];
                        }
                        $scriptsFound['gtag']['sites'][] = [
                            'handle' => $site->handle,
                            'name' => $site->name,
                            'id' => $measurementId,
                        ];
                    }

                    // Check Facebook Pixel
                    $fbScript = $scriptService->get('facebookPixel');
                    if ($fbScript && $fbScript->include) {
                        $fbId = $fbScript->vars['facebookPixelId']['value'] ?? null;

                        // Only add if there's an actual Facebook Pixel ID
                        if (!empty($fbId)) {
                            if (!isset($scriptsFound['facebookPixel'])) {
                                $scriptsFound['facebookPixel'] = [
                                    'active' => true,
                                    'name' => 'Facebook Pixel',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['facebookPixel']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $fbId,
                            ];
                        }
                    }

                    // Check LinkedIn Insight
                    $linkedInScript = $scriptService->get('linkedInInsight');
                    if ($linkedInScript && $linkedInScript->include) {
                        // Correct key is 'dataPartnerId'
                        $partnerId = $linkedInScript->vars['dataPartnerId']['value'] ?? null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($partnerId)) {
                            if (strpos($partnerId, '$') !== false) {
                                $partnerId = App::env($partnerId);
                            }
                            $partnerId = trim($partnerId);
                        }

                        // Only add if there's an actual partner ID configured
                        if (!empty($partnerId)) {
                            if (!isset($scriptsFound['linkedInInsight'])) {
                                $scriptsFound['linkedInInsight'] = [
                                    'active' => true,
                                    'name' => 'LinkedIn Insight Tag',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['linkedInInsight']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $partnerId,
                            ];
                        }
                    }

                    // Check HubSpot
                    $hubSpotScript = $scriptService->get('hubSpot');
                    if ($hubSpotScript && $hubSpotScript->include) {
                        $hubSpotId = $hubSpotScript->vars['hubSpotId']['value'] ?? null;

                        if (!empty($hubSpotId)) {
                            if (!isset($scriptsFound['hubSpot'])) {
                                $scriptsFound['hubSpot'] = [
                                    'active' => true,
                                    'name' => 'HubSpot',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['hubSpot']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $hubSpotId,
                            ];
                        }
                    }

                    // Check Pinterest Tag
                    // Correct handle is 'pinterestTag'
                    $pinterestScript = $scriptService->get('pinterestTag');
                    if ($pinterestScript && $pinterestScript->include) {
                        // Correct key is 'pinterestTagId'
                        $pinterestId = $pinterestScript->vars['pinterestTagId']['value'] ?? null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($pinterestId)) {
                            if (strpos($pinterestId, '$') !== false) {
                                $pinterestId = App::env($pinterestId);
                            }
                            $pinterestId = trim($pinterestId);
                        }

                        if (!empty($pinterestId)) {
                            if (!isset($scriptsFound['pinterest'])) {
                                $scriptsFound['pinterest'] = [
                                    'active' => true,
                                    'name' => 'Pinterest Tag',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['pinterest']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $pinterestId,
                            ];
                        }
                    }

                    // Check Fathom Analytics
                    $fathomScript = $scriptService->get('fathom');
                    if ($fathomScript && $fathomScript->include) {
                        // Correct key is 'siteId'
                        $fathomSiteId = $fathomScript->vars['siteId']['value'] ?? null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($fathomSiteId)) {
                            if (strpos($fathomSiteId, '$') !== false) {
                                $fathomSiteId = App::env($fathomSiteId);
                            }
                            $fathomSiteId = trim($fathomSiteId);
                        }

                        if (!empty($fathomSiteId)) {
                            if (!isset($scriptsFound['fathom'])) {
                                $scriptsFound['fathom'] = [
                                    'active' => true,
                                    'name' => 'Fathom Analytics',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['fathom']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $fathomSiteId,
                            ];
                        }
                    }

                    // Check Matomo
                    $matomoScript = $scriptService->get('matomo');
                    if ($matomoScript && $matomoScript->include) {
                        // Correct key is 'siteId'
                        $matomoSiteId = $matomoScript->vars['siteId']['value'] ?? null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($matomoSiteId)) {
                            if (strpos($matomoSiteId, '$') !== false) {
                                $matomoSiteId = App::env($matomoSiteId);
                            }
                            $matomoSiteId = trim($matomoSiteId);
                        }

                        if (!empty($matomoSiteId)) {
                            if (!isset($scriptsFound['matomo'])) {
                                $scriptsFound['matomo'] = [
                                    'active' => true,
                                    'name' => 'Matomo Analytics',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['matomo']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $matomoSiteId,
                            ];
                        }
                    }

                    // Check Plausible
                    $plausibleScript = $scriptService->get('plausible');
                    if ($plausibleScript && $plausibleScript->include) {
                        // Correct key is 'siteDomain'
                        $plausibleDomain = $plausibleScript->vars['siteDomain']['value'] ?? null;

                        // Parse environment variables only if it looks like an env var (starts with $)
                        if (is_string($plausibleDomain)) {
                            if (strpos($plausibleDomain, '$') !== false) {
                                $plausibleDomain = App::env($plausibleDomain);
                            }
                            $plausibleDomain = trim($plausibleDomain);
                        }

                        if (!empty($plausibleDomain)) {
                            if (!isset($scriptsFound['plausible'])) {
                                $scriptsFound['plausible'] = [
                                    'active' => true,
                                    'name' => 'Plausible Analytics',
                                    'sites' => [],
                                ];
                            }
                            $scriptsFound['plausible']['sites'][] = [
                                'handle' => $site->handle,
                                'name' => $site->name,
                                'id' => $plausibleDomain,
                            ];
                        }
                    }
                }

                // Restore original site
                Craft::$app->sites->setCurrentSite(Craft::$app->sites->getSiteById($currentSiteId));
            }

            $status['scripts'] = $scriptsFound;

            // Get configuration from settings
            $settings = \lindemannrock\smartlinkmanager\SmartLinkManager::getInstance()->getSettings();
            $status['configuration'] = [
                'eventPrefix' => $settings->seomaticEventPrefix ?? 'smart_links',
                'trackingEvents' => $settings->seomaticTrackingEvents ?? [],
            ];
        } catch (\Throwable $e) {
            $this->logError('Error getting SEOmatic status', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $status;
    }

    /**
     * Get list of available tracking scripts
     *
     * @return array
     * @since 1.1.0
     */
    public function getAvailableScripts(): array
    {
        $status = $this->getStatus();
        return $status['scripts'] ?? [];
    }

    /**
     * Check if GTM is active
     *
     * @return bool
     * @since 1.1.0
     */
    public function hasGoogleTagManager(): bool
    {
        $scripts = $this->getAvailableScripts();
        return isset($scripts['googleTagManager']) && $scripts['googleTagManager']['active'];
    }

    /**
     * Check if Google Analytics is active
     *
     * @return bool
     * @since 1.1.0
     */
    public function hasGoogleAnalytics(): bool
    {
        $scripts = $this->getAvailableScripts();
        return isset($scripts['gtag']) && $scripts['gtag']['active'];
    }
}
