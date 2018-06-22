<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for node list paragraphs.
 *
 * @ImportProcessor(
 *   id = "node_list_paragraph_processor",
 *   name = @Translation("node list paragraph processor"),
 *   description = @Translation("Prepares node list paragraphs before import."),
 *   type = "paragraph:node_list"
 * )
 */
class NodeListParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    $this->mapBasicField($values, 'field_nl_view_mode');
    $this->mapBasicField($values, 'field_nl_layout_classes');
    $this->mapBasicField($values, 'field_nl_colour_classes');
    $this->mapBasicField($values, 'field_nl_style_classes');
    $this->mapBasicField($values, 'field_read_more');

    if (!empty($item['field_nl_nodes'])) {
      $values['field_nl_nodes'] = $this->mapEntityReferenceField('field_nl_nodes');
    }

  }

}
