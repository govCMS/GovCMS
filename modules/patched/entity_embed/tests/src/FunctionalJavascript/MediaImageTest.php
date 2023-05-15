<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\Component\Utility\Html;
use Drupal\editor\Entity\Editor;
use Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay\MediaImageDecorator;
use Drupal\field\Entity\FieldConfig;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\TestFileCreationTrait;

/**
 * Test Media Image specific functionality.
 *
 * @group entity_embed
 */
class MediaImageTest extends EntityEmbedTestBase {

  use ContentTypeCreationTrait;
  use TestFileCreationTrait;

  /**
   * The user to use during testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The sample Media entity to embed.
   *
   * @var \Drupal\media\MediaInterface
   */
  protected $media;

  /**
   * A host entity with a body field to embed media in.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $host;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable9';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_embed_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Note that media_install() grants 'view media' to all users by default.
    $this->adminUser = $this->drupalCreateUser([
      'use text format full_html',
      'bypass node access',
    ]);

    $this->createNode([
      'type' => 'article',
      'title' => 'Red-lipped batfish',
    ]);

    // Create a sample media entity to be embedded.
    File::create([
      'uri' => $this->getTestFiles('image')[0]->uri,
    ])->save();
    $this->media = Media::create([
      'bundle' => 'image',
      'name' => 'Screaming hairy armadillo',
      'field_media_image' => [
        [
          'target_id' => 1,
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $this->media->save();

    // Create a sample host entity to embed media in.
    $this->drupalCreateContentType(['type' => 'blog']);
    $this->host = $this->createNode([
      'type' => 'blog',
      'title' => 'Animals with strange names',
      'body' => [
        'value' => '',
        'format' => 'full_html',
      ],
    ]);
    $this->host->save();

    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests alt and title overriding for embedded images.
   */
  public function testAltAndTitle() {
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();

    $this->assignNameToCkeditorIframe();

    $this->pressEditorButton('test_node');
    $this->assertSession()->waitForId('drupal-modal');

    // Test that node embed doesn't display alt and title fields.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Red-lipped batfish (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert that the review step displays the selected entity with the label.
    $text = $form->getText();
    $this->assertStringContainsString('Red-lipped batfish', $text);

    $select = $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]');

