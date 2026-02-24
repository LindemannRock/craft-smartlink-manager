<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\integrations;

use Craft;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Redirect Manager Integration
 *
 * NOTE: This integration is different from SEOmatic.
 * - SEOmatic: Pushes analytics EVENTS to external service (via pushEvent)
 * - Redirect Manager: Creates REDIRECTS via service methods (direct API calls)
 *
 * This class exists for:
 * - Status checking (isAvailable, isEnabled)
 * - UI display (getStatus)
 * - Architecture consistency
 *
 * Actual redirect creation happens in SmartLinksService:
 * - handleSlugChange() -> calls redirect-manager service methods with runtime checks
 * - handleDeletedSmartLink() -> calls redirect-manager service methods with runtime checks
 *
 * @since 1.1.0
 */
class RedirectManagerIntegration extends BaseIntegration
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->handle = 'redirect-manager';
        $this->name = 'Redirect Manager';
    }

    /**
     * Check if Redirect Manager plugin is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isPluginInstalled('redirect-manager');
    }

    /**
     * Push event to Redirect Manager
     *
     * NOTE: This method is not used for Redirect Manager integration.
     * Redirect creation happens directly in SmartLinksService via redirect-manager service calls.
     * This is a no-op to satisfy the IntegrationInterface contract.
     *
     * @param string $eventType Event type (not applicable)
     * @param array $data Event data (not applicable)
     * @return bool Always returns true (no-op)
     */
    public function pushEvent(string $eventType, array $data): bool
    {
        // Redirect Manager integration doesn't use event pushing
        // Redirects are created via service method calls in the service layer:
        // - SmartLinksService::handleSlugChange() -> calls redirect-manager service
        // - SmartLinksService::handleDeletedSmartLink() -> calls redirect-manager service
        return true;
    }

    /**
     * Get Redirect Manager integration status
     *
     * @return array
     */
    public function getStatus(): array
    {
        $settings = SmartLinkManager::getInstance()->getSettings();
        $redirectManagerEvents = $settings->redirectManagerEvents ?? [];

        return [
            'name' => $this->getName(),
            'handle' => $this->getHandle(),
            'available' => $this->isAvailable(),
            'enabled' => $this->isEnabled(),
            'events' => $redirectManagerEvents,
            'description' => Craft::t('smartlink-manager', 'Creates permanent redirects when {pluginName} slugs change or links are deleted', [
                'pluginName' => SmartLinkManager::$plugin->getSettings()->getLowerDisplayName(),
            ]),
        ];
    }

    /**
     * Validate event data
     *
     * NOTE: Not used for Redirect Manager integration.
     * This is a no-op to satisfy the IntegrationInterface contract.
     *
     * @param string $eventType
     * @param array $data
     * @return bool Always returns true
     */
    public function validateEventData(string $eventType, array $data): bool
    {
        // No event validation needed for Redirect Manager
        return true;
    }
}
