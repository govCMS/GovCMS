<?php

declare(strict_types = 1);

namespace Drupal\entity_embed\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DrupalEntityDeriver extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $embedButtonStorage;

  /**
   * Constructs a new DrupalEntityDeriver object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->embedButtonStorage = $entity_type_manager->getStorage('embed_button');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    assert($base_plugin_definition instanceof CKEditor5PluginDefinition);
    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    foreach ($this->embedButtonStorage->loadMultiple() as $embed_button) {
      $embed_button_id = $embed_button->id();
      $embed_button_label = Html::escape($embed_button->label());
      $plugin_id = "entity_embed_{$embed_button_id}";
      $definition = $base_plugin_definition->toArray();
      $definition['id'] .= $embed_button_id;
      $definition['drupal']['label'] = $this->t('Entity Embed - @label', ['@label' => $embed_button_label])->render();
      $definition['drupal']['toolbar_items'] = [
        $embed_button_id => [
          'label' => $embed_button_label,
        ],
      ];
      $definition['drupal']['elements'][] = '<drupal-entity data-embed-button="' . $embed_button_id . '">';
      $definition['drupal']['elements'][] = '<drupal-entity data-entity-type="' . $embed_button->getTypeSetting('entity_type') . '">';
      $this->derivatives[$plugin_id] = new CKEditor5PluginDefinition($definition);
    }

    return $this->derivatives;
  }

}
