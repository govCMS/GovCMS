<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormState;

/**
 * Tests the image field formatter provided by entity_embed.
 *
 * @group entity_embed
 */
class ImageFieldFormatterTest extends EntityEmbedTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['file', 'image', 'responsive_image'];

  /**
   * Created file entity.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $image;

  /**
   * Created file entity.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $file;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->image = $this->getTestFile('image');
    $this->file = $this->getTestFile('text');
  }

  /**
   * Tests image field formatter Entity Embed Display plugin.
   */
  public function testImageFieldFormatter() {
    // Ensure that image field formatters are available as plugins.
    $this->assertAvailableDisplayPlugins($this->image, [
      'entity_reference:entity_reference_label',
      'entity_reference:entity_reference_entity_id',
      'file:file_default',
      'file:file_table',
      'file:file_url_plain',
      'image:responsive_image',
      'image:image',
    ]);

    // Ensure that correct form attributes are returned for the image plugin.
    $form = [];
    $form_state = new FormState();
    $display = $this->container->get('plugin.manager.entity_embed.display')
      ->createInstance('image:image', []);
    $display->setContextValue('entity', $this->image);
    $conf_form = $display->buildConfigurationForm($form, $form_state);
    if (version_compare(\Drupal::VERSION, '9.4', '<')) {
      $expected = [
        'image_style',
        'image_link',
        'alt',
        'title',
      ];
    }
    else {
      // Drupal 9.4+ added a new option to the image formatter settings.
      $expected = [
        'image_style',
        'image_link',
        'image_loading',
        'alt',
        'title',
      ];
    }
    $this->assertSame($expected, array_keys($conf_form));
    $this->assertSame('select', $conf_form['image_style']['#type']);
    $this->assertSame('Image style', (string) $conf_form['image_style']['#title']);
    $this->assertSame('select', $conf_form['image_link']['#type']);
    $this->assertSame('Link image to', (string) $conf_form['image_link']['#title']);
    $this->assertSame('textfield', $conf_form['alt']['#type']);
    $this->assertSame('Alternate text', (string) $conf_form['alt']['#title']);
    $this->assertSame('textfield', $conf_form['title']['#type']);
    $this->assertSame('Title', (string) $conf_form['title']['#title']);

    // Test entity embed using 'Image' Entity Embed Display plugin.
    $alt_text = "This is sample description";
    $title = "This is sample title";
    $embed_settings = ['image_link' => 'file'];
    $content = '<drupal-entity data-entity-type="file" data-entity-uuid="' . $this->image->uuid() . '" data-entity-embed-display="image:image" data-entity-embed-display-settings=\'' . Json::encode($embed_settings) . '\' alt="' . $alt_text . '" title="' . $title . '">This placeholder should not be rendered.</drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test entity embed with image:image';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
    // Verify alternate text for the embedded image is visible
    // when embed is successful.
    $this->assertSession()->responseContains($alt_text);
    $this->assertSession()->responseNotContains('This placeholder should not be rendered.');
    $this->assertSession()->linkByHrefExists(\Drupal::service('file_url_generator')->generateString($this->image->getFileUri()), 0, 'Link to the embedded image exists.');

    // Embed all three field types in one, to ensure they all render correctly.
    $content = '<drupal-entity data-entity-type="node" data-entity-uuid="' . $this->node->uuid() . '" data-entity-embed-display="entity_reference:entity_reference_label"></drupal-entity>';
    $content .= '<drupal-entity data-entity-type="file" data-entity-uuid="' . $this->file->uuid() . '" data-entity-embed-display="file:file_default"></drupal-entity>';
    $content .= '<drupal-entity data-entity-type="file" data-entity-uuid="' . $this->image->uuid() . '" data-entity-embed-display="image:image"></drupal-entity>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test node entity embedded first then a file entity';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->drupalCreateNode($settings);
    $this->drupalGet('node/' . $node->id());
  }

}
