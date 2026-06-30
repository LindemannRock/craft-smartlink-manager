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
 * @since 5.31.1
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
     * Render the client-side tracked redirect script.
     *
     * @param string $autoRedirectUrl Server-side resolver URL
     * @param bool|null $allowDebugOverride Whether ?debug=1 should stop redirects; null limits it to devMode
     * @return Markup|null HTML script tag or null if rendering fails
     * @since 5.33.0
     */
    public function renderRedirectScript(string $autoRedirectUrl, ?bool $allowDebugOverride = null): ?Markup
    {
        $view = Craft::$app->getView();
        $oldMode = $view->getTemplateMode();

        try {
            $view->setTemplateMode(View::TEMPLATE_MODE_CP);

            $html = $view->renderTemplate('smartlink-manager/_frontend/redirect', [
                'autoRedirectUrl' => $autoRedirectUrl,
                'skipDebugRedirect' => $allowDebugOverride ?? Craft::$app->getConfig()->getGeneral()->devMode,
            ]);

            return new Markup($html, 'UTF-8');
        } catch (\Throwable $e) {
            $this->logError('Failed to render redirect script', [
                'error' => $e->getMessage(),
                'autoRedirectUrl' => $autoRedirectUrl,
            ]);

            return null;
        } finally {
            $view->setTemplateMode($oldMode);
        }
    }
}
