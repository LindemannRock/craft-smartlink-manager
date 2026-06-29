<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\services;

use Craft;
use craft\base\Component;
use craft\web\View;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use Twig\Markup;

/**
 * Frontend rendering helpers for SmartLink templates
 *
 * @since 5.32.0
 */
class FrontendService extends Component
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

    /**
     * Render the cache-safe auto-redirect resolver script
     *
     * @param string $autoRedirectUrl Server-side resolver URL
     * @return Markup|null HTML script tag or null if rendering fails
     */
    public function renderAutoRedirectScript(string $autoRedirectUrl): ?Markup
    {
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();

        try {
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);

            $html = $view->renderTemplate('smartlink-manager/_frontend/auto-redirect', [
                'autoRedirectUrl' => $autoRedirectUrl,
                'skipDebugRedirect' => Craft::$app->getConfig()->getGeneral()->devMode,
            ]);

            return new Markup($html, 'UTF-8');
        } catch (\Throwable $e) {
            $this->logError('Failed to render auto-redirect script', [
                'error' => $e->getMessage(),
                'autoRedirectUrl' => $autoRedirectUrl,
            ]);

            return null;
        } finally {
            $view->setTemplateMode($oldMode);
        }
    }
}
