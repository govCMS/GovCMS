<?php

namespace Drupal\entity_embed_test;

/**
 * Controller routines for Twig theme test routes.
 */
class EntityEmbedTestTwigController {

  /**
   * Menu callback for testing entity_embed twig extension using entity ID.
   */
  public function idRender() {
    return [
      '#theme' => 'entity_embed_twig_test',
      '#entity_type' => 'node',
      '#id' => '1',
    ];
  }

  /**
   * Menu callback.
   *
   * Used for testing entity_embed twig extension using 'label' Entity Embed
   * Display plugin.
   */
  public function labelPluginRender() {
    return [
      '#theme' => 'entity_embed_twig_test',
      '#entity_type' => 'node',
      '#id' => '1',
      '#display_plugin' => 'entity_reference:entity_reference_label',
    ];
  }

  /**
   * Menu callback.
   *
   * Used for testing entity_embed twig extension using 'label' Entity Embed
   * Display plugin without linking to the node.
   */
  public function labelPluginNoLinkRender() {
    return [
      '#theme' => 'entity_embed_twig_test',
      '#entity_type' => 'node',
      '#id' => '1',
      '#display_plugin' => 'entity_reference:entity_reference_label',
      '#display_settings' => ['link' => 0],
    ];
  }

}
