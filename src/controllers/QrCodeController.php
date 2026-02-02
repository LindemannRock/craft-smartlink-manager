<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\web\Controller;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\NotFoundHttpException;
use yii\web\Response;

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
    public function actionDisplay(string $slug): Response
    {
        // Get the smart link
        $smartLink = SmartLink::find()
            ->slug($slug)
            ->status(null) // Allow any status
            ->one();

        if (!$smartLink) {
            throw new NotFoundHttpException('QR code not found.');
        }

        // Check if link is trashed
        if ($smartLink->trashed) {
            throw new NotFoundHttpException('QR code not found.');
        }

        // Check if SmartLink Manager is enabled for the smart link's site
        $settings = SmartLinkManager::$plugin->getSettings();
        if (!$settings->isSiteEnabled($smartLink->siteId)) {
            $this->logInfo('SmartLink Manager disabled for this site', ['siteId' => $smartLink->siteId, 'slug' => $slug]);
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // If QR is disabled, redirect to 404 redirect URL (consistent with smart link behavior)
        if (!$smartLink->qrCodeEnabled) {
            $settings = SmartLinkManager::$plugin->getSettings();
            $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
            return $this->redirect($redirectUrl);
        }

        // Get parameters
        $request = Craft::$app->request;
        $size = $request->getQueryParam('size', SmartLinkManager::$plugin->getSettings()->defaultQrSize);
        $format = $request->getQueryParam('format', SmartLinkManager::$plugin->getSettings()->defaultQrFormat);
        
        // Generate QR code data
        $settings = SmartLinkManager::$plugin->getSettings();
        $options = [
            'size' => $size,
            'color' => $request->getQueryParam('color', str_replace('#', '', $smartLink->qrCodeColor)),
            'bg' => $request->getQueryParam('bg', str_replace('#', '', $smartLink->qrCodeBgColor)),
            'format' => $format,
            'margin' => $request->getQueryParam('margin'),
            'moduleStyle' => $request->getQueryParam('moduleStyle'),
            'eyeStyle' => $request->getQueryParam('eyeStyle'),
            'eyeColor' => $request->getQueryParam('eyeColor', str_replace('#', '', $smartLink->qrCodeEyeColor)),
        ];

        // Add logo if enabled (don't accept from query params for security)
        if ($settings->enableQrLogo) {
            $logoId = $smartLink->qrLogoId ?: $settings->defaultQrLogoId;
            if ($logoId) {
                $options['logo'] = $logoId;
            }
        }

        // Remove null values
        $options = array_filter($options, fn($value) => $value !== null);

        // Generate full URL for the smart link with QR tracking parameter
        $url = $smartLink->getRedirectUrl();

        // Debug logging
        $this->logInfo('SmartLink redirect URL (display)', ['url' => $url]);

        // The redirect URL should already be a full URL from UrlHelper::siteUrl()
        // Add the QR source parameter to track QR code scans
        $separator = strpos($url, '?') !== false ? '&' : '?';
        $fullUrl = $url . $separator . 'src=qr';

        // Note: Tracking is handled client-side via JavaScript (redirect-tracking.js)
        // QR codes contain static URLs - no cache busting needed

        $this->logInfo('Full URL for QR', ['fullUrl' => $fullUrl]);

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
            $template = $settings->qrTemplate ?: 'smartlink-manager/qr';

            return $this->renderTemplate($template, $templateVars);
        } catch (\Exception $e) {
            $this->logError('Failed to generate QR code', ['error' => $e->getMessage()]);
            throw new NotFoundHttpException('Failed to generate QR code.');
        }
    }

    /**
     * Generate QR code for smart link
     *
     * @param string|null $slug
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionGenerate(?string $slug = null): Response
    {
        $request = Craft::$app->request;
        
        // Check if this is a preview request
        $isPreview = $request->getQueryParam('preview');
        $url = $request->getQueryParam('url');
        
        if ($isPreview && $url) {
            // Preview mode - generate QR code for any URL (requires login)
            $this->requireLogin();
            $fullUrl = $url;
            $smartLink = null;
        } else {
            // Normal mode - require a smart link
            if (!$slug) {
                throw new NotFoundHttpException('Smart link not specified.');
            }
            
            // Get the smart link - allow all statuses except trashed
            $smartLink = SmartLink::find()
                ->slug($slug)
                ->status(null) // Allow any status
                ->one();

            if (!$smartLink) {
                throw new NotFoundHttpException('QR code not found.');
            }

            // Check if link is trashed
            if ($smartLink->trashed) {
                throw new NotFoundHttpException('QR code not found.');
            }

            // Check if SmartLink Manager is enabled for the smart link's site
            $settings = SmartLinkManager::$plugin->getSettings();
            if (!$settings->isSiteEnabled($smartLink->siteId)) {
                $this->logInfo('SmartLink Manager disabled for this site', ['siteId' => $smartLink->siteId, 'slug' => $slug]);
                $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
                return $this->redirect($redirectUrl);
            }

            // If QR is disabled, redirect to 404 redirect URL (consistent with smart link behavior)
            if (!$smartLink->qrCodeEnabled) {
                $redirectUrl = $settings->notFoundRedirectUrl ?: '/';
                return $this->redirect($redirectUrl);
            }

            // Generate full URL for the smart link with QR tracking parameter
            $url = $smartLink->getRedirectUrl();

            // Debug logging
            $this->logInfo('SmartLink redirect URL (generate)', ['url' => $url]);

            // The redirect URL should already be a full URL from UrlHelper::siteUrl()
            // Add the QR source parameter to track QR code scans
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $fullUrl = $url . $separator . 'src=qr';

            // Note: Tracking is handled client-side via JavaScript (redirect-tracking.js)
            // QR codes contain static URLs - no cache busting needed

            $this->logInfo('Full URL for QR', ['fullUrl' => $fullUrl]);
        }

        // Get parameters
        $settings = SmartLinkManager::$plugin->getSettings();

        if ($smartLink) {
            // Normal mode - use smartlink's configured settings, allow style overrides
            $options = [
                'size' => $request->getQueryParam('size', $smartLink->qrCodeSize),
                'color' => $request->getQueryParam('color', str_replace('#', '', $smartLink->qrCodeColor ?: $settings->defaultQrColor)),
                'bg' => $request->getQueryParam('bg', str_replace('#', '', $smartLink->qrCodeBgColor ?: $settings->defaultQrBgColor)),
                'format' => $request->getQueryParam('format', $smartLink->qrCodeFormat ?: $settings->defaultQrFormat),
                'margin' => $request->getQueryParam('margin', $settings->defaultQrMargin),
                'moduleStyle' => $request->getQueryParam('moduleStyle', $settings->qrModuleStyle),
                'eyeStyle' => $request->getQueryParam('eyeStyle', $settings->qrEyeStyle),
                'eyeColor' => $request->getQueryParam('eyeColor', $smartLink->qrCodeEyeColor ? str_replace('#', '', $smartLink->qrCodeEyeColor) : ($settings->qrEyeColor ? str_replace('#', '', $settings->qrEyeColor) : null)),
            ];

            // Add logo if enabled (don't accept from query params for security)
            if ($settings->enableQrLogo) {
                $logoId = $smartLink->qrLogoId ?: $settings->defaultQrLogoId;
                if ($logoId) {
                    $options['logo'] = $logoId;
                }
            }
        } else {
            // Preview mode (requires login) - accept all params from query
            $options = [
                'size' => $request->getQueryParam('size'),
                'color' => $request->getQueryParam('color'),
                'bg' => $request->getQueryParam('bg'),
                'format' => $request->getQueryParam('format'),
                'margin' => $request->getQueryParam('margin'),
                'moduleStyle' => $request->getQueryParam('moduleStyle'),
                'eyeStyle' => $request->getQueryParam('eyeStyle'),
                'eyeColor' => $request->getQueryParam('eyeColor'),
                'logo' => $request->getQueryParam('logo'),
                'logoSize' => $request->getQueryParam('logoSize'),
                'errorCorrection' => $request->getQueryParam('errorCorrection'),
            ];
        }

        // Remove null values
        $options = array_filter($options, fn($value) => $value !== null);

        // Generate QR code
        try {
            $qrCode = SmartLinkManager::$plugin->qrCode->generateQrCode($fullUrl, $options);

            // Determine content type
            $format = $options['format'] ?? SmartLinkManager::$plugin->getSettings()->defaultQrFormat;
            $contentType = $format === 'svg' ? 'image/svg+xml' : 'image/png';

            // Return response
            $response = Craft::$app->response;
            $response->format = Response::FORMAT_RAW;
            $response->headers->set('Content-Type', $contentType);
            $response->headers->set('Cache-Control', 'public, max-age=86400'); // Cache for 1 day - tracking happens via redirect with ?src=qr
            
            // Handle download request
            if ($request->getQueryParam('download') && $smartLink) {
                $settings = SmartLinkManager::$plugin->getSettings();
                $filename = strtr($settings->qrDownloadFilename, [
                    '{slug}' => $smartLink->slug,
                    '{size}' => $options['size'] ?? $settings->defaultQrSize,
                    '{format}' => $format,
                ]);
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '.' . $format . '"');
            }
            
            $response->content = $qrCode;
            
            return $response;
        } catch (\Exception $e) {
            $this->logError('Failed to generate QR code', ['error' => $e->getMessage()]);
            throw new NotFoundHttpException('Failed to generate QR code.');
        }
    }
}
