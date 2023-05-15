<?php

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\MediaImageDecorator;

/**
 * Provides an Entity Embed display plugin manager.
 *
 * @see \Drupal\entity_embed\Annotation\EntityEmbedDisplay
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayBase
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 * @see plugin_api
 */
class EntityEmbedDisplayManager extends DefaultPluginManager {

  /**
   * Constructs a new EntityEmbedDisplayManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/entity_embed/EntityEmbedDisplay', $namespaces, $module_handler, 'Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface', 'Drupal\entity_embed\Annotation\EntityEmbedDisplay');
    $this->alterInfo('entity_embed_display_plugins');
    // @todo Move the cache tag to the derivers once https://www.drupal.org/node/3001284 lands.
    $this->setCacheBackend($cache_backend, 'entity_embed_display_plugins', ['config:entity_view_mode_list']);
  }

  /**
   * Determines plugins whose constraints are satisfied by a set of contexts.
   *
   * @param array $contexts
   *   An array of contexts.
   *
   * @return array
   *   An array of plugin definitions.
   *
   * @todo At some point convert this to use ContextAwarePluginManagerTrait
   *
   * @see https://drupal.org/node/2277981
   */
  public function getDefinitionsForContexts(array $contexts = []) {
    $definitions = $this->getDefinitions();

    if (!empty($contexts['embed_button'])) {
      $button_plugins = $contexts['embed_button']->getTypeSetting('display_plugins');
      if (!empty($button_plugins)) {
        $allowed_definitions = [];
        foreach ($button_plugins as $plugin_id) {
          if (!empty($definitions[$plugin_id])) {
            $allowed_definitions[$plugin_id] = $definitions[$plugin_id];
          }
        }
        $definitions = $allowed_definitions;
      }
    }

    $valid_ids = array_filter(array_keys($definitions), function ($id) use ($contexts) {
      try {
        $display = $this->createInstance($id);
        foreach ($contexts as $name => $value) {
          $display->setContextValue($name, $value);
        }
        // We lose cacheability metadata at this point. We should refactor to
        // avoid this. @see https://www.drupal.org/node/2593379#comment-11368447
        return $display->access()->isAllowed();
      }
      catch (PluginException $e) {
        return FALSE;
      }
    });
    $definitions_for_context = array_intersect_key($definitions, array_flip($valid_ids));
    $this->moduleHandler->alter('entity_embed_display_plugins_for_context', $definitions_for_context, $contexts);
    return $definitions_for_context;
  }

  /**
   * Gets definition options for context.
   *
   * Provides a list of plugins that can be used for a certain context and
   * filters out plugins that should be hidden in the UI.
   *
   * @param array $context
   *   An array of context options; possible keys are 'entity', 'entity_type'
   *   and 'embed_button'.
   *
   * @return string[]
   *   An array of valid plugin labels, keyed by plugin ID.
   */
  public function getDefinitionOptionsForContext(array $context) {
    $values = [
      'entity' => TRUE,
      'entity_type' => TRUE,
      'embed_button' => TRUE,
    ];
    assert(empty(array_diff_key($context, $values)));
    $definitions = $this->getDefinitionsForContexts($context);
    $definitions = $this->filterExposedDefinitions($definitions);
    $options = array_map(function ($definition) {
      return (string) $definition['label'];
    }, $definitions);
    natsort($options);
    return $options;
  }

  /**
   * Gets definition options for entity.
   *
   * Provides a list of plugins that can be used for a certain entity and
   * filters out plugins that should be hidden in the UI.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity object.
   *
   * @return array
   *   An array of valid plugin labels, keyed by plugin ID.
   */
  public function getDefinitionOptionsForEntity(EntityInterface $entity) {
    $definitions = $this->getDefinitionsForContexts(['entity' => $entity, 'entity_type' => $entity->getEntityTypeId()]);
    $definitions = $this->filterExposedDefinitions($definitions);
    return array_map(function ($definition) {
      return (string) $definition['label'];
    }, $definitions);
  }

  /**
   * Filters out plugins from definitions that should be hidden in the UI.
   *
   * @param array $definitions
   *   The array of plugin definitions.
   *
   * @return array
   *   Returns plugin definitions that should be displayed in the UI.
   */
  protected function filterExposedDefinitions(array $definitions) {
    return array_filter($definitions, function ($definition) {
      return empty($definition['no_ui']);
    });
  }

  /**
   * Gets definition options for entity type.
   *
   * Provides a list of plugins that can be used for a certain entity type and
   * filters out plugins that should be hidden in the UI.
   *
   * @param string $entity_type
   *   The entity type id.
   *
   * @return array
   *   An array of valid plugin labels, keyed by plugin ID.
   */
  public function getDefinitionOptionsForEntityType($entity_type) {
    $definitions = $this->getDefinitionsForContexts(['entity_type' => $entity_type]);
    $definitions = $this->filterExposedDefinitions($definitions);
    return array_map(function ($definition) {
      return (string) $definition['label'];
    }, $definitions);
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = []) {
    $instance = parent::createInstance($plugin_id, $configuration);
    $definition = $instance->getPluginDefinition();

    if (empty($definition['supports_image_alt_and_title'])) {
      return $instance;
    }
    else {
      // Use decorator pattern to add alt and title fields to dialog when
      // embedding media with image source.
      return new MediaImageDecorator($instance);
    }
  }

}
