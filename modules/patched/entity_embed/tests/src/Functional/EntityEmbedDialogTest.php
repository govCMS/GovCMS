<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\editor\Entity\Editor;

/**
 * Tests the entity_embed dialog controller and route.
 *
 * @group entity_embed
 */
class EntityEmbedDialogTest extends EntityEmbedTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['image'];

  /**
   * Tests the entity embed dialog.
   */
  public function testEntityEmbedDialog() {
    // Ensure that the route is not accessible without specifying all the
    // parameters.
    $this->drupalGet('/entity-embed/dialog');
    // Verify embed dialog is not accessible without specifying filter format
    // and embed button.
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/entity-embed/dialog/custom_format');
    // Verify embed dialog is not accessible without specifying embed button.
    $this->assertSession()->statusCodeEquals(404);

    // Ensure that the route is not accessible with an invalid embed button.
    $this->drupalGet('/entity-embed/dialog/custom_format/invalid_button');
    // Verify embed dialog is not accessible without specifying filter format
    // and embed button.
    $this->assertSession()->statusCodeEquals(404);

    // Ensure that the route is not accessible with text format without the
    // button configured.
    $this->drupalGet('/entity-embed/dialog/plain_text/node');
    // Verify embed dialog is not accessible with a filter that does not have
    // an editor configuration.
    $this->assertSession()->statusCodeEquals(404);

    // Add an empty configuration for the plain_text editor configuration.
    $editor = Editor::create([
      'format' => 'plain_text',
      'editor' => 'ckeditor',
    ]);
    $editor->save();
    $this->drupalGet('/entity-embed/dialog/plain_text/node');
    // Verify embed dialog is not accessible with a filter that does not have
    // the embed button assigned to it.
    $this->assertSession()->statusCodeEquals(403);

    // Ensure that the route is accessible with a valid embed button.
    // 'Node' embed button is provided by default by the module and hence the
    // request must be successful.
    $this->drupalGet('/entity-embed/dialog/custom_format/node');
    // Verify embed dialog is accessible with correct filter format
    // and embed button.
    $this->assertSession()->statusCodeEquals(200);

    // Ensure form structure of the 'select' step and submit form.
    $this->assertSession()->fieldExists('entity_id');
  }

  /**
   * Tests the entity embed button markup.
   */
  public function testEntityEmbedButtonMarkup() {
    // Ensure that the route is not accessible with text format without the
    // button configured.
    $this->drupalGet('/entity-embed/dialog/plain_text/node');
    // Verify embed dialog is not accessible with a filter that does not have
    // an editor configuration.
    $this->assertSession()->statusCodeEquals(404);

    // Add an empty configuration for the plain_text editor configuration.
    $editor = Editor::create([
      'format' => 'plain_text',
      'editor' => 'ckeditor',
    ]);
    $editor->save();
    $this->drupalGet('/entity-embed/dialog/plain_text/node');
    // Verify embed dialog is not accessible with a filter that does not have
    // the embed button assigned to it.
    $this->assertSession()->statusCodeEquals(403);

    // Ensure that the route is accessible with a valid embed button.
    // 'Node' embed button is provided by default by the module and hence the
    // request must be successful.
    $this->drupalGet('/entity-embed/dialog/custom_format/node');
    // Verify embed dialog is accessible with correct filter format
    // and embed button.
    $this->assertSession()->statusCodeEquals(200);

    // Ensure form structure of the 'select' step and submit form.
    $this->assertSession()->fieldExists('entity_id');

    // Check that 'Next' is a primary button.
    $this->assertSession()->elementExists('xpath', '//input[contains(@class, "button--primary")]');
  }

  /**
   * Tests entity embed functionality.
   */
  public function testEntityEmbedFunctionality() {
    $edit = [
      'entity_id' => $this->node->getTitle() . ' (' . $this->node->id() . ')',
    ];
    $this->drupalGet('/entity-embed/dialog/custom_format/node');
    $this->submitForm($edit, 'Next');
    // Tests that the embed dialog doesn't trow a fatal in
    // ImageFieldFormatter::isValidImage()
    $this->assertSession()->statusCodeEquals(200);
  }

}
