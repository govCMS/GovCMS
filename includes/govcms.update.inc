<?php

/**
 * @file
 * GovCMS update helper functions.
 */

/**
 * Implements hook_update_last_removed().
 */
function govcms_update_last_removed() {
  return 10012;
}

/**
 * Implements hook_update_N().
 * Uninstall admin_toolbar_links_access_filter module if marked as 'obsolete'.
 */
function govcms_update_11000() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['admin_toolbar_links_access_filter'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}