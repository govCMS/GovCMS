<?php

namespace Drupal\securitytxt\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure the security.txt file.
 */
class SecuritytxtConfigureForm extends ConfigFormBase {

  /**
   * A 'securitytxt.settings' config instance.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * Constructs a SecuritytxtConfigureForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->settings = $config_factory->getEditable('securitytxt.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'securitytxt_configure';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['securitytxt.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable the security.txt file for your site'),
      '#default_value' => $this->settings->get('enabled'),
      '#description' => $this->t('When enabled the security.txt file will be accessible to all users with the "view securitytxt" permission, you will almost certinaly want to give this permission to everyone i.e. authenticated and anonymous users.'),
    ];

    $form['contact'] = [
      '#type' => 'details',
      '#title' => $this->t('Contact'),
      '#open' => TRUE,
      '#description' => $this->t('You must provide at least one means of contact: email, phone or contact page URL.'),
    ];
    $form['contact']['contact_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $this->settings->get('contact_email'),
      '#description' => $this->t('Typically this would be of the form <kbd>security@example.com</kbd>. Leave it blank if you do not want to provide an email address.'),
    ];
    $form['contact']['contact_phone'] = [
      '#type' => 'tel',
      '#title' => $this->t('Phone'),
      '#default_value' => $this->settings->get('contact_phone'),
      '#description' => $this->t('Use full international format e.g. <kbd>+1-201-555-0123</kbd>. Leave it blank if you do not want to provide a phone number.'),
    ];
    $form['contact']['contact_page_url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#default_value' => $this->settings->get('contact_page_url'),
      '#description' => $this->t('The URL of a contact page which should be loaded over HTTPS. Leave it blank if you do not want to provide a contact page.'),
    ];

    $form['encryption'] = [
      '#type' => 'details',
      '#title' => $this->t('Encryption'),
      '#open' => TRUE,
      '#description' => $this->t('Allow people to send you encrypted messages by providing your public key.'),
    ];
    $form['encryption']['encryption_key_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Public key URL'),
      '#default_value' => $this->settings->get('encryption_key_url'),
      '#description' => $this->t('The URL of your public key file, or a page which contains your public key. This URL should use the HTTPS protocol.'),
    ];

    $form['policy'] = [
      '#type' => 'details',
      '#title' => $this->t('Policy'),
      '#open' => TRUE,
      '#description' => $this->t('A security and/or disclosure policy can help security researchers understand  how to work with you when reporting security vulnerabilities.'),
    ];
    $form['policy']['policy_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Security policy URL'),
      '#default_value' => $this->settings->get('policy_url'),
      '#description' => $this->t('The URL of a page which provides details of your security and/or disclosure policy. Leave it blank if you do not have such a page.'),
    ];

    $form['acknowledgement'] = [
      '#type' => 'details',
      '#title' => $this->t('Acknowledgement'),
      '#open' => TRUE,
      '#description' => $this->t('A security acknowldgements page should list the individuals or companies that have disclosed security vulnerabilities and worked with you to fix them.'),
    ];
    $form['acknowledgement']['acknowledgement_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Acknowledgements page URL'),
      '#default_value' => $this->settings->get('acknowledgement_url'),
      '#description' => $this->t('The URL of your security acknowledgements page. Leave it blank if you do not have such a page.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $enabled = $form_state->getValue('enabled');
    $contact_email = $form_state->getValue('contact_email');
    $contact_phone = $form_state->getValue('contact_phone');
    $contact_page_url = $form_state->getValue('contact_page_url');

    /* When enabled, check that at least one contact field is specified. */
    if ($enabled && $contact_email == '' && $contact_phone == '' && $contact_page_url == '') {
      $form_state->setErrorByName('contact', $this->t('You must specify at least one method of contact.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $enabled = $form_state->getValue('enabled');
    $contact_email = $form_state->getValue('contact_email');
    $contact_phone = $form_state->getValue('contact_phone');
    $contact_page_url = $form_state->getValue('contact_page_url');
    $encryption_key_url = $form_state->getValue('encryption_key_url');
    $policy_url = $form_state->getValue('policy_url');
    $acknowledgement_url = $form_state->getValue('acknowledgement_url');

    /* Warn if contact URL is not loaded over HTTPS */
    if ($contact_page_url != '' && substr($contact_page_url, 0, 8) !== 'https://') {
      $this->messenger()->addWarning($this->t('Your contact URL should really be loaded over HTTPS.'));
    }

    /* Warn if encryption URL is not loaded over HTTPS */
    if ($encryption_key_url != '' && substr($encryption_key_url, 0, 8) !== 'https://') {
      $this->messenger()->addWarning($this->t('Your public key URL should really be loaded over HTTPS.'));
    }

    /* Message the user to proceed to the sign page if they have enabled security.txt */
    if ($enabled) {
      $this->messenger()->addStatus($this->t(
        'You should now <a href=":sign">sign your security.txt file</a>.',
        [':sign' => Url::fromRoute('securitytxt.sign')->toString()]
      ));
    }

    /* Save the configuration */
    $this->settings
      ->set('enabled', $enabled)
      ->set('contact_email', $contact_email)
      ->set('contact_phone', $contact_phone)
      ->set('contact_page_url', $contact_page_url)
      ->set('encryption_key_url', $encryption_key_url)
      ->set('policy_url', $policy_url)
      ->set('acknowledgement_url', $acknowledgement_url)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
