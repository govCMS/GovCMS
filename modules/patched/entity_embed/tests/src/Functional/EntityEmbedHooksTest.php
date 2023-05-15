<?php

namespace Drupal\Tests\entity_embed\Functional;

/**
 * Tests the hooks provided by entity_embed module.
 *
 * @group entity_embed
 */
class EntityEmbedHooksTest extends EntityEmbedTestBase {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->state = $this->container->get('state');
  }

  /**
   * Tests hook_entity_embed_display_plugins_alter().
   */
  public function testDisplayPluginAlterHooks() {
    // Enable entity_embed_test.module's
    // hook_entity_embed_display_plugins_alter() implementation and ensure it is
    // working as designed.
    $this->state->set('entity_embed_test_entity_embed_display_plugins_alter', TRUE);
    $plugins = $this->container->get('plugin.manager.entity_embed.display')
      ->getDefinitionOptionsForEntity($this->node);
    // Ensure that name of each plugin is prefixed with 'testing_hook:'.
    foreach ($plugins as $plugin => $plugin_info) {
      $this->assertTrue(strpos($plugin, 'testing_hook:') === 0, 'Name of the plugin is prefixed by hook_entity_embed_display_plugins_alter()');
    }
  }

  /**
   * Tests the hooks provided by entity_embed module.
   *
   * This method tests all the hooks provided by entity_embed except
   * hook_entity_embed_display_plugins_alter, which is tested by a separate
   * method.
   */
  public function testEntityEmbedHooks() {
    // Enable entity_embed_test.module's hook_entity_embed_alter()
    // implementation and ensure it is working as designed.
    $this->state->set('entity_embed_test_entity_embed_alter', TRUE);
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-display-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test hook_entity_embed_alter()';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    // Verify embedded node body exists in page.
    $this->assertSession()->responseContains($this->node->body->value);
    $this->assertSession()->responseNotContains('This placeholder should not be rendered.');
    // Ensure that embedded node's title has been replaced.
    $this->assertSession()->responseContains('Title set by hook_entity_embed_alter');
    $this->assertSession()->responseContains('test-class-added-in-alter-hook');
    // Verify the original title of the embedded node is not visible.
    $this->assertSession()->responseNotContains($this->node->title->value);
    $this->state->set('entity_embed_test_entity_embed_alter', FALSE);

    // Enable entity_embed_test.module's hook_entity_embed_context_alter()
    // implementation and ensure it is working as designed.
    $this->state->set('entity_embed_test_entity_embed_context_alter', TRUE);
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="default" data-entity-embed-display-settings=\'{"view_mode":"teaser"}\'>This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test hook_entity_embed_context_alter()';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->responseNotContains('This placeholder should not be rendered.');
    // To ensure that 'label' plugin is used, verify that the body of the
    // embedded node is not visible and the title links to the embedded node.
    $this->assertSession()->responseNotContains($this->node->body->value);
    $this->assertSession()->responseContains('Title set by hook_entity_embed_context_alter');
    $this->assertSession()->linkByHrefExists('node/' . $this->node->id(), 0, 'Link to the embedded node exists.');
    $this->state->set('entity_embed_test_entity_embed_context_alter', FALSE);
  }

}
