<?php

namespace Drupal\Tests\securitytxt\Functional;

/**
 * Form tests.
 *
 * Verify that the securitytxt module forms accept valid parameters
 * and refuse invalid ones.
 *
 * @group securitytxt
 */
class SecuritytxtFormTest extends SecuritytxtBaseTest {

  /**
   * Tests must specify which theme they are using.
   *
   * @see https://www.drupal.org/node/3083055
   */
  protected $defaultTheme = 'stark';

  /**
   * Test valid configuration submission.
   */
  public function testValidConfigurationSubmission() {
    /* Submit a valid configuration. */
    $valid_configuration = $this->submitValidConfiguration();

    /* Log in as an administrative user and check that the form fields are
     * correct. */
    $this->drupalLogin($this->administerPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->checkboxChecked('edit-enabled');
    $this->assertSession()->fieldValueEquals('contact_email', $valid_configuration['contact_email']);
    $this->assertSession()->fieldValueEquals('contact_phone', $valid_configuration['contact_phone']);
    $this->assertSession()->fieldValueEquals('contact_page_url', $valid_configuration['contact_page_url']);
    $this->assertSession()->fieldValueEquals('encryption_key_url', $valid_configuration['encryption_key_url']);
    $this->assertSession()->fieldValueEquals('policy_url', $valid_configuration['policy_url']);
    $this->assertSession()->fieldValueEquals('acknowledgement_url', $valid_configuration['acknowledgement_url']);
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->fieldValueEquals('signature_text', $valid_configuration['signature_text']);
    $this->drupalLogout();

    /* Log in as an Authenticated user with 'view securitytxt' permissions and
     * check that security.txt and security.txt.sig are correct. */
    $this->drupalLogin($this->viewPermissionUser);
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains('Contact: ' . $valid_configuration['contact_email']);
    $this->assertSession()->responseContains('Contact: ' . $valid_configuration['contact_phone']);
    $this->assertSession()->responseContains('Contact: ' . $valid_configuration['contact_page_url']);
    $this->assertSession()->responseContains('Encryption: ' . $valid_configuration['encryption_key_url']);
    $this->assertSession()->responseContains('Policy: ' . $valid_configuration['policy_url']);
    $this->assertSession()->responseContains('Acknowledgement: ' . $valid_configuration['acknowledgement_url']);
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains($valid_configuration['signature_text']);
    $this->drupalLogout();
  }

}
