<?php

/**
 * @file
 * GovCMS update helper functions.
 */

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
 * Transfer Adminimal theme to Claro. (experimental/test only)
 */
function govcms_update_10001() {
  // Load the system.theme config.
  $config = \Drupal::configFactory()->getEditable('system.theme');
  $admin_theme = $config->get('admin');
  if ($admin_theme == 'adminimal_theme') {
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

/**
 * Removes the entity_embed filter from the rich_text format.
 * (experimental/test only)
 */
function govcms_update_10002() {
  // Check if the filter plugin exists.
  $plugin_id = 'entity_embed';
  $filter_format_id = 'rich_text';
  $filter_config = \Drupal::service('config.factory')->getEditable('filter.format.' . $filter_format_id);
  $filters = $filter_config->get('filters');
  if (isset($filters[$plugin_id])) {
    // Remove the filter plugin.
    unset($filters[$plugin_id]);
    $filter_config->set('filters', $filters);
    $filter_config->save();
  }
}

/**
 * Replaces the ckeditor with ckeditor5 in the rich_text format.
 * (experimental/test only)
 */
function govcms_update_10003() {
  // Get the rich_text configuration.
  $config = \Drupal::configFactory()->getEditable('editor.editor.rich_text');

  // Set ckeditor5 as a dependency module.
  $config->set('dependencies.module', ['ckeditor5']);

  // Set the editor to ckeditor5.
  $config->set('editor', 'ckeditor5');

  // Set the default settings for ckeditor5.
  $settings = [
    // Add any additional settings as needed.
  ];
  $config->set('settings', $settings);

  // Save the updated configuration.
  $config->save();
}

/**
 * Update the pathauto.settings configuration if 'safe_tokens' is not set.
 * (experimental/test only)
 */
function govcms_update_10004() {
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
 * Uninstall outdated modules from GovCMS Drupal 9.
 */
function govcms_update_10005() {
  // Placeholder.
}
