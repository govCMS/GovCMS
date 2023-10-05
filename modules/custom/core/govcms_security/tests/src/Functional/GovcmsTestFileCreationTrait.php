<?php

namespace Drupal\Tests\govcms_security\Functional;

/**
 * Provides methods to create test files from given values.
 *
 * This trait is meant to be used only by test classes.
 */
trait GovcmsTestFileCreationTrait {

  /**
   * Absolute path of the test file folder.
   *
   * @var string
   */
  protected $testFileFolder = DRUPAL_ROOT . '/sites/simpletest/test_files';

  /**
   * Test files.
   *
   * @var array
   */
  protected $testFiles;

  /**
   * Generates a test file.
   *
   * @param string $filename
   *   The name of the file, including the path.
   *   directory.
   * @param string $content
   *   The content of the file.
   * @param bool $encode
   *   If true, encodes file content with MIME base64.
   *
   * @return string
   *   The name of the file, including the path.
   */
  protected function generateFile($filename, $content, $encode = TRUE) {
    if ($encode) {
      $content = chunk_split(base64_encode($content));
    }

    // Make sure the folder exist.
    if (!is_dir($this->testFileFolder)) {
      mkdir($this->testFileFolder, 0777, TRUE);
    }
    // Include the path into the file name.
    $filename = $this->testFileFolder . '/' . $filename;
    // Create the test file folder.
    file_put_contents($filename, $content);
    return $filename;
  }

  /**
   * Gets a list of files that can be used in tests.
   *
   * @param string $extension
   *   The file extension needed from the test file folder.
   *
   * @return object[]
   *   List of file object in the test file folder. Each file is an
   *   object with 'uri', 'filename', and 'name' properties.
   */
  protected function getTestFiles($extension) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $files = $file_system->scanDirectory($this->testFileFolder, '/' . $extension . '\-.*/');

    return $files;
  }

}
