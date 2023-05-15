<?php

namespace Drupal\entity_embed\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FormatterPluginManager;

/**
 * Provides Entity Embed Display plugin definitions for field formatters.
 *
 * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase
 */
class FieldFormatterDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The manager for formatter plugins.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterManager;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs new FieldFormatterEntityEmbedDisplayBase.
   *
   * @param \Drupal\Core\Field\FormatterPluginManager $formatter_manager
   *   The field formatter plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   */
  public function __construct(FormatterPluginManager $formatter_manager, ConfigFactoryInterface $config_factory) {
    $this->formatterManager = $formatter_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.field.formatter'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \LogicException
   *   Throws an exception if field type is not defined in the annotation of the
   *   Entity Embed Display plugin.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // The field type must be defined in the annotation of the Entity Embed
    // Display plugin.
    if (!isset($base_plugin_definition['field_type'])) {
      throw new \LogicException("Undefined field_type definition in plugin {$base_plugin_definition['id']}.");
    }

    $no_media_image_decorator = [
      'entity_reference_entity_id',
      'entity_reference_label',
    ];

    foreach ($this->formatterManager->getOptions($base_plugin_definition['field_type']) as $formatter => $label) {
      $this->derivatives[$formatter] = $base_plugin_definition;
      $this->derivatives[$formatter]['label'] = $label;

      // The base entity embed display plugin annotation has opted into
      // `supports_image_alt_and_title`. For some derivatives we know that they
      // do not support this, so opt them back out.
      if (in_array($formatter, $no_media_image_decorator, TRUE)) {
        $this->derivatives[$formatter]['supports_image_alt_and_title'] = FALSE;
      }
    }
    return $this->derivatives;
  }

}
