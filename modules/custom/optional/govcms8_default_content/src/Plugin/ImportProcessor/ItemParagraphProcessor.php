<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for item paragraphs.
 *
 * @ImportProcessor(
 *   id = "item_paragraph_processor",
 *   name = @Translation("item paragraph processor"),
 *   description = @Translation("Prepares item paragraphs before import."),
 *   type = "paragraph:item"
 * )
 */
class ItemParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    if (!empty($item['field_item_summary'])) {
      $values['field_item_summary'] = [
        'value' => $item['field_item_summary'],
        'format' => 'rich_text',
      ];
    }

    $this->mapBasicField($values, 'field_item_info');
    $this->mapBasicField($values, 'field_item_url');

    if (!empty($item['field_item_image'])) {
      $values['field_item_image'] = $this->populateMediaField('field_item_image');
    }
  }

}
