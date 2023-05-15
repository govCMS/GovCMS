<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\Core\Entity\EntityInterface;
use Drupal\editor\Entity\Editor;
use Drupal\file\Entity\File;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Base class for all entity_embed tests.
 */
abstract class EntityEmbedTestBase extends BrowserTestBase {

  use TestFileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'entity_embed',
    'entity_embed_test',
    'node',
    'ckeditor',
  ];

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
        'filter_align' => [
          'status' => 1,
        ],
        'filter_caption' => [
          'status' => 1,
        ],
        'filter_html_image_secure' => [
          'status' => 1,
        ],
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
    $settings['body'] = ['value' => 'This node is to be used for embedding in other nodes.', 'format' => 'custom_format'];
    $this->node = $this->drupalCreateNode($settings);
  }

  /**
   * Retrieves a sample file of the specified type.
   *
   * @return \Drupal\file\FileInterface
   *   The test file created.
   */
  protected function getTestFile($type_name, $size = NULL) {
    // Get a file to upload.
    $file = current($this->getTestFiles($type_name, $size));

    // Add a filesize property to files as would be read by
    // \Drupal\file\Entity\File::load().
    $file->filesize = filesize($file->uri);

    $file = File::create((array) $file);
    $file->save();
    return $file;
  }

  /**
   * Assert that the expected display plugins are available for the entity.
   */
  public function assertAvailableDisplayPlugins(EntityInterface $entity, array $expected_plugins, $message = '') {
    $plugin_options = $this->container->get('plugin.manager.entity_embed.display')
      ->getDefinitionOptionsForEntity($entity);
    $this->assertEquals([], array_diff($expected_plugins, array_keys($plugin_options)), $message);
  }

}
