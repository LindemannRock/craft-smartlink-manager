<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use lindemannrock\smartlinkmanager\elements\SmartLink;

/**
 * SmartLinkQuery represents a SELECT SQL statement for smart links.
 *
 * @method SmartLink[]|array all($db = null)
 * @method SmartLink|array|null one($db = null)
 * @method SmartLink|array|null nth(int $n, ?Connection $db = null)
 * @since 1.0.0
 */
class SmartLinkQuery extends ElementQuery
{
    // Store slug in custom property to prevent Craft from adding elements_sites.slug filter
    /**
     * @var mixed|null Smart link slug filter stored separately from the base query
     */
    private mixed $_smartLinkSlug = null;

    /**
     * @var mixed|null Whether to filter by analytics tracking state
     */
    public mixed $trackAnalytics = null;

    /**
     * @var mixed|null Whether to filter by QR code enabled state
     */
    public mixed $qrCodeEnabled = null;

    /**
     * @var int|null Field ID for relational queries
     */
    public ?int $fieldId = null;

    /**
     * @var int|null Owner element ID for relational queries
     */
    public ?int $ownerId = null;

    /**
     * Narrows the query results based on the smart links' slugs.
     *
     * NOTE: We store slug in a private property to prevent Craft from
     * adding elements_sites.slug to the query. SmartLinks store slug in
     * the smartlinks table, not elements_sites.
     */
    public function slug($value): static
    {
        $this->_smartLinkSlug = $value;
        // DON'T set $this->slug or call parent - prevents elements_sites.slug filter
        return $this;
    }


    /**
     * Narrows the query results based on whether the smart links track analytics.
     */
    public function trackAnalytics($value = true): static
    {
        $this->trackAnalytics = $value;
        return $this;
    }

    /**
     * Narrows the query results based on whether the smart links have QR codes enabled.
     */
    public function qrCodeEnabled($value = true): static
    {
        $this->qrCodeEnabled = $value;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status): mixed
    {
        // Always consider "now" to be the current time @ 59 seconds into the minute.
        // This makes smart link queries more cacheable, since they only change once every
        // minute, while not excluding smart links published in the past minute.
        $now = new \DateTime();
        $now->setTime((int)$now->format('H'), (int)$now->format('i'), 59);
        $currentTimeDb = Db::prepareDateForDb($now);

        return match ($status) {
            SmartLink::STATUS_ENABLED => [
                'and',
                ['elements.enabled' => true, 'elements_sites.enabled' => true],
                ['<=', 'smartlinkmanager.postDate', $currentTimeDb],
                ['or', ['smartlinkmanager.dateExpired' => null], ['>', 'smartlinkmanager.dateExpired', $currentTimeDb]],
            ],
            SmartLink::STATUS_PENDING => [
                'and',
                ['elements.enabled' => true, 'elements_sites.enabled' => true],
                ['>', 'smartlinkmanager.postDate', $currentTimeDb],
            ],
            SmartLink::STATUS_EXPIRED => [
                'and',
                ['elements.enabled' => true, 'elements_sites.enabled' => true],
                ['not', ['smartlinkmanager.dateExpired' => null]],
                ['<=', 'smartlinkmanager.dateExpired', $currentTimeDb],
            ],
            default => parent::statusCondition($status),
        };
    }

    protected function beforePrepare(): bool
    {
        // Call parent first to handle revisions/drafts properly
        if (!parent::beforePrepare()) {
            return false;
        }

        // Join in the smartlinkmanager table
        $this->joinElementTable('smartlinkmanager');

        // Select the columns from main table (non-translatable fields)
        $this->query->select([
            'smartlinkmanager.title',
            'smartlinkmanager.slug',
            'smartlinkmanager.authorId',
            'smartlinkmanager.postDate',
            'smartlinkmanager.dateExpired',
            'smartlinkmanager.trackAnalytics',
            'smartlinkmanager.hideTitle',
            'smartlinkmanager.qrCodeEnabled',
            'smartlinkmanager.qrCodeSize',
            'smartlinkmanager.qrCodeColor',
            'smartlinkmanager.qrCodeBgColor',
            'smartlinkmanager.qrCodeFormat',
            'smartlinkmanager.qrCodeEyeColor',
            'smartlinkmanager.qrLogoId',
            'smartlinkmanager.languageDetection',
            'smartlinkmanager.metadata',
            // Ensure we get the enabled status from elements_sites
            'elements_sites.enabled',
        ]);

        if ($this->_smartLinkSlug !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinkmanager.slug', $this->_smartLinkSlug));
        }

        // Note: The enabled/disabled status is handled by Craft's ElementQuery
        // through the elements_sites.enabled column, not smartlinkmanager.active

        if ($this->trackAnalytics !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinkmanager.trackAnalytics', is_bool($this->trackAnalytics) ? (int)$this->trackAnalytics : $this->trackAnalytics));
        }

        if ($this->qrCodeEnabled !== null) {
            $this->subQuery->andWhere(Db::parseParam('smartlinkmanager.qrCodeEnabled', is_bool($this->qrCodeEnabled) ? (int)$this->qrCodeEnabled : $this->qrCodeEnabled));
        }

        return true;
    }
}
