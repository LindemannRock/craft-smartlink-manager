<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\jobs;

use Craft;
use craft\queue\BaseJob;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Track Analytics Job
 *
 * @since 1.0.0
 */
class TrackAnalyticsJob extends BaseJob
{
    use LoggingTrait;

    /**
     * @var int Smart link ID
     */
    public int $linkId;

    /**
     * @var array Device info
     */
    public array $deviceInfo = [];

    /**
     * @var array Additional metadata
     */
    public array $metadata = [];

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        // Save analytics (IP is already in metadata from the service)
        $result = SmartLinkManager::$plugin->analytics->saveAnalytics(
            $this->linkId,
            $this->deviceInfo,
            $this->metadata
        );

        if (!$result) {
            $this->logError('Failed to save analytics for link', ['linkId' => $this->linkId]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('smartlink-manager', 'Tracking analytics for {pluginName} {id}', [
            'pluginName' => SmartLinkManager::$plugin->getSettings()->getLowerDisplayName(),
            'id' => $this->linkId,
        ]);
    }
}
