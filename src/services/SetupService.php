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
use craft\models\Site;
use craft\web\View;
use lindemannrock\smartlinkmanager\models\Settings;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * Computes setup readiness for SmartLink Manager.
 *
 * @since 5.27.0
 */
class SetupService extends Component
{
    /**
     * @return array{complete: bool, missing: list<string>, setupUrl: string, ipSaltConfigured: bool, templatesReady: bool, templateStatuses: array<int, array{key: string, label: string, setting: string, template: string, source: string, destination: string, destinationDir: string, destinationDirExists: bool, destinationExists: bool, exists: bool, copyable: bool}>}
     */
    public function getStatus(?Settings $settings = null): array
    {
        $settings ??= SmartLinkManager::$plugin->getSettings();
        $ipSaltConfigured = $this->isIpSaltConfigured($settings);
        $templateStatuses = $this->templateStatuses($settings);
        $templatesReady = true;

        foreach ($templateStatuses as $templateStatus) {
            if (!$templateStatus['exists']) {
                $templatesReady = false;
                break;
            }
        }

        $missing = [];
        if (!$ipSaltConfigured) {
            $missing[] = 'ipSalt';
        }
        if (!$templatesReady) {
            $missing[] = 'templates';
        }

        return [
            'complete' => $missing === [],
            'missing' => $missing,
            'setupUrl' => 'smartlink-manager/setup',
            'ipSaltConfigured' => $ipSaltConfigured,
            'templatesReady' => $templatesReady,
            'templateStatuses' => $templateStatuses,
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, setting: string, template: string, source: string, destination: string, destinationDir: string, destinationDirExists: bool, destinationExists: bool, exists: bool, copyable: bool}>
     */
    public function templateStatuses(Settings $settings): array
    {
        $templates = [
            [
                'key' => 'redirect',
                'label' => Craft::t('smartlink-manager', 'Redirect Template'),
                'setting' => 'redirectTemplate',
                'template' => $settings->getResolvedRedirectTemplate(),
                'source' => 'vendor/lindemannrock/craft-smartlink-manager/src/templates/redirect.twig',
            ],
            [
                'key' => 'qr',
                'label' => Craft::t('smartlink-manager', 'QR Code Template'),
                'setting' => 'qrTemplate',
                'template' => $settings->getResolvedQrTemplate(),
                'source' => 'vendor/lindemannrock/craft-smartlink-manager/src/templates/qr.twig',
            ],
        ];

        $statuses = [];
        foreach ($templates as $template) {
            $path = $template['template'];
            $copyable = $this->isTemplatePathAllowed($path);
            $destination = $copyable ? $this->copyDestination($path) : '';
            $destinationDir = $copyable ? $this->destinationDirectory($path) : '';
            $statuses[] = [
                'key' => $template['key'],
                'label' => $template['label'],
                'setting' => $template['setting'],
                'template' => $template['template'],
                'source' => $template['source'],
                'destination' => $destination,
                'destinationDir' => $destinationDir,
                'destinationDirExists' => $copyable && $this->siteTemplateDirectoryExists($destinationDir),
                'destinationExists' => $copyable && $this->siteTemplateFileExists($destination),
                'exists' => $this->siteTemplateExists($path, $settings),
                'copyable' => $copyable,
            ];
        }

        return $statuses;
    }

    public function isIpSaltConfigured(Settings $settings): bool
    {
        $salt = trim((string) ($settings->ipHashSalt ?? ''));

        return $salt !== '' && $salt !== '$SMARTLINK_MANAGER_IP_SALT';
    }

    private function siteTemplateExists(string $template, Settings $settings): bool
    {
        if (!$this->isTemplatePathAllowed($template)) {
            return false;
        }

        $sites = Craft::$app->getSites();
        $enabledSiteIds = array_map('intval', $settings->getEnabledSiteIds());
        $enabledSites = array_values(array_filter(
            $sites->getAllSites(false),
            static fn(Site $site): bool => in_array((int)$site->id, $enabledSiteIds, true),
        ));
        if ($enabledSites === []) {
            return false;
        }

        $hadCurrentSite = $sites->getHasCurrentSite();
        $originalSite = $hadCurrentSite ? $sites->getCurrentSite() : null;
        $originalLanguage = Craft::$app->language;
        $craftSiteExisted = array_key_exists('CRAFT_SITE', $_SERVER);
        $craftSite = $craftSiteExisted ? $_SERVER['CRAFT_SITE'] : null;
        $craftSiteUpperExisted = array_key_exists('CRAFT_SITE_UPPER', $_SERVER);
        $craftSiteUpper = $craftSiteUpperExisted ? $_SERVER['CRAFT_SITE_UPPER'] : null;

        try {
            foreach ($enabledSites as $site) {
                $sites->setCurrentSite($site);
                Craft::$app->language = $site->language;

                // Craft's template-path cache does not include the current site,
                // so each site requires an isolated resolver instance.
                $view = new View();
                if (!$view->doesTemplateExist($template, View::TEMPLATE_MODE_SITE)) {
                    return false;
                }
            }

            return true;
        } finally {
            try {
                $sites->setCurrentSite($hadCurrentSite ? $originalSite : null);
            } finally {
                Craft::$app->language = $originalLanguage;
                $this->restoreServerValue('CRAFT_SITE', $craftSiteExisted, $craftSite);
                $this->restoreServerValue('CRAFT_SITE_UPPER', $craftSiteUpperExisted, $craftSiteUpper);
            }
        }
    }

    private function isTemplatePathAllowed(string $template): bool
    {
        return $template !== '' && !str_contains($template, '..');
    }

    private function restoreServerValue(string $key, bool $existed, mixed $value): void
    {
        if ($existed) {
            $_SERVER[$key] = $value;
        } else {
            unset($_SERVER[$key]);
        }
    }

    private function copyDestination(string $template): string
    {
        $fileName = basename($template);

        return 'templates/' . $template . (pathinfo($fileName, PATHINFO_EXTENSION) === '' ? '.twig' : '');
    }

    private function siteTemplateFileExists(string $destination): bool
    {
        $relativePath = trim(preg_replace('#^templates/?#', '', $destination) ?? '', '/');
        if ($relativePath === '') {
            return false;
        }

        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();

        return is_file(
            $templatesPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath),
        );
    }

    private function destinationDirectory(string $template): string
    {
        $parts = explode('/', $template);
        array_pop($parts);

        return $parts === [] ? 'templates' : 'templates/' . implode('/', $parts);
    }

    private function siteTemplateDirectoryExists(string $destinationDir): bool
    {
        $relativeDir = trim(preg_replace('#^templates/?#', '', $destinationDir) ?? '', '/');
        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();
        $directory = $relativeDir === ''
            ? $templatesPath
            : $templatesPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);

        return is_dir($directory);
    }
}
