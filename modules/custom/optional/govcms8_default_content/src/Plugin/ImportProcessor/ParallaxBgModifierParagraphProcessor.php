<?php

namespace Drupal\govcms8_default_content\Plugin\ImportProcessor;

use Drupal\govcms8_default_content\ImportProcessorBase;

/**
 * Provides processor for parallax bg modifier paragraphs.
 *
 * @ImportProcessor(
 *   id = "parallax_bg_modifier",
 *   name = @Translation("parallax bg modifier paragraph processor"),
 *   description = @Translation("Prepares parallax bg modifier paragraphs before import."),
 *   type = "paragraph:parallax_bg_modifier"
 * )
 */
class ParallaxBgModifierParagraphProcessor extends ImportProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$values) {
    $item = $this->item;
    $this->mapBasicField($values, 'field_mod_bgp_color_val');
    $this->mapBasicField($values, 'field_mod_media_query');
    $this->mapBasicField($values, 'field_mod_parallax');

    if (!empty($item['field_mod_parallax'])) {
      $values['field_mod_parallax'] = $this->populateMediaField('field_mod_parallax');
    }

  }

}
