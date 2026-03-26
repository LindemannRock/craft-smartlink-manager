<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\integrations;

use Craft;
use craft\base\ElementInterface;
use craft\fields\Link;
use craft\fields\linktypes\BaseElementLinkType;
use craft\helpers\Cp;
use craft\helpers\Html;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Smart Link Type for Link Field
 *
 * @since 1.0.0
 */
class SmartLinkType extends BaseElementLinkType
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getDisplayName();
    }

    /**
     * @inheritdoc
     */
    public static function elementType(): string
    {
        return SmartLink::class;
    }

    /**
     * @inheritdoc
     */
    public function inputHtml(Link $field, ?string $value, string $containerId): string
    {
        $id = sprintf('smartlink%s', mt_rand());
        
        // Get the site ID based on the current context
        $siteId = null;
        
        // Try to get site from POST data (when saving)
        if (Craft::$app->getRequest()->getIsPost()) {
            $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        }
        
        // Try to get site from query param (when editing)
        if (!$siteId) {
            $siteHandle = Craft::$app->getRequest()->getQueryParam('site');
            if ($siteHandle) {
                $site = Craft::$app->sites->getSiteByHandle($siteHandle);
                if ($site) {
                    $siteId = $site->id;
                }
            }
        }
        
        // Fall back to current site
        if (!$siteId) {
            $siteId = Craft::$app->sites->currentSite->id;
        }

        // Check if smart links are enabled for this site
        $settings = SmartLinkManager::$plugin->getSettings();
        $enabledSites = $settings->enabledSites ?? [];
        $siteEnabled = empty($enabledSites) || in_array($siteId, $enabledSites, true);

        // Parse the value to get the element
        $smartLink = null;
        if ($value) {
            $matches = [];
            if (preg_match('/^{smartLink:(\d+)(@(\d+))?:url}$/', $value, $matches)) {
                $elementId = $matches[1];
                // Always use current site context (ignore stored siteId)
                $smartLink = SmartLink::find()
                    ->id($elementId)
                    ->siteId($siteId)
                    ->status(null)
                    ->one();
            }
        }

        // Get site for the field
        $currentSite = Craft::$app->sites->getSiteById($siteId);

        // If site is not enabled, show warning
        if (!$siteEnabled) {
            $pluginName = SmartLinkManager::$plugin->getSettings()->getFullName();
            return Html::tag('div',
                Html::tag('p', Craft::t('smartlink-manager', '{pluginName} is not enabled for site "{site}". Enable it in plugin settings to use {pluginNameLower} here.', [
                    'pluginName' => $pluginName,
                    'pluginNameLower' => SmartLinkManager::$plugin->getSettings()->getPluralLowerDisplayName(),
                    'site' => $currentSite->name,
                ]), ['class' => 'warning']),
                ['class' => 'field']
            );
        }

        return Cp::elementSelectFieldHtml([
            'id' => $id,
            'name' => 'value',
            'elements' => $smartLink ? [$smartLink] : [],
            'elementType' => SmartLink::class,
            'sources' => $this->sources,
            'criteria' => [
                'status' => 'enabled',
                'siteId' => $currentSite->id,
            ],
            'single' => true,
            'showSiteMenu' => false,
            'modalSettings' => [
                'defaultSiteId' => $currentSite->id,
                'criteria' => [
                    'siteId' => $currentSite->id,
                ],
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function renderValue(string $value): string
    {
        $element = $this->element($value);
        if (!$element instanceof SmartLink) {
            return '';
        }

        // Get destination URL for current site
        try {
            return $element->getRedirectUrl();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function linkLabel(string $value): string
    {
        $element = $this->element($value);
        return $element instanceof SmartLink ? $element->title : '';
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(ElementInterface|string|int $value): string
    {
        if ($value instanceof SmartLink) {
            return sprintf('{smartLink:%s@%s:url}', $value->id, $value->siteId);
        }
        
        if (is_numeric($value)) {
            // If we get a numeric ID, we need to determine the correct site
            $siteId = $this->detectCurrentSiteId();
            return sprintf('{smartLink:%s@%s:url}', $value, $siteId);
        }
        
        return parent::normalizeValue($value);
    }
    
    /**
     * @inheritdoc
     */
    public function value(mixed $element): ?string
    {
        if ($element instanceof SmartLink) {
            return sprintf('{smartLink:%s@%s:url}', $element->id, $element->siteId);
        }
        return null;
    }
    
    /**
     * Detect the current site ID from the request context
     */
    private function detectCurrentSiteId(): int
    {
        // On frontend, always use the current site
        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            return Craft::$app->getSites()->getCurrentSite()->id;
        }

        // In CP, try POST data first (when saving)
        if (Craft::$app->getRequest()->getIsPost()) {
            $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
            if ($siteId) {
                return (int)$siteId;
            }
        }

        // Try query param (CP editing)
        $siteHandle = Craft::$app->getRequest()->getQueryParam('site');
        if ($siteHandle && $site = Craft::$app->sites->getSiteByHandle($siteHandle)) {
            return $site->id;
        }

        // Default to current site
        return Craft::$app->getSites()->getCurrentSite()->id;
    }
    
    /**
     * @inheritdoc
     */
    public function validateValue(string $value, ?string &$error = null): bool
    {
        // Parse the value to get the element ID
        $matches = [];
        if (!preg_match('/^{smartLink:(\d+)(@(\d+))?:url}$/', $value, $matches)) {
            $error = Craft::t('smartlink-manager', 'Invalid {pluginName} format.', [
                'pluginName' => SmartLinkManager::$plugin->getSettings()->getLowerDisplayName(),
            ]);
            return false;
        }

        $elementId = $matches[1];

        // Use current site context for validation
        $currentSiteId = $this->detectCurrentSiteId();

        $smartLink = SmartLink::find()
            ->id($elementId)
            ->siteId($currentSiteId)
            ->status(null)
            ->one();

        if (!$smartLink) {
            $error = Craft::t('smartlink-manager', '{pluginName} not found.', [
                'pluginName' => SmartLinkManager::$plugin->getSettings()->getDisplayName(),
            ]);
            return false;
        }

        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function isValueEmpty(string $value): bool
    {
        return !$this->element($value);
    }

    /**
     * @inheritdoc
     */
    public function element(?string $value): ?ElementInterface
    {
        if (!$value) {
            return null;
        }

        $matches = [];
        if (!preg_match('/^{smartLink:(\d+)(@(\d+))?:url}$/', $value, $matches)) {
            return null;
        }

        $elementId = $matches[1];

        // Always use CURRENT site context (not the stored siteId)
        // This ensures the smart link adapts to the current site like Entry fields do
        $currentSiteId = $this->detectCurrentSiteId();

        // Check if smart links are enabled for the current site
        $settings = SmartLinkManager::$plugin->getSettings();
        $enabledSites = $settings->enabledSites ?? [];
        $siteEnabled = empty($enabledSites) || in_array($currentSiteId, $enabledSites, true);

        // If site is not enabled, return null (field will be empty)
        if (!$siteEnabled) {
            return null;
        }

        $smartLink = SmartLink::find()
            ->id($elementId)
            ->siteId($currentSiteId)
            ->status(null)
            ->one();

        // If not found for current site, try to find in any enabled site (fallback)
        if (!$smartLink) {
            $smartLink = SmartLink::find()
                ->id($elementId)
                ->siteId('*')
                ->status(null)
                ->one();
        }

        return $smartLink;
    }
}
