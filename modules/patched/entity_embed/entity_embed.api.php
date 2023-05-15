<?php

use Drupal\file\FileInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
/**
 * @file
 * Hooks provided by the Entity Embed module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the Entity Embed Display plugin definitions.
 *
 * @param array &$info
 *   An associative array containing the plugin definitions keyed by plugin ID.
 */
function hook_entity_embed_display_plugins_alter(array &$info) {

}

/**
 * Alter the Entity Embed Display plugin definitions for a given context.
 *
 * Usually used to remove certain Entity Embed Display plugins for specific
 * entities.
 *
 * @param array &$definitions
 *   Remove options from this list if they should not be available for the given
 *   context.
 * @param array $contexts
 *   The provided context, typically an entity.
 */
function hook_entity_embed_display_plugins_for_context_alter(array &$definitions, array $contexts) {
  // Do nothing if no entity is provided.
  if (!isset($contexts['entity'])) {
    return;
  }
  $entity = $contexts['entity'];

  // For video and audio files, limit the available options to the media player.
  if ($entity instanceof FileInterface && in_array($entity->bundle(), ['audio', 'video'])) {
    $definitions = array_intersect_key($definitions, array_flip(['file:jwplayer_formatter']));
  }

  // For images, use the image formatter.
  if ($entity instanceof FileInterface && in_array($entity->bundle(), ['image'])) {
    $definitions = array_intersect_key($definitions, array_flip(['image:image']));
  }

  // For nodes, use the default option.
  if ($entity instanceof NodeInterface) {
    $definitions = array_intersect_key($definitions, array_flip(['entity_reference:entity_reference_entity_view']));
  }
}

/**
 * Alter the context of an embedded entity before it is rendered.
 *
 * @param array &$context
 *   The context array.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 */
function hook_entity_embed_context_alter(array &$context, EntityInterface $entity) {
  if (isset($context['overrides']) && is_array($context['overrides'])) {
    foreach ($context['overrides'] as $key => $value) {
      $entity->key = $value;
    }
  }
}

/**
 * Alter the context of a particular embedded entity type before it is rendered.
 *
 * @param array &$context
 *   The context array.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity object.
 */
function hook_ENTITY_TYPE_embed_context_alter(array &$context, EntityInterface $entity) {
  if (isset($context['overrides']) && is_array($context['overrides'])) {
    foreach ($context['overrides'] as $key => $value) {
      $entity->key = $value;
    }
  }
}

/**
 * Alter the results of an embedded entity build array.
 *
 * This hook is called after the content has been assembled in a structured
 * array and may be used for doing processing which requires that the complete
 * block content structure has been built.
 *
 * @param array &$build
 *   A renderable array representing the embedded entity content.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The embedded entity object.
 * @param array $context
 *   The context array.
 */
function hook_entity_embed_alter(array &$build, EntityInterface $entity, array &$context) {
  // Remove the contextual links.
  if (isset($build['#contextual_links'])) {
    unset($build['#contextual_links']);
  }
}

/**
 * Alter the results of the particular embedded entity type build array.
 *
 * @param array &$build
 *   A renderable array representing the embedded entity content.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The embedded entity object.
 * @param array $context
 *   The context array.
 */
function hook_ENTITY_TYPE_embed_alter(array &$build, EntityInterface $entity, array &$context) {
  // Remove the contextual links.
  if (isset($build['#contextual_links'])) {
    unset($build['#contextual_links']);
  }
}

/**
 * @} End of "addtogroup hooks".
 */
