<?php

namespace Drupal\entity_embed\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Entity Embed Display plugin definitions for view modes.
 *
 * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase
 */
class ViewModeDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a ViewModeDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   */
  public function __construct(EntityDisplayRepositoryInterface $entity_display_repository, ConfigFactoryInterface $config_factory) {
    $this->entityDisplayRepository = $entity_display_repository;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_display.repository'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $no_ui = $this->configFactory->get('entity_embed.settings')->get('rendered_entity_mode');
    foreach ($this->entityDisplayRepository->getAllViewModes() as $view_modes) {
      foreach ($view_modes as $view_mode => $definition) {
        $this->derivatives[$definition['id']] = [
          'label' => $definition['label'],
          'view_mode' => $view_mode,
          'entity_types' => [$definition['targetEntityType']],
          'no_ui' => $no_ui,
          // Check if the plugin should run through MediaImageDecorator. A more
          // fine-grained access check happens there.
          'supports_image_alt_and_title' => ($definition['targetEntityType'] === 'media'),
        ] + $base_plugin_definition;
      }
    }
    return $this->derivatives;
  }

}
