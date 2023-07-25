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
 * Updates the visibility condition for the node type in the context module.
 */
function govcms_post_update_replace_context_node_type_condition() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('context.context.') as $context_config_name) {
    $context = $config_factory->getEditable($context_config_name);

    if ($context->get('conditions.node_type')) {
      $configuration = $context->get('conditions.node_type');
      $configuration['id'] = 'entity_bundle:node';
      $context->set('conditions.entity_bundle:node', $configuration);
      $context->clear('conditions.node_type');
      $context->save(TRUE);
    }
  }
}

/**
 * Updates the condition for the node type in the pathauto module.
 */
function govcms_post_update_replace_pathauto_node_type_condition() {
  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('pathauto.pattern.') as $pattern_config_name) {
    $pattern_config = $config_factory->getEditable($pattern_config_name);

    if ($pattern_config->get('type') === 'canonical_entities:node') {
      $selection_criteria = $pattern_config->get('selection_criteria');
      foreach ($selection_criteria as $uuid => $condition) {
        if ($condition['id'] === 'node_type') {
          $pattern_config->set("selection_criteria.$uuid.id", 'entity_bundle:node');
          $pattern_config->save();
          break;
        }
      }
    }
  }
}
