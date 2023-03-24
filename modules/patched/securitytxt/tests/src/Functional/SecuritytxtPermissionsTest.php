<?php

namespace Drupal\Tests\securitytxt\Functional;

/**
 * Permission check.
 *
 * Verify that the securitytxt module permissions grant/deny access to
 * the pages we expect.
 *
 * @group securitytxt
 */
class SecuritytxtPermissionsTest extends SecuritytxtBaseTest {

  /**
   * Tests must specify which theme they are using.
   *
   * @see https://www.drupal.org/node/3083055
   */
  protected $defaultTheme = 'stark';

  /**
   * Test permissions to all Security.txt paths when Security.txt is disabled.
   */
  public function testDisabledAccess() {
    /* Test access for Anonymous role with no permissions. */
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to security.txt.sig page.');

    /* Test access for Authenticated user with no permissions. */
    $this->drupalLogin($this->authenticatedUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to security.txt.sig page.');
    $this->drupalLogout();

    /* Test access for Authenticated user with 'view securitytxt' permissions. */
    $this->drupalLogin($this->viewPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "view securitytxt" to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "view securitytxt" to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(404, 'File Not Found for Authenticated user with "view securitytxt" to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(404, 'File Not Found for Authenticated user with "view securitytxt" to security.txt.sig page.');
    $this->drupalLogout();

    /* Test access for Authenticated user with 'administer securitytxt' permissions. */
    $this->drupalLogin($this->administerPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with "administer securitytxt" to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with "administer securitytxt" to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "administer securitytxt" to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "administer securitytxt" to security.txt.sig page.');
    $this->drupalLogout();

    /* Test access for Authenticated user with 'view securitytxt' & 'administer securitytxt' permissions. */
    $this->drupalLogin($this->viewAndAdministerPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with both securitytxt perms to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with both securitytxt perms to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(404, 'Access denied for Authenticated user with both securitytxt perms to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(404, 'Access denied for Authenticated user with both securitytxt perms to security.txt.sig page.');
    $this->drupalLogout();
  }

  /**
   * Test permissions to all Security.txt paths when Security.txt is enabled.
   */
  public function testEnabledAccess() {
    /* Set a valid configuration. */
    $this->submitValidConfiguration();

    /* Repeat the access permission tests. */
    /* Test access for Anonymous role with no permissions. */
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Anonymous user to security.txt.sig page.');

    /* Test access for Authenticated user with no permissions. */
    $this->drupalLogin($this->authenticatedUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with no permissions to security.txt.sig page.');
    $this->drupalLogout();

    /* Test access for Authenticated user with 'view securitytxt' permissions. */
    $this->drupalLogin($this->viewPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "view securitytxt" to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "view securitytxt" to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(200, 'Accesss granted for Authenticated user with "view securitytxt" to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(200, 'Access granted for Authenticated user with "view securitytxt" to security.txt.sig page.');
    $this->drupalLogout();

    /* Test access for Authenticated user with 'administer securitytxt' permissions. */
    $this->drupalLogin($this->administerPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with "administer securitytxt" to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with "administer securitytxt" to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "administer securitytxt" to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(403, 'Access denied for Authenticated user with "administer securitytxt" to security.txt.sig page.');
    $this->drupalLogout();

    /* Test access for Authenticated user with 'view securitytxt' & 'administer securitytxt' permissions. */
    $this->drupalLogin($this->viewAndAdministerPermissionUser);
    $this->drupalGet('admin/config/system/securitytxt');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with both securitytxt perms to securitytxt configure page.');
    $this->drupalGet('admin/config/system/securitytxt/sign');
    $this->assertSession()->statusCodeEquals(200, 'Access granted to Authenticated user with both securitytxt perms to securitytxt sign page.');
    $this->drupalGet('.well-known/security.txt');
    $this->assertSession()->statusCodeEquals(200, 'Access granted for Authenticated user with both securitytxt perms to security.txt page.');
    $this->drupalGet('.well-known/security.txt.sig');
    $this->assertSession()->statusCodeEquals(200, 'Access granted for Authenticated user with both securitytxt perms to security.txt.sig page.');
    $this->drupalLogout();
  }

}
