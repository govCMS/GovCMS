<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;

/**
 * Tests the creation and configuration of entity embed buttons.
 *
 * @group entity_embed
 */
class ButtonAdminTest extends WebDriverTestBase {

  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'user',
    'media',
    'entity_embed',
  ];

  /**
   * The user to use during testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container
      ->get('entity_type.manager')
      ->getStorage('entity_view_mode')
      ->create([
        'id' => 'media.thumb',
        'targetEntityType' => 'media',
      ])
      ->save();

    $this->createContentType([
      'type' => 'article',
      'label' => 'Article',
    ]);

    $this->createMediaType('image', [
      'id' => 'image',
      'label' => 'Image',
    ]);

    $this->adminUser = $this->drupalCreateUser([
      'administer embed buttons',
    ]);

    // Delete the existing node button provided by entity_embed module, so that
    // we can create a button with the same machine name.
    $this->container->get('entity_type.manager')
      ->getStorage('embed_button')
      ->load('node')
      ->delete();
  }

  /**
   * Tests the entity embed button administration functionality.
   *
   * @param string $entity_type_id
   *   The entity type ID as well as the label and machine name of the button.
   * @param string $bundle_id
   *   The bundle to select, if provided.
   * @param string $entity_embed_display_plugin_id
   *   The entity embed display plugin ID to select on the form.
   *
   * @dataProvider embedButtonAdminProvider
   */
  public function testEmbedButtonAdmin($entity_type_id, $bundle_id, $entity_embed_display_plugin_id) {
    $this->drupalLogin($this->adminUser);

    $this->drupalGet('admin/config/content/embed/button/add');

    $page = $this->getSession()->getPage();
    $page->fillField('label', $entity_type_id);
    $page->selectFieldOption('type_id', 'entity');
    $page->waitFor(10, function () use ($page) {
      return $page->hasField('type_settings[entity_type]');
    });
    $page->selectFieldOption('type_settings[entity_type]', $entity_type_id);
    $page->waitFor(10, function () use ($page, $bundle_id) {
      return $page->hasField('type_settings[bundles][' . $bundle_id . ']');
    });
    $page->checkField('type_settings[display_plugins][' . $entity_embed_display_plugin_id . ']');
    $page->pressButton('Save');

    $this->assertStringContainsString('The embed button ' . $entity_type_id . ' has been added.', $page->getText());
    $this->assertSession()->linkByHrefExists('/admin/config/content/embed/button/manage/' . $entity_type_id);

    $this->drupalGet('/admin/config/content/embed/button/manage/' . $entity_type_id);

    $page->findField('type_id')->hasAttribute('disabled');
    $page->findField('type_settings[entity_type]')->hasAttribute('disabled');
  }

  /**
   * Data provider for ::testEmbedButtonAdmin().
   */
  public function embedButtonAdminProvider() {
    return [
      'article nodes embedded using teaser view mode' => [
        'node',
        'article',
        'view_mode:node.teaser',
      ],
      'users embedded using full view mode' => [
        'user',
        NULL,
        'view_mode:user.full',
      ],
      'image media items embedded using thumb view mode' => [
        'media',
        'image',
        'view_mode:media.thumb',
      ],
      'files embedded using plain URL' => [
        'file',
        NULL,
        'file:file_url_plain',
      ],
    ];
  }

}
