<?php

namespace Drupal\govcms8_modifiers\Plugin\modifiers;

use Drupal\modifiers\Modification;
use Drupal\modifiers\ModifierPluginBase;

/**
 * Provides a Modifier to set the relative height on an element.
 */
class RelativeHeightModifier extends ModifierPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function modification($selector, array $config) {

    if (!empty($config['relative_height'])) {
      $media = parent::getMediaQuery($config);

      if (!empty($config['vertical_align'])) {
        $css[$media][$selector][] = 'display:flex';

        switch ($config['vertical_align']) {

          case 'top':
            $css[$media][$selector][] = 'align-items:flex-start';
            break;

          case 'middle':
            $css[$media][$selector][] = 'align-items:center';
            break;

          case 'bottom':
            $css[$media][$selector][] = 'align-items:flex-end';
            break;
        }
      }
      $libraries = [
        'govcms8_modifiers/modifiers_relative_height_apply',
      ];
      $settings = [
        'namespace' => 'RelativeHeightModifier',
        'callback' => 'apply',
        'selector' => $selector,
        'media' => $media,
        'args' => [
          'ratio' => $config['relative_height'],
        ],
      ];
      $css[$media][$selector][] = 'overflow:hidden';

      return new Modification($css, $libraries, $settings);
    }
    return NULL;
  }

}
