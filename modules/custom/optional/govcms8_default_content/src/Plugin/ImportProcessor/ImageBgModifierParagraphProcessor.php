<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for Image bg modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "image_bg_modifier",
 *   name = @Translation("Image bg modifier paragraph processor"),
 *   description = @Translation("Prepares Image bg modifier paragraphs before import."),
 *   type = "paragraph:image_bg_modifier"
 * )
 */
class ImageBgModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;
    $this->mapBasicField($values, 'field_mod_bgi_color_val');
    $this->mapBasicField($values, 'field_mod_image_position');

    $this->mapBasicField($values, 'field_mod_image_style');
    $this->mapBasicField($values, 'field_mod_media_query');

    if (!empty($item['field_mod_image'])) {
      $values['field_mod_image'] = $this->populateMediaField('field_mod_image');
    }

  }

}
