<?php
$tableEventnames = $modx->getFullTablename('system_eventnames');
$tablePlugins    = $modx->getFullTablename('site_plugins');
$tableEvents     = $modx->getFullTablename('site_plugin_events');
$npCitiesTable = $modx->getFullTablename('np_cities');

$modx->db->query("
    CREATE TABLE IF NOT EXISTS {$npCitiesTable} (
      `id` int(6) unsigned NOT NULL auto_increment,
      `ref` varchar(50) NOT NULL,
      `city` varchar(60) NOT NULL,
      `city_ru` varchar(65) NOT NULL,
      `update_status` int(1) unsigned NOT NULL DEFAULT 0,
       PRIMARY KEY (`id`),
       INDEX (`ref`)  
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$npDepartmentsTable = $modx->getFullTablename('np_departments');

$modx->db->query("
    CREATE TABLE IF NOT EXISTS {$npDepartmentsTable} (
      `id` int(6) unsigned NOT NULL auto_increment,
      `ref` varchar(50) NOT NULL,
      `city_ref` varchar(50) NOT NULL,
      `num` int(3) unsigned NOT NULL,
      `address` varchar(120) NOT NULL,
      `address_ru` varchar(120) NOT NULL,
      `update_status` int(1) unsigned NOT NULL,        
      PRIMARY KEY (`id`),
      INDEX (`ref`)  
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");


// remove installer
$query = $modx->db->select('id', $tablePlugins, "`name` = 'CommerceDeliveryNovaposhtaPickupInstall'");

if ($id = $modx->db->getValue($query)) {
    $modx->db->delete($tablePlugins, "`id` = '$id'");
    $modx->db->delete($tableEvents, "`pluginid` = '$id'");
};
