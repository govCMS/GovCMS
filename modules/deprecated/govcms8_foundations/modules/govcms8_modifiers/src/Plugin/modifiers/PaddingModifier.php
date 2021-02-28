<?php

namespace Drupal\govcms8_modifiers\Plugin\modifiers;

use Drupal\modifiers\Modification;
use Drupal\modifiers\ModifierPluginBase;

/**
 * Provides a Modifier to set the padding on an element.
 *
 * @Modifier(
 *   id = "padding_modifier",
 *   label = @Translation("Padding Modifier"),
 *   description = @Translation("Provides a Modifier to set the padding on an element"),
 * )
 */
class PaddingModifier extends ModifierPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function modification($selector, array $config) {

    $media = parent::getMediaQuery($config);

    if (!empty($config['padding_t_size']) && !empty($config['padding_t_units'])) {
      $css[$media][$selector][] = 'padding-top:' . $config['padding_t_size'] .
        $config['padding_t_units'];
    }
    if (!empty($config['padding_r_size']) && !empty($config['padding_r_units'])) {
      $css[$media][$selector][] = 'padding-right:' . $config['padding_r_size'] .
        $config['padding_r_units'];
    }
    if (!empty($config['padding_b_size']) && !empty($config['padding_b_units'])) {
      $css[$media][$selector][] = 'padding-bottom:' . $config['padding_b_size'] .
        $config['padding_b_units'];
    }
    if (!empty($config['padding_l_size']) && !empty($config['padding_l_units'])) {
      $css[$media][$selector][] = 'padding-left:' . $config['padding_l_size'] .
        $config['padding_l_units'];
    }

    if (!empty($css)) {

      return new Modification($css);
    }
    return NULL;
  }

}
