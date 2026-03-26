<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services;

use Craft;
use craft\base\Component;
use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\events\SmartLinkEvent;
use lindemannrock\smartlinkmanager\models\DeviceInfo;
use lindemannrock\smartlinkmanager\records\SmartLinkRecord;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\base\Event;

/**
 * SmartLink Manager Service
 *
 * @property-read SmartLinkManager $module
 * @since 1.0.0
 */
class SmartLinksService extends Component
{
    use LoggingTrait;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    // Events
    // =========================================================================

    /**
     * @event SmartLinkEvent The event that is triggered before a smart link redirect.
     */
    public const EVENT_BEFORE_REDIRECT = 'beforeRedirect';

    /**
     * @event SmartLinkEvent The event that is triggered after analytics are tracked.
     */
    public const EVENT_AFTER_TRACK_ANALYTICS = 'afterTrackAnalytics';

    // Public Methods
    // =========================================================================

    /**
     * Save a smart link
     *
     * @param SmartLink $smartLink
     * @param bool $runValidation
     * @return bool
     */
    public function saveSmartLink(SmartLink $smartLink, bool $runValidation = true): bool
    {
        if ($runValidation && !$smartLink->validate()) {
            $this->logInfo('Smart link not saved due to validation errors', [
                'errors' => $smartLink->getErrors(),
            ]);
            return false;
        }

        $oldSlug = null;

        // Get old slug if this is an update (element has an ID)
        if ($smartLink->id) {
            $oldRecord = SmartLinkRecord::findOne($smartLink->id);
            if ($oldRecord) {
                $oldSlug = $oldRecord->slug;
            }
        }

        // Save the element
        $success = Craft::$app->elements->saveElement($smartLink, true, true, true);

        if ($success) {
            // Handle slug changes - create redirect from old to new
            if ($oldSlug && $oldSlug !== $smartLink->slug) {
                $this->handleSlugChange($oldSlug, $smartLink);
            }
        }

        return $success;
    }

    /**
     * Delete a smart link
     *
     * @param SmartLink $smartLink
     * @return bool
     */
    public function deleteSmartLink(SmartLink $smartLink): bool
    {
        return Craft::$app->elements->deleteElement($smartLink);
    }

    /**
     * Get a smart link by ID
     *
     * @param int $id
     * @param int|null $siteId
     * @return SmartLink|null
     */
    public function getSmartLinkById(int $id, ?int $siteId = null): ?SmartLink
    {
        return SmartLink::find()
            ->id($id)
            ->siteId($siteId)
            ->status(null)
            ->one();
    }

    /**
     * Get a smart link by slug
     *
     * @param string $slug
     * @param int|null $siteId
     * @return SmartLink|null
     */
    public function getSmartLinkBySlug(string $slug, ?int $siteId = null): ?SmartLink
    {
        $slug = strtolower(trim($slug));

        $query = SmartLink::find()
            ->slug($slug)
            ->status(null);

        if ($siteId) {
            $query->siteId($siteId);
        } else {
            $query->siteId('*');
        }

        return $query->one();
    }

    /**
     * Generate QR code for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $options
     * @return string
     */
    public function generateQrCode(SmartLink $smartLink, array $options = []): string
    {
        return SmartLinkManager::$plugin->qrCode->generateQrCode($smartLink->getRedirectUrl(), $options);
    }

    /**
     * Generate QR code data URL for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $options
     * @return string
     */
    public function generateQrCodeDataUrl(SmartLink $smartLink, array $options = []): string
    {
        return SmartLinkManager::$plugin->qrCode->generateQrCodeDataUrl($smartLink->getRedirectUrl(), $options);
    }

    /**
     * Trigger before redirect event
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $device
     * @param string $redirectUrl
     * @return string The redirect URL (possibly modified by event handlers)
     */
    public function triggerBeforeRedirect(SmartLink $smartLink, DeviceInfo $device, string $redirectUrl): string
    {
        $event = new SmartLinkEvent([
            'smartLink' => $smartLink,
            'device' => $device,
            'redirectUrl' => $redirectUrl,
        ]);

        $this->trigger(self::EVENT_BEFORE_REDIRECT, $event);

        return $event->redirectUrl;
    }

