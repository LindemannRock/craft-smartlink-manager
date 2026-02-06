<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\variables;

use Craft;
use lindemannrock\smartlinkmanager\elements\db\SmartLinkQuery;
use lindemannrock\smartlinkmanager\elements\SmartLink;
use lindemannrock\smartlinkmanager\SmartLinkManager;

/**
 * SmartLink Manager Variable
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     1.0.0
 */
class SmartLinkManagerVariable
{
    /**
     * Returns a new SmartLinkQuery instance.
     *
     * @param array $criteria
     * @return SmartLinkQuery
     * @since 1.0.0
     */
    public function find(array $criteria = []): SmartLinkQuery
    {
        $query = SmartLink::find();
        
        if (!empty($criteria)) {
            Craft::configure($query, $criteria);
        }
        
        return $query;
    }

    /**
     * Returns smart links that match the given criteria.
     *
     * @param array $criteria
     * @return SmartLink[]
     * @since 1.0.0
     */
    public function all(array $criteria = []): array
    {
        return $this->find($criteria)->all();
    }

    /**
     * Returns one smart link that matches the given criteria.
     *
     * @param array $criteria
     * @return SmartLink|null
     * @since 1.0.0
     */
    public function one(array $criteria = []): ?SmartLink
    {
        return $this->find($criteria)->one();
    }

    /**
     * Returns a smart link by its ID.
     *
     * @param int $id
     * @return SmartLink|null
     * @since 1.0.0
     */
    public function getById(int $id): ?SmartLink
    {
        return SmartLink::find()->id($id)->one();
    }

    /**
     * Returns a smart link by its slug.
     *
     * @param string $slug
     * @return SmartLink|null
     * @since 1.0.0
     */
    public function getBySlug(string $slug): ?SmartLink
    {
        return SmartLink::find()->slug($slug)->one();
    }

    /**
     * Alias for getBySlug()
     *
     * @param string $slug
     * @return SmartLinkQuery
     * @since 1.0.0
     */
    public function slug(string $slug): SmartLinkQuery
    {
        return SmartLink::find()->slug($slug);
    }

    /**
     * Returns only active smart links.
     *
     * @return SmartLinkQuery
     * @since 1.0.0
     */
    public function active(): SmartLinkQuery
    {
        return SmartLink::find()->status(SmartLink::STATUS_ENABLED);
    }


    /**
     * Creates a new smart link (for demonstration/documentation)
     * Note: This doesn't save to database, just shows structure
     *
     * @param array $config
     * @return SmartLink
     * @since 1.0.0
     */
    public function create(array $config = []): SmartLink
    {
        $smartLink = new SmartLink();
        
        if (!empty($config)) {
            Craft::configure($smartLink, $config);
        }
        
        return $smartLink;
    }

    /**
     * Get analytics data for a smart link
     *
     * @param SmartLink $smartLink
     * @param array $criteria
     * @return array
     * @since 1.0.0
     */
    public function getAnalytics(SmartLink $smartLink, array $criteria = []): array
    {
        return SmartLinkManager::$plugin->analytics->getAnalytics($smartLink, $criteria);
    }

    /**
     * Get the module instance
     *
     * @return SmartLinkManager
     * @since 1.0.0
     */
    public function getModule(): SmartLinkManager
    {
        return SmartLinkManager::$plugin;
    }

    /**
     * Get module settings
     *
     * @return \lindemannrock\smartlinkmanager\models\Settings
     * @since 1.0.0
     */
    public function getSettings(): \lindemannrock\smartlinkmanager\models\Settings
    {
        return SmartLinkManager::$plugin->getSettings();
    }
}
