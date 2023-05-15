<?php

namespace Drupal\entity_embed;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;

/**
 * Builds embedded entities.
 *
 * @internal
 */
class EntityEmbedBuilder implements EntityEmbedBuilderInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity embed display plugin manager service.
   *
   * @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
   */
  protected $displayPluginManager;

  /**
   * Constructs a EntityEmbedBuilder object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $display_manager
   *   The entity embed display plugin manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityEmbedDisplayManager $display_manager) {
    $this->moduleHandler = $module_handler;
    $this->displayPluginManager = $display_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntityEmbed(EntityInterface $entity, array $context = []) {
    // Support the deprecated view-mode data attribute.
    if (isset($context['data-view-mode']) && !isset($context['data-entity-embed-display']) && !isset($context['data-entity-embed-display-settings'])) {
      $context['data-entity-embed-display'] = 'entity_reference:entity_reference_entity_view';
      $context['data-entity-embed-display-settings'] = ['view_mode' => &$context['data-view-mode']];
    }

    // Merge in default attributes.
    $context += [
      'data-entity-type' => $entity->getEntityTypeId(),
      'data-entity-uuid' => $entity->uuid(),
      'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
      'data-entity-embed-display-settings' => [],
    ];

    // If the data-entity-embed-display-settings isn't an array reset  it,
    // otherwise we'll encounter a fatal error when calling
    // $this->buildEntityEmbedDisplayPlugin() further down the line.
    if (!is_array($context['data-entity-embed-display-settings'])) {
      \Drupal::logger('entity_embed')->warning('Invalid display settings encountered. Could not process following settings for entity type "@entity_type" with the uuid "@uuid": @settings', [
        '@settings' => $context['data-entity-embed-display-settings'],
        '@entity_type' => $entity->getEntityTypeId(),
        '@uuid' => $entity->uuid(),
      ]);
      $context['data-entity-embed-display-settings'] = [];
    }

    // The default Entity Embed Display plugin has been deprecated by the
    // rendered entity field formatter.
    if ($context['data-entity-embed-display'] === 'default') {
      $context['data-entity-embed-display'] = 'entity_reference:entity_reference_entity_view';
    }

    // The caption text is double-encoded, so decode it here.
    if (isset($context['data-caption'])) {
      $context['data-caption'] = Html::decodeEntities($context['data-caption']);
    }

    // Allow modules to alter the entity prior to embed rendering.
    $this->moduleHandler->alter(["{$context['data-entity-type']}_embed_context", 'entity_embed_context'], $context, $entity);

    // Build and render the Entity Embed Display plugin, allowing modules to
    // alter the result before rendering.
    $build = [
      '#theme_wrappers' => ['entity_embed_container'],
      '#entity' => $entity,
      '#context' => $context,
    ];
    $build['entity'] = $this->buildEntityEmbedDisplayPlugin(
      $entity,
      $context['data-entity-embed-display'],
      $context['data-entity-embed-display-settings'],
      $context
    );
    // Don't ever cache a representation of an embedded entity, since the host
    // entity may be overriding specific values (such as an `alt` attribute)
    // which means that this particular rendered representation is unique to
    // the host entity, and hence nonsensical to cache separately anyway.
    unset($build['entity']['#cache']['keys']);

    if (isset($context['class'])) {
      if (is_string($context['class'])) {
        $context['class'] = explode(' ', $context['class']);
      }
    }
    else {
      $context['class'] = [];
    }
    $context['class'][] = 'embedded-entity';

    // Maintain data- attributes.
    if (isset($context)) {
      $build['#attributes'] = $context;
    }

    // Make sure that access to the entity is respected.
    $build['#access'] = $entity->access('view', NULL, TRUE);

    // @todo Should this hook get invoked if $build is an empty array?
    $this->moduleHandler->alter(["{$context['data-entity-type']}_embed", 'entity_embed'], $build, $entity, $context);
    return $build;
  }

  /**
   * Builds the render array for an entity using an Entity Embed Display plugin.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to be rendered.
   * @param string $plugin_id
   *   The Entity Embed Display plugin ID.
   * @param array $plugin_configuration
   *   (optional) Array of plugin configuration values.
   * @param array $context
   *   (optional) Array of additional context values, usually the embed HTML
   *   tag's attributes.
   *
   * @return array
   *   A render array for the Entity Embed Display plugin.
   */
  protected function buildEntityEmbedDisplayPlugin(EntityInterface $entity, $plugin_id, array $plugin_configuration = [], array $context = []) {
    // Build the Entity Embed Display plugin.
    /** @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayBase $display */
    $display = $this->displayPluginManager->createInstance($plugin_id, $plugin_configuration);
    $display->setContextValue('entity', $entity);
    $display->setAttributes($context);

    // Check if the Entity Embed Display plugin is accessible. This also checks
    // entity access, which is why we never call $entity->access() here.
    if (!$display->access()) {
      return [];
    }

    return $display->build();
  }

}
