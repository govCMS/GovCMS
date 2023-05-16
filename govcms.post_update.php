<?php

/**
 * @file
 * Post update functions for GovCMS.
 */

/**
 * Updates the visibility condition for the node type in the block module.
 */
function govcms_post_update_replace_block_node_type_condition() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('block.block.') as $block_config_name) {
    $block = $config_factory->getEditable($block_config_name);

    if ($block->get('visibility.node_type')) {
      $configuration = $block->get('visibility.node_type');
      $configuration['id'] = 'entity_bundle:node';
      $block->set('visibility.entity_bundle:node', $configuration);
      $block->clear('visibility.node_type');
      $block->save(TRUE);
    }
  }
}

/**
 * Replaces the CKEditor to 5 from 4.
 */
function govcms_post_update_replace_ckeditor5() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('editor.editor.') as $editor_config_name) {
    $editor = $config_factory->getEditable($editor_config_name);

    if ($editor->get('editor') == 'ckeditor') {
      // Set ckeditor5 as a dependency module.
      $editor->set('dependencies.module', ['ckeditor5']);
      // Set the editor to ckeditor5.
      $editor->set('editor', 'ckeditor5');
      // Set the default settings for ckeditor5.
      $settings = [
        // Add any additional settings as needed.
      ];
      $editor->set('settings', $settings);
      // Save the updated configuration.
      $editor->save();
    }
  }
}
