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
    // Clear the caches after installing the module.
    drupal_flush_all_caches();
  }
}

/**
 * Transfer Adminimal theme to Claro.
 */
function govcms_update_10001() {
  // Check if the current admin theme is adminimal.
  $config = \Drupal::configFactory()->getEditable('system.theme');
  $admin_theme = $config->get('admin');
  if ($admin_theme == 'adminimal_theme') {
    // Set claro as the default admin theme.
    $config->set('admin', 'claro')->save();
    // Enable claro theme.
    \Drupal::service('theme_installer')->install(['claro']);
    // Clear the caches after changing the theme settings.
    drupal_flush_all_caches();
  }
}

/**
 * Removes the entity_embed filter from the rich_text format.
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
    // Clear the caches after removing the filter.
    drupal_flush_all_caches();
  }
}

/**
 * Replaces the ckeditor with ckeditor5 in the rich_text format. (experimental)
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
 * Remove outdated modules from GovCMS Drupal 9.
 */
function govcms_update_10004() {
  // Placeholder.
}
