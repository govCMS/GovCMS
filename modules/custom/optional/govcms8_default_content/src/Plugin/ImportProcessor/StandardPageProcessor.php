<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for pages.
 *
 * @ImportProcessor(
 *   id = "standard_page_processor",
 *   name = @Translation("Pages processor"),
 *   description = @Translation("Prepares pages before import."),
 *   type = "node:govcms_standard_page"
 * )
 */
class StandardPageProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    if (!empty($item['field_thumbnail'])) {
      $values['field_thumbnail'] = $this->populateMediaField('field_thumbnail');
    }

    if (!empty($item['field_featured_image'])) {
      $values['field_featured_image'] = $this->populateMediaField('field_featured_image');
    }

    if (!empty($item['field_components'])) {
      $values['field_components'] = $this->mapParagraphField($item['field_components']);
    }

    if (!empty($item['panelizer'])) {
      $values['panelizer'] = [
        'view_mode' => 'full',
        'default' => 'page_edgy',
        'panels_display' => [],
      ];
    }
  }

}
