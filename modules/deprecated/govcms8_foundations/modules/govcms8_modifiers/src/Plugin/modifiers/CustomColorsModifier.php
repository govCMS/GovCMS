<?php

namespace Drupal\govcms8_modifiers\Plugin\modifiers;

use Drupal\modifiers\Modification;
use Drupal\modifiers\ModifierPluginBase;

/**
 * Provides a Modifier to set the custom colors on an element.
 *
 * @Modifier(
 *   id = "custom_colors_modifier",
 *   label = @Translation("Custom Colors Modifier"),
 *   description = @Translation("Provides a Modifier to set the custom colors on an element"),
 * )
 */
class CustomColorsModifier extends ModifierPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function modification($selector, array $config) {

    $css = [];
    $attributes = [];
    $media = parent::getMediaQuery($config);

    if (!empty($config['background_color_val'])) {
      $css[$media][$selector][] = 'background-color:' . $config['background_color_val'];
      $attributes[$media][$selector]['class'][] = 'modifiers-has-background';
    }
    if (!empty($config['text_color_val'])) {
      $css[$media][$selector][] = 'color:' . $config['text_color_val'];
    }
    if (!empty($config['link_color_val'])) {
      $css[$media][$selector . ' a'][] = 'color:' . $config['link_color_val'];
    }
    if (!empty($config['h_background_color_val'])) {
      $css[$media][$selector . ':hover'][] = 'background-color:' . $config['h_background_color_val'];
      if (empty($config['background_color_val'])) {
        $attributes[$media][$selector]['class'][] = 'modifiers-has-background';
      }
    }
    if (!empty($config['h_link_color_val'])) {
      $css[$media][$selector . ' a:hover'][] = 'color:' . $config['h_link_color_val'];
    }
    if (!empty($config['h_text_color_val'])) {
      $css[$media][$selector . ':hover'][] = 'color:' . $config['h_text_color_val'];
    }
    if (!empty($config['transition_duration'])) {
      $css[$media][$selector][] = 'transition-duration:' . $config['transition_duration'] . 's';
      $css[$media][$selector . ':hover'][] = 'transition-duration:' . $config['transition_duration'] . 's';
      $css[$media][$selector . ' a'][] = 'transition-duration:' . $config['transition_duration'] . 's';
      $css[$media][$selector . ' a:hover'][] = 'transition-duration:' . $config['transition_duration'] . 's';
    }

    if (!empty($css) || !empty($attributes)) {

      return new Modification($css, [], [], $attributes);
    }
    return NULL;
  }

}
