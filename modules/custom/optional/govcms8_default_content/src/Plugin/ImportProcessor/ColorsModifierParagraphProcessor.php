<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for Colors modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "colors_modifier_processor",
 *   name = @Translation("Colors modifier paragraph processor"),
 *   description = @Translation("Prepares Colors modifier paragraphs before import."),
 *   type = "paragraph:custom_colors_modifier"
 * )
 */
class ColorsModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $this->mapBasicField($values, 'field_mod_background_color_val');
    $this->mapBasicField($values, 'field_mod_transition_duration');
    $this->mapBasicField($values, 'field_mod_h_background_color_val');
    $this->mapBasicField($values, 'field_mod_h_link_color_val');
    $this->mapBasicField($values, 'field_mod_h_text_color_val');
    $this->mapBasicField($values, 'field_mod_link_color_val');
    $this->mapBasicField($values, 'field_mod_media_query');
    $this->mapBasicField($values, 'field_mod_text_color_val');
  }

}
