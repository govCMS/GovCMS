<?php

namespace Drupal\govcms_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;

/**
 * An Entity Browser widget for creating media entities from embed codes.
 *
 * Code from \Drupal\lightning_media\Plugin\EntityBrowser\Widget\EmbedCode
 * in lightning_media 8.x-2.3 submodule.
 *
 * @EntityBrowserWidget(
 *   id = "embed_code",
 *   label = @Translation("Embed Code"),
 *   description = @Translation("Allows creation of media entities from embed codes."),
 * )
 */
class EmbedCode extends EntityFormProxy {

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    $form['input'] = [
      '#type' => 'textarea',
      '#placeholder' => $this->t('Enter a URL...'),
      '#attributes' => [
        'class' => ['keyup-change'],
      ],
      '#ajax' => [
        'event' => 'change',
        'wrapper' => 'entity',
        'method' => 'html',
        'callback' => [static::class, 'ajax'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $value = trim($this->getInputValue($form_state));

    if ($value) {
      parent::validate($form, $form_state);
    }
    else {
      $form_state->setError($form['widget'], $this->t('You must enter a URL or embed code.'));
    }
  }

}
