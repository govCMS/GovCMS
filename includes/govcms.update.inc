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
 * Implements hook_update_N().
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
 *
 * Installs events_log_track_file module if it is not already installed.
 */
function govcms_update_10008() {
    $moduleHandler = \Drupal::service('module_handler');
    $notInstalled = !$moduleHandler->moduleExists('event_log_track_file');
    if ($notInstalled) {
        $moduleInstaller = \Drupal::service('module_installer');
        $moduleInstaller->install(['event_log_track_file']);
    }
}

/**
 * Implements hook_update_N().
 * Uninstall aggregator module if marked as 'obsolete'.
 */
function govcms_update_10009() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');

  // List of modules to uninstall.
  $modules_to_uninstall = ['aggregator'];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}

/**
 * Implements hook_update_N().
 * Uninstall config_filter module if marked as 'obsolete'.
 * Uninstall jquery_ui_accordion module if marked as 'obsolete'.
 * Uninstall Rest UI module if marked as 'obsolete'.
 * Uninstall purge_purger_http module if marked as 'obsolete'.
 * Uninstall help_topics module if marked as 'obsolete'.
 * Uninstall tour module if marked as 'obsolete'.
 * Uninstall Page Manager modules if marked as 'obsolete'.
 */
function govcms_update_10010() {
  // Get the Lifecycle service.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');
   
  // List of modules to uninstall.
  $modules_to_uninstall = ['config_filter',
                           'jquery_ui_accordion',
                           'restui',
                           'purge_purger_http',
                           'help_topics',
                           'tour',
                           'page_manager',
                           'page_manager_ui',
                          ];

  // Call the service method to uninstall specified modules marked as 'obsolete'.
  $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
}

/**
 * Implements hook_update_N().
 * Update Stage File Proxy config if missing 'proxy_headers' setting.
 */
function govcms_update_10011() {
  $config_factory = \Drupal::service('config.factory');
  $config_name = 'stage_file_proxy.settings';
  $existing_configs = $config_factory->loadMultiple([$config_name]);

  if (!empty($existing_configs) && is_null($existing_configs[$config_name]->get('proxy_headers'))) {
    $config = $config_factory->getEditable($config_name);
    $config->set('proxy_headers', '')->save();
  }
}

/**
 * Implements hook_update_N().
 * Uninstall Panels module if Page Manager module is not in use.
 */
function govcms_update_10012() {
  // Get required services.
  $lifecycle_service = \Drupal::service('govcms.modules.lifecycle');
  $module_handler = \Drupal::service('module_handler');

  // List of modules to uninstall.
  $modules_to_uninstall = ['panels'];

  // Only run if Page Manager not present
  if (!$module_handler->moduleExists('page_manager')) {
    // Call the service method to uninstall specified modules marked as 'obsolete'.
    $lifecycle_service->uninstallObsoleteModules($modules_to_uninstall);
  }
}
