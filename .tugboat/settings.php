<?php

/**
 * GovCMS Drupal settings for Tugboat only.
 *
 * Most settings based on Drupal defaults.
 * Comments removed for brevity.
 *
 * @see https://git.drupalcode.org/project/drupal/blob/9.{*}.x/sites/default/default.settings.php
 */

$databases['default']['default'] = [
  'database' => 'tugboat',
  'username' => 'tugboat',
  'password' => 'tugboat',
  'prefix' => '',
  'host' => 'mysql',
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];

$settings['config_sync_directory'] = 'sites/default/files/sync';

// Private path.
$settings['file_private_path'] = '/var/lib/tugboat/files-private';

// Use the TUGBOAT_REPO_ID to generate a hash salt for Tugboat sites.
$settings['hash_salt'] = hash('sha256', getenv('TUGBOAT_REPO_ID'));
$settings['entity_update_batch_size'] = 50;
$settings['trusted_host_patterns'] = ['.*'];
$settings['update_free_access'] = FALSE;
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

if (PHP_SAPI === 'cli') {
  ini_set('memory_limit', '256M');
}

// Debugging.
$config['system.logging']['error_level'] = 'verbose';
error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

/**
 * Load security services definition file.
 */
if (file_exists($app_root . '/' . $site_path . '/security.settings.php')) {
  include $app_root . '/' . $site_path . '/security.settings.php';
}
