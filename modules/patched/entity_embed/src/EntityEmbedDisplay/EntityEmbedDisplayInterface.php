<?php

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DependentPluginInterface;

/**
 * Defines the interface for Entity Embed Display plugins.
 *
 * The abstraction layer provided by this plugin type may seem unnecessary at
 * first sight. Why not just allow users of Entity Embed to choose a view mode
 * (and its corresponding bundle-specific view display)?
 *
 * There are two reasons:
 * - It may be necessary to have metadata (a description for example) that is
 *   specific to a particular instance of embedding an entity. You may reference
 *   the same entity many times, but each time you want different metadata (a
 *   different description). If Entity Embed only allowed one to embed using a
 *   particular view mode, this would not be possible, since every embed would
 *   need to be rendered exactly the same.
 * - Some entities can not be rendered by default because they do not have a
 *   view builder. (particularly: the File entity which is crucial for embedding
 *   of media). To still be able to embed them, an Entity Embed Display plugin
 *   can be provided.
 *
 * @see \Drupal\Core\Entity\Entity\EntityViewMode
 * @see \Drupal\Core\Entity\Entity\EntityViewDisplay
 * @see \Drupal\Core\Entity\EntityViewBuilderInterface
 *
 * The ability to embed an entity using a view mode/display is then just one of
 * many Entity Embed Display plugins. It is available for all entities that can
 * be rendered (that have a view builder).
 *
 * @see \Drupal\entity_embed\Annotation\EntityEmbedDisplay
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayBase
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
 * @see plugin_api
 *
 * @ingroup entity_embed_api
 */
interface EntityEmbedDisplayInterface extends ConfigurableInterface, DependentPluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Indicates whether this Entity Embed display can be used.
   *
   * This method allows base implementations to add general access restrictions
   * that should apply to all extending Entity Embed display plugins.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (optional) The user for which to check access, or NULL to check access
   *   for the current user. Defaults to NULL.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account = NULL);

  /**
   * Builds the renderable array for this Entity Embed display plugin.
   *
   * @return array
   *   A renderable array representing the content of the embedded entity.
   */
  public function build();

}
