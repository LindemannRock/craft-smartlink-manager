<?php
/**
 * LindemannRock SmartLink Manager
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

namespace lindemannrock\smartlinkmanager\tests\Support;

use Craft;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\models\Site;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use RuntimeException;

/**
 * Owns the disposable data for the authenticated multisite HTTP smoke.
 *
 * @since 5.37.4
 */
final class TemplateMultisiteHttpSmokeSetup
{
    /**
     * @return array<string, mixed>
     */
    public static function seed(): array
    {
        $sites = Craft::$app->getSites();
        $primary = $sites->getPrimarySite();
        if ($primary->id === null || $primary->handle === null || $primary->language === null) {
            throw new RuntimeException('The disposable primary site is incomplete.');
        }
        if ($primary->handle === $primary->language) {
            throw new RuntimeException('The disposable primary site handle must differ from its language.');
        }

        $suffix = bin2hex(random_bytes(4));
        $secondary = self::createSite('templateFrench' . $suffix, 'fr-FR', true);
        $craftDisabled = self::createSite('templateDisabled' . $suffix, 'de-DE', false);
        $pluginDisabled = self::createSite('templateOutside' . $suffix, 'ja-JP', true);

        $configPath = Craft::$app->getConfig()->configDir . DIRECTORY_SEPARATOR . 'smartlink-manager.php';
        $config = [
            'enabledSites' => [(int)$primary->id, (int)$secondary->id, (int)$craftDisabled->id],
            'redirectTemplate' => '$SMARTLINK_TEMPLATE_HTTP_REDIRECT',
            'qrTemplate' => '$SMARTLINK_TEMPLATE_HTTP_QR',
            'ipHashSalt' => str_repeat('a', 64),
            'enableAnalytics' => false,
            'enableQrCodeCache' => false,
            'enabledIntegrations' => [],
        ];
        if (file_put_contents($configPath, "<?php\nreturn " . var_export($config, true) . ";\n") === false) {
            throw new RuntimeException('Unable to write the disposable SmartLink configuration.');
        }

        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();
        $globalDir = $templatesPath . DIRECTORY_SEPARATOR . 'http-smoke';
        $primaryDir = $templatesPath . DIRECTORY_SEPARATOR . $primary->handle . DIRECTORY_SEPARATOR . 'http-smoke';
        FileHelper::createDirectory($globalDir);
        FileHelper::createDirectory($primaryDir);
        self::writeTemplate($globalDir . DIRECTORY_SEPARATOR . 'redirect.twig', 'GLOBAL_REDIRECT_TEMPLATE');
        self::writeTemplate($globalDir . DIRECTORY_SEPARATOR . 'qr.twig', 'GLOBAL_QR_TEMPLATE');
        self::writeTemplate($primaryDir . DIRECTORY_SEPARATOR . 'redirect.twig', 'SITE_REDIRECT_TEMPLATE');
        self::writeTemplate($primaryDir . DIRECTORY_SEPARATOR . 'qr.twig', 'SITE_QR_TEMPLATE');

        $primaryLink = self::createLink((int)$primary->id, 'http-smoke-primary-' . $suffix);
        $secondaryLink = self::createLink((int)$secondary->id, 'http-smoke-secondary-' . $suffix);

        return [
            'primarySiteId' => (int)$primary->id,
            'primaryHandle' => $primary->handle,
            'primaryLanguage' => $primary->language,
            'secondarySiteId' => (int)$secondary->id,
            'secondaryHandle' => $secondary->handle,
            'secondaryLanguage' => $secondary->language,
            'craftDisabledSiteId' => (int)$craftDisabled->id,
            'pluginDisabledSiteId' => (int)$pluginDisabled->id,
            'createdSiteIds' => [(int)$secondary->id, (int)$craftDisabled->id, (int)$pluginDisabled->id],
            'elementIds' => [(int)$primaryLink->id, (int)$secondaryLink->id],
            'primarySlug' => $primaryLink->slug,
            'secondarySlug' => $secondaryLink->slug,
            'globalQrPath' => $globalDir . DIRECTORY_SEPARATOR . 'qr.twig',
            'configPath' => $configPath,
            'templateRoots' => [$globalDir, dirname($primaryDir)],
        ];
    }

    /**
     * @param array<string, mixed> $state
     */
    public static function cleanup(array $state): void
    {
        foreach (($state['elementIds'] ?? []) as $elementId) {
            $element = Craft::$app->getElements()->getElementById((int)$elementId, SmartLink::class, null, ['status' => null]);
            if ($element instanceof SmartLink) {
                Craft::$app->getElements()->deleteElement($element, true);
            }
        }

        foreach (array_reverse($state['createdSiteIds'] ?? []) as $siteId) {
            Craft::$app->getSites()->deleteSiteById((int)$siteId);
        }

        foreach (($state['templateRoots'] ?? []) as $path) {
            if (is_string($path)) {
                FileHelper::removeDirectory($path);
            }
        }
        if (isset($state['configPath']) && is_string($state['configPath']) && is_file($state['configPath'])) {
            unlink($state['configPath']);
        }
    }

    private static function createSite(string $handle, string $language, bool $enabled): Site
    {
        $primary = Craft::$app->getSites()->getPrimarySite();
        $site = new Site([
            'uid' => StringHelper::UUID(),
            'name' => 'Template Smoke ' . $handle,
            'handle' => $handle,
            'language' => $language,
            'baseUrl' => '@web/' . $handle . '/',
            'groupId' => $primary->groupId,
            'enabled' => $enabled,
        ]);
        if (!Craft::$app->getSites()->saveSite($site) || $site->id === null) {
            throw new RuntimeException('Unable to create disposable Craft site: ' . implode(', ', $site->getFirstErrors()));
        }
        if ($site->handle === $site->language) {
            throw new RuntimeException('A disposable site handle unexpectedly matches its language.');
        }

        return $site;
    }

    private static function createLink(int $siteId, string $slug): SmartLink
    {
        $link = new SmartLink();
        $link->title = 'Template HTTP Smoke ' . $slug;
        $link->slug = $slug;
        $link->fallbackUrl = 'https://example.com/fallback';
        $link->iosUrl = 'https://example.com/ios';
        $link->siteId = $siteId;
        $link->trackAnalytics = false;
        $link->qrCodeEnabled = true;
        $link->setEnabledForSite(true);
        if (!SmartLinkManager::$plugin->smartLinks->saveSmartLink($link) || $link->id === null) {
            throw new RuntimeException('Unable to create disposable SmartLink: ' . implode(', ', $link->getFirstErrors()));
        }

        return $link;
    }

    private static function writeTemplate(string $path, string $contents): void
    {
        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException('Unable to write disposable template: ' . $path);
        }
    }
}
