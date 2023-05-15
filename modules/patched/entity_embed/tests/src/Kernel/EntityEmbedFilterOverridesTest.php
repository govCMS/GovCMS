<?php

namespace Drupal\Tests\entity_embed\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Tests that entity embeds can have per-embed overrides for e.g. `alt`.
 *
 * @coversDefaultClass \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter
 * @group entity_embed
 */
class EntityEmbedFilterOverridesTest extends EntityEmbedFilterTestBase {

  use MediaTypeCreationTrait;
  use TestFileCreationTrait;

  /**
   * The image file to use in tests.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'file',
    'image',
    'media',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('file', ['file_usage']);
    $this->installEntitySchema('file');
    $this->installEntitySchema('media');
    $this->installConfig('image');
    $this->installConfig('media');
    $this->installConfig('system');

    $this->image = File::create([
      'uri' => $this->getTestFiles('image')[0]->uri,
      'uid' => 2,
    ]);
    $this->image->setPermanent();
    $this->image->save();
  }

  /**
   * Tests overriding of `alt` and `title` for default image field formatter.
   */
  public function testOverrideAltAndTitleForImage() {
    $content = $this->createEmbedCode([
      'data-entity-type' => 'file',
      'data-entity-uuid' => $this->image->uuid(),
      'data-entity-embed-display' => 'image:image',
      'data-entity-embed-display-settings' => '{"image_style":"","image_link":""}',
      'alt' => 'This is alt text',
      'title' => 'This is title text',
    ]);

    $this->applyFilter($content);

    $this->assertHasAttributes($this->cssSelect('div.embedded-entity')[0], [
      'alt' => 'This is alt text',
      'data-entity-embed-display' => 'image:image',
      'data-entity-type' => 'file',
      'data-entity-uuid' => $this->image->uuid(),
      'title' => 'This is title text',
      'data-langcode' => 'en',
    ]);
    $this->assertHasAttributes($this->cssSelect('div.embedded-entity img')[0], [
      'alt' => 'This is alt text',
      'title' => 'This is title text',
    ]);
  }

  /**
   * Tests overriding of `alt` and `title` for image media items.
   */
  public function testOverridesAltAndTitleForImageMedia() {
    $this->createMediaType('image', ['id' => 'image']);
    // The `alt` field property is enabled by default, the `title` one is not.
    // Since we want to test it, enable it.
    $source_field = FieldConfig::load('media.image.field_media_image');
    $source_field->setSetting('title_field', TRUE);
    $source_field->save();
    $this->container->get('current_user')
      ->addRole($this->drupalCreateRole(['view media']));

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'Screaming hairy armadillo',
      'field_media_image' => [
        [
          'target_id' => $this->image->id(),
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $media->save();

    $base = [
      'data-entity-embed-display' => 'view_mode:media.full',
      'data-entity-embed-display-settings' => '',
      'data-entity-type' => 'media',
      'data-entity-uuid' => $media->uuid(),
    ];
    $input = $this->createEmbedCode($base);
    $input .= $this->createEmbedCode([
      'alt' => 'alt 1',
      'title' => 'title 1',
    ] + $base);
    $input .= $this->createEmbedCode([
      'alt' => 'alt 2',
      'title' => 'title 2',
    ] + $base);
    $input .= $this->createEmbedCode([
      'alt' => 'alt 3',
      'title' => 'title 3',
    ] + $base);

    $this->applyFilter($input);

    $img_nodes = $this->cssSelect('img');
    $this->assertCount(4, $img_nodes);
    $this->assertHasAttributes($img_nodes[0], [
      'alt' => 'default alt',
    ]);
    $this->assertHasAttributes($img_nodes[1], [
      'alt' => 'alt 1',
      'title' => 'title 1',
    ]);
    $this->assertHasAttributes($img_nodes[2], [
      'alt' => 'alt 2',
      'title' => 'title 2',
    ]);
    $this->assertHasAttributes($img_nodes[3], [
      'alt' => 'alt 3',
      'title' => 'title 3',
    ]);
  }

}
