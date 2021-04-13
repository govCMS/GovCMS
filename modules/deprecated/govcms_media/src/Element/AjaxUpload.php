<?php

namespace Drupal\govcms_media\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;

/**
 * An interactive, AJAX-ey file upload form element.
 *
 * Code from \Drupal\lightning_media\Element\AjaxUpload
 * in lightning_media 8.x-2.3 submodule.
 *
 * @FormElement("ajax_upload")
 */
class AjaxUpload extends InteractiveUpload {

  /**
   * {@inheritdoc}
   */
  public static function process(array $element, FormStateInterface $form_state) {
    $element = parent::process($element, $form_state);

    // Generate a CSS ID for the wrapping DIV.
    $wrapper_id = implode('-', $element['#parents']);
    $wrapper_id = Html::cleanCssIdentifier($wrapper_id);

    // The element being processed is just a wrapper, and does not accept input
    // or support AJAX directly. Still, store the wrapping DIV ID in a spot
    // where other elements can access it if they need to refer to it.
    $element['#ajax']['wrapper'] = $wrapper_id;

    // Bring in the File module's slick auto-uploading stuff.
    $element['#attached']['library'][] = 'file/drupal.file';

    // The js-form-managed-file class is needed for the File module's
    // auto-upload JavaScript to target this element.
    $element['#prefix'] = '<div class="js-form-managed-file" id="' . $wrapper_id . '">';
    $element['#suffix'] = '</div>';

    // Hide the upload button. It will be triggered by the auto-upload JS.
    $element['upload']['#attributes']['class'][] = 'js-hide';

    // As far as AJAX is concerned, the Upload and Remove buttons do the same
    // thing (return their parent element). The differences lie in their
    // respective submit functions.
    $element['upload']['#ajax'] = $element['remove']['#ajax'] = [
      'callback' => [static::class, 'el'],
      'wrapper' => $wrapper_id,
    ];

    return $element;
  }

}
