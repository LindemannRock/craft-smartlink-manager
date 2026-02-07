<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\widgets;

use Craft;
use craft\base\Widget;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * SmartLink Manager Analytics Summary Widget
 *
 * @since 1.0.0
 */
class AnalyticsSummaryWidget extends Widget
{
    /**
     * @var string Date range for analytics
     */
    public string $dateRange = 'last7days';

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['dateRange'], 'string'];
        $rules[] = [['dateRange'], 'default', 'value' => 'last7days'];
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        $pluginName = SmartLinkManager::$plugin->getSettings()->getFullName();
        return Craft::t('smartlink-manager', '{pluginName} - Analytics', ['pluginName' => $pluginName]);
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
        return '@app/icons/chart-line.svg';
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
        return Craft::t('smartlink-manager', '{pluginName} - Analytics', ['pluginName' => $pluginName]);
    }

    /**
     * @inheritdoc
     */
    public function getSubtitle(): ?string
    {
        $labels = [
            'today' => Craft::t('smartlink-manager', 'Today'),
            'yesterday' => Craft::t('smartlink-manager', 'Yesterday'),
            'last7days' => Craft::t('smartlink-manager', 'Last 7 days'),
            'last30days' => Craft::t('smartlink-manager', 'Last 30 days'),
            'last90days' => Craft::t('smartlink-manager', 'Last 90 days'),
            'all' => Craft::t('smartlink-manager', 'All time'),
        ];

        return $labels[$this->dateRange] ?? Craft::t('smartlink-manager', 'Last 7 days');
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('smartlink-manager/widgets/analytics-summary/settings', [
            'widget' => $this,
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

        // Get analytics data scoped to user's editable sites
        $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();
        $analyticsData = SmartLinkManager::$plugin->analytics->getAnalyticsSummary($this->dateRange, null, $editableSiteIds);

        return Craft::$app->getView()->renderTemplate('smartlink-manager/widgets/analytics-summary/body', [
            'widget' => $this,
            'data' => $analyticsData,
        ]);
    }
}
