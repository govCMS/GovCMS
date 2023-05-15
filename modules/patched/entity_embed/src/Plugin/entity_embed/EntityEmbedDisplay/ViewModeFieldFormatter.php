<?php

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Form\FormStateInterface;

/**
 * Entity Embed Display reusing entity reference field formatters.
 *
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 *
 * @EntityEmbedDisplay(
 *   id = "view_mode",
 *   label = @Translation("View Mode"),
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\ViewModeDeriver",
 *   field_type = "entity_reference"
 * )
 */
class ViewModeFieldFormatter extends EntityReferenceFieldFormatter {

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatter() {
    if (!isset($this->fieldFormatter)) {
      $display = [
        'type' => $this->getFieldFormatterId(),
        'settings' => [
          'view_mode' => $this->getPluginDefinition()['view_mode'],
        ],
        'label' => 'hidden',
      ];

      // Create the formatter plugin. Will use the default formatter for that
      // field type if none is passed.
      $this->fieldFormatter = $this->formatterPluginManager->getInstance(
        [
          'field_definition' => $this->getFieldDefinition(),
          'view_mode' => '_entity_embed',
          'configuration' => $display,
        ]
      );
    }
    return $this->fieldFormatter;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Configuration form is not needed as the view mode is defined implicitly.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterId() {
    return 'entity_reference_entity_view';
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $definition = $this->getPluginDefinition();
    $view_mode = $definition['view_mode'];

    $view_modes = [];

    foreach ($definition['entity_types'] as $type) {
      $view_modes[] = "$type.$view_mode";
    }

    $entity_view_modes = $this->entityTypeManager
      ->getStorage('entity_view_mode')
      ->loadMultiple($view_modes);

    foreach ($entity_view_modes as $view_mode) {
      $this->addDependency($view_mode->getConfigDependencyKey(), $view_mode->getConfigDependencyName());
    }

    return $this->dependencies;
  }

}
