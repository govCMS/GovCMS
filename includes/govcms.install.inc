<?php

/**
 * @file
 * GovCMS install helper functions.
 */

use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Implements hook_system_info_alter(arry &$info, Extention $file, string $type).
 * array $info: The .info.yml file contents, passed by reference so that it can be altered.
 * \Drupal\Core\Extension\Extension $file: Full information about the module or theme.
 * string $type: Either 'module' or 'theme', depending on the type of .info.yml file that was passed.
 */
function govcms_system_info_alter(&$info, $file, $type) {
  // To set a module to be obsolte, add to $obsoleteModules[module_name1, module_name2...] array
  $obsoleteModules = ["config_filter"];
  
  if ($type == 'module' && in_array($file->getName(), $obsoleteModules)) {
    $info['lifecycle'] = 'obsolete';
    $info['lifecycle_link'] = 'https://github.com/GovCMS/GovCMS';
  }
}

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
