<?php

/**
 * GovCMS Drupal settings for Tugboat only.
 *
 * Most settings based on Drupal defaults.
 * Comments removed for brevity.
 * @see https://git.drupalcode.org/project/drupal/blob/8.8.x/sites/default/default.settings.php
 */

$databases = [];

$config_directories = array(
  CONFIG_SYNC_DIRECTORY => 'sites/default/files/sync',
);

$settings['hash_salt'] = 'djfu983roawei90tk3;mva9023i9523';

$settings['update_free_access'] = FALSE;

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['entity_update_batch_size'] = 50;

$databases['default']['default'] = array (
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
if (PHP_SAPI === 'cli') {
  ini_set('memory_limit', '256M');
}

$settings['trusted_host_patterns'] = ['.*'];

$config['system.logging']['error_level'] = 'verbose';
error_reporting(E_COMPILE_ERROR|E_RECOVERABLE_ERROR|E_ERROR|E_CORE_ERROR);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
