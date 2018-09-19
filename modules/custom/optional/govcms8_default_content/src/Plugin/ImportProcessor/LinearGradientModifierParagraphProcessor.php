<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for Linear gradient modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "linear_gradient_modifier_processor",
 *   name = @Translation("Linear gradient modifier paragraph processor"),
 *   description = @Translation("Prepares Linear gradient modifier paragraphs before import."),
 *   type = "paragraph:custom_linear_gradient_modifier"
 * )
 */
class LinearGradientModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $this->mapBasicField($values, 'field_mod_cl_gradient_colors');
    $this->mapBasicField($values, 'field_mod_cl_gradient_direction');
    $this->mapBasicField($values, 'field_mod_media_query');
  }

}
