<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\elements;

use Craft;
use craft\base\Element;
use craft\elements\actions\Delete;
use craft\elements\actions\Duplicate;
use craft\elements\actions\Restore;
use craft\elements\actions\SetStatus;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\helpers\DateTimeHelper;
use craft\helpers\Html;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use craft\validators\UniqueValidator;
use lindemannrock\logginglibrary\traits\LoggingTrait;
use lindemannrock\smartlinkmanager\elements\db\SmartLinkQuery;
use lindemannrock\smartlinkmanager\records\SmartLinkContentRecord;
use lindemannrock\smartlinkmanager\records\SmartLinkRecord;
use lindemannrock\smartlinkmanager\SmartLinkManager;
use yii\validators\RequiredValidator;
use yii\validators\UrlValidator;

/**
 * SmartLink element
 *
 * @property-read string $redirectUrl
 * @property-read string $qrCodeUrl
 * @property-read array $analyticsData
 * @property int $clicks
 * @property array|null $metadata
 * @since 1.0.0
 */
class SmartLink extends Element
{
    use LoggingTrait;

    // Properties
    // =========================================================================


    /**
     * @var string|null Slug
     */
    public ?string $slug = null;

    /**
     * @var string|null Description (translatable)
     */
    public ?string $description = null;

    /**
     * @var string|null iOS URL
     */
    public ?string $iosUrl = null;

    /**
     * @var string|null Android URL
     */
    public ?string $androidUrl = null;

    /**
     * @var string|null Huawei URL
     */
    public ?string $huaweiUrl = null;

    /**
     * @var string|null Amazon URL
     */
    public ?string $amazonUrl = null;

    /**
     * @var string|null Windows URL
     */
    public ?string $windowsUrl = null;

    /**
     * @var string|null Mac URL
     */
    public ?string $macUrl = null;

    /**
     * @var string|null Fallback URL
     */
    public ?string $fallbackUrl = null;

    /**
     * @var string|null Icon
     */
    public ?string $icon = null;
    
    /**
     * @var int|null Image asset ID
     */
    public ?int $imageId = null;
    
    /**
     * @var string Image size
     */
    public string $imageSize = 'xl';

    /**
     * @var int|null Author ID
     */
    public ?int $authorId = null;

    /**
     * @var \DateTime|null Post date
     */
    public ?\DateTime $postDate = null;

    /**
     * @var \DateTime|null Expiry date
     */
    public ?\DateTime $dateExpired = null;

    /**
     * @var bool Track analytics
     */
    public bool $trackAnalytics = true;
    
    /**
     * @var bool Hide title on landing page
     */
    public bool $hideTitle = false;


    /**
     * @var bool QR code enabled
     */
    public bool $qrCodeEnabled = true;

    /**
     * @var int QR code size
     */
    public int $qrCodeSize = 256;

    /**
     * @var string|null QR code color
     */
    public ?string $qrCodeColor = null;

    /**
     * @var string|null QR code background color
     */
    public ?string $qrCodeBgColor = null;

    /**
     * @var string|null QR code format override
     */
    public ?string $qrCodeFormat = null;
    
    /**
     * @var string|null QR code eye color
     */
    public ?string $qrCodeEyeColor = null;
    
    /**
     * @var int|null QR code logo asset ID (overrides default)
     */
    public ?int $qrLogoId = null;

    /**
     * @var bool Language detection enabled
     */
    public bool $languageDetection = false;

    /**
     * @var array|null Localized URLs
     */
    public ?array $localizedUrls = null;

    /**
     * @var array|null Metadata
     */
    private ?array $_metadata = null;

    /**
     * @var int|null Total clicks (cached value)
     */
    private ?int $_clicks = null;

    // Public Methods
    // =========================================================================

