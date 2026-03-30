<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 */

namespace lindemannrock\smartlinkmanager\controllers;

use Craft;
use craft\helpers\Db;
use craft\helpers\StringHelper;
use craft\web\Controller;
use craft\web\UploadedFile;
use lindemannrock\base\helpers\CsvImportHelper;
use lindemannrock\base\helpers\DateFormatHelper;
use lindemannrock\base\helpers\ExportHelper;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\records\ImportHistoryRecord;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ImportExportController extends Controller
{
    use LoggingTrait;

    public function init(): void
    {
        parent::init();
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);
    }

    public function actionIndex(): Response
    {
        $this->requirePermission('smartLinkManager:manageImportExport');

        $canImport = $this->canImport();
        $canExport = $this->canExport();
        $canClearHistory = $this->canClearHistory();

        /** @var ImportHistoryRecord[] $records */
        $records = ImportHistoryRecord::find()
            ->orderBy(['dateCreated' => SORT_DESC])
            ->limit(20)
            ->all();

        // Pre-fetch all users in one query to avoid N+1
        $userIds = array_unique(array_filter(array_map(
            fn($r) => $r->userId,
            $records,
        )));
        $usersById = [];
        if ($userIds) {
            $usersById = \craft\elements\User::find()
                ->id($userIds)
                ->indexBy('id')
                ->all();
        }

        $history = [];
        foreach ($records as $record) {
            $user = $usersById[$record->userId] ?? null;
            $history[] = [
                'formattedDate' => DateFormatHelper::formatDatetime($record->dateCreated),
                'user' => $user?->username ?? Craft::t('smartlink-manager', 'Unknown'),
                'filename' => $record->filename,
                'formattedSize' => $record->filesize
                    ? Craft::$app->getFormatter()->asShortSize($record->filesize, 2)
                    : '-',
                'imported' => (int)$record->imported,
                'failed' => (int)$record->failed,
            ];
        }

        return $this->renderTemplate('smartlink-manager/import-export/index', [
            'canImport' => $canImport,
            'canExport' => $canExport,
            'canClearHistory' => $canClearHistory,
            'importHistory' => $history,
            'importLimits' => [
                'maxRows' => CsvImportHelper::DEFAULT_MAX_ROWS,
                'maxBytes' => CsvImportHelper::DEFAULT_MAX_BYTES,
            ],
        ]);
    }

    public function actionExport(): Response
    {
        $this->requirePostRequest();
        $this->requireExportPermission();

        $rows = [];
        $headers = [
            'slug', 'title', 'description', 'iosUrl', 'androidUrl', 'huaweiUrl', 'amazonUrl', 'windowsUrl', 'macUrl', 'fallbackUrl',
            'imageId', 'imageSize', 'enabled', 'siteId', 'siteHandle', 'trackAnalytics',
            'qrCodeEnabled', 'qrCodeSize', 'qrCodeColor', 'qrCodeBgColor', 'qrCodeEyeColor', 'qrCodeFormat', 'qrLogoId',
            'hideTitle', 'languageDetection', 'postDate', 'dateExpired',
        ];

        $smartLinks = SmartLink::find()->site('*')->status(null)->orderBy(['elements.dateCreated' => SORT_DESC])->all();
        foreach ($smartLinks as $smartLink) {
            $site = Craft::$app->getSites()->getSiteById($smartLink->siteId);
            $rows[] = [
                'slug' => $smartLink->slug,
                'title' => $smartLink->title,
                'description' => $smartLink->description,
                'iosUrl' => $smartLink->iosUrl,
                'androidUrl' => $smartLink->androidUrl,
                'huaweiUrl' => $smartLink->huaweiUrl,
                'amazonUrl' => $smartLink->amazonUrl,
                'windowsUrl' => $smartLink->windowsUrl,
                'macUrl' => $smartLink->macUrl,
                'fallbackUrl' => $smartLink->fallbackUrl,
                'imageId' => $smartLink->imageId,
                'imageSize' => $smartLink->imageSize,
                'enabled' => $smartLink->getEnabledForSite($smartLink->siteId) ? '1' : '0',
                'siteId' => $smartLink->siteId,
                'siteHandle' => $site?->handle,
                'trackAnalytics' => $smartLink->trackAnalytics ? '1' : '0',
                'qrCodeEnabled' => $smartLink->qrCodeEnabled ? '1' : '0',
                'qrCodeSize' => $smartLink->qrCodeSize,
                'qrCodeColor' => $smartLink->qrCodeColor,
                'qrCodeBgColor' => $smartLink->qrCodeBgColor,
                'qrCodeEyeColor' => $smartLink->qrCodeEyeColor,
                'qrCodeFormat' => $smartLink->qrCodeFormat,
                'qrLogoId' => $smartLink->qrLogoId,
                'hideTitle' => $smartLink->hideTitle ? '1' : '0',
                'languageDetection' => $smartLink->languageDetection ? '1' : '0',
                'postDate' => $smartLink->postDate,
                'dateExpired' => $smartLink->dateExpired,
            ];
        }

        if (empty($rows)) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'No smart links to export.'));
            return $this->redirect('smartlink-manager/import-export');
        }

        $settings = SmartLinkManager::$plugin->getSettings();
        $filename = ExportHelper::filename($settings, ['export'], 'csv');

        return ExportHelper::toCsv($rows, $headers, $filename, ['postDate', 'dateExpired']);
    }

    public function actionUpload(): Response
    {
        $this->requirePostRequest();
        $this->requireImportPermission();

        $file = UploadedFile::getInstanceByName('csvFile');
        if (!$file) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Please select a CSV file to upload.'));
            return $this->redirect('smartlink-manager/import-export');
        }

        $delimiter = (string)Craft::$app->getRequest()->getBodyParam('delimiter', 'auto');
        $detectDelimiter = $delimiter === 'auto';
        $delimiter = $detectDelimiter ? null : $delimiter;

        try {
            $parsed = CsvImportHelper::parseUpload($file, [
                'maxRows' => CsvImportHelper::DEFAULT_MAX_ROWS,
                'maxBytes' => CsvImportHelper::DEFAULT_MAX_BYTES,
                'delimiter' => $delimiter,
                'detectDelimiter' => $detectDelimiter,
            ]);

            Craft::$app->getSession()->set('smartlink-import', [
                'headers' => $parsed['headers'],
                'allRows' => $parsed['allRows'],
                'rowCount' => $parsed['rowCount'],
                'filename' => $file->name,
                'filesize' => $file->size,
            ]);

            return $this->redirect('smartlink-manager/import-export/map');
        } catch (\Throwable $e) {
            $this->logError('Failed to parse smartlink CSV', ['error' => $e->getMessage()]);
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Failed to parse CSV: {error}', ['error' => $e->getMessage()]));
            return $this->redirect('smartlink-manager/import-export');
        }
    }

    public function actionMap(): Response
    {
        $this->requireImportPermission();

        $importData = Craft::$app->getSession()->get('smartlink-import');
        if (!$importData || !isset($importData['allRows'])) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'No import data found. Please upload a CSV file.'));
            return $this->redirect('smartlink-manager/import-export');
        }

        return $this->renderTemplate('smartlink-manager/import-export/map', [
            'headers' => $importData['headers'],
            'previewRows' => array_slice($importData['allRows'], 0, 5),
            'rowCount' => $importData['rowCount'],
        ]);
    }

    public function actionPreview(): Response
    {
        $this->requireImportPermission();

        if (!Craft::$app->getRequest()->getIsPost()) {
            $previewData = Craft::$app->getSession()->get('smartlink-preview');
            if (!$previewData) {
                Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'No preview data found. Please map columns first.'));
                return $this->redirect('smartlink-manager/import-export');
            }
            return $this->renderTemplate('smartlink-manager/import-export/preview', $previewData);
        }

        $importData = Craft::$app->getSession()->get('smartlink-import');
        if (!$importData || !isset($importData['allRows'])) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Import session expired. Please upload the file again.'));
            return $this->redirect('smartlink-manager/import-export');
        }

        $mapping = Craft::$app->getRequest()->getBodyParam('mapping', []);
        $columnMap = [];
        foreach ($mapping as $colIndex => $fieldName) {
            if (!empty($fieldName)) {
                $columnMap[(int)$colIndex] = $fieldName;
            }
        }

        $mappedFields = array_values($columnMap);
        if (!in_array('slug', $mappedFields, true)) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Slug must be mapped.'));
            return $this->redirect('smartlink-manager/import-export/map');
        }

        $validRows = [];
        $duplicateRows = [];
        $errorRows = [];
        $defaultSiteId = Craft::$app->getSites()->getCurrentSite()->id;

        $existingSlugs = (new \craft\db\Query())
            ->select(['slug'])
            ->from('{{%smartlinkmanager}}')
            ->column();
        $existingLookup = array_fill_keys(array_map(static fn($slug) => strtolower((string)$slug), $existingSlugs), true);
        $seenImportRows = [];

        $rowNumber = 1;
        foreach ($importData['allRows'] as $row) {
            $rowNumber++;

            $item = [
                'slug' => '',
                'title' => '',
                'description' => '',
                'iosUrl' => '',
                'androidUrl' => '',
                'huaweiUrl' => '',
                'amazonUrl' => '',
                'windowsUrl' => '',
                'macUrl' => '',
                'fallbackUrl' => '',
                'imageId' => null,
                'imageSize' => 'xl',
                'enabled' => true,
                'siteId' => null,
                'trackAnalytics' => true,
                'qrCodeEnabled' => true,
                'qrCodeSize' => 256,
                'qrCodeColor' => null,
                'qrCodeBgColor' => null,
                'qrCodeEyeColor' => null,
                'qrCodeFormat' => null,
                'qrLogoId' => null,
                'hideTitle' => false,
                'languageDetection' => false,
                'postDate' => null,
                'dateExpired' => null,
            ];

            foreach ($columnMap as $colIndex => $fieldName) {
                if (!isset($row[$colIndex])) {
                    continue;
                }

                $value = trim((string)$row[$colIndex]);
                $value = CsvImportHelper::stripFormulaEscapePrefix($value);

                if (in_array($fieldName, ['enabled', 'trackAnalytics', 'qrCodeEnabled', 'hideTitle', 'languageDetection'], true)) {
                    $item[$fieldName] = $this->parseBool($value);
                    continue;
                }

                if (in_array($fieldName, ['siteId', 'imageId', 'qrCodeSize', 'qrLogoId'], true)) {
                    $item[$fieldName] = $value === '' ? null : (int)$value;
                    continue;
                }

                if ($fieldName === 'siteHandle') {
                    $site = Craft::$app->getSites()->getSiteByHandle($value);
                    if ($site) {
                        $item['siteId'] = (int)$site->id;
                    }
                    continue;
                }

                if (in_array($fieldName, ['postDate', 'dateExpired'], true)) {
                    $item[$fieldName] = $this->parseDateOrNull($value);
                    continue;
                }

                $item[$fieldName] = $value;
            }

            $item['slug'] = $this->normalizeSlug((string)$item['slug']);
            if ($item['slug'] === '') {
                $errorRows[] = [
                    'rowNumber' => $rowNumber,
                    'slug' => '-',
                    'fallbackUrl' => $item['fallbackUrl'] ?: '-',
                    'error' => 'Missing required field: slug',
                ];
                continue;
            }

            if ($item['title'] === '') {
                $item['title'] = $item['slug'];
            }

            if ($item['fallbackUrl'] === '') {
                $errorRows[] = [
                    'rowNumber' => $rowNumber,
                    'slug' => $item['slug'],
                    'fallbackUrl' => '-',
                    'error' => 'Missing required field: fallbackUrl',
                ];
                continue;
            }

            if (!$this->isValidUrl($item['fallbackUrl'])) {
                $errorRows[] = [
                    'rowNumber' => $rowNumber,
                    'slug' => $item['slug'],
                    'fallbackUrl' => $item['fallbackUrl'],
                    'error' => 'Invalid fallback URL',
                ];
                continue;
            }

            foreach (['iosUrl', 'androidUrl', 'huaweiUrl', 'amazonUrl', 'windowsUrl', 'macUrl'] as $urlField) {
                if (!empty($item[$urlField]) && !$this->isValidUrl((string)$item[$urlField])) {
                    $errorRows[] = [
                        'rowNumber' => $rowNumber,
                        'slug' => $item['slug'],
                        'fallbackUrl' => $item['fallbackUrl'],
                        'error' => 'Invalid URL in field: ' . $urlField,
                    ];
                    continue 2;
                }
            }

            if (!in_array((string)$item['imageSize'], ['xl', 'lg', 'md', 'sm'], true)) {
                $item['imageSize'] = 'xl';
            }

            if (!in_array((string)$item['qrCodeFormat'], ['', 'png', 'svg'], true)) {
                $errorRows[] = [
                    'rowNumber' => $rowNumber,
                    'slug' => $item['slug'],
                    'fallbackUrl' => $item['fallbackUrl'],
                    'error' => 'QR format must be png or svg',
                ];
                continue;
            }

            $resolvedSiteId = (int)($item['siteId'] ?: $defaultSiteId);
            $site = Craft::$app->getSites()->getSiteById($resolvedSiteId);
            if (!$site) {
                $errorRows[] = [
                    'rowNumber' => $rowNumber,
                    'slug' => $item['slug'],
                    'fallbackUrl' => $item['fallbackUrl'],
                    'error' => 'Invalid site',
                ];
                continue;
            }

            if (!empty($item['imageId']) && !Craft::$app->getAssets()->getAssetById((int)$item['imageId'])) {
                $errorRows[] = [
                    'rowNumber' => $rowNumber,
                    'slug' => $item['slug'],
                    'fallbackUrl' => $item['fallbackUrl'],
                    'error' => 'Image asset not found for imageId',
                ];
                continue;
            }

            $slugKey = strtolower((string)$item['slug']);
            if (isset($existingLookup[$slugKey])) {
                $duplicateRows[] = [
                    'slug' => $item['slug'],
                    'fallbackUrl' => $item['fallbackUrl'],
                    'reason' => 'Slug already exists',
                ];
                continue;
            }

            $importRowKey = $slugKey . '|' . $resolvedSiteId;
            if (isset($seenImportRows[$importRowKey])) {
                $duplicateRows[] = [
                    'slug' => $item['slug'],
                    'fallbackUrl' => $item['fallbackUrl'],
                    'reason' => 'Duplicate row for same slug and site',
                ];
                continue;
            }

            $item['resolvedSiteId'] = $resolvedSiteId;
            $validRows[] = $item;
            $seenImportRows[$importRowKey] = true;
        }

        $summary = [
            'totalRows' => count($importData['allRows']),
            'validRows' => count($validRows),
            'duplicates' => count($duplicateRows),
            'errors' => count($errorRows),
        ];

        $previewData = [
            'validRows' => $validRows,
            'duplicateRows' => $duplicateRows,
            'errorRows' => $errorRows,
            'summary' => $summary,
        ];

        Craft::$app->getSession()->set('smartlink-preview', $previewData);

        return $this->renderTemplate('smartlink-manager/import-export/preview', $previewData);
    }

    public function actionImport(): ?Response
    {
        $this->requirePostRequest();
        $this->requireImportPermission();

        $previewData = Craft::$app->getSession()->get('smartlink-preview');
        $importData = Craft::$app->getSession()->get('smartlink-import');

        if (!$previewData || !$importData) {
            Craft::$app->getSession()->setError(Craft::t('smartlink-manager', 'Import session expired. Please upload the file again.'));
            return $this->redirect('smartlink-manager/import-export');
        }

        $imported = 0;
        $failed = 0;

        /** @var array<string, array<int, array<string, mixed>>> $rowsBySlug */
        $rowsBySlug = [];
        foreach ($previewData['validRows'] as $row) {
            $slug = strtolower((string)($row['slug'] ?? ''));
            if ($slug === '') {
                $failed++;
                continue;
            }
            $rowsBySlug[$slug][] = $row;
        }

        foreach ($rowsBySlug as $slugRows) {
            try {
                $primaryRow = $slugRows[0];
                $siteId = (int)($primaryRow['resolvedSiteId'] ?? $primaryRow['siteId'] ?? Craft::$app->getSites()->getCurrentSite()->id);
                $site = Craft::$app->getSites()->getSiteById($siteId);
                if (!$site) {
                    $failed += count($slugRows);
                    continue;
                }

                $smartLink = new SmartLink();
                $smartLink->siteId = $siteId;
                $this->applyRowToSmartLink($smartLink, $primaryRow, true);
                $smartLink->setEnabledForSite((bool)($primaryRow['enabled'] ?? true));

                if (!SmartLinkManager::$plugin->smartLinks->saveSmartLink($smartLink)) {
                    $failed += count($slugRows);
                    continue;
                }

                $imported++;

                foreach (array_slice($slugRows, 1) as $siteRow) {
                    $siteRowSiteId = (int)($siteRow['resolvedSiteId'] ?? $siteRow['siteId'] ?? 0);
                    if ($siteRowSiteId <= 0) {
                        $failed++;
                        continue;
                    }

                    $siteVariant = SmartLink::find()
                        ->id($smartLink->id)
                        ->siteId($siteRowSiteId)
                        ->status(null)
                        ->one();

                    if (!$siteVariant) {
                        $failed++;
                        continue;
                    }

                    $siteVariant->siteId = $siteRowSiteId;
                    $this->applyRowToSmartLink($siteVariant, $siteRow, false);
                    $siteVariant->setEnabledForSite((bool)($siteRow['enabled'] ?? true));

                    if (!SmartLinkManager::$plugin->smartLinks->saveSmartLink($siteVariant)) {
                        $failed++;
                        continue;
                    }

                    $imported++;
                }
            } catch (\Throwable $e) {
                Craft::error("Import failed for slug group: {$e->getMessage()}", __METHOD__);
                $failed += count($slugRows);
            }
        }

        Db::insert('{{%smartlinkmanager_import_history}}', [
            'userId' => Craft::$app->getUser()->getId(),
            'filename' => $importData['filename'] ?? null,
            'filesize' => $importData['filesize'] ?? null,
            'imported' => $imported,
            'failed' => $failed,
            'dateCreated' => Db::prepareDateForDb(new \DateTime()),
            'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
            'uid' => StringHelper::UUID(),
        ]);

        Craft::$app->getSession()->remove('smartlink-import');
        Craft::$app->getSession()->remove('smartlink-preview');

        $pluginName = SmartLinkManager::$plugin->getSettings()->getPluralLowerDisplayName();

        if ($failed > 0) {
            Craft::$app->getSession()->setNotice(Craft::t('smartlink-manager', 'Import completed: {imported} {pluginName} imported, {failed} failed.', [
                'imported' => $imported,
                'pluginName' => $pluginName,
                'failed' => $failed,
            ]));
        } else {
            Craft::$app->getSession()->setNotice(Craft::t('smartlink-manager', 'Import completed: {imported} {pluginName} imported.', [
                'imported' => $imported,
                'pluginName' => $pluginName,
            ]));
        }

        return $this->redirect('smartlink-manager/import-export');
    }

    public function actionClearLogs(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $this->requireClearImportHistoryPermission();

        try {
            Db::delete(ImportHistoryRecord::tableName());
            return $this->asJson(['success' => true]);
        } catch (\Throwable) {
            return $this->asJson(['success' => false, 'error' => Craft::t('smartlink-manager', 'Failed to clear import history.')]);
        }
    }

    private function applyRowToSmartLink(SmartLink $smartLink, array $row, bool $applyGlobalFields): void
    {
        $smartLink->title = (string)($row['title'] ?? $smartLink->title ?? '');
        $smartLink->description = (string)($row['description'] ?? '');
        $smartLink->iosUrl = (string)($row['iosUrl'] ?? '');
        $smartLink->androidUrl = (string)($row['androidUrl'] ?? '');
        $smartLink->huaweiUrl = (string)($row['huaweiUrl'] ?? '');
        $smartLink->amazonUrl = (string)($row['amazonUrl'] ?? '');
        $smartLink->windowsUrl = (string)($row['windowsUrl'] ?? '');
        $smartLink->macUrl = (string)($row['macUrl'] ?? '');
        $smartLink->fallbackUrl = (string)($row['fallbackUrl'] ?? '');
        $smartLink->imageId = !empty($row['imageId']) ? (int)$row['imageId'] : null;
        $smartLink->imageSize = in_array((string)($row['imageSize'] ?? ''), ['xl', 'lg', 'md', 'sm'], true)
            ? (string)$row['imageSize']
            : 'xl';

        if ($applyGlobalFields) {
            $smartLink->slug = (string)($row['slug'] ?? $smartLink->slug);
            $smartLink->trackAnalytics = (bool)($row['trackAnalytics'] ?? true);
            $smartLink->qrCodeEnabled = (bool)($row['qrCodeEnabled'] ?? true);
            $smartLink->qrCodeSize = max(100, min(1000, (int)($row['qrCodeSize'] ?? 256)));
            $smartLink->qrCodeColor = $this->normalizeHexColor($row['qrCodeColor'] ?? null);
            $smartLink->qrCodeBgColor = $this->normalizeHexColor($row['qrCodeBgColor'] ?? null);
            $smartLink->qrCodeEyeColor = $this->normalizeHexColor($row['qrCodeEyeColor'] ?? null);
            $smartLink->qrCodeFormat = in_array((string)($row['qrCodeFormat'] ?? ''), ['png', 'svg'], true)
                ? (string)$row['qrCodeFormat']
                : null;
            $smartLink->qrLogoId = !empty($row['qrLogoId']) ? (int)$row['qrLogoId'] : null;
            $smartLink->hideTitle = (bool)($row['hideTitle'] ?? false);
            $smartLink->languageDetection = (bool)($row['languageDetection'] ?? false);
            $smartLink->postDate = $row['postDate'] instanceof \DateTime ? $row['postDate'] : null;
            $smartLink->dateExpired = $row['dateExpired'] instanceof \DateTime ? $row['dateExpired'] : null;
        }
    }

    private function requireImportPermission(): void
    {
        if (!$this->canImport()) {
            throw new ForbiddenHttpException('User does not have permission to import smart links.');
        }
    }

    private function requireExportPermission(): void
    {
        if (!$this->canExport()) {
            throw new ForbiddenHttpException('User does not have permission to export smart links.');
        }
    }

    private function requireClearImportHistoryPermission(): void
    {
        if (!$this->canClearHistory()) {
            throw new ForbiddenHttpException('User does not have permission to clear import history.');
        }
    }

    private function canImport(): bool
    {
        return Craft::$app->getUser()->checkPermission('smartLinkManager:importLinks');
    }

    private function canExport(): bool
    {
        return Craft::$app->getUser()->checkPermission('smartLinkManager:exportLinks');
    }

    private function canClearHistory(): bool
    {
        return Craft::$app->getUser()->checkPermission('smartLinkManager:clearImportHistory');
    }

    private function parseBool(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'enabled', 'on'], true);
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9\-_]/', '-', $slug) ?? '';
        $slug = preg_replace('/-+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }

    private function parseDateOrNull(string $value): ?\DateTime
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            $localDate = DateFormatHelper::toCraftTimezone($value, false);
            if ($localDate === null) {
                return null;
            }

            $utcDate = clone $localDate;
            $utcDate->setTimezone(new \DateTimeZone('UTC'));

            return $utcDate;
        } catch (\Throwable) {
            return null;
        }
    }

    private function isValidUrl(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function normalizeHexColor(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if ($trimmed[0] !== '#') {
            $trimmed = '#' . $trimmed;
        }

        return preg_match('/^#[0-9A-F]{6}$/i', $trimmed) ? strtoupper($trimmed) : null;
    }
}
