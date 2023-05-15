<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\Component\Utility\Html;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Test integration with content_translation.
 *
 * @group entity_embed
 */
class ContentTranslationTest extends EntityEmbedTestBase {

  /**
   * The 'translator' user to use during testing.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $translator;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['entity_embed_translation_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->translator = $this->drupalCreateUser([
      'use text format full_html',
      'administer nodes',
      'edit any article content',
      'translate any entity',
    ]);

    $this->config('field.storage.node.body')
      ->set('translatable', TRUE)
      ->save();
  }

  /**
   * Return autocomplete suggestions from the entity_id field.
   *
   * @param string $search_string
   *   The search string.
   *
   * @return string
   *   The text of the autocomplete suggestions.
   */
  protected function getAutocompleteSuggestions($search_string) {
    $page = $this->getSession()->getPage();
    $autocomplete_field = $field = $page->findField('entity_id');
    $this->assertNotEmpty($autocomplete_field);
    $autocomplete_field->setValue($search_string);
    $this->getSession()->getDriver()->keyDown($autocomplete_field->getXpath(), ' ');
    $this->assertSession()->waitOnAutocomplete();
    $suggestions = $this->assertSession()
      ->waitForElementVisible('css', '.ui-autocomplete');
    $this->assertNotEmpty($suggestions);
    return $suggestions->getText();
  }

  /**
   * Tests the host entity's langcode is available in EntityEmbedDialog.
   */
  public function testHostEntityLangcode() {
    $node = $this->createNode([
      'type' => 'article',
      'title' => 'Clark Kent',
    ]);
    $node_fr = $node->addTranslation('fr');
    $node_fr->title = 'Superhomme';
    $node_fr->save();

    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://Smeagol.jpg');
    /** @var \Drupal\file\FileInterface $file */
    $file = File::create([
      'uri' => 'public://Smeagol.jpg',
      'uid' => $this->translator->id(),
    ]);
    $file->save();

    $media = Media::create([
      'bundle' => 'image',
      'name' => 'Smeagol likes cheese',
      'field_media_image' => [
        [
          'target_id' => $file->id(),
          'alt' => 'Smeagol likes cheese alt',
          'title' => 'Smeagol likes cheese title',
        ],
      ],
    ]);
    $media->save();

    $media_fr = $media->addTranslation('fr');
    $media_fr->name = "Gollum n'aime que la bague";
    $media_fr->field_media_image->setValue([
      [
        'target_id' => $file->id(),
        'alt' => "Gollum n'aime que la bague alt",
        'title' => "Gollum n'aime que la bague title",
      ],
    ]);
    $media_fr->save();

    $host = $this->createNode([
      'type' => 'article',
      'title' => 'host',
      'body' => [
        'value' => '',
        'format' => 'full_html',
      ],
    ]);
    $host_fr = $host->addTranslation('fr');
    $host_fr->title = 'host';
    $host_fr->body->value = '';
    $host_fr->body->format = 'full_html';
    $host_fr->body->lang = 'fr';
    $host_fr->save();

    // Test the default language, as a baseline for comparison.
    $this->drupalLogin($this->translator);
    $this->drupalGet('node/' . $host->id() . '/edit');
    $this->waitForEditor();
    $this->pressEditorButton('test_node');
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#entity-embed-dialog-form'));

    // Assert autocomplete suggestions are in host entity language (en).
    $suggestions = $this->getAutocompleteSuggestions('clar');
    $this->assertStringContainsString('Clark Kent', $suggestions);

    // Assert autocomplete does not show suggestions for translations not
    // matching the host entity language.
    $suggestions = $this->getAutocompleteSuggestions('super');
    $this->assertStringNotContainsString('Superhomme', $suggestions);

    // Select the suggestion matching the host entity language, and proceed to
    // the review step.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Clark Kent (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert that the review step displays the selected entity with the label
    // in the host language.
    $text = $form->getText();
    $this->assertStringContainsString('Clark Kent', $text);
    $this->assertStringNotContainsString('Superhomme', $text);

    // Repeat the same test pattern, but now for a Media entity instead of Node.
    $this->getSession()->reload();
    $this->assertSession()
      ->waitForElementVisible('css', 'a.cke_button__test_media_entity_embed')
      ->click();
    $this->assertSession()->waitForId('drupal-modal');

    // Assert autocomplete suggestions are in host entity language (en).
    $suggestions = $this->getAutocompleteSuggestions('Smeagol likes cheese');
    $this->assertStringContainsString('Smeagol likes cheese', $suggestions);

    // Assert autocomplete does not show suggestions for translations not
    // matching the host entity language.
    $suggestions = $this->getAutocompleteSuggestions("Gollum n'aime que la bague");
    $this->assertStringNotContainsString("Gollum n'aime que la bague", $suggestions);

    // Select the suggestion matching the host entity language, and proceed to
    // the review step.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Smeagol likes cheese (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert that the review step displays the selected entity with the label
    // in the host language.
    $text = $form->getText();
    $this->assertStringContainsString('Smeagol likes cheese', $text);
    $this->assertStringNotContainsString("Gollum n'aime que la bague", $text);

    // Get translation of host entity.
    $this->drupalGet('/fr/node/' . $host->id() . '/edit');
    $this->waitForEditor();
    $this->pressEditorButton('test_node');
    $this->assertNotEmpty($this->assertSession()->waitForElementVisible('css', '#entity-embed-dialog-form'));

    // Assert autocomplete suggestions are in host entity language (fr).
    $suggestions = $this->getAutocompleteSuggestions('super');
    $this->assertStringContainsString('Superhomme', $suggestions);

    // Assert autocomplete does not show suggestions for translations not
    // matching the host entity language.
    $suggestions = $this->getAutocompleteSuggestions('clark');
    $this->assertStringNotContainsString('Clark Kent', $suggestions);

    // Select the suggestion matching the host entity language, and proceed to
    // the review step.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue('Superhomme (1)');
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert the translated label appears, not the original.
    $text = $form->getText();
    $this->assertStringContainsString('Superhomme', $text);
    $this->assertStringNotContainsString('Clark Kent', $text);

    // Choose to display as label without link.
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('entity_reference:entity_reference_label');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()
      ->fieldExists('attributes[data-entity-embed-display-settings][link]')
      ->uncheck();
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Verify that the embedded entity preview in CKEditor displays the label in
    // the correct language (fr).
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $this->assertSession()->pageTextContains('Superhomme');

    // Repeat the same test pattern, but now for a Media entity instead of Node.
    $this->getSession()->reload();
    $this->assertSession()
      ->waitForElementVisible('css', 'a.cke_button__test_media_entity_embed')
      ->click();
    $this->assertSession()->waitForId('drupal-modal');

    // Assert autocomplete suggestions are in host entity language (fr).
    $suggestions = $this->getAutocompleteSuggestions("Gollum n'aime que la bague");
    $this->assertStringContainsString("Gollum n'aime que la bague", $suggestions);

    // Assert autocomplete does not show suggestions for translations not
    // matching the host entity language.
    $suggestions = $this->getAutocompleteSuggestions('Smeagol likes cheese');
    $this->assertStringNotContainsString('Smeagol likes cheese', $suggestions);

    // Select the suggestion matching the host entity language, and proceed to
    // the review step.
    $this->assertSession()
      ->fieldExists('entity_id')
      ->setValue("Gollum n'aime que la bague (1)");
    $this->assertSession()->elementExists('css', 'button.js-button-next')->click();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');

    // Assert the translated label appears, not the original.
    $text = $form->getText();
    $this->assertStringContainsString("Gollum n'aime que la bague", $text);
    $this->assertStringNotContainsString('Smeagol likes cheese', $text);

    // Choose to display as thumbnail with 'medium' image style.
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display]')
      ->setValue('entity_reference:media_thumbnail');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()
      ->selectExists('attributes[data-entity-embed-display-settings][image_style]')
      ->setValue('medium');
    $this->assertSession()->elementExists('css', 'button.button--primary')->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Verify that the embedded entity preview in CKEditor displays the image
    // with an `alt` attribute in the correct language (fr).
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');
    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertStringContainsString('Smeagol.jpg', $img->getAttribute('src'));
    $this->assertEquals("Gollum n'aime que la bague alt", $img->getAttribute('alt'));

