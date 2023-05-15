<?php

namespace Drupal\Tests\entity_embed\FunctionalJavascript;

/**
 * Extends \Drupal\FunctionalJavascriptTests\SortableTestTrait.
 *
 * This trait is a bridge to allow dragging to work on all versions of Drupal.
 * Drupal 8.8 and higher use Sortable (i.e., the HTML5 drag and drop API), and
 * are therefore incompatible with Chromedriver, at least for now.
 *
 * @see https://www.drupal.org/project/entity_embed/issues/3108151
 *
 * @internal
 *   This trait is completely and totally internal and not meant to be used in
 *   ANY way by code that is not part of the Entity Embed module. It may be
 *   changed in any number of ways, or even deleted outright, at any time
 *   without warning. External code should not rely on or use this trait at all.
 *   If you need its functionality, copy it wholesale into your own code base.
 *
 * @todo Remove this trait entirey when Drupal 8.8 is the minimum version of
 *   core supported by Entity Embed.
 */
trait SortableTestTrait {

  /**
   * {@inheritdoc}
   */
  protected function sortableUpdate($item, $from, $to = NULL) {
    $script = <<<JS
(function () {
  // Set backbone model after a DOM change.
  Drupal.ckeditor.models.Model.set('isDirty', true);
})()

JS;

    $options = [
      'script' => $script,
      'args'   => [],
    ];

    $this->getSession()->getDriver()->getWebDriverSession()->execute($options);
  }

  /**
   * Simulates a drag on an element from one container to another.
   *
   * @param string $item
   *   The HTML selector for the element to be moved.
   * @param string $from
   *   The HTML selector for the previous container element.
   * @param null|string $to
   *   The HTML selector for the target container.
   */
  protected function sortableTo($item, $from, $to) {
    // Versions of Drupal older than 8.8 allow normal Selenium-style dragging
    // and dropping.
    if (version_compare(\Drupal::VERSION, '8.8.0', '<')) {
      $this->doLegacyDrag($item, $to);
      return;
    }

    $item = addslashes($item);
    $from = addslashes($from);
    $to   = addslashes($to);

    $script = <<<JS
(function (src, to) {
  var sourceElement = document.querySelector(src);
  var toElement = document.querySelector(to);

  toElement.insertBefore(sourceElement, toElement.firstChild);
})('{$item}', '{$to}')

JS;

    $options = [
      'script' => $script,
      'args'   => [],
    ];

    $this->getSession()->getDriver()->getWebDriverSession()->execute($options);
    $this->sortableUpdate($item, $from, $to);
  }

  /**
   * Simulates a drag moving an element after its sibling in the same container.
   *
   * @param string $item
   *   The HTML selector for the element to be moved.
   * @param string $target
   *   The HTML selector for the sibling element.
   * @param string $from
   *   The HTML selector for the element container.
   */
  protected function sortableAfter($item, $target, $from) {
    $item   = addslashes($item);
    $target = addslashes($target);
    $from   = addslashes($from);

    $script = <<<JS
(function (src, to) {
  var sourceElement = document.querySelector(src);
  var toElement = document.querySelector(to);

  toElement.insertAdjacentElement('afterend', sourceElement);
})('{$item}', '{$target}')

JS;

    $options = [
      'script' => $script,
      'args'   => [],
    ];

    $this->getSession()->getDriver()->getWebDriverSession()->execute($options);
    $this->sortableUpdate($item, $from);
  }

  protected function doLegacyDrag($item, $target) {
    $assert_session = $this->assertSession();
    $target = $assert_session->elementExists('css', $target);
    $assert_session->elementExists('css', $item)->dragTo($target);
  }

}
