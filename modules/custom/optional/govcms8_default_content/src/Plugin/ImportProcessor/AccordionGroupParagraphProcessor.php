<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for accordion group paragraphs.
 *
 * @ImportProcessor(
 *   id = "accordion_group_paragraph_processor",
 *   name = @Translation("accordion group paragraph processor"),
 *   description = @Translation("Prepares accordion group paragraphs before import."),
 *   type = "paragraph:accordion_group"
 * )
 */
class AccordionGroupParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    $this->mapBasicField($values, 'field_show_close_all');
    $this->mapBasicField($values, 'field_show_open_all');
    $this->mapBasicField($values, 'field_show_toggle_all');
    $this->mapBasicField($values, 'field_ag_style_classes');
    $this->mapBasicField($values, 'field_ag_colour_classes');

    if (!empty($item['field_body'])) {
      $values['field_body'] = [
        'value' => $item['field_body'],
        'format' => 'rich_text',
      ];
    }

    if (!empty($item['field_accordions'])) {
      $values['field_accordions'] = $this->mapParagraphField($item['field_accordions']);
    }

  }

}
