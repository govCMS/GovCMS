<?php

namespace Drupal\Tests\govcms_security\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the create user administration page.
 *
 * @group govcms_security
 */
class UserCreateTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['govcms_security'];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'govcms';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Set up the test here.
  }

  /**
   * Tests user creation and display from the administration interface.
   */
  public function testUserAdd() {
    $user = $this->drupalCreateUser([
      'administer users',
    ]);
    $this->drupalLogin($user);

    // Test user creation page for valid password length.
    $name = $this->randomMachineName();
    $edit = [
      'name' => $name,
      'mail' => $this->randomMachineName() . '@example.com',
      'pass[pass1]' => $pass = $this->randomString(13),
      'pass[pass2]' => $pass,
      'notify' => FALSE,
    ];

    $this->drupalGet('admin/people/create');
    $this->submitForm($edit, 'Create new account');
    $this->assertSession()->pageTextContains('The password does not satisfy the password policies.');
    $this->assertSession()->pageTextContains('Password length must be at least 14 characters.');
  }

}
