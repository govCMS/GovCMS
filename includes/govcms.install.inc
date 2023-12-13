<?php

/**
 * @file
 * GovCMS install helper functions.
 */

use Drupal\user\RoleInterface;
use Drupal\govcms\Event\ModuleInstalledEvent;

/**
 * Implements hook_modules_installed().
 */
function govcms_modules_installed($modules, $is_syncing) {
  // Predefined array of module names to check.
  $modulesToCheck = ['role_delegation', 'module_permissions'];

  // Check if any module from the predefined array is being installed.
  $modules_installed = array_intersect($modulesToCheck, $modules);
  if (!empty(($modules_installed)) && !$is_syncing) {
    // Instantiate our event.
    $event = new ModuleInstalledEvent($modules_installed);

    // Get the event_dispatcher service and dispatch the event.
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, ModuleInstalledEvent::MODULES_INSTALLED);
  }
}

/**
 * Provide the default permissions to a newly installed GovCMS site.
 */
function govcms_default_permissions() {
  $module_handler = \Drupal::moduleHandler();
  if ($module_handler->moduleExists('securitytxt')) {
    // Anonymous role.
    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, ['view securitytxt']);
    // Authenticated role.
    user_role_grant_permissions(RoleInterface::AUTHENTICATED_ID, ['view securitytxt']);
  }
}
