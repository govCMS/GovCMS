<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for events.
 *
 * @ImportProcessor(
 *   id = "event_processor",
 *   name = @Translation("Event processor"),
 *   description = @Translation("Prepares event before import."),
 *   type = "node:govcms_event"
 * )
 */
class EventProcessor extends ImportProcessorBase {

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

    if (!empty($item['field_event_date'])) {
      $values['field_event_date'] = $item['field_event_date'];
    }

    if (!empty($item['field_event_location'])) {
      $values['field_event_location'] = $item['field_event_location'];
    }

    if (!empty($item['field_event_categories'])) {
      $values['field_event_categories'] = $this->populateTaxonomyTermField('field_event_categories', 'event_categories');
    }
  }

}
