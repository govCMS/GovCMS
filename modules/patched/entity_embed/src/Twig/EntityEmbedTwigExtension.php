<?php

namespace Drupal\entity_embed\Twig;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_embed\EntityEmbedBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provide entity embedding function within Twig templates.
 */
class EntityEmbedTwigExtension extends AbstractExtension {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity embed builder service.
   *
   * @var \Drupal\entity_embed\EntityEmbedBuilderInterface
   */
  protected $builder;

  /**
   * Constructs a new EntityEmbedTwigExtension.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\entity_embed\EntityEmbedBuilderInterface $builder
   *   The Entity embed builder service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityEmbedBuilderInterface $builder) {
    $this->entityTypeManager = $entity_type_manager;
    $this->builder = $builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_embed.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'entity_embed.twig.entity_embed_twig_extension';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('entity_embed', [$this, 'getRenderArray']),
    ];
  }

  /**
   * Return the render array for an entity.
   *
   * @param string $entity_type
   *   The machine name of an entity_type like 'node'.
   * @param string $entity_id
   *   The entity ID.
   * @param string $display_plugin
   *   (optional) The Entity Embed Display plugin to be used to render the
   *   entity.
   * @param array $display_settings
   *   (optional) A list of settings for the Entity Embed Display plugin.
   *
   * @return array
   *   A render array from entity_view().
   */
  public function getRenderArray($entity_type, $entity_id, $display_plugin = 'default', array $display_settings = []) {
    $entity = $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
    $context = [
      'data-entity-type' => $entity_type,
      'data-entity-uuid' => $entity->uuid(),
      'data-entity-embed-display' => $display_plugin,
      'data-entity-embed-display-settings' => $display_settings,
    ];
    return $this->builder->buildEntityEmbed($entity, $context);
  }

}
