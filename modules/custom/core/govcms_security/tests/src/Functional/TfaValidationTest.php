<?php

declare(strict_types=1);

namespace Drupal\Tests\govcms_security\Functional;

use Drupal\Tests\tfa\Functional\TfaTestBase;
use Drupal\user\Entity\User;

/**
 * Test GovCMS's desired custom configuration of the TFA module.
 *
 * Extends the TFA module's base test class, as this does the necessary
 * set up to interact with the TFA settings, such as creating encryption
 * profiles and keys.
 *
 * @group govcms_security
 */
class TfaValidationTest extends TfaTestBase {

    /**
     * {@inheritdoc}
     */
    protected static $modules = ['govcms_security'];

    /**
     * {@inheritdoc}
     */
    protected $profile = 'govcms';

    /**
     * A site admin user with permission to configure the TFA settings.
     */
    protected User $adminUser;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void {
        parent::setUp();

        // create and log in as site admin user
        $this->adminUser = $this->drupalCreateUser([
            'admin tfa settings',
        ]);
        $this->drupalLogin($this->adminUser);
    }


    /**
     * Test valid 'Skip Validation' settings
     */
    public function testValidValidationAttempt(): void {
        for ($i = 0; $i <= 10; $i++) {
            $this->drupalGet('/admin/config/people/tfa');
            $edit = ['users_without_tfa[validation_skip]' => $i];

            $this->submitForm($edit, 'Save configuration');

            $this->assertSession()->pageTextContains('The configuration options have been saved.');
            $config = $this->config('tfa.settings');
            $this->assertEquals($i, $config->get('validation_skip'), "Value $i should be saved.");
        }
    }
    /**
     * Test invalid 'Skip Validation' settings
     */
    public function testInvalidSettingValues() {
        $invalid_values = [-1, 11, 20, -10];

        foreach ($invalid_values as $value) {
            $this->drupalGet('/admin/config/people/tfa');
            $edit = [
                'users_without_tfa[validation_skip]' => $value,
            ];

            // Attempt settings form submission.
            $this->submitForm($edit, 'Save configuration');

            // The browser's native HTML5 validation message can't be checked
            // directly, but we can check for the absence of the form
            // submission success message
            $this->assertSession()->pageTextNotContains('The configuration options have been saved.');

            // Check TFA settings have not been updated to the invalid value.
            $config = $this->config('tfa.settings');
            $this->assertNotEquals($value, $config->get('validation_skip'), "Value $value should not be saved.");
    }
  }
}
