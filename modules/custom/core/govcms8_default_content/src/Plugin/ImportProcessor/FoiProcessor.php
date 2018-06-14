<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for FOI.
 *
 * @ImportProcessor(
 *   id = "foi_processor",
 *   name = @Translation("Foi processor"),
 *   description = @Translation("Prepares foi before import."),
 *   type = "node:govcms_foi"
 * )
 */
class FoiProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;

    if (!empty($item['field_thumbnail'])) {
      $values['field_thumbnail'] = $this->populateMediaField('field_thumbnail');
    }

    if (!empty($item['field_foi_date_listed'])) {
      $values['field_foi_date_listed'] = $item['field_foi_date_listed'];
    }

    if (!empty($item['field_foi_location'])) {
      $values['field_foi_proposed_deletion_date'] = $item['field_foi_proposed_deletion_date'];
    }
  }

}
