<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\integrations;

use lindemannrock\base\helpers\PluginHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Base Integration
 *
 * Abstract base class for all third-party integrations
 * Provides common functionality and helpers
 *
 * @since 1.23.0
 */
abstract class BaseIntegration implements IntegrationInterface
{
    use LoggingTrait;

    /**
     * @var string Integration handle
     */
    protected string $handle;

    /**
     * @var string Integration name
     */
    protected string $name;

    /**
     * @var array Required event data fields by event type
     */
    protected array $requiredFields = [
        'redirect' => ['slug', 'title', 'destinationUrl', 'platform', 'source'],
        'button_click' => ['slug', 'title', 'destinationUrl', 'platform', 'buttonType'],
        'qr_scan' => ['slug', 'title'],
    ];

    /**
     * Get the integration handle
     *
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * Get the integration name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Check if the integration is enabled in settings
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = SmartLinkManager::getInstance()->getSettings();

        // Check if analytics is globally enabled
        if (!$settings->enableAnalytics) {
            return false;
        }

        // Check if this specific integration is enabled
        $enabledIntegrations = $settings->enabledIntegrations ?? [];
        return in_array($this->handle, $enabledIntegrations, true);
    }

    /**
     * Validate event data structure
     *
     * @param string $eventType
     * @param array $data
     * @return bool
     */
    public function validateEventData(string $eventType, array $data): bool
    {
        // Check if event type is supported
        if (!isset($this->requiredFields[$eventType])) {
            $this->logWarning("Unknown event type: {$eventType}");
            return false;
        }

        // Check required fields
        $missingFields = [];
        foreach ($this->requiredFields[$eventType] as $field) {
            if (!isset($data[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $this->logWarning("Missing required fields for {$eventType}: " . implode(', ', $missingFields));
            return false;
        }

        return true;
    }

    /**
     * Format event data for the integration
     *
     * @param string $eventType
     * @param array $data
     * @return array Formatted data
     */
    protected function formatEventData(string $eventType, array $data): array
    {
        $settings = SmartLinkManager::getInstance()->getSettings();
        $eventPrefix = $settings->seomaticEventPrefix ?? 'smart_links';

        // Build base event structure
        $formattedData = [
            'event' => "{$eventPrefix}_{$eventType}",
            'smart_link' => [],
        ];

        // Map common fields
        $fieldMapping = [
            'slug' => 'slug',
            'title' => 'title',
            'destinationUrl' => 'destination_url',
            'platform' => 'platform',
            'source' => 'source',
            'buttonType' => 'button_type',
            'clickType' => 'click_type',
        ];

        foreach ($fieldMapping as $source => $target) {
            if (isset($data[$source])) {
                $formattedData['smart_link'][$target] = $data[$source];
            }
        }

        // Add device info if available
        if (isset($data['deviceInfo'])) {
            $device = $data['deviceInfo'];
            $formattedData['smart_link']['device_type'] = $device->deviceType ?? null;
            $formattedData['smart_link']['os'] = $device->osName ?? null;
            $formattedData['smart_link']['os_version'] = $device->osVersion ?? null;
            $formattedData['smart_link']['browser'] = $device->browser ?? null;
            $formattedData['smart_link']['browser_version'] = $device->browserVersion ?? null;
            $formattedData['smart_link']['is_mobile'] = $device->isMobile ?? null;
            $formattedData['smart_link']['is_tablet'] = $device->isTablet ?? null;
        }

        // Add geographic data if available
        if (isset($data['country'])) {
            $formattedData['smart_link']['country'] = $data['country'];
        }
        if (isset($data['city'])) {
            $formattedData['smart_link']['city'] = $data['city'];
        }

        // Clean up null values
        $formattedData['smart_link'] = array_filter(
            $formattedData['smart_link'],
            fn($value) => $value !== null
        );

        return $formattedData;
    }

    /**
     * Check if specific event type should be tracked
     *
     * @param string $eventType
     * @return bool
     */
    protected function shouldTrackEvent(string $eventType): bool
    {
        $settings = SmartLinkManager::getInstance()->getSettings();
        $trackingEvents = $settings->seomaticTrackingEvents ?? [];

        return in_array($eventType, $trackingEvents, true);
    }

    /**
     * Safe plugin check helper
     *
     * @param string $pluginHandle
     * @return bool
     */
    protected function isPluginInstalled(string $pluginHandle): bool
    {
        return PluginHelper::isPluginEnabled($pluginHandle);
    }

    // Abstract methods that must be implemented by child classes

    abstract public function isAvailable(): bool;

    abstract public function pushEvent(string $eventType, array $data): bool;

    abstract public function getStatus(): array;
}
