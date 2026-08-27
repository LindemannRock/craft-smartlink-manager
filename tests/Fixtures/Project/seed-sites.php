<?php
/**
 * SmartLink Manager plugin for Craft CMS 5.x
 *
 * @link      https://lindemannrock.com
 * @copyright Copyright (c) 2026 LindemannRock
 */

declare(strict_types=1);

use craft\models\Site;

$projectRoot = $_SERVER['SMARTLINK_MANAGER_TEST_PROJECT_ROOT'] ?? null;
$expected = '#^' . preg_quote(rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR), '#')
    . '/smartlink-manager-fixture-[a-f0-9]{16}$#';
if (!is_string($projectRoot) || preg_match($expected, $projectRoot) !== 1) {
    throw new RuntimeException('Site seeding requires the exact disposable project boundary.');
}

require $projectRoot . '/bootstrap.php';
require $projectRoot . '/vendor/craftcms/cms/bootstrap/console.php';

$sites = Craft::$app->getSites();
$primary = $sites->getPrimarySite();
$primary->handle = 'en';
if (!$sites->saveSite($primary)) {
    throw new RuntimeException('Unable to normalize the primary fixture site: ' . json_encode($primary->getErrors()));
}
foreach ([
    ['name' => 'SmartLink DE', 'handle' => 'smartlinkDe', 'language' => 'de-DE', 'baseUrl' => 'https://de.smartlink.example.test'],
    ['name' => 'SmartLink FR', 'handle' => 'smartlinkFr', 'language' => 'fr-FR', 'baseUrl' => 'https://fr.smartlink.example.test'],
    ['name' => 'SmartLink NL', 'handle' => 'smartlinkNl', 'language' => 'nl-NL', 'baseUrl' => 'https://nl.smartlink.example.test'],
    ['name' => 'SmartLink ES', 'handle' => 'smartlinkEs', 'language' => 'es-ES', 'baseUrl' => 'https://es.smartlink.example.test'],
    ['name' => 'SmartLink AR', 'handle' => 'smartlinkAr', 'language' => 'ar', 'baseUrl' => 'https://ar.smartlink.example.test'],
    ['name' => 'SmartLink IT', 'handle' => 'smartlinkIt', 'language' => 'it-IT', 'baseUrl' => 'https://it.smartlink.example.test'],
    ['name' => 'SmartLink JA', 'handle' => 'smartlinkJa', 'language' => 'ja-JP', 'baseUrl' => 'https://ja.smartlink.example.test'],
] as $definition) {
    $site = new Site([
        ...$definition,
        'groupId' => $primary->groupId,
        'primary' => false,
        'enabled' => true,
    ]);
    if (!$sites->saveSite($site)) {
        throw new RuntimeException('Unable to save fixture site: ' . json_encode($site->getErrors()));
    }
}

if (count($sites->getAllSites()) !== 8) {
    throw new RuntimeException('SmartLink Manager fixture must contain exactly eight sites.');
}
