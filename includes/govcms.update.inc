<?php

/**
 * @file
 * GovCMS update helper functions.
 */

 use Drupal\Core\Extension\ExtensionLifecycle;

/**
 * Implements hook_update_last_removed().
 */
function govcms_update_last_removed() {
  return 9003;
}

/**
 * Enables ckeditor5 module if it is not already enabled.
 */
function govcms_update_10000() {
  // Check if the module is already installed.
  if (!\Drupal::moduleHandler()->moduleExists('ckeditor5')) {
    // If the module is not installed, enable it.
    \Drupal::service('module_installer')->install(['ckeditor5']);
  }
}

/**
 * Transfer obsolete admin theme to Claro.
 */
function govcms_update_10001() {
  // Load the available themes.
  $theme_extension_list = \Drupal::service('extension.list.theme');
  $theme_extension_list->reset();
  // Load the system.theme config.
  $config = \Drupal::configFactory()->getEditable('system.theme');
  $admin_theme = $config->get('admin');
  $admin_theme_info = $theme_extension_list->getExtensionInfo($admin_theme);

  if (isset($admin_theme_info[ExtensionLifecycle::LIFECYCLE_IDENTIFIER])) {
    if ($admin_theme_info[ExtensionLifecycle::LIFECYCLE_IDENTIFIER] === ExtensionLifecycle::OBSOLETE) {
      // Check if the Claro theme is installed.
      $claro_theme = \Drupal::service('theme_handler')->themeExists('claro');
      if (!$claro_theme) {
        // Install the Claro theme.
        \Drupal::service('theme_installer')->install(['claro']);
      }
      // Set claro as the default admin theme.
      $config->set('admin', 'claro')->save();
    }
  }
}

/**
 * Update the pathauto.settings configuration if 'safe_tokens' is not set.
 */
function govcms_update_10002() {
  // Check if the pathauto module is enabled.
  if (\Drupal::moduleHandler()->moduleExists('pathauto')) {
    // Load the current pathauto settings configuration.
    $config = \Drupal::configFactory()->getEditable('pathauto.settings');
    // Check if 'safe_tokens' is not set.
    if (!$config->get('safe_tokens')) {
      // Set default safe tokens.
      $safe_tokens = [
        'alias',
        'alias',
        'path',
        'join-path',
        'login-url',
        'url',
        'url-brief',
      ];
      // Set 'safe_tokens'.
      $config->set('safe_tokens', $safe_tokens)->save();
    }
  }
}

/**
 * Implements hook_update_N().
 * Disables specified modules if marked as 'obsolete'.
 */
function govcms_update_10003() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['color', 'hal', 'module_filter', 'tracker'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}

/**
 * Implements hook_update_N().
 * Disables specified modules if marked as 'obsolete'.
 */
function govcms_update_10004() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['features', 'features_ui', 'govcms_media', 'govcms_news_and_media', 'govcms_search', 'govcms_standard_page'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}

/**
 * Implements hook_update_N().
 * Disables swiftmailer module if marked as 'obsolete'.
 */
function govcms_update_10005() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['swiftmailer'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}

/**
 * Implements hook_update_N().
 * Uninstall rdf, quickedit, and adminimal_admin_toolbar module if marked as 'obsolete'.
 */
function govcms_update_10006() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['rdf', 'quickedit', 'adminimal_admin_toolbar'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}
/**
 * Uninstall rdf, quickedit, and adminimal_admin_toolbar module if marked as 'obsolete'.
 */
function govcms_update_10007() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['block_place'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}
  
/**
 * Implements hook_update_N().
 * Install events_log_track_file module for any sites with file_to_media module
 * installed and events_log_track_file uninstalled.
 */
function govcms_update_10008() {
    $moduleHandler = \Drupal::service('module_handler');
    $dependencyNotMet = $moduleHandler->moduleExists('file_to_media') && !$moduleHandler->moduleExists('event_log_track_file');
    if ($dependencyNotMet) {
        $moduleInstaller = \Drupal::service('module_installer');
        $moduleInstaller->install(['event_log_track_file']);
    }
}
