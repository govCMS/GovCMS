<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for news and media items.
 *
 * @ImportProcessor(
 *   id = "news_media_processor",
 *   name = @Translation("News and Media processor"),
 *   description = @Translation("Prepares News and media items before import."),
 *   type = "node:govcms_news_and_media"
 * )
 */
class NewsMediaProcessor extends ImportProcessorBase {

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

    if (!empty($item['field_news_categories'])) {
      $values['field_news_categories'] = $this->populateTaxonomyTermField('field_news_categories', 'news_categories');
    }

    if (!empty($item['field_media_release_type'])) {
      $values['field_media_release_type'] = $item['field_media_release_type'];
    }
  }

}
