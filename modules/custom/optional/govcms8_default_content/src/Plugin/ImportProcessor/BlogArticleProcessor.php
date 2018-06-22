<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for blog articles.
 *
 * @ImportProcessor(
 *   id = "blog_article_processor",
 *   name = @Translation("Blog article processor"),
 *   description = @Translation("Prepares blog articles before import."),
 *   type = "node:govcms_blog_article"
 * )
 */
class BlogArticleProcessor extends ImportProcessorBase {

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

    if (!empty($item['field_blog_article_categories'])) {
      $values['field_blog_article_categories'] = $this->populateTaxonomyTermField('field_blog_article_categories', 'blog_article_categories');
    }
  }

}
