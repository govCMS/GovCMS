<?php

/**
 * @file
 * Drush commands used for importing and rolling back content.
 */

use Drupal\govcms8_default_content\InstallHelper;

/**
 * Implements hook_drush_command().
 */
function govcms8_default_content_drush_command() {
  $commands['govcms8-default-content-import'] = [
    'description' => 'Import default content for GovCMS8',
    'aliases' => ['govcms-import'],
  ];

  $commands['govcms8-default-content-rollback'] = [
    'description' => 'Rollback default content for GovCMS8',
    'aliases' => ['govcms-rollback'],
  ];

  return $commands;
}

/**
 * Content import drush command.
 */
function drush_govcms8_default_content_import() {
  \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->importContent();
  drush_log(dt('Imported GovCMS8 default content.'), 'ok');
}

/**
 * Content rollback drush command.
 */
function drush_govcms8_default_content_rollback() {
  \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->deleteImportedContent();
  drush_log(dt('Rolled back GovCMS8 default content.'), 'ok');
}
