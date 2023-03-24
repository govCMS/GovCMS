<?php

namespace Drupal\securitytxt\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Upload the security.txt.sig file.
 */
class SecuritytxtSignForm extends ConfigFormBase {

  /**
   * A 'securitytxt.settings' config instance.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $settings;

  /**
   * Constructs a SecuritytxtSignForm object.
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
    return 'securitytxt_sign';
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
    $enabled = $this->settings->get('enabled');

    if (!$enabled) {
      $form['instructions'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t('You must <a href=":configure">configure and enable</a> your security.txt file before you can sign it.', [':configure' => Url::fromRoute('securitytxt.configure')->toString()]) . '</p>',
      ];

      return $form;
    }

    $form['instructions'] = [
      '#type' => 'markup',
      '#markup' => '<ol>' . '<li>' . $this->t('<a href=":download" download="security.txt">Download</a> your security.txt file.', [':download' => Url::fromRoute('securitytxt.securitytxt_file')->toString()]) . '</li>' .
      '<li><p>Sign your security.txt file with the encryption key you specified in your security.txt file. This can be done with the following GPG command:</p><p><kbd>gpg -u KEYID --output security.txt.sig  --armor --detach-sig security.txt</kbd></p></li>' .
      '<li>Paste the contents of the <kbd>security.txt.sig</kbd> file into the text box below.</li>' .
      '</ol>',
    ];
    $form['signature_text'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Signature'),
      '#default_value' => $this->settings->get('signature_text'),
      '#rows' => 20,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->settings->set('signature_text', $form_state->getValue('signature_text'))->save();

    parent::submitForm($form, $form_state);
  }

}
