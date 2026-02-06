<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2025 LindemannRock
 */

namespace lindemannrock\smartlinkmanager\migrations;

use craft\db\Migration;
use craft\helpers\Db;
use craft\helpers\StringHelper;

/**
 * SmartLink Manager Install Migration
 *
 * @author    LindemannRock
 * @package   SmartLinkManager
 * @since     1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Create the smartlinkmanager table
        if (!$this->db->tableExists('{{%smartlinkmanager}}')) {
            $this->createTable('{{%smartlinkmanager}}', [
                'id' => $this->integer()->notNull(),
                'title' => $this->string()->notNull(),
                'slug' => $this->string()->notNull(),
                'trackAnalytics' => $this->boolean()->defaultValue(true),
                'qrCodeEnabled' => $this->boolean()->defaultValue(true),
                'qrCodeSize' => $this->integer()->defaultValue(200),
                'qrCodeColor' => $this->string(7)->null(),
                'qrCodeBgColor' => $this->string(7)->null(),
                'qrCodeEyeColor' => $this->string(7)->null(),
                'qrCodeFormat' => $this->string(10)->null(),
                'qrLogoId' => $this->integer()->null(),
                'hideTitle' => $this->boolean()->defaultValue(false)->notNull(),
                'languageDetection' => $this->boolean()->defaultValue(false),
                'metadata' => $this->json()->null(),
                'authorId' => $this->integer()->null(),
                'postDate' => $this->dateTime()->null(),
                'dateExpired' => $this->dateTime()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
                'PRIMARY KEY(id)',
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinkmanager}}', ['slug'], true);
            $this->createIndex(null, '{{%smartlinkmanager}}', ['dateCreated']);
            $this->createIndex(null, '{{%smartlinkmanager}}', ['authorId']);
            $this->createIndex(null, '{{%smartlinkmanager}}', ['postDate']);
            $this->createIndex(null, '{{%smartlinkmanager}}', ['dateExpired']);
            $this->createIndex(null, '{{%smartlinkmanager}}', ['qrLogoId']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinkmanager}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinkmanager}}', ['authorId'], '{{%users}}', ['id'], 'SET NULL');
            $this->addForeignKey(null, '{{%smartlinkmanager}}', ['qrLogoId'], '{{%assets}}', ['id'], 'SET NULL');
        }

        // Create the smartlinkmanager_content table for multi-site support
        if (!$this->db->tableExists('{{%smartlinkmanager_content}}')) {
            $this->createTable('{{%smartlinkmanager_content}}', [
                'id' => $this->primaryKey(),
                'smartLinkId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->notNull(),
                'title' => $this->string()->notNull(),
                'description' => $this->text()->null(),
                'iosUrl' => $this->text()->null(),
                'androidUrl' => $this->text()->null(),
                'huaweiUrl' => $this->text()->null(),
                'amazonUrl' => $this->text()->null(),
                'windowsUrl' => $this->text()->null(),
                'macUrl' => $this->text()->null(),
                'fallbackUrl' => $this->text()->notNull(),
                'imageId' => $this->integer()->null(),
                'imageSize' => $this->string(2)->defaultValue('xl')->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinkmanager_content}}', ['smartLinkId', 'siteId'], true);
            $this->createIndex(null, '{{%smartlinkmanager_content}}', ['siteId']);
            $this->createIndex(null, '{{%smartlinkmanager_content}}', ['imageId']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinkmanager_content}}', ['smartLinkId'], '{{%smartlinkmanager}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinkmanager_content}}', ['siteId'], '{{%sites}}', ['id'], 'CASCADE');
            $this->addForeignKey(null, '{{%smartlinkmanager_content}}', ['imageId'], '{{%assets}}', ['id'], 'SET NULL');
        }

        // Create the smartlinkmanager_analytics table
        if (!$this->db->tableExists('{{%smartlinkmanager_analytics}}')) {
            $this->createTable('{{%smartlinkmanager_analytics}}', [
                'id' => $this->primaryKey(),
                'linkId' => $this->integer()->notNull(),
                'siteId' => $this->integer()->null(),
                'deviceType' => $this->string(50)->null(),
                'deviceBrand' => $this->string(50)->null(),
                'deviceModel' => $this->string(100)->null(),
                'osName' => $this->string(50)->null(),
                'osVersion' => $this->string(50)->null(),
                'browser' => $this->string(100)->null(),
                'browserVersion' => $this->string(20)->null(),
                'browserEngine' => $this->string(50)->null(),
                'clientType' => $this->string(50)->null(),
                'isRobot' => $this->boolean()->defaultValue(false),
                'isMobileApp' => $this->boolean()->defaultValue(false),
                'botName' => $this->string(100)->null(),
                'country' => $this->string(2)->null(),
                'city' => $this->string(100)->null(),
                'region' => $this->string(100)->null(),
                'timezone' => $this->string(50)->null(),
                'latitude' => $this->decimal(10, 8)->null(),
                'longitude' => $this->decimal(11, 8)->null(),
                'language' => $this->string(10)->null(),
                'referrer' => $this->string()->null(),
                'ip' => $this->string(64)->null(),
                'userAgent' => $this->text()->null(),
                'metadata' => $this->text()->null(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes for performance
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['linkId', 'dateCreated']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['siteId']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['deviceType']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['country']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['dateCreated']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['city']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['region']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['deviceBrand']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['osName']);
            $this->createIndex(null, '{{%smartlinkmanager_analytics}}', ['clientType']);

            // Add foreign keys
            $this->addForeignKey(null, '{{%smartlinkmanager_analytics}}', ['linkId'], '{{%smartlinkmanager}}', ['id'], 'CASCADE');
        }

        // Create the smartlinkmanager_settings table
        if (!$this->db->tableExists('{{%smartlinkmanager_settings}}')) {
            $this->createTable('{{%smartlinkmanager_settings}}', [
                'id' => $this->primaryKey(),
                // Plugin settings
                'pluginName' => $this->string(255)->notNull()->defaultValue('SmartLink Manager'),
                // Site settings
                'enabledSites' => $this->text()->null()->comment('JSON array of enabled site IDs'),
                // Asset/Volume settings
                'imageVolumeUid' => $this->string()->null(),
                // URL settings
                'slugPrefix' => $this->string(50)->notNull()->defaultValue('go'),
                'qrPrefix' => $this->string(50)->notNull()->defaultValue('go/qr'),
                // QR Code settings
                'defaultQrSize' => $this->integer()->notNull()->defaultValue(256),
                'defaultQrColor' => $this->string(7)->notNull()->defaultValue('#000000'),
                'defaultQrBgColor' => $this->string(7)->notNull()->defaultValue('#FFFFFF'),
                'defaultQrFormat' => $this->string(3)->notNull()->defaultValue('png'),
                'defaultQrErrorCorrection' => $this->string(1)->notNull()->defaultValue('M'),
                'defaultQrMargin' => $this->integer()->notNull()->defaultValue(4),
                'qrModuleStyle' => $this->string(10)->notNull()->defaultValue('square'),
                'qrEyeStyle' => $this->string(10)->notNull()->defaultValue('square'),
                'qrEyeColor' => $this->string(7)->null(),
                'enableQrLogo' => $this->boolean()->notNull()->defaultValue(false),
                'qrLogoVolumeUid' => $this->string()->null(),
                'defaultQrLogoId' => $this->integer()->null(),
                'qrLogoSize' => $this->integer()->notNull()->defaultValue(20),
                'enableQrCodeCache' => $this->boolean()->notNull()->defaultValue(true),
                'qrCodeCacheDuration' => $this->integer()->notNull()->defaultValue(86400),
                'cacheStorageMethod' => $this->string(10)->notNull()->defaultValue('file')->comment('Cache storage method: file or redis'),
                'enableQrDownload' => $this->boolean()->notNull()->defaultValue(true),
                'qrDownloadFilename' => $this->string()->notNull()->defaultValue('{slug}-qr-{size}'),
                // Analytics settings
                'enableAnalytics' => $this->boolean()->notNull()->defaultValue(true),
                'analyticsRetention' => $this->integer()->notNull()->defaultValue(90),
                'anonymizeIpAddress' => $this->boolean()->notNull()->defaultValue(false),
                // Template settings
                'redirectTemplate' => $this->string()->null(),
                'qrTemplate' => $this->string()->null(),
                // Device & Geo Detection
                'enableGeoDetection' => $this->boolean()->notNull()->defaultValue(false),
                'geoProvider' => $this->string(50)->notNull()->defaultValue('ip-api.com'),
                'geoApiKey' => $this->string(255)->null(),
                'cacheDeviceDetection' => $this->boolean()->notNull()->defaultValue(true),
                'deviceDetectionCacheDuration' => $this->integer()->notNull()->defaultValue(3600),
                'languageDetectionMethod' => $this->string(10)->notNull()->defaultValue('browser'),
                // Interface settings
                'itemsPerPage' => $this->integer()->notNull()->defaultValue(100),
                'notFoundRedirectUrl' => $this->string()->notNull()->defaultValue('/'),
                // Export settings
                'includeDisabledInExport' => $this->boolean()->defaultValue(false),
                'includeExpiredInExport' => $this->boolean()->notNull()->defaultValue(false),
                // Integration settings
                'enabledIntegrations' => $this->text()->null()->comment('JSON array of enabled integration handles'),
                'seomaticTrackingEvents' => $this->text()->null()->comment('JSON array of event types to track in SEOmatic'),
                'seomaticEventPrefix' => $this->string(50)->defaultValue('smart_links')->comment('Event prefix for GTM/GA events'),
                'redirectManagerEvents' => $this->text()->null()->comment('JSON array of redirect manager event types'),
                // Logging
                'logLevel' => $this->string(20)->notNull()->defaultValue('error'),
                // Timestamps
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            // Create indexes
            $this->createIndex(null, '{{%smartlinkmanager_settings}}', ['enableAnalytics']);
            $this->createIndex(null, '{{%smartlinkmanager_settings}}', ['enableGeoDetection']);
            $this->createIndex(null, '{{%smartlinkmanager_settings}}', ['cacheDeviceDetection']);

            // Add foreign key for logo
            $this->addForeignKey(null, '{{%smartlinkmanager_settings}}', ['defaultQrLogoId'], '{{%assets}}', ['id'], 'SET NULL');

            // Insert default settings row
            $this->insert('{{%smartlinkmanager_settings}}', [
                'dateCreated' => Db::prepareDateForDb(new \DateTime()),
                'dateUpdated' => Db::prepareDateForDb(new \DateTime()),
                'uid' => StringHelper::UUID(),
            ]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Drop tables in reverse order due to foreign key constraints
        $this->dropTableIfExists('{{%smartlinkmanager_analytics}}');
        $this->dropTableIfExists('{{%smartlinkmanager_content}}');
        $this->dropTableIfExists('{{%smartlinkmanager_settings}}');
        $this->dropTableIfExists('{{%smartlinkmanager}}');

        return true;
    }
}
