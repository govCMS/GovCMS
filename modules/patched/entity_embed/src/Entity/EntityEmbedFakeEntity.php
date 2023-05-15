<?php

namespace Drupal\entity_embed\Entity;

use Drupal\Core\Entity\ContentEntityBase;

/**
 * Fake entity type.
 *
 * @ContentEntityType(
 *   id = "entity_embed_fake_entity",
 *   label = @Translation("Fake entity type"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\ContentEntityNullStorage",
 *   },
 *   internal = true,
 * )
 */
class EntityEmbedFakeEntity extends ContentEntityBase {
}