    /**
     * Set metadata - handles JSON decoding from database
     */
    public function setMetadata(array|string|null $value): void
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $this->_metadata = is_array($decoded) ? $decoded : null;
        } else {
            $this->_metadata = $value;
        }
    }

    // Magic property getter/setter for metadata
    public function __get($name)
    {
        if ($name === 'metadata') {
            return $this->_metadata;
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($name === 'metadata') {
            $this->setMetadata($value);
        } else {
            parent::__set($name, $value);
        }
    }

    // Static Methods
    // =========================================================================

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
    public static function lowerDisplayName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getLowerDisplayName();
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getPluralDisplayName();
    }

    /**
     * @inheritdoc
     */
    public static function pluralLowerDisplayName(): string
    {
        return SmartLinkManager::$plugin->getSettings()->getPluralLowerDisplayName();
    }

    /**
     * @inheritdoc
     */
    public static function refHandle(): ?string
    {
        return 'smartLink';
    }

    /**
     * @inheritdoc
     */
    public static function trackChanges(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }
    

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }
    
    /**
     * @inheritdoc
     */
    protected static function defineSupportedSites(): array
    {
        // Smart links are available in all sites
        return Craft::$app->getSites()->getAllSiteIds();
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }



    /**
     * @var string Status expired
     */
    public const STATUS_EXPIRED = 'expired';
    
    /**
     * @var string Status pending
     */
    public const STATUS_PENDING = 'pending';

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ENABLED => Craft::t('app', 'Enabled'),
            self::STATUS_DISABLED => Craft::t('app', 'Disabled'),
            self::STATUS_PENDING => Craft::t('app', 'Pending'),
            self::STATUS_EXPIRED => Craft::t('app', 'Expired'),
        ];
    }

    /**
     * @inheritdoc
     * @return SmartLinkQuery
     */
    public static function find(): ElementQueryInterface
    {
        return new SmartLinkQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    public static function supportedSites(): array
    {
        $settings = SmartLinkManager::getInstance()->getSettings();
        $enabledSiteIds = $settings->getEnabledSiteIds();


        // Return array of site IDs that support this element type
        return array_map(function($siteId) {
            return ['siteId' => $siteId, 'enabledByDefault' => true];
        }, $enabledSiteIds);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(?string $context = null): array
    {
        return [
            [
                'key' => '*',
                'label' => Craft::t('smartlink-manager', 'All {pluginName}', [
                    'pluginName' => SmartLinkManager::$plugin->getSettings()->getPluralDisplayName(),
                ]),
                'criteria' => [],
                'defaultSort' => ['dateCreated', 'desc'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source): array
    {
        $actions = [];

        // Set Status
        $actions[] = SetStatus::class;

        // Delete
        $actions[] = Craft::$app->elements->createAction([
            'type' => Delete::class,
            'confirmationMessage' => Craft::t('smartlink-manager', 'Are you sure you want to delete the selected smart links?'),
            'successMessage' => Craft::t('smartlink-manager', 'Smart links deleted.'),
        ]);

        // Duplicate
        $actions[] = Duplicate::class;

        // Restore
        $actions[] = Craft::$app->elements->createAction([
            'type' => Restore::class,
            'successMessage' => Craft::t('smartlink-manager', 'Smart links restored.'),
            'partialSuccessMessage' => Craft::t('smartlink-manager', 'Some smart links restored.'),
            'failMessage' => Craft::t('smartlink-manager', 'Smart links not restored.'),
        ]);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function includeSetStatusAction(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('smartlink-manager', 'Title'),
            'slug' => Craft::t('smartlink-manager', 'Slug'),
            [
                'label' => Craft::t('app', 'Post Date'),
                'orderBy' => 'smartlinkmanager.postDate',
                'attribute' => 'postDate',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'Expiry Date'),
                'orderBy' => 'smartlinkmanager.dateExpired',
                'attribute' => 'dateExpired',
                'defaultDir' => 'asc',
            ],
            [
                'label' => Craft::t('app', 'Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated',
                'defaultDir' => 'desc',
            ],
            [
                'label' => Craft::t('app', 'ID'),
                'orderBy' => 'elements.id',
                'attribute' => 'id',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'title' => ['label' => Craft::t('smartlink-manager', 'Title')],
            'slug' => ['label' => Craft::t('smartlink-manager', 'Slug')],
            'status' => ['label' => Craft::t('app', 'Status')],
            'postDate' => ['label' => Craft::t('app', 'Post Date')],
            'dateExpired' => ['label' => Craft::t('app', 'Expiry Date')],
            'clicks' => ['label' => Craft::t('smartlink-manager', 'Interactions')],
            'dateCreated' => ['label' => Craft::t('app', 'Date Created')],
            'dateUpdated' => ['label' => Craft::t('app', 'Date Updated')],
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return [
            'slug',
            'status',
            'clicks',
            'postDate',
            'dateCreated',
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['title', 'slug', 'description', 'imageTitle', 'imageFilename'];
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords(string $attribute): string
    {
        switch ($attribute) {
            case 'imageTitle':
                $image = $this->getImage();
                return $image?->title ?? '';
            case 'imageFilename':
                $image = $this->getImage();
                return $image?->filename ?? '';
            default:
                return parent::getSearchKeywords($attribute);
        }
    }

    // Public Methods
    // =========================================================================

    /**
     * Get total clicks count
     *
     * @return int
     */
    public function getClicks(): int
    {
        if ($this->_clicks === null) {
            $this->_clicks = (int) (new \craft\db\Query())
                ->from('{{%smartlinkmanager_analytics}}')
                ->where(['linkId' => $this->id])
                ->count();
        }
        
        return $this->_clicks;
    }
    
    /**
     * Set clicks value (for caching purposes)
     *
     * @param int $clicks
     */
    public function setClicks(int $clicks): void
    {
        $this->_clicks = $clicks;
    }

    /**
     * Get the author user element
     *
     * @return User|null
     */
    public function getAuthor(): ?User
    {
        if ($this->authorId) {
            return User::find()->id($this->authorId)->one();
        }
        return null;
    }
    
    /**
     * Get the image asset element
     *
     * @return \craft\elements\Asset|null
     */
    public function getImage(): ?\craft\elements\Asset
    {
        if ($this->imageId) {
            return \craft\elements\Asset::find()->id($this->imageId)->one();
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function defineAttributes(): array
    {
        return [
            'slug' => null,
            'description' => null,
            'iosUrl' => null,
            'androidUrl' => null,
            'huaweiUrl' => null,
            'amazonUrl' => null,
            'windowsUrl' => null,
            'macUrl' => null,
            'fallbackUrl' => null,
            'icon' => null,
            'imageId' => null,
            'imageSize' => 'xl',
            'authorId' => null,
            'postDate' => null,
            'dateExpired' => null,
            'trackAnalytics' => true,
            'hideTitle' => false,
            'qrCodeEnabled' => true,
            'qrCodeSize' => 256,
            'qrCodeColor' => null,
            'qrCodeBgColor' => null,
            'qrCodeFormat' => null,
            'qrCodeEyeColor' => null,
            'qrLogoId' => null,
            'languageDetection' => false,
            'metadata' => null,
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function safeAttributes(): array
    {
        $attributes = parent::safeAttributes();
        $attributes[] = 'imageId';
        $attributes[] = 'imageSize';
        return $attributes;
    }
    
    /**
     * @inheritdoc
     */
    public function init(): void
    {
        parent::init();

        // Set logging handle for LoggingTrait
        $this->setLoggingHandle(SmartLinkManager::$plugin->id);

        // If we have an ID but no content loaded yet, load it now
        if ($this->id && $this->siteId && $this->fallbackUrl === null) {
            $this->loadContent();
        }

        // Normalize date values
        $this->normalizeDateTime('postDate');
        $this->normalizeDateTime('dateExpired');
    }
    
    
    /**
     * Normalize a date property to DateTime object
     */
    private function normalizeDateTime(string $property): void
    {
        if ($this->$property !== null && !($this->$property instanceof \DateTime)) {
            try {
                $this->$property = DateTimeHelper::toDateTime($this->$property);
            } catch (\Exception) {
                $this->$property = null;
            }
        }
    }
    
    /**
     * Load content for the current site
     */
    public function loadContent(): void
    {
        if (!$this->id || !$this->siteId) {
            return;
        }
        
        // Skip loading from content table if this is a revision
        if ($this->getIsRevision()) {
            return;
        }
        
        $contentRecord = SmartLinkContentRecord::findOne([
            'smartLinkId' => $this->id,
            'siteId' => $this->siteId,
        ]);

        if ($contentRecord) {
            // Override with site-specific content
            $this->title = $contentRecord->title;
            $this->description = $contentRecord->description;
            $this->iosUrl = $contentRecord->iosUrl;
            $this->androidUrl = $contentRecord->androidUrl;
            $this->huaweiUrl = $contentRecord->huaweiUrl;
            $this->amazonUrl = $contentRecord->amazonUrl;
            $this->windowsUrl = $contentRecord->windowsUrl;
            $this->macUrl = $contentRecord->macUrl;
            $this->fallbackUrl = $contentRecord->fallbackUrl;
            $this->imageId = $contentRecord->imageId;
            $this->imageSize = $contentRecord->imageSize ?? 'xl';
        }
    }

    /**
     * @inheritdoc
     */
    public function afterPopulate(): void
    {
        // Load content data for current site
        $this->loadContent();
    }

    /**
     * @inheritdoc
     */
    public static function defineNativeFields(): array
    {
        return [
            'slug',
            'description',
            'iosUrl',
            'androidUrl',
            'huaweiUrl',
            'amazonUrl',
            'windowsUrl',
            'macUrl',
            'fallbackUrl',
            'icon',
            'imageId',
            'imageSize',
            'qrCodeEnabled',
            'qrLogoId',
            'qrSize',
            'qrColor',
            'qrBgColor',
            'qrEyeColor',
            'qrLogoSize',
            'qrMargin',
            'hideTitle',
            'trackAnalytics',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout(): ?FieldLayout
    {
        // Get field layouts from project config
        $fieldLayouts = Craft::$app->getProjectConfig()->get('smartlink-manager.fieldLayouts') ?? [];

        if (!empty($fieldLayouts)) {
            // Get the first (and only) field layout
            $fieldLayoutUid = array_key_first($fieldLayouts);
            $fieldLayout = Craft::$app->getFields()->getLayoutByUid($fieldLayoutUid);
            if ($fieldLayout) {
                return $fieldLayout;
            }
        }

        // Fallback to getting by type (for backwards compatibility)
        return Craft::$app->fields->getLayoutByType(SmartLink::class);
    }

    /**
     * @inheritdoc
     */
    public function canView(User $user): bool
    {
        return $user->can('smartLinkManager:viewLinks');
    }

    /**
     * @inheritdoc
     */
    public function canSave(User $user): bool
    {
        if (!$this->id) {
            return $user->can('smartLinkManager:createLinks');
        }

        return $user->can('smartLinkManager:editLinks');
    }

    /**
     * @inheritdoc
     */
    public function canDelete(User $user): bool
    {
        return $user->can('smartLinkManager:deleteLinks');
    }

    /**
     * @inheritdoc
     */
    public function canDuplicate(User $user): bool
    {
        return $user->can('smartLinkManager:createLinks');
    }
    
    /**
     * @inheritdoc
     */
    public function canCreateDrafts(User $user): bool
    {
        return false;
    }
    

    /**
     * @inheritdoc
     */
    public function hasRevisions(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasDrafts(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function previewTargets(): array
    {
        return [
            [
                'label' => Craft::t('smartlink-manager', 'Redirect Page'),
                'url' => $this->getRedirectUrl(),
            ],
        ];
    }

    /**
     * Get the redirect URL for this smart link
     */
    public function getRedirectUrl(): string
    {
        // Get the slug prefix from settings
        $settings = SmartLinkManager::$plugin->getSettings();
        $slugPrefix = $settings->slugPrefix ?? 'go';

        // Generate URL for the element's site (respects CP site switcher)
        $url = UrlHelper::siteUrl("{$slugPrefix}/{$this->slug}", null, null, $this->siteId);

        return $url;
    }
    
    /**
     * @inheritdoc
     */
    public function getUrl(): ?string
    {
        $status = $this->getStatus();
        
        // Only return URL for enabled/live links
        if ($status !== self::STATUS_ENABLED) {
            return null;
        }
        
        return $this->getRedirectUrl();
    }

    /**
     * Get the QR code URL for this smart link
     */
    public function getQrCodeUrl(array $options = []): string
    {
        // Get the current default format from settings
        $settings = SmartLinkManager::$plugin->getSettings();
        
        $params = array_merge([
            'size' => $this->qrCodeSize,
            'color' => str_replace('#', '', $this->qrCodeColor ?: $settings->defaultQrColor),
            'bg' => str_replace('#', '', $this->qrCodeBgColor ?: $settings->defaultQrBgColor),
            'format' => $this->qrCodeFormat ?: ($settings->defaultQrFormat ?? 'png'),
            'margin' => $settings->defaultQrMargin,
            'moduleStyle' => $settings->qrModuleStyle,
            'eyeStyle' => $settings->qrEyeStyle,
            'eyeColor' => $this->qrCodeEyeColor ? str_replace('#', '', $this->qrCodeEyeColor) : ($settings->qrEyeColor ? str_replace('#', '', $settings->qrEyeColor) : null),
        ], $options);
        
        // Add logo ID if logos are enabled and one is set
        if ($settings->enableQrLogo) {
            $logoId = $this->qrLogoId ?: $settings->defaultQrLogoId;
            if ($logoId) {
                $params['logo'] = $logoId;
            }
        }

        // Get the QR prefix from settings
        $qrPrefix = $settings->qrPrefix ?? 'qr';

        return UrlHelper::siteUrl("{$qrPrefix}/{$this->slug}", $params);
    }
    
    /**
     * Get the QR code display page URL for this smart link
     */
    public function getQrCodeDisplayUrl(array $options = []): string
    {
        // Get the same parameters as getQrCodeUrl to ensure consistency
        $settings = SmartLinkManager::$plugin->getSettings();
        
        $params = array_merge([
            'size' => $this->qrCodeSize,
            'color' => str_replace('#', '', $this->qrCodeColor ?: $settings->defaultQrColor),
            'bg' => str_replace('#', '', $this->qrCodeBgColor ?: $settings->defaultQrBgColor),
            'format' => $this->qrCodeFormat ?: ($settings->defaultQrFormat ?? 'png'),
            'eyeColor' => $this->qrCodeEyeColor ? str_replace('#', '', $this->qrCodeEyeColor) : ($settings->qrEyeColor ? str_replace('#', '', $settings->qrEyeColor) : null),
        ], $options);
        
        // Remove null values
        $params = array_filter($params, fn($value) => $value !== null);

        // Get the QR prefix from settings
        $qrPrefix = $settings->qrPrefix ?? 'qr';

        return UrlHelper::siteUrl("{$qrPrefix}/{$this->slug}/view", $params);
    }

    /**
     * Get QR code as data URI (base64 encoded inline image)
     *
     * @param array $options Optional parameters to override defaults
     * @return string Data URI string
     */
    public function getQrCodeDataUri(array $options = []): string
    {
        // Get settings for fallback values
        $settings = SmartLinkManager::$plugin->getSettings();

        if (!$this->qrCodeEnabled) {
            return '';
        }

        // Get logo ID - use per-link logo or fall back to default
        $logoId = $this->qrLogoId ?: $settings->defaultQrLogoId;

        // Merge per-link settings with options
        $qrOptions = array_merge([
            'size' => $this->qrCodeSize,
            'color' => str_replace('#', '', $this->qrCodeColor ?: $settings->defaultQrColor),
            'bg' => str_replace('#', '', $this->qrCodeBgColor ?: $settings->defaultQrBgColor),
            'eyeColor' => $this->qrCodeEyeColor ? str_replace('#', '', $this->qrCodeEyeColor) : null,
            'format' => $this->qrCodeFormat,
            'logo' => $logoId,
        ], $options);

        return SmartLinkManager::$plugin->qrCode->generateQrCodeDataUrl($this->getRedirectUrl(), $qrOptions);
    }

    /**
     * Get QR code binary data
     *
     * @param array $options Optional parameters to override defaults
     * @return string Binary image data (PNG or SVG)
     */
    public function getQrCode(array $options = []): string
    {
        // Get settings for fallback values
        $settings = SmartLinkManager::$plugin->getSettings();

        if (!$this->qrCodeEnabled) {
            return '';
        }

        // Get logo ID - use per-link logo or fall back to default
        $logoId = $this->qrLogoId ?: $settings->defaultQrLogoId;

        // Merge per-link settings with options
        $qrOptions = array_merge([
            'size' => $this->qrCodeSize,
            'color' => str_replace('#', '', $this->qrCodeColor ?: $settings->defaultQrColor),
            'bg' => str_replace('#', '', $this->qrCodeBgColor ?: $settings->defaultQrBgColor),
            'eyeColor' => $this->qrCodeEyeColor ? str_replace('#', '', $this->qrCodeEyeColor) : null,
            'format' => $this->qrCodeFormat,
            'logo' => $logoId,
        ], $options);

        return SmartLinkManager::$plugin->qrCode->generateQrCode($this->getRedirectUrl(), $qrOptions);
    }

    /**
     * Get analytics data for this smart link
     */
    public function getAnalyticsData(array $criteria = []): array
    {
        return SmartLinkManager::$plugin->analytics->getAnalytics($this, $criteria);
    }

    /**
     * Render SEOmatic tracking script for this smart link
     *
     * @param string $eventType Event type: 'qr_scan' or 'redirect'
     * @return \Twig\Markup|null HTML script tag or null if SEOmatic is not enabled
     */
    public function renderSeomaticTracking(string $eventType = 'qr_scan'): ?\Twig\Markup
    {
        return SmartLinkManager::$plugin->integration->renderSeomaticTracking($this, $eventType);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?string
    {
        // Check if enabled for the current site using Craft's built-in method
        // This checks the elements_sites.enabled column
        if ($this->enabled === false) {
            return self::STATUS_DISABLED;
        }
        
        // Check if expired
        if ($this->dateExpired && $this->dateExpired < new \DateTime()) {
            return self::STATUS_EXPIRED;
        }
        
        // Check if pending (future post date)
        if ($this->postDate && $this->postDate > new \DateTime()) {
            return self::STATUS_PENDING;
        }
        
        return self::STATUS_ENABLED;
    }

    /**
     * @inheritdoc
     */
    protected function cpEditUrl(): ?string
    {
        return sprintf('smartlink-manager/%s', $this->getCanonicalId());
    }
    
    /**
     * @inheritdoc
     */
    protected static function defineIndexUrl(?string $source = null, ?string $siteHandle = null): ?string
    {
        return 'smartlink-manager';
    }
    
    
    
    /**
     * @inheritdoc
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['title', 'slug', 'fallbackUrl'], RequiredValidator::class];
        $rules[] = [['slug'], 'match', 'pattern' => '/^[a-zA-Z0-9_\-]+$/', 'message' => Craft::t('smartlink-manager', '{attribute} should only contain letters, numbers, underscores, and hyphens.')];
        
        // Handle slug uniqueness
        $rules[] = [
            ['slug'],
            UniqueValidator::class,
            'targetClass' => SmartLinkRecord::class,
            'targetAttribute' => 'slug',
            'comboNotUnique' => Craft::t('yii', '{attribute} "{value}" has already been taken.'),
            'filter' => $this->id ? ['not', ['id' => $this->id]] : null,
            'when' => function($model) {
                // Skip standard unique validation during duplication
                return !($model->duplicateOf && !$model->id);
            },
        ];
        
        $rules[] = [
            ['iosUrl', 'androidUrl', 'huaweiUrl', 'amazonUrl', 'windowsUrl', 'macUrl', 'fallbackUrl'],
            UrlValidator::class,
            'defaultScheme' => 'https',
        ];
        $rules[] = [['trackAnalytics', 'qrCodeEnabled'], 'boolean'];
        $rules[] = [['qrCodeSize'], 'integer', 'min' => 100, 'max' => 1000];
        $rules[] = [['qrCodeColor', 'qrCodeBgColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i'];
        $rules[] = [['qrCodeEyeColor'], 'match', 'pattern' => '/^#[0-9A-F]{6}$/i', 'when' => function($model) {
            return !empty($model->qrCodeEyeColor);
        }];
        $rules[] = [['qrCodeFormat'], 'in', 'range' => ['png', 'svg', null], 'allowArray' => false];
        $rules[] = [['imageSize'], 'in', 'range' => ['xl', 'lg', 'md', 'sm'], 'allowArray' => false];

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'analytics':
            case 'clicks':
                $count = $this->getClicks();
                return $count > 0 ? number_format($count) : '—';
            
            case 'postDate':
                return $this->postDate ? Html::tag('span', $this->postDate->format('M j, Y'), [
                    'title' => $this->postDate->format('D, M j, Y g:i A'),
                ]) : '—';
            
            case 'dateExpired':
                if (!$this->dateExpired) {
                    return '—';
                }
                $isPast = $this->dateExpired < new \DateTime();
                return Html::tag('span', $this->dateExpired->format('M j, Y'), [
                    'title' => $this->dateExpired->format('D, M j, Y g:i A'),
                    'class' => $isPast ? 'error' : '',
                ]);
        }

        return (string)$this->$attribute;
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate(): bool
    {
        // Handle new SmartLinks or duplication
        if (!$this->id) {
            $primarySiteId = Craft::$app->sites->getPrimarySite()->id;
            
            // For new SmartLinks (not duplications), ensure unique slug
            if (!$this->duplicateOf && $this->slug && ($this->siteId == $primarySiteId || !$this->propagating)) {
                $baseSlug = $this->slug;
                $testSlug = $baseSlug;
                $num = 1;
                
                // Keep trying until we find a unique slug
                while (true) {
                    // Check ALL elements regardless of status (enabled, disabled, pending, expired, trashed)
                    $exists = (new \craft\db\Query())
                        ->from('{{%smartlinkmanager}}')
                        ->where(['slug' => $testSlug])
                        ->exists();
                    
                    if (!$exists) {
                        break;
                    }
                    
                    $testSlug = $baseSlug . '-' . $num;
                    $num++;
                    
                    // Safety check to prevent infinite loop
                    if ($num > 100) {
                        break;
                    }
                }
                
                $this->slug = $testSlug;
            }
        }
        
        // Handle duplication
        if ($this->duplicateOf instanceof SmartLink && !$this->id) {

            // Only generate unique slug on the primary site
            // For other sites, slug will be the same
            $primarySiteId = Craft::$app->sites->getPrimarySite()->id;

            if ($this->siteId == $primarySiteId || !$this->propagating) {
                // Ensure duplicateOf has its content loaded
                if (!$this->duplicateOf->fallbackUrl) {
                    $this->duplicateOf->loadContent();
                }

                // Copy required fields if not set
                if (!$this->title && $this->duplicateOf->title) {
                    $this->title = $this->duplicateOf->title;
                }
                if (!$this->fallbackUrl && $this->duplicateOf->fallbackUrl) {
                    $this->fallbackUrl = $this->duplicateOf->fallbackUrl;
                }
                
                // Copy all URL fields
                if (!$this->iosUrl && $this->duplicateOf->iosUrl) {
                    $this->iosUrl = $this->duplicateOf->iosUrl;
                }
                if (!$this->androidUrl && $this->duplicateOf->androidUrl) {
                    $this->androidUrl = $this->duplicateOf->androidUrl;
                }
                if (!$this->huaweiUrl && $this->duplicateOf->huaweiUrl) {
                    $this->huaweiUrl = $this->duplicateOf->huaweiUrl;
                }
                if (!$this->amazonUrl && $this->duplicateOf->amazonUrl) {
                    $this->amazonUrl = $this->duplicateOf->amazonUrl;
                }
                if (!$this->windowsUrl && $this->duplicateOf->windowsUrl) {
                    $this->windowsUrl = $this->duplicateOf->windowsUrl;
                }
                if (!$this->macUrl && $this->duplicateOf->macUrl) {
                    $this->macUrl = $this->duplicateOf->macUrl;
                }
                if (!$this->description && $this->duplicateOf->description) {
                    $this->description = $this->duplicateOf->description;
                }
                if (!$this->imageId && $this->duplicateOf->imageId) {
                    $this->imageId = $this->duplicateOf->imageId;
                }
                if (!$this->imageSize && $this->duplicateOf->imageSize) {
                    $this->imageSize = $this->duplicateOf->imageSize;
                }
                
                // Set author and post date
                if (!$this->authorId) {
                    $this->authorId = Craft::$app->user->id;
                }
                if (!$this->postDate) {
                    $this->postDate = new \DateTime();
                }
                
                // Generate unique slug only on primary site
                if ($this->siteId == $primarySiteId) {
                    $baseSlug = $this->duplicateOf->slug ?: $this->slug;
                    $testSlug = $baseSlug;
                    $num = 1;
                    
                    // Keep trying until we find a unique slug
                    while (true) {
                        // Check ALL elements regardless of status (enabled, disabled, pending, expired, trashed)
                        $exists = (new \craft\db\Query())
                            ->from('{{%smartlinkmanager}}')
                            ->where(['slug' => $testSlug])
                            ->exists();
                        
                        if (!$exists) {
                            break;
                        }
                        
                        $testSlug = $baseSlug . '-' . $num;
                        $num++;
                        
                        // Safety check to prevent infinite loop
                        if ($num > 100) {
                            break;
                        }
                    }
                    
                    $this->slug = $testSlug;
                    
                    // Store the generated slug so other sites can use it
                    self::$_generatedSlug = $testSlug;
                }
            } else {
                // For non-primary sites during propagation, use the generated slug
                if (isset(self::$_generatedSlug)) {
                    $this->slug = self::$_generatedSlug;
                }
            }
        }
        
        return parent::beforeValidate();
    }
    
    /**
     * @var string|null Temporary storage for generated slug during duplication
     */
    private static ?string $_generatedSlug = null;
    
    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        // If we have a generated slug and this is a propagating save, use it
        if (isset(self::$_generatedSlug) && $this->propagating && !$isNew) {
            $this->slug = self::$_generatedSlug;
        }

        // CRITICAL: Always set elements.enabled = true for SmartLinks
        // We use per-site enabling (elements_sites.enabled), not global enabling
        // Craft's default behavior sets elements.enabled=false when ANY site is disabled,
        // which breaks our queries that check "elements.enabled AND elements_sites.enabled"
        $this->enabled = true;

        // Don't save QR settings that match global defaults - only save custom values
        $settings = SmartLinkManager::$plugin->getSettings();

        // Normalize and compare colors (strip # and check if empty or matches default)
        $normalizeColor = fn($color) => $color ? strtolower(ltrim($color, '#')) : '';

        $thisColor = $normalizeColor($this->qrCodeColor);
        $defaultColor = $normalizeColor($settings->defaultQrColor);

        if (empty($thisColor) || $thisColor === $defaultColor) {
            $this->qrCodeColor = null;
        }

        $thisBgColor = $normalizeColor($this->qrCodeBgColor);
        $defaultBgColor = $normalizeColor($settings->defaultQrBgColor);
        if (empty($thisBgColor) || $thisBgColor === $defaultBgColor) {
            $this->qrCodeBgColor = null;
        }

        $thisEyeColor = $normalizeColor($this->qrCodeEyeColor);
        $defaultEyeColor = $normalizeColor($settings->qrEyeColor);
        if (empty($thisEyeColor) || $thisEyeColor === $defaultEyeColor) {
            $this->qrCodeEyeColor = null;
        }

        if (empty($this->qrLogoId) || $this->qrLogoId === $settings->defaultQrLogoId) {
            $this->qrLogoId = null;
        }

        return parent::beforeSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew): void
    {
        // Skip saving to custom tables if this is a revision
        if (!$this->getIsRevision()) {
            if (!$isNew) {
                $record = SmartLinkRecord::findOne($this->id);

                if (!$record) {
                    throw new \Exception('Invalid smart link ID: ' . $this->id);
                }
            } else {
                $record = new SmartLinkRecord();
                $record->id = $this->id;
            }

            // Save non-translatable fields to main table
            $record->title = $this->title;

            // Use the generated slug if available during duplication
            if (isset(self::$_generatedSlug) && $this->duplicateOf) {
                $record->slug = self::$_generatedSlug;
            } else {
                $record->slug = $this->slug;
            }

            $record->icon = $this->icon;
            $record->authorId = $this->authorId;
            $record->postDate = $this->postDate ? DateTimeHelper::toIso8601($this->postDate) : null;
            $record->dateExpired = $this->dateExpired ? DateTimeHelper::toIso8601($this->dateExpired) : null;
            $record->trackAnalytics = $this->trackAnalytics;
            $record->hideTitle = $this->hideTitle;
            $record->qrCodeEnabled = $this->qrCodeEnabled;
            $record->qrCodeSize = $this->qrCodeSize;
            $record->qrCodeColor = $this->qrCodeColor;
            $record->qrCodeBgColor = $this->qrCodeBgColor;
            $record->qrCodeFormat = $this->qrCodeFormat;
            $record->qrCodeEyeColor = $this->qrCodeEyeColor;
            $record->qrLogoId = $this->qrLogoId;
            $record->languageDetection = $this->languageDetection ? [] : null;
            $record->metadata = $this->_metadata ? json_encode($this->_metadata) : null;

            $record->save(false);

            // Save translatable fields to content table
            $contentRecord = SmartLinkContentRecord::findOne([
                'smartLinkId' => $this->id,
                'siteId' => $this->siteId,
            ]);

            if (!$contentRecord) {
                $contentRecord = new SmartLinkContentRecord();
                $contentRecord->smartLinkId = $this->id;
                $contentRecord->siteId = $this->siteId;
            }

            $contentRecord->title = $this->title;
            $contentRecord->description = $this->description;
            $contentRecord->iosUrl = $this->iosUrl;
            $contentRecord->androidUrl = $this->androidUrl;
            $contentRecord->huaweiUrl = $this->huaweiUrl;
            $contentRecord->amazonUrl = $this->amazonUrl;
            $contentRecord->windowsUrl = $this->windowsUrl;
            $contentRecord->macUrl = $this->macUrl;
            $contentRecord->fallbackUrl = $this->fallbackUrl;
            $contentRecord->imageId = $this->imageId;
            $contentRecord->imageSize = $this->imageSize;

            if (!$contentRecord->save(false)) {
                $this->logError('Failed to save content record', ['errors' => $contentRecord->getErrors()]);
            }
        }

        parent::afterSave($isNew);
    }
    
    /**
     * @inheritdoc
     */
    protected function attributeHtml(string $attribute): string
    {
        // Ensure content is loaded before displaying attributes
        if ($this->id && $this->siteId && $this->fallbackUrl === null) {
            $this->loadContent();
        }
        
        return parent::attributeHtml($attribute);
    }
    
    /**
     * @inheritdoc
     */
    public function afterPropagate(bool $isNew): void
    {
        parent::afterPropagate($isNew);
        
        // Clear the generated slug after propagation is complete
        if ($isNew && $this->duplicateOf) {
            self::$_generatedSlug = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // Delete analytics data
        SmartLinkManager::$plugin->analytics->deleteAnalyticsForLink($this);

        return true;
    }
    
    /**
     * @inheritdoc
     */
    public function afterValidate(): void
    {
        parent::afterValidate();
    }
}
