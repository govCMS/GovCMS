<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for content paragraphs.
 *
 * @ImportProcessor(
 *   id = "content_paragraph_processor",
 *   name = @Translation("Content paragraph processor"),
 *   description = @Translation("Prepares content paragraphs before import."),
 *   type = "paragraph:content"
 * )
 */
class ContentParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    $this->mapBasicField($values, 'field_c_colour_classes');
    $this->mapBasicField($values, 'field_c_style_classes');
    $this->mapBasicField($values, 'field_read_more');

    if (!empty($item['field_modifiers'])) {
      $values['field_modifiers'] = $this->mapParagraphField($item['field_modifiers']);
    }
  }

}
