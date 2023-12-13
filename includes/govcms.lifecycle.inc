<?php

/**
 * @file
 * GovCMS lifecycle helper functions.
 */

require_once __DIR__ . '/../src/modules/Lifecycle.php';

use Drupal\Core\Extension\ExtensionLifecycle;
use Drupal\govcms\Modules\Lifecycle as ModuleLifeCycle;

/**
 * Implements hook_system_info_alter().
 */
function govcms_system_info_alter(&$info, $file, $type) {
  if ($type === 'module') {
    $module_name = $file->getName();

    $ModuleLifeCycle = new ModuleLifeCycle();

    if (in_array($module_name, ModuleLifeCycle::DEPRECATED_MODULES)) {
      // Update module information for deprecated modules.
      $ModuleLifeCycle->updateModuleInfo($info, ExtensionLifecycle::DEPRECATED);
    } elseif (in_array($module_name, ModuleLifeCycle::OBSOLETE_MODULES)) {
      // Update module information for obsolete modules.
      $ModuleLifeCycle->updateModuleInfo($info, ExtensionLifecycle::OBSOLETE);
    }
  }
}