    // Save the host entity, verify that it also shows up the same way on the
    // front end, so again with an `alt` attribute in the correct language (fr).
    // This tests the filter plugin's integration.
    $this->getSession()->switchToIFrame();
    $this->assertSession()->buttonExists('Save')->press();
    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertStringContainsString('Smeagol.jpg', $img->getAttribute('src'));
    $this->assertEquals("Gollum n'aime que la bague alt", $img->getAttribute('alt'));

    // Verify that editing the host entity and then triggering the Entity Embed
    // Dialog for the embedded entity again shows the embedded entity in the
    // same language (fr).
    $this->drupalGet('/fr/node/' . $host->id() . '/edit');
    $this->waitForEditor();
    $select_and_edit_embed = "var editor = CKEDITOR.instances['edit-body-0-value'];
      var entityEmbed = editor.widgets.getByElement(editor.editable().findOne('div'));
      entityEmbed.focus();
      editor.execCommand('editdrupalentity');";
    $this->getSession()->executeScript($select_and_edit_embed);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $form = $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');
    $text = $form->getText();
    $this->assertStringContainsString("Gollum n'aime que la bague", $text);
    $this->assertStringNotContainsString('Smeagol likes cheese', $text);

    // Close the Entity Embed Dialog, and enter CKEditor's "source" mode.
    $this->assertSession()->elementExists('css', '.ui-dialog-titlebar-close')->press();
    $this->assertSession()
      ->waitForElementVisible('css', 'a.cke_button__source')
      ->click();

    // Manually override the langcode to set it back to 'en', so that that the
    // embed shows the original language, even though this node is translated.
    $source = $this->assertSession()
      ->waitForElementVisible('xpath', "//textarea[contains(@class, 'cke_source')]");
    $value = $source->getValue();
    $dom = Html::load($value);
    $xpath = new \DOMXPath($dom);
    $drupal_entity = $xpath->query('//drupal-entity')[0];
    $drupal_entity->setAttribute("data-langcode", "en");
    $source->setValue(Html::serialize($dom));

    // Exit "source" mode.
    $this->pressEditorButton('source');
    $this->assignNameToCkeditorIframe();
    $this->getSession()->switchToIFrame('ckeditor');

    // Assert that the image appears with correct alt text (en).
    $img = $this->assertSession()->waitForElementVisible('css', 'img');
    $this->assertStringContainsString('Smeagol.jpg', $img->getAttribute('src'));
    $this->assertEquals("Smeagol likes cheese alt", $img->getAttribute('alt'));

    // Save the host entity, verify that it also shows up the same way on the
    // front end, so again with an `alt` attribute in the correct language (en).
    // This tests the filter plugin's integration.
    $this->getSession()->switchToIFrame();
    $this->assertSession()->buttonExists('Save')->press();

    // Assert that the image appears with correct alt text.
    $img = $this->assertSession()->elementExists('css', 'img');
    $this->assertStringContainsString('Smeagol.jpg', $img->getAttribute('src'));
    $this->assertEquals("Smeagol likes cheese alt", $img->getAttribute('alt'));
  }

}
