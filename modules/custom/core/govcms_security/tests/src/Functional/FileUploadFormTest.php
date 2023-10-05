<?php

namespace Drupal\Tests\govcms_security\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\govcms_security\GovcmsFileConstraintInterface;

/**
 * Tests the file uploaded by a Drupal form.
 *
 * @group file
 */
class FileUploadFormTest extends BrowserTestBase {

  use GovcmsTestFileCreationTrait {
    generateFile as govcmsGenerateTestFile;
    getTestFiles as govcmsGetTestFiles;
  }

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['file', 'govcms_file_test', 'govcms_security'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $account = $this->drupalCreateUser([]);
    $this->drupalLogin($account);

    $this->testFiles = [];
    foreach (GovcmsFileConstraintInterface::BLOCKED_EXTENSIONS as $index => $extension) {
      $this->testFiles[] = $this->govcmsGenerateTestFile($index . '.' . $extension, $this->randomString());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    // Delete all test files.
    foreach ($this->testFiles as $file) {
      unlink($file);
    }

    parent::tearDown();
  }

  /**
   * Tests the file_save_upload() function.
   */
  public function testFileUploadByForm() {

    // Make sure all test files exist.
    foreach ($this->testFiles as $file) {
      $this->assertFileExists($file, "$file checked.");
    }

    // Test single file uploading for all blocked file extensions.
    foreach ($this->testFiles as $file) {
      $edit = [
        'files[file_test_upload][]' => $file,
      ];
      // Go to the file upload test form provided by govcms_file_test module.
      $this->drupalGet('govcms-file-test/save_upload_from_form_test');
      $this->submitForm($edit, 'Submit');
      $this->assertSession()->statusCodeEquals(403);
      $this->assertFileDoesNotExist('temporary://' . basename($file));
    }

    // Test multiple file uploading for all blocked file extensions.
    $multiple_files = [];
    foreach ($this->testFiles as $file) {
      $multiple_files['files']['file_test_upload'][] = $file;
    }
    $this->drupalGet('govcms-file-test/save_upload_from_form_test');
    // Multiple file uploading can't use submitForm().
    $client = $this->getSession()->getDriver()->getClient();
    $submit_xpath = $this->assertSession()->buttonExists('Submit')->getXpath();
    $form = $client->getCrawler()->filterXPath($submit_xpath)->form();
    $edit = [];
    $edit += $form->getPhpValues();
    // Submit the form.
    $client->request($form->getMethod(), $form->getUri(), $edit, $multiple_files);
    $this->assertSession()->statusCodeEquals(403);
    // Check the temporary folder to make sure no blocked file was uploaded.
    foreach ($this->testFiles as $file) {
      $this->assertFileDoesNotExist('temporary://' . basename($file));
    }

    // Test Ajax file uploading via a form.
    $this->drupalGet('govcms-file-test/save_upload_from_form_test');
    $edit = [];
    // Files with blocked extensions.
    foreach ($this->testFiles as $i => $file) {
      $edit["files[file_test_ajax][$i]"] = $file;
    }
    // Submit the form.
    $client->request($form->getMethod(), $form->getUri(), $form->getPhpValues(), $edit);
    $this->assertSession()->statusCodeEquals(403);
    // Check the temporary folder to make sure no blocked file was uploaded.
    foreach ($this->testFiles as $file) {
      $this->assertFileDoesNotExist('temporary://' . basename($file));
    }

    // Test an image file to make sure non-blocked file still can be uploaded.
    $image_file = $this->govcmsGenerateTestFile('image_1.png', $this->randomString());
    $edit = [
      'files[file_test_upload][]' => $image_file,
    ];
    // Go to the file upload test form provided by govcms_file_test module.
    $this->drupalGet('govcms-file-test/save_upload_from_form_test');
    $this->submitForm($edit, 'Submit');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('File uploaded successfully!');
    $this->assertFileExists('temporary://' . basename($image_file));
    // Delete the test image file.
    unlink($image_file);
  }

}
