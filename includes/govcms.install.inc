<?php

/**
 * @file
 * GovCMS install helper functions.
 */

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Implements hook_modules_installed().
 */
function govcms_modules_installed($modules, $is_syncing) {
  $potential_conflicts = [
    'panelizer',
  ];

  if (!empty(array_intersect($modules, $potential_conflicts))) {
    \Drupal::messenger()
      ->addWarning(t('Some modules are deprecated. See the <a href=":govcms-docs">GovCMS support documentation</a> for more information.', [
        ':govcms-docs' => 'https://github.com/govCMS/GovCMS',
      ]));
  }

  if (!$is_syncing) {
    if (in_array('role_delegation', $modules)) {
      // Site administrator.
      if ($role = Role::load('govcms_site_administrator')) {
        $role->grantPermission('assign govcms_content_approver role');
        $role->grantPermission('assign govcms_content_author role');
        $role->grantPermission('assign govcms_site_administrator role');
        $role->save();
      }
    }

    if (in_array('module_permissions', $modules)) {
      // Site administrator.
      if ($role = Role::load('govcms_site_administrator')) {
        $role->grantPermission('administer managed modules');
        $role->grantPermission('administer managed modules permissions');
        $role->save();
      }
    }
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
