<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\base\Element;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use lindemannrock\base\helpers\CpNavHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\Response;

/**
 * Smartlinks Controller
 *
 * @since 1.0.0
 */
class SmartlinksController extends Controller
{
    use LoggingTrait;
    /**
     * @var array<int|string>|bool|int
     */
    protected array|bool|int $allowAnonymous = false;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * List all links (element index)
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionIndex(): Response
    {
        $user = Craft::$app->getUser();
        $settings = SmartLinkManager::$plugin->getSettings();

        // If user doesn't have viewLinks permission, redirect to first accessible section
        if (!$user->checkPermission('smartLinkManager:viewLinks')) {
            $sections = SmartLinkManager::$plugin->getCpSections($settings, false, true);
            $route = CpNavHelper::firstAccessibleRoute($user, $settings, $sections);
            if ($route) {
                return $this->redirect($route);
            }
            // No access at all
            $this->requirePermission('smartLinkManager:viewLinks');
        }

        // Get current site from request or Craft's current site
        $siteHandle = $this->request->getParam('site');
        $currentSite = $siteHandle
            ? Craft::$app->getSites()->getSiteByHandle($siteHandle)
            : Craft::$app->getSites()->getCurrentSite();

        // If current site is not enabled, redirect to first enabled site
        if (!$settings->isSiteEnabled($currentSite->id)) {
            $enabledSiteIds = $settings->getEnabledSiteIds();
            if (!empty($enabledSiteIds)) {
                $firstEnabledSite = Craft::$app->getSites()->getSiteById($enabledSiteIds[0]);
                if ($firstEnabledSite) {
                    return $this->redirect('smartlink-manager?site=' . $firstEnabledSite->handle);
                }
            }
        }

        return $this->renderTemplate('smartlink-manager/smartlinks/index');
    }

    /**
     * Edit a smart link
     *
     * @param int|null $smartLinkId
     * @param SmartLink|null $smartLink
     * @return Response
     * @since 1.0.0
     */
    public function actionEdit(?int $smartLinkId = null, ?SmartLink $smartLink = null): Response
    {
        $this->requirePermission('smartLinkManager:viewLinks');

        $variables = [
            'smartLinkId' => $smartLinkId,
            'smartLink' => $smartLink,
        ];

        // Get the site
        $site = Craft::$app->getRequest()->getQueryParam('site');
        if ($site) {
            $site = is_numeric($site) ? Craft::$app->getSites()->getSiteById($site) : Craft::$app->getSites()->getSiteByHandle($site);
            if (!$site) {
                throw new \yii\web\BadRequestHttpException('Invalid site handle: ' . $site);
            }
        } else {
            $site = Craft::$app->getSites()->getCurrentSite();
        }

        // Check if SmartLink Manager is enabled for this site
        $settings = SmartLinkManager::getInstance()->getSettings();
        if (!$settings->isSiteEnabled($site->id)) {
            throw new \yii\web\ForbiddenHttpException('SmartLink Manager is not enabled for this site.');
        }

        // Get the smart link
        if ($smartLinkId !== null) {
            if ($smartLink === null) {
                // Try to find the element
                $smartLink = SmartLink::find()
                    ->id($smartLinkId)
                    ->siteId($site->id)
                    ->status(null)
                    ->trashed(null)
                    ->one();

                if (!$smartLink) {
                    throw new \yii\web\NotFoundHttpException('Smart link not found');
                }

                // Don't allow editing trashed elements
                if ($smartLink->trashed) {
                    Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Cannot edit trashed smart links.'));
                    return $this->redirect('smartlink-manager');
                }
            }

            $this->requirePermission('smartLinkManager:editLinks');

            // Set the title
            $variables['title'] = $smartLink->title;
        } else {
            $this->requirePermission('smartLinkManager:createLinks');

            if ($smartLink === null) {
                $smartLink = new SmartLink();
                $smartLink->siteId = $site->id;

                // Set default QR code values from settings
                $settings = SmartLinkManager::$plugin->getSettings();
                $smartLink->qrCodeSize = $settings->defaultQrSize;
                $smartLink->qrCodeColor = $settings->defaultQrColor;
                $smartLink->qrCodeBgColor = $settings->defaultQrBgColor;
            }

            $variables['title'] = Craft::t('smartlink-manager', 'Create a new smart link');
        }

        $variables['smartLink'] = $smartLink;
        $variables['fullPageForm'] = true;
        $variables['saveShortcutRedirect'] = 'smartlink-manager/smartlinks/{id}';
        $variables['continueEditingUrl'] = 'smartlink-manager/smartlinks/{id}';

        // Breadcrumbs
        $variables['crumbs'] = [
            [
                'label' => SmartLinkManager::$plugin->getSettings()->getFullName(),
                'url' => 'smartlink-manager',
            ],
        ];

        // Set the base CP edit URL
        $variables['baseCpEditUrl'] = 'smartlink-manager/smartlinks/{id}';

        // Pass analytics service to the template
        // Always pass analytics service if the smart link exists and analytics is enabled
        $plugin = SmartLinkManager::getInstance();
        if ($smartLink->id && $plugin && $plugin->getSettings()->enableAnalytics) {
            $variables['analyticsService'] = $plugin->analytics;
        }

        // Pass enabled sites for site switcher
        $variables['enabledSites'] = $plugin->getEnabledSites();

        return $this->renderTemplate('smartlink-manager/smartlinks/edit', $variables);
    }

