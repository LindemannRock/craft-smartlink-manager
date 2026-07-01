<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025-2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\widgets;

use Craft;
use craft\base\Widget;
use lindemannrock\base\helpers\DateRangeHelper;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * SmartLink Manager Top Performing Links Widget
 *
 * @since 5.3.0
 */
class TopLinksWidget extends Widget
{
    use SiteFilterTrait;

    /**
     * @var string Date range for analytics
     */
    public string $dateRange = 'last7days';

    /**
     * @var int Number of links to show
     */
    public int $limit = 5;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['dateRange'], 'in', 'range' => array_keys(DateRangeHelper::getOptions('assoc'))];
        $rules[] = [['siteId'], 'in', 'range' => array_column($this->siteOptions(), 'value')];
        $rules[] = [['dateRange'], 'default', 'value' => 'last7days'];
        $rules[] = [['siteId'], 'default', 'value' => 'all'];
        $rules[] = [['limit'], 'integer', 'min' => 1, 'max' => 20];
        $rules[] = [['limit'], 'default', 'value' => 5];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $pluginName = SmartLinkManager::$plugin->getSettings()->getFullName();
        return Craft::t('smartlink-manager', '{pluginName} - Top Links', ['pluginName' => $pluginName]);
    }

    /**
     * @inheritdoc
     */
    public static function isSelectable(): bool
    {
        return parent::isSelectable() &&
            Craft::$app->getUser()->checkPermission('smartLinkManager:viewAnalytics');
    }

    /**
     * @inheritdoc
     */
    public static function icon(): ?string
    {
        return '@lindemannrock/smartlinkmanager/icon-mask.svg';
    }

    /**
     * @inheritdoc
     */
    public static function maxColspan(): ?int
    {
        return 2;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        $pluginName = SmartLinkManager::$plugin->getSettings()->getFullName();
        return Craft::t('smartlink-manager', '{pluginName} - Top Links', ['pluginName' => $pluginName]);
    }

    /**
     * @inheritdoc
     */
    public function getSubtitle(): ?string
    {
        $labels = DateRangeHelper::getOptions('assoc');

        return $labels[$this->dateRange] ?? $labels['last7days'];
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('smartlink-manager/widgets/top-links/settings', [
            'widget' => $this,
            'siteOptions' => $this->siteOptions(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml(): ?string
    {
        // Check permission
        if (!Craft::$app->getUser()->checkPermission('smartLinkManager:viewAnalytics')) {
            return '<p class="light">' . Craft::t('smartlink-manager', 'You don\'t have permission to view analytics.') . '</p>';
        }

        // Check if analytics are enabled
        if (!SmartLinkManager::$plugin->getSettings()->enableAnalytics) {
            return '<p class="light">' . Craft::t('smartlink-manager', 'Analytics are disabled in plugin settings.') . '</p>';
        }

        $analyticsData = SmartLinkManager::$plugin->analytics->getAnalyticsSummary($this->dateRange, null, $this->effectiveSiteId());
        $topLinks = array_slice($analyticsData['topLinks'] ?? [], 0, $this->limit);

        return Craft::$app->getView()->renderTemplate('smartlink-manager/widgets/top-links/body', [
            'widget' => $this,
            'links' => $topLinks,
        ]);
    }
}
