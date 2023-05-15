<?php

namespace Drupal\entity_embed;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines services that are responsible for building of embedded entities.
 *
 * @internal
 */
interface EntityEmbedBuilderInterface {

  /**
   * Builds the render array for an embedded entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be rendered.
   * @param array $context
   *   (optional) Array of context values, corresponding to the attributes on
   *   the embed HTML tag.
   *
   * @return array
   *   A render array.
   *
   * @todo improve documentation
   */
  public function buildEntityEmbed(EntityInterface $entity, array $context = []);

}