    /**
     * Save a smart link
     *
     * @return Response|null
     * @since 1.0.0
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        try {
            $request = Craft::$app->getRequest();


            $smartLinkId = $request->getBodyParam('smartLinkId');
            $siteId = $request->getBodyParam('siteId');

            // Get the smart link
            if ($smartLinkId) {
                $smartLink = SmartLinkManager::$plugin->smartLinks->getSmartLinkById($smartLinkId, $siteId);

                if (!$smartLink) {
                    throw new \yii\web\NotFoundHttpException('Smart link not found');
                }

                $this->requirePermission('smartLinkManager:editLinks');
            } else {
                $this->requirePermission('smartLinkManager:createLinks');
                $smartLink = new SmartLink();
                $smartLink->siteId = $siteId ?? Craft::$app->getSites()->getPrimarySite()->id;
            }

            // Set non-translatable attributes (main table)
            $smartLink->title = $request->getBodyParam('title');
            $smartLink->slug = $request->getBodyParam('slug');
            $smartLink->description = $request->getBodyParam('description');

            // Handle authorId - elementSelectField returns an array
            $authorIds = $request->getBodyParam('authorId');
            $smartLink->authorId = is_array($authorIds) ? ($authorIds[0] ?? null) : $authorIds;
            $smartLink->trackAnalytics = (bool)$request->getBodyParam('trackAnalytics');
            $smartLink->hideTitle = (bool)$request->getBodyParam('hideTitle');

            $smartLink->qrCodeEnabled = (bool)$request->getBodyParam('qrCodeEnabled');
            $smartLink->qrCodeSize = $request->getBodyParam('qrCodeSize') ?: 200;

            // Fix color values - ensure they have # prefix, or set to null if empty
            $qrCodeColor = $request->getBodyParam('qrCodeColor');
            $smartLink->qrCodeColor = $qrCodeColor ? (strpos($qrCodeColor, '#') === 0 ? $qrCodeColor : '#' . $qrCodeColor) : null;

            $qrCodeBgColor = $request->getBodyParam('qrCodeBgColor');
            $smartLink->qrCodeBgColor = $qrCodeBgColor ? (strpos($qrCodeBgColor, '#') === 0 ? $qrCodeBgColor : '#' . $qrCodeBgColor) : null;

            // QR code eye color (can be empty)
            $qrCodeEyeColor = $request->getBodyParam('qrCodeEyeColor');
            $smartLink->qrCodeEyeColor = $qrCodeEyeColor ? (strpos($qrCodeEyeColor, '#') === 0 ? $qrCodeEyeColor : '#' . $qrCodeEyeColor) : null;

            // QR code format (empty string means use default, store as null)
            $qrCodeFormat = $request->getBodyParam('qrCodeFormat');
            $smartLink->qrCodeFormat = $qrCodeFormat ? $qrCodeFormat : null;

            // QR logo (elementSelectField returns an array)
            $qrLogoIds = $request->getBodyParam('qrLogoId');
            $smartLink->qrLogoId = is_array($qrLogoIds) ? ($qrLogoIds[0] ?? null) : (empty($qrLogoIds) ? null : (int)$qrLogoIds);

            // Smart Link image (elementSelectField returns an array)
            $imageIds = $request->getBodyParam('imageId');
            $smartLink->imageId = is_array($imageIds) ? ($imageIds[0] ?? null) : (empty($imageIds) ? null : (int)$imageIds);

            // Smart Link image size
            $smartLink->imageSize = $request->getBodyParam('imageSize', 'xl');

            $smartLink->languageDetection = (bool)$request->getBodyParam('languageDetection');

            // Handle enabled status - set BEFORE setFieldValuesFromRequest
            // This is per-site and managed by Craft's element system
            $enabledParam = $request->getBodyParam('enabled');
            $enabled = $enabledParam === '1' || $enabledParam === 1 || $enabledParam === true;

            // Set enabled ONLY for the current site being edited
            $smartLink->setEnabledForSite($enabled);

            // Set translatable attributes (content table) - these need to be set on the element
            $smartLink->iosUrl = $request->getBodyParam('iosUrl');
            $smartLink->androidUrl = $request->getBodyParam('androidUrl');
            $smartLink->huaweiUrl = $request->getBodyParam('huaweiUrl');
            $smartLink->amazonUrl = $request->getBodyParam('amazonUrl');
            $smartLink->windowsUrl = $request->getBodyParam('windowsUrl');
            $smartLink->macUrl = $request->getBodyParam('macUrl');
            $smartLink->fallbackUrl = $request->getBodyParam('fallbackUrl');

            // Set field values
            $smartLink->setFieldValuesFromRequest('fields');

            // Handle dates
            $postDate = $request->getBodyParam('postDate');
            if ($postDate) {
                $dateTime = DateTimeHelper::toDateTime($postDate, true);
                $smartLink->postDate = $dateTime !== false ? $dateTime : null;
            }

            $expiryDate = $request->getBodyParam('expiryDate');
            if ($expiryDate) {
                $dateTime = DateTimeHelper::toDateTime($expiryDate, true);
                $smartLink->dateExpired = $dateTime !== false ? $dateTime : null;
            }

            // Save it
            if (!SmartLinkManager::$plugin->smartLinks->saveSmartLink($smartLink)) {
                $this->logError('Smart link save failed', ['errors' => $smartLink->getErrors()]);
                // If it's an AJAX request, return JSON response
                if ($this->request->getAcceptsJson()) {
                    return $this->asModelFailure(
                    $smartLink,
                    Craft::t('smartlink-manager', 'Couldn\'t save smart link.'),
                    'smartLink'
                );
                }

                // Otherwise, set error flash and re-render the template
                Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Couldn\'t save smart link.'));

                // Set route params so Craft can re-render the template with errors
                Craft::$app->getUrlManager()->setRouteParams([
                'smartLink' => $smartLink,
                'title' => $smartLink->id ? $smartLink->title : Craft::t('smartlink-manager', 'New smart link'),
            ]);

                return null;
            }

            // Clear ALL caches for this element across all sites
            Craft::$app->getElements()->invalidateCachesForElement($smartLink);

            // Reload the element in the correct site context for the response
            // This ensures the notification chip shows the correct enabled status
            $smartLink = SmartLink::find()
            ->id($smartLink->id)
            ->siteId($smartLink->siteId)
            ->status(null)
            ->one();

            return $this->asModelSuccess(
            $smartLink,
            Craft::t('smartlink-manager', 'Smart link saved.'),
            'smartLink'
        );
        } catch (\Exception $e) {
            $this->logError('Smart link save error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // Return error response
            Craft::$app->getSession()->setError('Error saving smart link: ' . $e->getMessage());

            $plugin = SmartLinkManager::getInstance();

            return $this->renderTemplate('smartlink-manager/smartlinks/edit', [
                'smartLink' => $smartLink ?? new SmartLink(),
                'title' => Craft::t('smartlink-manager', 'New smart link'),
                'enabledSites' => $plugin->getEnabledSites(),
                'analyticsService' => $plugin->analytics,
            ]);
        }
    }

    /**
     * Delete a smart link
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $this->requirePermission('smartLinkManager:deleteLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');
        $smartLink = SmartLinkManager::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        if (!SmartLinkManager::$plugin->smartLinks->deleteSmartLink($smartLink)) {
            return $this->asFailure(Craft::t('smartlink-manager', 'Couldn\'t delete smart link.'));
        }

        return $this->asSuccess(Craft::t('smartlink-manager', 'Smart link deleted.'));
    }


    /**
     * Restore a soft-deleted smart link
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionRestore(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('smartLinkManager:editLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        // Find the trashed smart link
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->trashed(true)
            ->status(null)
            ->one();

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        // Restore the element
        if (!Craft::$app->elements->restoreElement($smartLink)) {
            return $this->asFailure(Craft::t('smartlink-manager', 'Couldn\'t restore smart link.'));
        }

        return $this->asSuccess(Craft::t('smartlink-manager', 'Smart link restored.'));
    }

    /**
     * Permanently delete a smart link
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionHardDelete(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requirePermission('smartLinkManager:deleteLinks');

        $smartLinkId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        // Find the smart link (including trashed)
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->trashed(null)
            ->status(null)
            ->one();

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        // Permanently delete the element
        if (!Craft::$app->elements->deleteElement($smartLink, true)) {
            return $this->asFailure(Craft::t('smartlink-manager', 'Couldn\'t delete smart link permanently.'));
        }

        return $this->asSuccess(Craft::t('smartlink-manager', 'Smart link permanently deleted.'));
    }

    /**
     * Get smart link details
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionGetDetails(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('accessPlugin-smartlink-manager');

        $smartLinkId = Craft::$app->getRequest()->getRequiredParam('id');
        $smartLink = SmartLinkManager::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            return $this->asFailure(Craft::t('smartlink-manager', 'Smart link not found'));
        }

        return $this->asJson([
            'success' => true,
            'smartLink' => [
                'id' => $smartLink->id,
                'name' => $smartLink->title,
                'slug' => $smartLink->slug,
                'description' => $smartLink->description,
                'redirectUrl' => $smartLink->getRedirectUrl(),
                'qrCodeUrl' => $smartLink->getQrCodeUrl(),
                'clicks' => $smartLink->clicks,
                'dateCreated' => DateTimeHelper::toIso8601($smartLink->dateCreated),
                'dateUpdated' => DateTimeHelper::toIso8601($smartLink->dateUpdated),
            ],
        ]);
    }

    /**
     * Generate QR code
     *
     * @return Response
     * @since 1.0.0
     */
    public function actionGenerateQrCode(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePermission('accessPlugin-smartlink-manager');

        $smartLinkId = Craft::$app->getRequest()->getRequiredParam('id');
        $smartLink = SmartLinkManager::$plugin->smartLinks->getSmartLinkById($smartLinkId);

        if (!$smartLink) {
            return $this->asFailure(Craft::t('smartlink-manager', 'Smart link not found'));
        }

        $options = [
            'size' => Craft::$app->getRequest()->getParam('size', 200),
            'format' => Craft::$app->getRequest()->getParam('format', 'png'),
        ];

        try {
            $qrCodeDataUrl = SmartLinkManager::$plugin->smartLinks->generateQrCodeDataUrl($smartLink, $options);

            return $this->asJson([
                'success' => true,
                'qrCode' => $qrCodeDataUrl,
            ]);
        } catch (\Exception $e) {
            return $this->asFailure($e->getMessage());
        }
    }

    /**
     * View revisions for a smart link
     *
     * @param int $smartLinkId
     * @return Response
     * @since 1.0.0
     */
    public function actionRevisions(int $smartLinkId): Response
    {
        $this->requirePermission('smartLinkManager:viewLinks');

        // Get the site
        $site = Craft::$app->getRequest()->getQueryParam('site');
        if ($site) {
            $site = is_numeric($site) ? Craft::$app->getSites()->getSiteById($site) : Craft::$app->getSites()->getSiteByHandle($site);
            if (!$site) {
                throw new \yii\web\BadRequestHttpException('Invalid site handle: ' . $site);
            }
        } else {
            $site = Craft::$app->getSites()->getCurrentSite();
        }

        // Get the smart link
        $smartLink = SmartLink::find()
            ->id($smartLinkId)
            ->siteId($site->id)
            ->status(null)
            ->one();

        if (!$smartLink) {
            throw new \yii\web\NotFoundHttpException('Smart link not found');
        }

        return $this->renderTemplate('smartlink-manager/smartlinks/revisions', [
            'smartLink' => $smartLink,
        ]);
    }
}
