<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025-2026 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\models\Site;
use craft\web\Controller;
use lindemannrock\base\helpers\SafeSegmentHelper;
use lindemannrock\base\helpers\UrlSafetyHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\helpers\PublicSiteHelper;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * QR Code Controller
 * Handles QR code generation for smart links
 *
 * @since 1.0.0
 */
class QrCodeController extends Controller
{
    use LoggingTrait;
    /**
     * @var array<int|string>|bool|int Allow anonymous access
     */
    protected array|int|bool $allowAnonymous = true;

    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    /**
     * Display QR code page for smart link
     *
     * @param string $slug
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDisplay(string $slug, ?string $siteHandle = null): Response
    {
        // Stored slugs are lowercase-normalized at write; lowercase the probe so
        // the lookup stays case-insensitive on PostgreSQL too.
        $slug = strtolower(trim($slug));
        $site = $this->resolveSite($siteHandle);
        if (!$site) {
            throw new NotFoundHttpException('QR code not found.');
        }

        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings->isSiteEnabled($site->id)) {
            return $this->redirectToNotFound();
        }

        $smartLink = $this->resolveAvailablePublicLink($slug, $site);
        if (!$smartLink->qrCodeEnabled) {
            return $this->redirectToNotFound();
        }

        $options = $this->canonicalOptions($smartLink);
        $size = (int)$options['size'];
        $format = (string)$options['format'];

        // Generate full URL for the smart link with QR tracking parameter
        $url = $smartLink->getRedirectUrl();

        $this->logDebug('SmartLink redirect URL (display)', ['url' => $url]);

        // The redirect URL should already be a full URL from UrlHelper::siteUrl()
        // Add the QR source parameter to track QR code scans
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $fullUrl = $url . $separator . 'src=qr';

        // Note: Tracking is handled client-side via JavaScript (redirect-tracking.js)
        // QR codes contain static URLs - no cache busting needed

        $this->logDebug('Full URL for QR', ['fullUrl' => $fullUrl]);

        try {
            $qrCode = SmartLinkManager::$plugin->qrCode->generateQrCode($fullUrl, $options);
            
            // Prepare template variables
            $templateVars = [
                'smartLink' => $smartLink,
                'size' => $size,
                'format' => $format,
            ];
            
            if ($format === 'svg') {
                $templateVars['qrCodeSvg'] = $qrCode;
            } else {
                $templateVars['qrCodeData'] = base64_encode($qrCode);
            }

            // Get custom template path from settings
            $settings = SmartLinkManager::$plugin->getSettings();
            $template = $settings->getResolvedQrTemplate();

            SmartLinkManager::$plugin->integration->prepareSeomaticMetadata($smartLink);

            return $this->renderTemplate($template, $templateVars);
        } catch (\Throwable $e) {
            $this->logError('Failed to generate QR code', [
                'format' => $format,
                'error' => $e->getMessage(),
            ]);
            throw new ServerErrorHttpException('QR code generation failed.');
        }
    }

    /**
     * Generate QR code for smart link
     *
     * @param string|null $slug
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGenerate(?string $slug = null, ?string $siteHandle = null): Response
    {
        $request = Craft::$app->request;
        $settings = SmartLinkManager::$plugin->getSettings();
        $isSettingsPreview = $slug === null
            && $this->queryFlag('preview')
            && is_string($request->getQueryParam('url'));
        $linkId = $slug === null ? $this->queryScalar('linkId') : null;
        $isExistingLinkMode = $linkId !== null;
        $isAuthenticatedMode = $isSettingsPreview || $isExistingLinkMode;
        $isDownload = $this->queryFlag('download');

        if ($isAuthenticatedMode) {
            $this->requireLogin();
            $this->requirePermission('smartLinkManager:editLinks');
        }

        if ($isSettingsPreview) {
            $url = (string)$request->getQueryParam('url');
            $scheme = parse_url($url, PHP_URL_SCHEME);
            if (!in_array(strtolower($scheme ?? ''), ['http', 'https'], true)) {
                throw new BadRequestHttpException('Only http and https URLs are allowed.');
            }

            $smartLink = null;
            $fullUrl = $url;
            $options = $this->authenticatedOptions();
            $options['_cache'] = false;
        } elseif ($isExistingLinkMode) {
            $smartLink = $this->resolveExistingLink($linkId, $siteHandle);
            if (!$settings->isSiteEnabled($smartLink->siteId) || !$smartLink->qrCodeEnabled) {
                throw new NotFoundHttpException('QR code not found.');
            }
            if ($isDownload && !$settings->enableQrDownload) {
                throw new NotFoundHttpException('QR code not found.');
            }

            $fullUrl = $this->trackedUrl($smartLink);
            $options = $this->authenticatedOptions();
            $options['size'] = $isDownload
                ? $this->normalizeExportSize($this->queryScalar('size'), (int)$smartLink->qrCodeSize)
                : 150;
            $options['_cache'] = false;
            $options['_sizeMax'] = 4096;
        } else {
            if ($slug === null || trim($slug) === '') {
                throw new NotFoundHttpException('Smart link not specified.');
            }

            $slug = strtolower(trim($slug));
            $site = $this->resolveSite($siteHandle);
            if (!$site) {
                throw new NotFoundHttpException('QR code not found.');
            }
            if (!$settings->isSiteEnabled($site->id)) {
                return $this->redirectToNotFound();
            }

            $smartLink = $this->resolveAvailablePublicLink($slug, $site);
            if (!$smartLink->qrCodeEnabled) {
                return $this->redirectToNotFound();
            }

            $fullUrl = $this->trackedUrl($smartLink);
            $options = $this->canonicalOptions($smartLink);
        }

        $format = SmartLinkManager::$plugin->qrCode->normalizeFormat($options['format'] ?? null);
        $options['format'] = $format;

        // Generate QR code
        try {
            $qrCode = SmartLinkManager::$plugin->qrCode->generateQrCode($fullUrl, $options);

            $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';

            // Return response
            $response = Craft::$app->response;
            $response->format = Response::FORMAT_RAW;
            $response->headers->set('Content-Type', $contentType);
            $response->headers->set(
                'Cache-Control',
                $isAuthenticatedMode ? 'private, no-store, no-cache, must-revalidate, max-age=0' : 'public, max-age=86400',
            );
            
            // Handle download request
            if ($isDownload && $smartLink && $settings->enableQrDownload) {
                $filename = strtr($settings->qrDownloadFilename, [
                    '{slug}' => $smartLink->slug,
                    '{size}' => (string)$options['size'],
                    '{format}' => $format,
                ]);
                $filename = SafeSegmentHelper::filenamePart($filename, 'qr-code', [
                    'allowDots' => true,
                ]);
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.' . $format . '"');
            }
            
            $response->content = $qrCode;
            
            return $response;
        } catch (\Throwable $e) {
            $this->logError('Failed to generate QR code', [
                'format' => $format,
                'error' => $e->getMessage(),
            ]);
            throw new ServerErrorHttpException('QR code generation failed.');
        }
    }

    /**
     * Return the saved QR configuration used by anonymous image and display routes.
     *
     * @return array<string, mixed>
     */
    private function canonicalOptions(SmartLink $smartLink): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $options = [
            'size' => $smartLink->qrCodeSize ?: $settings->defaultQrSize,
            'color' => str_replace('#', '', $smartLink->qrCodeColor ?: $settings->defaultQrColor),
            'bg' => str_replace('#', '', $smartLink->qrCodeBgColor ?: $settings->defaultQrBgColor),
            'format' => SmartLinkManager::$plugin->qrCode->normalizeFormat($smartLink->qrCodeFormat ?: $settings->defaultQrFormat),
            'errorCorrection' => $settings->defaultQrErrorCorrection,
            'margin' => $settings->defaultQrMargin,
            'moduleStyle' => $settings->qrModuleStyle,
            'eyeStyle' => $settings->qrEyeStyle,
            'eyeColor' => $smartLink->qrCodeEyeColor
                ? str_replace('#', '', $smartLink->qrCodeEyeColor)
                : ($settings->qrEyeColor ? str_replace('#', '', $settings->qrEyeColor) : null),
        ];

