<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;

/**
 * Tests ckeditor integration.
 *
 * @group entity_embed
 */
class ImageFieldFormatterTest extends WebDriverTestBase {

  use SortableTestTrait;

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
   * The test button.
   *
   * @var Drupal\embed\Entity\EmbedButton
   */
  protected $button;

  /**
   * The test administrative user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->button = $this->container->get('entity_type.manager')
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

    $this->button->save();

    $format = FilterFormat::create([
      'format' => 'embed_test',
      'name' => 'Embed format',
      'filters' => [],
    ]);
    $format->save();
    $editor = Editor::create([
      'format' => 'embed_test',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [],
        ],
      ],
    ]);
    $editor->save();

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

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
      $format->getPermissionName(),
    ]);

    $this->drupalLogin($this->adminUser);

    // Create a sample image to embed.
    \Drupal::service('file_system')->copy(\Drupal::root() . '/core/misc/druplicon.png', 'public://rainbow-kitten.png');

    // Resize the test image so that it will be scaled down during token
    // replacement.
    $image1 = $this->container->get('image.factory')->get('public://rainbow-kitten.png');
    $image1->resize(500, 500);
    $image1->save();

    $this->image = $this->container->get('entity_type.manager')
      ->getStorage('file')
      ->create([
        'uri' => 'public://rainbow-kitten.png',
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
  }

  /**
   * Test invalid image error.
   */
  public function testInvalidImageError() {
    $this->drupalGet('admin/config/content/formats/manage/embed_test');
    $this->assertSession()->buttonExists('Show group names')->press();
    $this->assertSession()->waitForElementVisible('css', '.ckeditor-add-new-group');
    $this->assertSession()->buttonExists('Add group')->press();
    $this->assertSession()->waitForElementVisible('css', '[name="group-name"]')->setValue('Embeds');
    $this->assertSession()->buttonExists('Apply')->press();

    $item = 'li[data-drupal-ckeditor-button-name="' . $this->button->id() . '"]';
    $from = "ul $item";
    $target = 'ul.ckeditor-toolbar-group-buttons';

    $this->assertSession()->waitForElementVisible('css', $target);
    $this->sortableTo($item, $from, $target);

    // Verify filter checkbox exists, then check it.
    $page = $this->getSession()->getPage();
    $page->checkField('filters[entity_embed][status]');
    $page->checkField('filters[filter_html][status]');
    $this->assertSession()->buttonExists('Show row weights')->press();
    $page->selectFieldOption('filters[entity_embed][weight]', '0');
    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The text format <em class="placeholder">Embed format</em> has been updated.');
    $this->assertSession()->responseNotContains('The <em class="placeholder">Image Embed</em> button requires "alt" and "title" among the attributes of the "drupal-entity" tag within the allowed html tags.');

    $filterFormat = $this->container->get('entity_type.manager')
      ->getStorage('filter_format')
      ->load('embed_test');

    $settings = $filterFormat->filters('filter_html')->settings;
    $allowed_html = $settings['allowed_html'];

    $this->assertStringContainsString('drupal-entity data-entity-type data-entity-uuid data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button data-langcode alt title', $allowed_html);

    $this->drupalGet('/node/add/page');
    $this->assertSession()->waitForElement('css', 'a.cke_button__' . $this->button->id())->click();
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->waitForField('entity_id')
      ->setValue($this->invalidImage->label() . ' (' . $this->invalidImage->id() . ')');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseContains('The selected image "' . $this->invalidImage->label() . '" is invalid.');
    $title = $this->image->label() . ' (' . $this->image->id() . ')';
    $this->assertSession()->fieldExists('entity_id')->setValue($title);
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->responseNotContains('The selected image "' . $this->image->label() . '" is invalid.');
    $this->assertSession()->responseContains('Selected entity');
    $this->assertSession()->responseContains($this->image->label());
    $alt_text = 'Hello world alt text';
    $title_text = 'Hello world title text';
    $this->assertSession()->fieldExists('attributes[alt]')->setValue($alt_text);
    $this->assertSession()->fieldExists('attributes[title]')->setValue($title_text);
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $drupal_entity = $this->assertSession()->waitForElementVisible('css', 'drupal-entity[data-embed-button="' . $this->button->id() . '"]');
    $this->assertEquals('Hello world alt text', $drupal_entity->getAttribute('alt'));
    $this->assertEquals('Hello world title text', $drupal_entity->getAttribute('title'));
    $image = $drupal_entity->find('css', 'img');
    $this->assertStringContainsString('rainbow-kitten.png', $image->getAttribute('src'));
    $this->getSession()->switchToIFrame();

    $this->assertSession()->fieldExists('title[0][value]')->setValue('Testing Page with Embed');
    $this->assertSession()->buttonExists('Save')->press();

    $wrapper = $this->assertSession()
      ->elementExists('xpath', "//div[contains(@data-embed-button, 'image_embed')]");
    $img = $wrapper->find('css', 'img');
    $this->assertStringContainsString('rainbow-kitten.png', $img->getAttribute('src'));
    $this->assertEquals('Hello world alt text', $img->getAttribute('alt'));
    $this->assertEquals('Hello world title text', $img->getAttribute('title'));

    // Test allowed_html validation.
    $this->drupalGet('admin/config/content/formats/manage/embed_test');
    $allowed_html_field = $this->assertSession()->fieldExists('filters[filter_html][settings][allowed_html]');
    $base_tags = '<a href hreflang> <em> <strong> <cite> <blockquote cite> <code> <ul type> <ol start type> <li> <dl> <dt> <dd> <h2 id> <h3 id> <h4 id> <h5 id> <h6 id>';
    $drupal_entity_no_entity_type = '<drupal-entity data-entity-uuid data-view-mode data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button alt title>';
    $drupal_entity_with_alt_title = '<drupal-entity data-entity-type data-entity-uuid data-view-mode data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button alt title>';

    // Verify error message when `<drupal-entity>` absent but `image_embed`
    // button in the active toolbar.
    $allowed_html_field->setValue($base_tags);
    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The <em class="placeholder">Image Embed</em> button requires <code>&lt;drupal-entity&gt;</code> among the allowed HTML tags.');

    // Verify error message when `<drupal-entity>` present, `alt` and `title`
    // absent, but `image_embed` button in the active toolbar.
    $allowed_html_field->setValue($base_tags . ' ' . $drupal_entity_no_entity_type);
    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The <code>&lt;drupal-entity&gt;</code> tag in the allowed HTML tags is missing the following attributes: <code><em class="placeholder">data-entity-type</em></code>.');

    // Verify if validation errors fixed, form is submitted successfully.
    $allowed_html_field->setValue($base_tags . ' ' . $drupal_entity_with_alt_title);
    $this->assertSession()->buttonExists('Save configuration')->press();
    $this->assertSession()->responseContains('The text format <em class="placeholder">Embed format</em> has been updated.');
  }

  /**
   * Assigns a name to the CKEditor iframe, to allow use of ::switchToIFrame().
   *
   * @see \Behat\Mink\Session::switchToIFrame()
   */
  protected function assignNameToCkeditorIframe() {
    $javascript = <<<JS
(function(){
  document.getElementsByClassName('cke_wysiwyg_frame')[0].id = 'ckeditor';
})()
JS;
    $this->getSession()->evaluateScript($javascript);
  }

}
