<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests ckeditor integration.
 *
 * @group entity_embed
 */
class CKEditorIntegrationTest extends EntityEmbedTestBase {

  use SortableTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field_ui',
    'node',
    'ckeditor',
    'views',
    'embed',
    'entity_embed',
    'entity_embed_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The test button.
   *
   * @var \Drupal\embed\Entity\EmbedButtonInterface
   */
  protected $button;

  /**
   * The test administrative user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

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

    $this->button = $this->container->get('entity_type.manager')
      ->getStorage('embed_button')
      ->load('node');
    $settings = $this->button->getTypeSettings();
    $settings['display_plugins'] = [
      'entity_reference:entity_reference_label',
    ];
    $this->button->set('type_settings', $settings);
    $this->button->save();

    $format = FilterFormat::create([
      'format' => 'embed_test',
      'name' => 'Embed format',
      'filters' => [],
    ]);
    $format->save();

    Editor::create([
      'format' => 'embed_test',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [
            [
              [
                'name' => 'Tools',
                'items' => [
                  'Source',
                  'Undo',
                  'Redo',
                ],
              ],
            ],
          ],
        ],
      ],
    ])->save();

    // Create a page content type.
    $this->drupalCreateContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);

    $this->adminUser = $this->drupalCreateUser([
      'access administration pages',
      'administer filters',
      'administer display modes',
      'administer embed buttons',
      'administer site configuration',
      'administer display modes',
      'administer content types',
      'administer node display',
      'access content',
      'create page content',
      'edit own page content',
      $format->getPermissionName(),
    ]);

    $this->drupalLogin($this->adminUser);

    // Create a sample node.
    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Billy Bones',
      'body' => [
        'value' => 'He lacks two fingers.',
      ],
    ]);

    $this->drupalCreateNode([
      'type' => 'page',
      'title' => 'Long John Silver',
      'body' => [
        'value' => 'A one-legged seafaring man.',
      ],
    ]);
  }

  /**
   * Test integration with Filter, Editor and Ckeditor.
   */
  public function testIntegration() {
    $this->drupalGet('admin/config/content/formats/manage/embed_test');

    $page = $this->getSession()->getPage();

    $page->checkField('filters[entity_embed][status]');
    $page->checkField('filters[filter_html][status]');

    // Add "Embeds" toolbar button group to the active toolbar.
    $this->assertSession()->buttonExists('Show group names')->press();
    $this->assertSession()->waitForElementVisible('css', '.ckeditor-add-new-group');
    $this->assertSession()->buttonExists('Add group')->press();
    $this->assertSession()->waitForElementVisible('css', '[name="group-name"]')->setValue('Embeds');
    $this->assertSession()->buttonExists('Apply')->press();

    // Verify the <drupal-entity> tag is not yet allowed.
    $allowed_html = $this->assertSession()->fieldExists('filters[filter_html][settings][allowed_html]')->getValue();
    $this->assertStringNotContainsString('drupal-entity', $allowed_html);

    // Verify that after dragging the Entity Embed CKEditor plugin button into
    // the active toolbar, the <drupal-entity> tag is allowed, as well as some
    // attributes.
    $item = 'li[data-drupal-ckeditor-button-name="' . $this->button->id() . '"]';
    $from = "ul $item";
    $target = 'ul.ckeditor-toolbar-group-buttons';

    $this->assertSession()->waitForElementVisible('css', $target);
    $this->sortableTo($item, $from, $target);
    $allowed_html_updated = $this->assertSession()
      ->fieldExists('filters[filter_html][settings][allowed_html]')
      ->getValue();
    $this->assertStringContainsString('drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button', $allowed_html_updated);

    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The text format <em class="placeholder">Embed format</em> has been updated.');
    $filterFormat = $this->container->get('entity_type.manager')
      ->getStorage('filter_format')
      ->load('embed_test');

    $settings = $filterFormat->filters('filter_html')->settings;
    $allowed_html = $settings['allowed_html'];

    $this->assertStringContainsString('drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button', $allowed_html);

    // Verify that the Entity Embed button shows up and results in an
    // operational entity embedding experience in the text editor.
    $this->drupalGet('/node/add/page');
    $this->waitForEditor();
    $this->assertSame(1, $this->getCkeditorUndoSnapshotCount());
    $this->getSession()->executeScript("CKEDITOR.instances['edit-body-0-value'].setData('<p>Goodbye world!</p>');");
    $this->assertSame(2, $this->getCkeditorUndoSnapshotCount());
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->responseContains('entity_embed.editor.css');
    $this->assertSession()->responseContains('hidden.module.css');
    $this->assertSession()->pageTextNotContains('Billy Bones');
    $this->pressEditorButton($this->button->id());
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Billy Bones (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseContains('Selected entity');
    $this->assertSession()->responseContains('Billy Bones');
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Verify that the embedded entity gets a preview inside the text editor.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->pageTextContains('Billy Bones');
    $this->getSession()->switchToIFrame();
    $this->assertSame(4, $this->getCkeditorUndoSnapshotCount());
    $this->getSession()
      ->getPage()
      ->find('css', 'input[name="title[0][value]"]')
      ->setValue('Pirates');
    // Verify that undo/redo work.
    $this->assertCkeditorUndoOrRedo('undo', ['Goodbye world!'], ['Billy Bones']);
    $this->assertCkeditorUndoOrRedo('undo', [], ['Billy Bones', 'Goodbye world!']);
    $this->assertCkeditorUndoOrRedo('redo', ['Goodbye world!'], ['Billy Bones']);
    $this->assertCkeditorUndoOrRedo('redo', ['Billy Bones', 'Goodbye world!'], []);
    // Verify that the embedded entity is rendered by the filter for end users.
    $this->assertSession()->buttonExists('Save')->press();
    $this->assertSession()->responseContains('Billy Bones');

    $this->drupalGet('/node/3/edit');
    $this->assignNameToCkeditorIframe();

    // Verify that the text editor previews the current embedded entity.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForText('Billy Bones');
    $this->getSession()->switchToIFrame();

    // Test opening the dialog and switching embedded nodes.
    $this->reopenDialog();

    $this->assertSession()
      ->waitForElementVisible('css', 'div.ui-dialog-buttonset')
      ->findButton('Back')
      ->click();

    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Long John Silver (2)');

    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseContains('Selected entity');
    $this->assertSession()->responseContains('Long John Silver');
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Verify that the text editor previews the updated embedded entity.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForText('Long John Silver');
    $this->getSession()->switchToIFrame();
    $this->assertSession()->buttonExists('Save')->press();
    // Verify that the embedded entity is rendered by the filter for end users.
    $this->assertSession()->responseContains('Long John Silver');
  }

  /**
   * Asserts the consequences of CKEditor undo/redo actions.
   *
   * @param string $action
   *   Either 'undo' or 'redo'.
   * @param array $contains
   *   The strings the CKEditor instance is expected to contain.
   * @param array $not_contains
   *   The strings the CKEditor instance is expected to not contain.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function assertCkeditorUndoOrRedo($action, array $contains, array $not_contains) {
    if ($action !== 'undo' && $action !== 'redo') {
      throw new \LogicException();
    }
    $this->pressEditorButton($action);
    $this->getSession()->switchToIFrame('ckeditor');
    foreach ($contains as $string) {
      $this->assertSession()->pageTextContains($string);
    }
    foreach ($not_contains as $string) {
      $this->assertSession()->pageTextNotContains($string);
    }
    $this->getSession()->switchToIFrame();
  }

  /**
   * Get a CKEditor instance's undo snapshot count.
   *
   * @param string $instance_id
   *   The CKEditor instance ID.
   *
   * @return int
   *   The undo snapshot count.
   */
  protected function getCkeditorUndoSnapshotCount($instance_id = 'edit-body-0-value') {
    $this->waitForEditor($instance_id);
    return $this->getSession()->evaluateScript("CKEDITOR.instances['$instance_id'].undoManager.snapshots.length");
  }

}
