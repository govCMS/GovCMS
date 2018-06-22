<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for Padding modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "padding_modifier_processor",
 *   name = @Translation("Padding modifier paragraph processor"),
 *   description = @Translation("Prepares Padding modifier paragraphs before import."),
 *   type = "paragraph:padding_modifier"
 * )
 */
class PaddingModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $this->mapBasicField($values, 'field_mod_padding_b_size');
    $this->mapBasicField($values, 'field_mod_padding_b_units');

    $this->mapBasicField($values, 'field_mod_padding_l_size');
    $this->mapBasicField($values, 'field_mod_padding_l_units');

    $this->mapBasicField($values, 'field_mod_padding_r_size');
    $this->mapBasicField($values, 'field_mod_padding_r_units');

    $this->mapBasicField($values, 'field_mod_padding_t_size');
    $this->mapBasicField($values, 'field_mod_padding_t_units');

    $this->mapBasicField($values, 'field_mod_media_query');

  }

}
