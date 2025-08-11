<?php

namespace Drupal\govcms_dlm\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Class GovcmsDlmForm.
 *
 * @package Drupal\govcms_dlm\Form
 */
class GovcmsDlmForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'govcms_dlm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $form['suffix'] = [
      '#type' => 'select',
      '#title' => $this->t('Select DLM to append to all outgoing e-mails'),
      '#default_value' => \Drupal::config('govcms_dlm.settings')->get('suffix'),
      '#options' => [
        '[SEC=UNOFFICIAL]' => '[SEC=UNOFFICIAL]',
        '[SEC=OFFICIAL]' => '[SEC=OFFICIAL]',
        '[SEC=OFFICIAL:Sensitive]' => '[SEC=OFFICIAL:Sensitive]',
      ],
      '#description' => $this->t("Note: Just because you set this doesn't ensure the email is sent securely, you must also ensure your email gateway is configured appropriately."),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('govcms_dlm.settings')
      ->set('suffix', $form_state->getValue('suffix'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['govcms_dlm.settings'];
  }

}
