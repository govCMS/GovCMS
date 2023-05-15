<?php

namespace Drupal\entity_embed\Plugin\CKEditor4To5Upgrade;

use Drupal\ckeditor5\HTMLRestrictions;
use Drupal\ckeditor5\Plugin\CKEditor4To5UpgradePluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\filter\FilterFormatInterface;

/**
 * Provides the CKEditor 4 to 5 upgrade path for entity embed buttons.
 *
 * @CKEditor4To5Upgrade(
 *   id = "entity_embed",
 *   cke4_buttons = {
 *   },
 *   cke4_plugin_settings = {
 *   },
 *   cke5_plugin_elements_subset_configuration = {
 *   }
 * )
 *
 * @internal
 *   Plugin classes are internal.
 */
class EntityEmbed extends PluginBase implements CKEditor4To5UpgradePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4ToolbarButtonToCKEditor5ToolbarItem(string $cke4_button, HTMLRestrictions $text_format_html_restrictions): ?array {
    $buttons = [];

    $embed_buttons = \Drupal::entityTypeManager()
      ->getStorage('embed_button')
      ->loadMultiple();
    foreach ($embed_buttons as $embed_button) {
      $buttons[] = $embed_button->id();
    }
    foreach ($buttons as $button) {
      if ($cke4_button == $button) {
        return [$button];
      }
    }

    throw new \OutOfBoundsException();
  }

  /**
   * {@inheritdoc}
   */
  public function mapCKEditor4SettingsToCKEditor5Configuration(string $cke4_plugin_id, array $cke4_plugin_settings): ?array {
    throw new \OutOfBoundsException();
  }

  /**
   * {@inheritdoc}
   */
  public function computeCKEditor5PluginSubsetConfiguration(string $cke5_plugin_id, FilterFormatInterface $text_format): ?array {
    throw new \OutOfBoundsException();
  }
}
