<?php

namespace Drupal\govcms_file_test\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * File test form class.
 */
class FileTestSaveUploadFromForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'govcms_file_test_save_upload_from_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['file_test_upload'] = [
      '#type' => 'file',
      '#upload_location' => 'temporary://',
      '#multiple' => TRUE,
      '#title' => $this->t('Upload a file'),
    ];

    // Ajax file upload element.
    $form['file_test_ajax'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Managed <em>@type</em>', ['@type' => 'file & butter']),
      '#upload_location' => 'temporary://',
      '#progress_message' => $this->t('Please wait...'),
      '#multiple' => TRUE,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file = file_save_upload('file_test_upload', [], $form['file_test_upload']['#upload_location'], 0);
    if ($file) {
      $form_state->setValue('file_test_upload', $file);
      \Drupal::messenger()->addStatus(t('File @filepath was uploaded.', ['@filepath' => $file->getFileUri()]));
      \Drupal::messenger()->addStatus(t('File name is @filename.', ['@filename' => $file->getFilename()]));
      \Drupal::messenger()->addStatus(t('File MIME type is @mimetype.', ['@mimetype' => $file->getMimeType()]));
      \Drupal::messenger()->addStatus(t('File uploaded successfully!'));
    }
    elseif ($file === FALSE) {
      \Drupal::messenger()->addError(t('Epic upload FAIL!'));
    }
  }

}
