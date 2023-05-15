<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\entity_browser\Entity\EntityBrowser;
use Drupal\embed\Entity\EmbedButton;

/**
 * Tests the entity_embed entity_browser integration.
 *
 * @group entity_embed
 *
 * @dependencies entity_browser
 */
class EntityEmbedEntityBrowserTest extends EntityEmbedDialogTest {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['entity_browser'];

  /**
   * Tests the entity browser integration.
   */
  public function testEntityEmbedEntityBrowserIntegration() {
    $this->drupalGet('/entity-embed/dialog/custom_format/node');
    // Verify embed dialog is accessible with custom filter format and
    // default embed button.
    $this->assertSession()->statusCodeEquals(200);

    // Verify that an autocomplete field is available by default.
    $this->assertSession()->fieldExists('entity_id');
    $this->assertSession()
      ->linkNotExists('Select entities to embed', 'Entity browser button is not present.');

    // Set up entity browser.
    $entity_browser = EntityBrowser::create([
      "name" => 'entity_embed_entity_browser_test',
      "label" => 'Test Entity Browser for Entity Embed',
      "display" => 'modal',
      "display_configuration" => [
        'width' => '650',
        'height' => '500',
        'link_text' => 'Select entities to embed',
      ],
      "selection_display" => 'no_display',
      "selection_display_configuration" => [],
      "widget_selector" => 'single',
      "widget_selector_configuration" => [],
      "widgets" => [],
    ]);
    $entity_browser->save();

    // Enable entity browser for the default entity embed button.
    $embed_button = EmbedButton::load('node');
    $embed_button->type_settings['entity_browser'] = 'entity_embed_entity_browser_test';
    $embed_button->save();

    // Rebuild routes, so the route called by getEmbedDialog() exists.
    $this->container->get('router.builder')->rebuild();

    $dependencies = $embed_button->getDependencies();
    $this->assertContains('entity_browser.browser.entity_embed_entity_browser_test', $dependencies['config']);

    $this->drupalGet('/entity-embed/dialog/custom_format/node');

    // Verify embed dialog is accessible with custom filter format and
    // default embed button.
    $this->assertSession()->statusCodeEquals(200);

    // Verify that the autocomplete field is replaced by an entity browser
    // button.
    $this->assertSession()->fieldNotExists('entity_id');
    $this->assertSession()->buttonExists('Select entities to embed');
  }

}
