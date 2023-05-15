<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Base class for all entity_embed tests.
 */
abstract class EntityEmbedTestBase extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_embed',
    'entity_embed_test',
    'node',
    'ckeditor',
  ];

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

  /**
   * Clicks a CKEditor button.
   *
   * @param string $name
   *   The name of the button, such as drupalink, source, etc.
   */
  protected function pressEditorButton($name) {
    $this->getSession()->switchToIFrame();
    $this->assertSession()
      ->waitForElementVisible('css', 'a.cke_button__' . $name)
      ->click();
  }

  /**
   * Waits for CKEditor to initialize.
   *
   * @param string $instance_id
   *   The CKEditor instance ID.
   * @param int $timeout
   *   (optional) Timeout in milliseconds, defaults to 10000.
   */
  protected function waitForEditor($instance_id = 'edit-body-0-value', $timeout = 10000) {
    $condition = <<<JS
      (function() {
        return (
          typeof CKEDITOR !== 'undefined'
          && typeof CKEDITOR.instances["$instance_id"] !== 'undefined'
          && CKEDITOR.instances["$instance_id"].instanceReady
        );
      }());
JS;

    $this->getSession()->wait($timeout, $condition);
  }

  /**
   * Helper function to reopen EntityEmbedDialog for first embed.
   */
  protected function reopenDialog() {
    $this->getSession()->switchToIFrame();
    $select_and_edit_embed = <<<JS
var editor = CKEDITOR.instances['edit-body-0-value'];
var entityEmbed = editor.widgets.getByElement(editor.editable().findOne('div'));
entityEmbed.focus();
editor.execCommand('editdrupalentity');
JS;
    $this->getSession()->executeScript($select_and_edit_embed);
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->waitForElementVisible('css', 'form.entity-embed-dialog-step--embed');
  }

  /**
   * Show visually hidden fields.
   */
  protected function showHiddenFields() {
    $script = <<<JS
      var hidden_fields = document.querySelectorAll(".visually-hidden");

      [].forEach.call(hidden_fields, function(el) {
        el.classList.remove("visually-hidden");
      });
JS;

    $this->getSession()->executeScript($script);
  }

}
