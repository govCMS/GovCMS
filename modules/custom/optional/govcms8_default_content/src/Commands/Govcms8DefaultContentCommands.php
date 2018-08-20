<?php

namespace Drupal\govcms8_default_content\Commands;

use Drush\Commands\DrushCommands;
use Drupal\govcms8_default_content\InstallHelper;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class Govcms8DefaultContentCommands extends DrushCommands {

  /**
   * Import default content for GovCMS8
   *
   *
   * @command govcms8:default-content-import
   * @aliases govcms-import,govcms8-default-content-import
   */
  public function defaultContentImport() {
    \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->importContent();
    drush_log(dt('Imported GovCMS8 default content.'), 'ok');
  }

  /**
   * Rollback default content for GovCMS8
   *
   *
   * @command govcms8:default-content-rollback
   * @aliases govcms-rollback,govcms8-default-content-rollback
   */
  public function defaultContentRollback() {
    \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->deleteImportedContent();
    drush_log(dt('Rolled back GovCMS8 default content.'), 'ok');
  }

}
