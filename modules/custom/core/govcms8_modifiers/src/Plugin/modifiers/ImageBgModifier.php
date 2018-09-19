<?php

namespace Drupal\govcms8_modifiers\Plugin\modifiers;

use Drupal\modifiers\Modification;
use Drupal\modifiers\ModifierPluginBase;

/**
 * Provides a Modifier to set the image background on an element.
 */
class ImageBgModifier extends ModifierPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function modification($selector, array $config) {

    if (!empty($config['image'])) {
      $media = parent::getMediaQuery($config);

      $css[$media][$selector][] = 'background-image:url("' . $config['image'] . '")';
      $attributes[$media][$selector]['class'][] = 'modifiers-has-background';

      if (!empty($config['image_style'])) {

        switch ($config['image_style']) {

          case 'stretch':
            $css[$media][$selector][] = 'background-size:cover';
            break;

          case 'no-repeat':
          case 'repeat':
          case 'repeat-x':
          case 'repeat-y':
            $css[$media][$selector][] = 'background-repeat:' . $config['image_style'];
            break;
        }
      }
      if (!empty($config['image_position'])) {
        $position = str_replace('-', ' ', $config['image_position']);
        $css[$media][$selector][] = 'background-position:' . $position;
      }
      if (!empty($config['bgi_color_val'])) {
        $css[$media][$selector][] = 'background-color:' . $config['bgi_color_val'];
      }

      return new Modification($css, [], [], $attributes);
    }
    return NULL;
  }

}
