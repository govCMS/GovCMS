<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests the entity_embed dialog controller and route.
 *
 * @group entity_embed
 * @requires function Drupal\FunctionalJavascriptTests\WebDriverTestBase::assertSession
 */
class EntityEmbedDialogTest extends EntityEmbedTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['image'];

  /**
   * The test user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $webUser;

  /**
   * A test node to be used for embedding.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a page content type.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Create a text format and enable the entity_embed filter.
    $format = FilterFormat::create([
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => [
        'entity_embed' => [
          'status' => 1,
        ],
      ],
    ]);
    $format->save();

    $editor_group = [
      'name' => 'Entity Embed',
      'items' => [
        'node',
      ],
    ];
    $editor = Editor::create([
      'format' => 'custom_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();

    // Create a user with required permissions.
    $this->webUser = $this->drupalCreateUser([
      'access content',
      'create page content',
      'use text format custom_format',
    ]);
    $this->drupalLogin($this->webUser);

    // Create a sample node to be embedded.
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Embed Test Node';
    $settings['body'] = [
      'value' => 'This node is to be used for embedding in other nodes.',
      'format' => 'custom_format',
    ];
    $this->node = $this->drupalCreateNode($settings);
  }

  /**
   * Tests the entity embed button markup.
   */
  public function testEntityEmbedButtonMarkup() {
    // Ensure that the route is accessible with a valid embed button.
    // 'Node' embed button is provided by default by the module and hence the
    // request must be successful.
    $this->drupalGet('/entity-embed/dialog/custom_format/node');

    // Ensure form structure of the 'select' step and submit form.
    $this->assertSession()->fieldExists('entity_id');

    // Check that 'Next' is a primary button.
    $this->assertSession()->elementExists('xpath', '//input[contains(@class, "button--primary")]');

    $title = $this->node->getTitle() . ' (' . $this->node->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->buttonExists('Next')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $plugins = [
      'entity_reference:entity_reference_label',
      'entity_reference:entity_reference_entity_id',
      'view_mode:node.full',
      'view_mode:node.rss',
      'view_mode:node.search_index',
      'view_mode:node.search_result',
      'view_mode:node.teaser',
    ];
    foreach ($plugins as $plugin) {
      $this->assertSession()->optionExists('Display as', $plugin);
    }

    $this->container->get('config.factory')->getEditable('entity_embed.settings')
      ->set('rendered_entity_mode', TRUE)->save();
    $this->container->get('plugin.manager.entity_embed.display')->clearCachedDefinitions();

    $this->drupalGet('/entity-embed/dialog/custom_format/node');
    $title = $this->node->getTitle() . ' (' . $this->node->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->buttonExists('Next')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $plugins = [
      'entity_reference:entity_reference_label',
      'entity_reference:entity_reference_entity_id',
      'entity_reference:entity_reference_entity_view',
    ];
    foreach ($plugins as $plugin) {
      $this->assertSession()->optionExists('Display as', $plugin);
    }
  }

}
