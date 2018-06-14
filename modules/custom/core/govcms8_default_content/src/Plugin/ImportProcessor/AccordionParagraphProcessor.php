<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for accordion paragraphs.
 *
 * @ImportProcessor(
 *   id = "accordion_paragraph_processor",
 *   name = @Translation("accordion paragraph processor"),
 *   description = @Translation("Prepares accordion paragraphs before import."),
 *   type = "paragraph:accordion"
 * )
 */
class AccordionParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    $this->mapBasicField($values, 'field_accordion_orientation');
    $this->mapBasicField($values, 'field_accordion_speed');
    $this->mapBasicField($values, 'field_title');
    $this->mapBasicField($values, 'field_heading');

    if (!empty($item['field_accordion_body'])) {
      $values['field_accordion_body'] = [
        'value' => $item['field_accordion_body'],
        'format' => 'rich_text',
      ];
    }

  }

}
