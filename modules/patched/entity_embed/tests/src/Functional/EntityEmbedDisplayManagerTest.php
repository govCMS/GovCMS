<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @coversDefaultClass \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
 * @group entity_embed
 */
class EntityEmbedDisplayManagerTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'file',
    'image',
    'ckeditor',
    'entity_embed',
  ];

  /**
   * The test button that embeds image files.
   *
   * @var \Drupal\embed\Entity\EmbedButton
   */
  protected $imageButton;

  /**
   * Created file entity.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * File created with invalid image.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $invalidImage;

  /**
   * The EntityEmbedDisplay plugin manager.
   *
   * @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
   */
  protected $entityEmbedDisplayManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->imageButton = $this->container->get('entity_type.manager')
      ->getStorage('embed_button')
      ->create([
        'label' => 'Image Embed',
        'id' => 'image_embed',
        'type_id' => 'entity',
        'type_settings' => [
          'entity_type' => 'file',
          'display_plugins' => [
            'image:image',
          ],
          'entity_browser' => '',
          'entity_browser_settings' => [
            'display_review' => FALSE,
          ],
        ],
        'icon_uuid' => NULL,
      ]);
    $this->imageButton->save();

    // Create a sample image to embed.
    \Drupal::service('file_system')->copy('core/tests/fixtures/files/image-1.png', 'public://example1.png');

    // Resize the test image so that it will be scaled down during token
    // replacement.
    $this->image1 = $this->container->get('image.factory')->get('public://example1.png');
    $this->image1->resize(500, 500);
    $this->image1->save();

    $this->image = $this->container->get('entity_type.manager')
      ->getStorage('file')
      ->create([
        'uri' => 'public://example1.png',
        'status' => 1,
      ]);
    $this->image->save();

    $this->invalidImage = $this->container->get('entity_type.manager')
      ->getStorage('file')
      ->create([
        'uri' => 'public://nonexistentimage.jpg',
        'filename' => 'nonexistentimage.jpg',
        'status' => 1,
      ]);
    $this->invalidImage->save();

    $this->entityEmbedDisplayManager = $this->container->get('plugin.manager.entity_embed.display');
  }

  /**
   * @covers ::getDefinitionsForContexts
   */
  public function testGetDefinitionsForContexts() {
    $options = $this->entityEmbedDisplayManager
      ->getDefinitionOptionsForContext([
        'entity' => $this->image,
        'entity_type' => $this->image->getEntityTypeId(),
        'embed_button' => $this->imageButton,
      ]);
    $expected = [
      'image:image' => 'Image',
    ];
    $this->assertEquals($expected, $options);

    $options = $this->entityEmbedDisplayManager
      ->getDefinitionOptionsForContext([
        'entity' => $this->image,
        'entity_type' => $this->image->getEntityTypeId(),
      ]);
    // All available plugins for the entity type.
    $expected = [
      'image:image' => 'Image',
      'entity_reference:entity_reference_entity_id' => 'Entity ID',
      'file:file_default' => 'Generic file',
      'entity_reference:entity_reference_label' => 'Label',
      'file:file_table' => 'Table of files',
      'file:file_url_plain' => 'URL to file',
      'image:image_url' => 'URL to image',
    ];
    $this->assertEquals($expected, $options);

    // Test that output is the same as ::getDefinitionOptionsForEntity().
    $options = $this->entityEmbedDisplayManager
      ->getDefinitionOptionsForEntity($this->image);
    $this->assertEquals($expected, $options);

    $options = $this->entityEmbedDisplayManager
      ->getDefinitionOptionsForContext([
        'entity' => $this->invalidImage,
        'entity_type' => $this->invalidImage->getEntityTypeId(),
        'embed_button' => $this->imageButton,
      ]);
    // Since the image is invalid, the `image:image` display isn't returned.
    $this->assertEmpty($options);

    $options = $this->entityEmbedDisplayManager
      ->getDefinitionOptionsForContext([
        'entity' => $this->invalidImage,
        'entity_type' => $this->invalidImage->getEntityTypeId(),
      ]);
    // Since the image is invalid, the image display plugins aren't returned.
    $expected = [
      'entity_reference:entity_reference_entity_id' => 'Entity ID',
      'file:file_default' => 'Generic file',
      'entity_reference:entity_reference_label' => 'Label',
      'file:file_table' => 'Table of files',
      'file:file_url_plain' => 'URL to file',
    ];
    $this->assertEquals($expected, $options);

    // Test that output is the same as ::getDefinitionOptionsForEntity().
    $options = $this->entityEmbedDisplayManager
      ->getDefinitionOptionsForEntity($this->invalidImage);
    $this->assertEquals($expected, $options);
  }

}