        if ($settings->enableQrLogo) {
            $logoId = $smartLink->qrLogoId ?: $settings->defaultQrLogoId;
            if ($logoId) {
                $options['logo'] = $logoId;
            }
        }

        return array_filter($options, static fn(mixed $value): bool => $value !== null);
    }

    /**
     * Return authenticated request styling after scalar and logo access checks.
     *
     * @return array<string, mixed>
     */
    private function authenticatedOptions(): array
    {
        $settings = SmartLinkManager::$plugin->getSettings();
        $logoId = $this->queryScalar('logo');

        if ($logoId !== null) {
            $logoAsset = $this->resolvePreviewLogoAsset($logoId);
            $allowedVolumeUids = array_filter([
                $settings->qrLogoVolumeUid,
                $settings->imageVolumeUid,
            ]);
            $volumeUid = $logoAsset?->getVolume()->uid;
            if (!$logoAsset
                || ($allowedVolumeUids && !in_array($volumeUid, $allowedVolumeUids, true))
                || !$this->canViewAssetVolume($volumeUid)
            ) {
                $logoId = null;
            }
        }

        $options = [
            'size' => $this->queryScalar('size'),
            'color' => $this->queryScalar('color'),
            'bg' => $this->queryScalar('bg'),
            'format' => $this->queryScalar('format'),
            'margin' => $this->queryScalar('margin'),
            'moduleStyle' => $this->queryScalar('moduleStyle'),
            'eyeStyle' => $this->queryScalar('eyeStyle'),
            'eyeColor' => $this->queryScalar('eyeColor'),
            'logo' => $logoId,
            'logoSize' => $this->queryScalar('logoSize'),
            'errorCorrection' => $this->queryScalar('errorCorrection'),
        ];

        return array_filter($options, static fn(mixed $value): bool => $value !== null);
    }

    private function queryScalar(string $name): string|int|float|bool|null
    {
        $value = Craft::$app->getRequest()->getQueryParam($name);

        return is_scalar($value) ? $value : null;
    }

    private function queryFlag(string $name): bool
    {
        $value = $this->queryScalar($name);

        return $value !== null && !in_array($value, ['', '0', 0, false], true);
    }

    private function normalizeExportSize(string|int|float|bool|null $size, int $fallback): int
    {
        if (!is_numeric($size)) {
            $size = $fallback;
        }

        return max(100, min(4096, (int)$size));
    }

    private function resolveExistingLink(string|int|float|bool $linkId, ?string $siteHandle): SmartLink
    {
        if (!is_numeric($linkId) || (int)$linkId < 1) {
            throw new NotFoundHttpException('QR code not found.');
        }

        $query = SmartLink::find()
            ->id((int)$linkId)
            ->status(null);

        if ($siteHandle !== null) {
            $site = $this->resolveSite($siteHandle);
            if (!$site) {
                throw new NotFoundHttpException('QR code not found.');
            }
            $query->siteId($site->id);
        } else {
            $query->site('*');
        }

        $smartLink = $query->one();
        if (!$smartLink instanceof SmartLink || $smartLink->trashed) {
            throw new NotFoundHttpException('QR code not found.');
        }

        return $smartLink;
    }

    private function resolveAvailablePublicLink(string $slug, Site $site): SmartLink
    {
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->siteId($site->id)
            ->status(null)
            ->one();

        if (!$smartLink instanceof SmartLink
            || $smartLink->trashed
            || $smartLink->getStatus() !== SmartLink::STATUS_ENABLED
        ) {
            throw new NotFoundHttpException('QR code not found.');
        }

        return $smartLink;
    }

    private function trackedUrl(SmartLink $smartLink): string
    {
        $url = $smartLink->getRedirectUrl();
        $separator = str_contains($url, '?') ? '&' : '?';

        return $url . $separator . 'src=qr';
    }

    /**
     * Resolve request site from a route identifier or configured public host.
     */
    private function resolveSite(?string $siteHandle): ?Site
    {
        if ($siteHandle) {
            return PublicSiteHelper::resolveIdentifier($siteHandle);
        }

        return PublicSiteHelper::resolveConfiguredRequest()
            ?? Craft::$app->getSites()->getCurrentSite();
    }

    /**
     * Resolve a preview logo through Craft's Asset element query.
     */
    protected function resolvePreviewLogoAsset(mixed $logoId): ?\craft\elements\Asset
    {
        $asset = \craft\elements\Asset::find()->id($logoId)->one();

        return $asset instanceof \craft\elements\Asset ? $asset : null;
    }

    /**
     * Check whether the current user may view a preview logo's volume.
     */
    protected function canViewAssetVolume(?string $volumeUid): bool
    {
        return Craft::$app->getUser()->checkPermission('viewAssets:' . $volumeUid);
    }

    /**
     * Redirect to configured not-found destination.
     */
    private function redirectToNotFound(): Response
    {
        $settings = SmartLinkManager::$plugin->getSettings();

        return $this->redirect(
            UrlSafetyHelper::sanitizeRedirectUrl($settings->getResolvedNotFoundRedirectUrl())
        );
    }
}
