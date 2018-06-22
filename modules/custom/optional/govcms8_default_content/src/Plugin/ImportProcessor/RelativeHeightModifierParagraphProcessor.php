<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for Relative height modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "relative_height_modifier_processor",
 *   name = @Translation("Relative height modifier paragraph processor"),
 *   description = @Translation("Prepares Relative height modifier paragraphs before import."),
 *   type = "paragraph:relative_height_modifier"
 * )
 */
class RelativeHeightModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $this->mapBasicField($values, 'field_mod_media_query');
    $this->mapBasicField($values, 'field_mod_relative_height');
    $this->mapBasicField($values, 'field_mod_vertical_align');
  }

}
