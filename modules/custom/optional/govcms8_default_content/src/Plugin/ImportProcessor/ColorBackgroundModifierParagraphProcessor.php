<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for Color background modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "color_background_modifier",
 *   name = @Translation("Color background modifier paragraph processor"),
 *   description = @Translation("Prepares Color background modifier paragraphs before import."),
 *   type = "paragraph:color_background_modifier"
 * )
 */
class ColorBackgroundModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $this->mapBasicField($values, 'field_mod_bc_bg_color_val');
    $this->mapBasicField($values, 'field_mod_transition_duration');
    $this->mapBasicField($values, 'field_mod_bc_bg_h_color_val');
    $this->mapBasicField($values, 'field_mod_media_query');
  }

}