    $select->setValue('view_mode:node.full');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The view_mode:node.full display shouldn't have alt and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $select = $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]');

    $select->setValue('view_mode:node.teaser');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The view_mode:node.teaser display shouldn't have alt and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    // Close the dialog.
    $this->assertSession()->elementExists('css', '.ui-dialog-titlebar-close')->press();

    // Now test with media.
    $this->pressEditorButton('test_media_entity_embed');
    $this->assertSession()->waitForId('drupal-modal');

    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Screaming hairy armadillo (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert that the review step displays the selected entity with the label.
    $text = $form->getText();
    $this->assertStringContainsString('Screaming hairy armadillo', $text);

    $select = $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]');

    $select->setValue('entity_reference:entity_reference_entity_id');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The entity_reference:entity_reference_entity_id display shouldn't have
    // alt and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $select->setValue('entity_reference:entity_reference_label');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // The entity_reference:entity_reference_label display shouldn't have alt
    // and title fields.
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    // Test the entity embed display that ships with core media.
    $select->setValue('entity_reference:media_thumbnail');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $alt = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertEquals($this->media->field_media_image->alt, $alt->getAttribute('placeholder'));
    $title = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]');
    $this->assertEquals($this->media->field_media_image->title, $title->getAttribute('placeholder'));

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("default alt", $img->getAttribute('alt'));
    $this->assertEquals("default title", $img->getAttribute('title'));

    $this->reopenDialog();

    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')
      ->setValue('Satanic leaf-tailed gecko alt');
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')
      ->setValue('Satanic leaf-tailed gecko title');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("Satanic leaf-tailed gecko alt", $img->getAttribute('alt'));
    $this->assertEquals("Satanic leaf-tailed gecko title", $img->getAttribute('title'));

    $this->reopenDialog();

    // Test a view mode that displays thumbnail field.
    $select->setValue('view_mode:media.thumb');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $alt = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $alt->getValue());
    $title = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]');
    $this->assertEquals('Satanic leaf-tailed gecko title', $title->getValue());

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $img->getAttribute('alt'));
    $this->assertEquals('Satanic leaf-tailed gecko title', $img->getAttribute('title'));

    $this->reopenDialog();

    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')
      ->setValue('Goblin shark alt');
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')
      ->setValue('Goblin shark title');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("Goblin shark alt", $img->getAttribute('alt'));
    $this->assertEquals("Goblin shark title", $img->getAttribute('title'));

    $this->reopenDialog();

    // Test a view mode that displays the media's image field.
    $select->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Test that the view_mode:media.embed display has alt and title fields,
    // and that the default values match the values on the media's
    // source image field.
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('view_mode:media.embed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $alt = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertEquals("Goblin shark alt", $alt->getValue());
    $title = $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]');
    $this->assertEquals("Goblin shark title", $title->getValue());

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals("Goblin shark alt", $img->getAttribute('alt'));
    $this->assertEquals("Goblin shark title", $img->getAttribute('title'));

    $this->reopenDialog();

    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')
      ->setValue('Satanic leaf-tailed gecko alt');
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')
      ->setValue('Satanic leaf-tailed gecko title');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $img->getAttribute('alt'));
    $this->assertEquals('Satanic leaf-tailed gecko title', $img->getAttribute('title'));

    $this->config('field.field.media.image.field_media_image')
      ->set('settings.alt_field', FALSE)
      ->set('settings.title_field', FALSE)
      ->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('default alt', $img->getAttribute('alt'));
    $this->assertEquals('default title', $img->getAttribute('title'));

    $field = FieldConfig::load('media.image.field_media_image');
    $settings = $field->getSettings();
    $settings['alt_field'] = TRUE;
    $field->set('settings', $settings);
    $field->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    // Test that when only the alt field is enabled, only alt field should
    // display.
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][alt]')->setValue('Satanic leaf-tailed gecko alt');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][title]');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko alt', $img->getAttribute('alt'));
    $this->assertEquals('default title', $img->getAttribute('title'));

    $field = FieldConfig::load('media.image.field_media_image');
    $settings = $field->getSettings();
    $settings['alt_field'] = FALSE;
    $settings['title_field'] = TRUE;
    $field->set('settings', $settings);
    $field->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    // With only title field enabled, only title field should display.
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][title]')->setValue('Satanic leaf-tailed gecko title');
    $this->assertSession()
      ->fieldNotExists('attributes[data-entity-embed-display-settings][alt]');

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('Satanic leaf-tailed gecko title', $img->getAttribute('title'));
    $this->assertEquals('default alt', $img->getAttribute('alt'));

    $field = FieldConfig::load('media.image.field_media_image');
    $settings = $field->getSettings();
    $settings['alt_field'] = TRUE;
    $settings['title_field'] = TRUE;
    $field->set('settings', $settings);
    $field->save();

    drupal_flush_all_caches();

    $this->reopenDialog();

    // Test that setting value to double quote will allow setting the alt
    // and title to empty.
    $alt->setValue(MediaImageDecorator::EMPTY_STRING);
    $title->setValue(MediaImageDecorator::EMPTY_STRING);

    $this->submitDialog();

    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEmpty($img->getAttribute('alt'));
    $this->assertEmpty($img->getAttribute('title'));

    $this->reopenDialog();

    // Test that *not* filling out the fields makes it fall back to the default.
    $alt->setValue('');
    $title->setValue('');
    $this->submitDialog();
    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertEquals('default alt', $img->getAttribute('alt'));
    $this->assertEquals('default title', $img->getAttribute('title'));
  }

  /**
   * Tests caption editing in the CKEditor widget.
   */
  public function testCkeditorWidgetHasEditableCaption() {
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->pressEditorButton('test_media_entity_embed');
    $this->assertSession()->waitForId('drupal-modal');

    // Embed media.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Screaming hairy armadillo (1)');
    $this->assertSession()
      ->elementExists('css', 'button.js-button-next')
      ->click();
    $this->assertSession()
      ->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('entity_reference:media_thumbnail');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue('Is this the real life? Is this just fantasy?');
    $this->submitDialog();

    // Assert that the embedded media was upcasted to a CKEditor widget.
    $figcaption = $this->assertSession()
      ->elementExists('css', 'figcaption');
    $this->assertTrue($figcaption->hasAttribute('contenteditable'));

    // Type in the widget's editable for the caption.
    $this->assertSession()->waitForElement('css', 'figcaption');
    $this->setCaption('Caught in a <strong>landslide</strong>! No escape from <em>reality</em>!');
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementExists('css', 'figcaption > em');
    $this->assertSession()->elementExists('css', 'figcaption > strong')->click();

    // Select the <strong> element and unbold it.
    $this->clickPathLinkByTitleAttribute("strong element");
    $this->pressEditorButton('bold');
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementExists('css', 'figcaption > em');
    $this->assertSession()->elementNotExists('css', 'figcaption > strong');

    // Select the <em> element and unitalicize it.
    $this->assertSession()->elementExists('css', 'figcaption > em')->click();
    $this->clickPathLinkByTitleAttribute("em element");
    $this->pressEditorButton('italic');

    // The "source" button should reveal the HTML source in a state matching
    // what is shown in the CKEditor widget.
    $this->pressEditorButton('source');
    $source = $this->assertSession()->elementExists('css', 'textarea.cke_source');
    $value = $source->getValue();
    $dom = Html::load($value);
    $xpath = new \DOMXPath($dom);
    $drupal_entity = $xpath->query('//drupal-entity')[0];
    $this->assertEquals('Caught in a landslide! No escape from reality!', $drupal_entity->getAttribute('data-caption'));

    // Change the caption by modifying the HTML source directly. When exiting
    // "source" mode, this should be respected.
    $poor_boy_text = "I'm just a <strong>poor boy</strong>, I need no sympathy!";
    $drupal_entity->setAttribute("data-caption", $poor_boy_text);
    $source->setValue(Html::serialize($dom));
    $this->pressEditorButton('source');
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $figcaption = $this->assertSession()->waitForElement('css', 'figcaption');
    $this->assertEquals($poor_boy_text, $figcaption->getHtml());

    // Select the <strong> element that we just set in "source" mode. This
    // proves that it was indeed rendered by the CKEditor widget.
    $figcaption->find('css', 'strong')->click();
    $this->pressEditorButton('bold');

    // Insert a link into the caption.
    $this->clickPathLinkByTitleAttribute("Caption element");
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('https://www.drupal.org');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Wait for the live preview in the CKEditor widget to finish loading, then
    // edit the link; no `data-cke-saved-href` attribute should exist on it.
    $this->getSession()->switchToIFrame('ckeditor');
    $figcaption = $this->assertSession()->waitForElement('css', 'figcaption');
    $figcaption->find('css', 'a')->click();
    $this->clickPathLinkByTitleAttribute("a element");
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('https://www.drupal.org/project/drupal');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->pressEditorButton('source');
    $source = $this->assertSession()->elementExists('css', "textarea.cke_source");
    $value = $source->getValue();
    $this->assertStringContainsString('https://www.drupal.org/project/drupal', $value);
    $this->assertStringNotContainsString('data-cke-saved-href', $value);

    // Save the entity.
    $this->assertSession()->buttonExists('Save')->press();

    // Verify the saved entity when viewed also contains the captioned media.
    $link = $this->assertSession()->elementExists('css', 'figcaption > a');
    $this->assertEquals('https://www.drupal.org/project/drupal', $link->getAttribute('href'));
    $this->assertEquals("I'm just a poor boy, I need no sympathy!", $link->getText());

    // Edit it again, type a different caption in the widget.
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForElementVisible('css', 'figcaption');
    $this->setCaption('Scaramouch, <em>Scaramouch</em>, will you do the <strong>Fandango</strong>?');

    // Verify that the element path usefully indicates the specific media type
    // that is being embedded.
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementExists('xpath', '//figcaption//em')->click();
    $this->getSession()->switchToIFrame();
    $this->assertSession()
      ->elementTextContains('css', '#cke_1_path', 'Embedded Media Entity Embed');

    // Test that removing caption in the EntityEmbedDialog form sets the embed
    // to be captionless.
    $this->reopenDialog();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue('');
    $this->submitDialog();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->elementExists('css', 'drupal-entity');
    $this->assertSession()->elementNotExists('css', 'figcaption');

    // Set a caption again; this time not using the CKEditor Widget, but through
    // the dialog. We're typing HTML in the form field, but it will have to be
    // HTML-encoded for it to actually show up properly in the CKEditor Widget.
    $this->reopenDialog();
    $freddys_lament = "Mama, life had just begun. But now I've gone and <strong>thrown it all away</strong>! :(";
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue($freddys_lament);
    $this->submitDialog();
    $this->assertSession()->elementExists('css', 'figcaption');

    // Change the caption in the dialog to contain a link.
    $wind_markup = '<a href="http://www.drupal.org">anyway the wind blows</a>';
    $this->reopenDialog();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue($wind_markup);
    $this->submitDialog();

    // Assert the caption in the CKEditor widget was updated.
    $figcaption = $this->assertSession()
      ->waitForElementVisible('css', 'figcaption');
    $this->assertEquals('anyway the wind blows', $figcaption->getText());

    // Change the text of the link in the caption.
    $gallileo = '<a href="http://www.drupal.org">Gallileo, figaro, magnifico</a>';
    $this->reopenDialog();
    $this->assertSession()
      ->fieldExists('attributes[data-caption]')
      ->setValue($gallileo);
    $this->submitDialog();

    // Assert the caption in the CKEditor widget was updated.
    $figcaption = $this->assertSession()
      ->waitForElementVisible('css', 'figcaption');
    $this->assertEquals('Gallileo, figaro, magnifico', $figcaption->getText());

    // Erase the caption in the CKEditor Widget, verify the <figcaption> still
    // exists and contains placeholder text, then type something else.
    $this->setCaption('');
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementContains('css', 'figcaption', '');
    $this->assertSession()->elementAttributeContains('css', 'figcaption', 'data-placeholder', 'Enter caption here');
    $this->setCaption('Fin.');
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementContains('css', 'figcaption', 'Fin.');
  }

  /**
   * Tests linkability of the CKEditor widget when `drupalimage` is disabled.
   */
  public function testCkeditorWidgetIsLinkableWhenDrupalImageIsAbsent() {
    // Remove the `drupalimage` plugin's `DrupalImage` button.
    $editor = Editor::load('full_html');
    $settings = $editor->getSettings();
    $rows = $settings['toolbar']['rows'];
    foreach ($rows as $row_key => $row) {
      foreach ($row as $group_key => $group) {
        foreach ($group['items'] as $item_key => $item) {
          if ($item === 'DrupalImage') {
            unset($settings['toolbar']['rows'][$row_key][$group_key]['items'][$item_key]);
          }
        }
      }
    }
    $editor->setSettings($settings);
    $editor->save();

    $this->testCkeditorWidgetIsLinkable();
  }

  /**
   * Tests linkability of the CKEditor widget.
   */
  public function testCkeditorWidgetIsLinkable() {
    $this->host->body->value = '<drupal-entity data-caption="baz" data-embed-button="test_media_entity_embed" data-entity-embed-display="entity_reference:media_thumbnail" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="' . $this->media->uuid() . '"></drupal-entity>';
    $this->host->save();

    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');

    // Select the CKEditor Widget and click the "link" button.
    $drupal_entity = $this->assertSession()->waitForElementVisible('css', 'drupal-entity');
    $this->assertNotEmpty($drupal_entity);
    $drupal_entity->click();
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');

    // Enter a link in the link dialog and save.
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('https://www.drupal.org');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Save the entity.
    $this->assertSession()->buttonExists('Save')->press();

    // Verify the saved entity when viewed also contains the linked media.
    $this->assertSession()->elementExists('css', 'figure > a[href="https://www.drupal.org"] > div[data-embed-button="test_media_entity_embed"] > img[src*="image-test.png"]');

    // Test that `drupallink` also still works independently: inserting a link
    // is possible.
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->pressEditorButton('drupallink');
    $this->assertSession()->waitForId('drupal-modal');
    $this->assertSession()
      ->waitForElementVisible('css', '#editor-link-dialog-form')
      ->findField('attributes[href]')
      ->setValue('https://wikipedia.org');
    $this->assertSession()->elementExists('css', 'button.form-submit')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->elementExists('css', 'body > a[href="https://wikipedia.org"]');
    $this->assertSession()->elementExists('css', 'body > .cke_widget_drupalentity > drupal-entity > figure > a[href="https://www.drupal.org"]');
  }

  /**
   * Tests that only <drupal-entity> tags are processed.
   *
   * @see \Drupal\Tests\entity_embed\Kernel\EntityEmbedFilterTest::testOnlyDrupalEntityTagProcessed()
   */
  public function testOnlyDrupalEntityTagProcessed() {
    $embed_code = '<drupal-entity data-caption="baz" data-embed-button="test_media_entity_embed" data-entity-embed-display="entity_reference:media_thumbnail" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="' . $this->media->uuid() . '"></drupal-entity>';
    $this->host->body->value = str_replace('drupal-entity', 'p', $embed_code);
    $this->host->save();

    // Assert that `<p data-* …>` is not upcast into a CKEditor Widget.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForElementVisible('css', 'img[src*="example.jpg"]', 1000);
    $this->assertSession()->elementNotExists('css', 'figure');

    $this->host->body->value = $embed_code;
    $this->host->save();

    // Assert that `<drupal-entity data-* …>` is upcast into a CKEditor Widget.
    $this->getSession()->reload();
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForElementVisible('css', 'img[src*="example.jpg"]');
    $this->assertSession()->elementExists('css', 'figure');
  }

  /**
   * The CKEditor Widget must load a preview generated using the default theme.
   */
  public function testPreviewUsesDefaultThemeAndIsClientCacheable() {
    // Make the node edit form use the admin theme, like on most Drupal sites.
    $this->config('node.settings')
      ->set('use_admin_theme', TRUE)
      ->save();
    $this->container->get('router.builder')->rebuild();

    // Allow the test user to view the admin theme.
    $this->adminUser->addRole($this->drupalCreateRole(['view the administration theme']));
    $this->adminUser->save();

    // Configure a different default and admin theme, like on most Drupal sites.
    $this->config('system.theme')
      ->set('default', 'stable9')
      ->set('admin', 'stark')
      ->save();

    // Assert that when looking at an embedded entity in the CKEditor Widget,
    // the preview is generated using the default theme, not the admin theme.
    // @see entity_embed_test_entity_view_alter()
    $this->host->body->value = '<drupal-entity data-caption="baz" data-embed-button="test_media_entity_embed" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings="full" data-entity-type="media" data-entity-uuid="' . $this->media->uuid() . '"></drupal-entity>';
    $this->host->save();
    $this->drupalGet($this->host->toUrl('edit-form'));
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForElementVisible('css', 'img[src*="image-test.png"]');
    $element = $this->assertSession()->elementExists('css', '[data-entity-embed-test-active-theme]');
    $this->assertSame('stable9', $element->getAttribute('data-entity-embed-test-active-theme'));

    // Assert that the first preview request transferred data over the wire.
    // Then toggle source mode on and off. This causes the CKEditor widget to be
    // destroyed and then reconstructed. Assert that during this reconstruction,
    // a second request is sent. This second request should have transferred 0
    // bytes: the browser should have cached the response, thus resulting in a
    // much better user experience.
    $this->assertGreaterThan(0, $this->getLastPreviewRequestTransferSize());
    $this->pressEditorButton('source');
    $this->assertSession()->waitForElement('css', 'textarea.cke_source');
    $this->pressEditorButton('source');
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->waitForElementVisible('css', 'img[src*="image-test.png"]');
    $this->assertSame(0, $this->getLastPreviewRequestTransferSize());
  }

  /**
   * Gets the transfer size of the last preview request.
   *
   * @return int
   *   The transfer size in octets.
   */
  protected function getLastPreviewRequestTransferSize() {
    $this->getSession()->switchToIFrame();
    $javascript = <<<JS
(function(){
  return window.performance
    .getEntries()
    .filter(function (entry) {
      return entry.initiatorType == 'xmlhttprequest' && entry.name.indexOf('/embed/preview/') !== -1;
    })
    .pop()
    .transferSize;
})()
JS;
    return $this->getSession()->evaluateScript($javascript);
  }

  /**
   * Tests even <drupal-entity> elements whose button is not present are upcast.
   *
   * @param string $data_embed_button_attribute
   *   The HTML for a data-embed-button atttribute.
   *
   * @dataProvider providerCkeditorWidgetWorksForAllEmbeds
   */
  public function testCkeditorWidgetWorksForAllEmbeds($data_embed_button_attribute) {
    $this->host->body->value = '<drupal-entity data-caption="baz" ' . $data_embed_button_attribute . ' data-entity-embed-display="entity_reference:media_thumbnail" data-entity-embed-display-settings="{&quot;image_style&quot;:&quot;&quot;,&quot;image_link&quot;:&quot;&quot;}" data-entity-type="media" data-entity-uuid="' . $this->media->uuid() . '"></drupal-entity>';
    $this->host->save();

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('node/' . $this->host->id() . '/edit');
    $this->waitForEditor();
    $this->assignNameToCkeditorIframe();

    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertNotNull($this->assertSession()->waitForElementVisible('css', 'figcaption'));
  }

  /**
   * Data provider for testCkeditorWidgetWorksForAllEmbeds().
   */
  public function providerCkeditorWidgetWorksForAllEmbeds() {
    return [
      'present and active CKEditor button ID' => [
        'data-embed-button="test_media_entity_embed"',
      ],
      'present and inactive CKEditor button ID' => [
        'data-embed-button="user"',
      ],
      'present and nonsensical CKEditor button ID' => [
        'data-embed-button="ceci nest pas une pipe"',
      ],
      'absent' => [
        '',
      ],
    ];
  }

  /**
   * Helper function to submit dialog and focus on ckeditor frame.
   */
  protected function submitDialog() {
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('ckeditor');
  }

  /**
   * Set the text of the editable caption to the given text.
   *
   * @param string $text
   *   The text to set in the caption.
   */
  protected function setCaption($text) {
    $this->getSession()->switchToIFrame();
    $select_and_edit_caption = "var editor = CKEDITOR.instances['edit-body-0-value'];
       var figcaption = editor.widgets.getByElement(editor.editable().findOne('figcaption'));
       figcaption.editables.caption.setData('" . $text . "')";
    $this->getSession()->executeScript($select_and_edit_caption);
  }

  /**
   * Clicks a link in the editor's path links with the given title text.
   *
   * @param string $text
   *   The title attribute of the link to click.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function clickPathLinkByTitleAttribute($text) {
    $this->getSession()->switchToIFrame();
    $selector = '//span[@id="cke_1_path"]//a[@title="' . $text . '"]';
    $this->assertSession()->elementExists('xpath', $selector)->click();
  }

}
