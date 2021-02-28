<?php

namespace Drupal\govcms8_modifiers\Plugin\modifiers;

use Drupal\modifiers\Modification;
use Drupal\modifiers\ModifierPluginBase;

/**
 * Provides a Modifier to set the linear gradient on an element.
 *
 * @Modifier(
 *   id = "custom_linear_gradient_modifier",
 *   label = @Translation("Custom Linear Gradient Modifier"),
 *   description = @Translation("Provides a Modifier to set the linear gradient on an element using custom colors"),
 * )
 */
class CustomLinearGradientModifier extends ModifierPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function modification($selector, array $config) {

    if (!empty($config['cl_gradient_colors'])) {
      $media = parent::getMediaQuery($config);
      $direction = '';

      if (!empty($config['cl_gradient_direction'])) {
        $direction = $config['cl_gradient_direction'] . 'deg,';
      }
      // If there is only one color specified we use single color fill.
      if (count($config['cl_gradient_colors']) === 1) {
        $css[$media][$selector][] = 'background:' . $config['cl_gradient_colors'][0];
      }
      else {
        $css[$media][$selector][] = 'background:linear-gradient('
          . $direction . implode(',', $config['cl_gradient_colors']) . ')';
      }
      $attributes[$media][$selector]['class'][] = 'modifiers-has-background';

      return new Modification($css, [], [], $attributes);
    }
    return NULL;
  }

}
