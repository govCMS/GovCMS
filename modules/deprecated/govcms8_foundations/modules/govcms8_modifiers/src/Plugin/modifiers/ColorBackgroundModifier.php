<?php

namespace Drupal\govcms8_modifiers\Plugin\modifiers;

use Drupal\modifiers\Modification;
use Drupal\modifiers\ModifierPluginBase;

/**
 * Provides a Modifier to set the color background color on an element.
 *
 * @Modifier(
 *   id = "color_background_modifier",
 *   label = @Translation("Color Background Modifier"),
 *   description = @Translation("Provides a Modifier to set the background color on an element"),
 * )
 */
class ColorBackgroundModifier extends ModifierPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function modification($selector, array $config) {

    $css = [];
    $attributes = [];
    $media = parent::getMediaQuery($config);

    if (!empty($config['bc_bg_color_val'])) {
      $css[$media][$selector][] = 'background-color:' . $config['bc_bg_color_val'];
      $attributes[$media][$selector]['class'][] = 'modifiers-has-background';
    }
    if (!empty($config['bc_bg_h_color_val'])) {
      $css[$media][$selector . ':hover'][] = 'background-color:' . $config['bc_bg_h_color_val'];
      if (empty($config['bc_bg_color_val'])) {
        $attributes[$media][$selector]['class'][] = 'modifiers-has-background';
      }
    }
    if (!empty($config['transition_duration'])) {
      $css[$media][$selector][] = 'transition-duration:' . $config['transition_duration'] . 's';
      $css[$media][$selector . ':hover'][] = 'transition-duration:' . $config['transition_duration'] . 's';
    }

    if (!empty($css) || !empty($attributes)) {

      return new Modification($css, [], [], $attributes);
    }
    return NULL;
  }

}