    /**
     * Trigger after track analytics event
     *
     * @param SmartLink $smartLink
     * @param DeviceInfo $device
     * @param array $metadata
     */
    public function triggerAfterTrackAnalytics(SmartLink $smartLink, DeviceInfo $device, array $metadata): void
    {
        $event = new SmartLinkEvent([
            'smartLink' => $smartLink,
            'device' => $device,
            'metadata' => $metadata,
        ]);

        $this->trigger(self::EVENT_AFTER_TRACK_ANALYTICS, $event);
    }

    /**
     * Handle slug change by creating redirect in Redirect Manager
     *
     * @param string $oldSlug
     * @param SmartLink $link
     * @return void
     */
    private function handleSlugChange(string $oldSlug, SmartLink $link): void
    {
        $settings = SmartLinkManager::$plugin->getSettings();

        // Check if Redirect Manager integration is enabled
        $enabledIntegrations = $settings->enabledIntegrations ?? [];
        if (!in_array('redirect-manager', $enabledIntegrations, true)) {
            return;
        }

        // Check if slug change event is enabled
        $redirectManagerEvents = $settings->redirectManagerEvents ?? [];
        if (!in_array('slug-change', $redirectManagerEvents, true)) {
            return;
        }

        $slugPrefix = trim((string) ($settings->slugPrefix ?? 'go'), '/');
        $slugPrefix = $slugPrefix !== '' ? $slugPrefix : 'go';
        $usePrefix = (bool) ($settings->usePrefix ?? true);

        $oldUrl = $usePrefix ? '/' . $slugPrefix . '/' . $oldSlug : '/' . $oldSlug;
        $newUrl = $usePrefix ? '/' . $slugPrefix . '/' . $link->slug : '/' . $link->slug;

        // Check if Redirect Manager integration is available and enabled
        $redirectIntegration = SmartLinkManager::$plugin->integration->getIntegration('redirect-manager');
        if (!$redirectIntegration || !$redirectIntegration->isAvailable() || !$redirectIntegration->isEnabled()) {
            $this->logDebug('Redirect Manager integration not available or not enabled');
            return;
        }

        // Get redirect manager plugin instance
        $redirectManager = PluginHelper::getPlugin('redirect-manager');
        if (!$redirectManager instanceof \lindemannrock\redirectmanager\RedirectManager) {
            $this->logDebug('Redirect Manager plugin not found');
            return;
        }

        // SCENARIO 1: Try to handle undo
        try {
            $undoHandled = $redirectManager->redirects->handleUndoRedirect(
                $oldUrl,
                $newUrl,
                null, // null = all sites
                'smart-link-slug-change',
                'smartlink-manager'
            );

            if ($undoHandled) {
                return; // Undo was handled
            }
        } catch (\Exception $e) {
            $this->logWarning('Failed to handle undo redirect', ['error' => $e->getMessage()]);
        }

        // SCENARIO 2: Create the redirect
        try {
            $success = $redirectManager->redirects->createRedirect([
                'sourceUrl' => $oldUrl,
                'sourceUrlParsed' => $oldUrl,
                'destinationUrl' => $newUrl,
                'matchType' => 'exact',
                'redirectSrcMatch' => 'pathonly',
                'statusCode' => 301,
                'siteId' => null, // Smart link slugs are shared across all sites
                'enabled' => true,
                'priority' => 0,
                'creationType' => 'smart-link-slug-change',
                'sourcePlugin' => 'smartlink-manager',
            ], true); // Show notification

            if ($success) {
                $this->logInfo('Created redirect for slug change', [
                    'oldSlug' => $oldSlug,
                    'newSlug' => $link->slug,
                    'oldUrl' => $oldUrl,
                    'newUrl' => $newUrl,
                ]);
            }
        } catch (\Exception $e) {
            $this->logError('Failed to create redirect rule', ['error' => $e->getMessage()]);
        }
    }
}
