<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for item list paragraphs.
 *
 * @ImportProcessor(
 *   id = "item_list_paragraph_processor",
 *   name = @Translation("item list paragraph processor"),
 *   description = @Translation("Prepares item list paragraphs before import."),
 *   type = "paragraph:item_list"
 * )
 */
class ItemListParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;
    if (!empty($item['field_il_view_mode'])) {
      $values['field_il_view_mode'] = $item['field_il_view_mode'];
    }

    if (!empty($item['field_il_layout_classes'])) {
      $values['field_il_layout_classes'] = $item['field_il_layout_classes'];
    }

    if (!empty($item['field_il_items'])) {
      $values['field_il_items'] = $this->mapParagraphField($item['field_il_items']);
    }

  }

}
