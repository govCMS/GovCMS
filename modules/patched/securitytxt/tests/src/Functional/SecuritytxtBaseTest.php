<?php

namespace Drupal\Tests\securitytxt\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Defines a base class for testing the Security.txt module.
 *
 * @group securitytxt
 */
abstract class SecuritytxtBaseTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   *  Modules which should be enabled by default.
   */
  protected static $modules = ['securitytxt'];

  /**
   * User with no permissions.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $authenticatedUser;

  /**
   * User with the 'view securitytxt' permission.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $viewPermissionUser;

  /**
   * User with the 'administer securitytxt' permission.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $administerPermissionUser;

  /**
   * User with the 'view securitytxt' and 'administer securitytxt' permissions.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $viewAndAdministerPermissionUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->authenticatedUser = $this->drupalCreateUser([]);
    $this->viewPermissionUser = $this->drupalCreateUser(['view securitytxt']);
    $this->administerPermissionUser = $this->drupalCreateUser(['administer securitytxt']);
    $this->viewAndAdministerPermissionUser = $this->drupalCreateUser(['view securitytxt', 'administer securitytxt']);
  }

  /**
   * Get a valid configuration array.
   *
   * @return array
   *   An array of valid configuration values.
   */
  protected function getValidConfiguration() {
    $valid_configuration = [];
    $valid_configuration['enabled'] = TRUE;
    $valid_configuration['contact_email'] = $this->randomMachineName(16) . '@example.com';
    $valid_configuration['contact_phone'] = '+44-7700-900' . rand(100, 999);
    $valid_configuration['contact_page_url'] = 'https://example.com/contact/' . $this->randomMachineName(16);
    $valid_configuration['encryption_key_url'] = 'https://example.com/key/' . $this->randomMachineName(16);
    $valid_configuration['policy_url'] = 'https://example.com/policy/' . $this->randomMachineName(16);
    $valid_configuration['acknowledgement_url'] = 'https://example.com/acknowledgement/' . $this->randomMachineName(16);
    $valid_configuration['signature_text'] = $this->randomMachineName(512);

    return $valid_configuration;
  }

  /**
   * Submit the 'Configure' form.
   *
   * @param array $edit
   *   An associated array suitable for the drupalPostForm() method. It should
   *   have the following keys defined: enabled, contact_email, contact_phone,
   *   contact_page_url, encryption_key_url, policy_url, acknowledgement_url.
   */
  protected function submitConfigureForm(array $edit) {
    $path = 'admin/config/system/securitytxt';
    $submit = 'Save configuration';
    $options = [];
    $this->drupalGet($path, $options);
    $this->submitForm($edit, $submit);
  }

  /**
   * Submit the 'Sign' form.
   *
   * @param array $edit
   *   An associated array suitable for the drupalPostForm() method. It should
   *   have the following key defined: security_text.
   */
  protected function submitSignForm(array $edit) {
    $path = 'admin/config/system/securitytxt/sign';
    $submit = 'Save configuration';
    $options = [];
    $this->drupalGet($path, $options);
    $this->submitForm($edit, $submit);
  }

  /**
   * Submit a valid configuration to both the 'Configure' and 'Sign' forms.
   *
   * @return array
   *   An array of valid configuration values.
   */
  protected function submitValidConfiguration() {
    $this->drupalLogin($this->administerPermissionUser);
    $this->assertSession()->statusCodeEquals(200);

    $valid_configuration = $this->getValidConfiguration();
    $configure_edit = $valid_configuration;
    unset($configure_edit['signature_text']);
    $this->submitConfigureForm($configure_edit);

    $sign_edit['signature_text'] = $valid_configuration['signature_text'];
    $this->submitSignForm($sign_edit);

    $this->drupalLogout();
    $this->assertSession()->statusCodeEquals(200);

    return $valid_configuration;
  }

}
