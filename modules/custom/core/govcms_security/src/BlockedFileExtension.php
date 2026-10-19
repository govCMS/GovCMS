<?php

namespace Drupal\govcms_security;

/**
 * Checks file names against the blocked file extensions.
 */
final class BlockedFileExtension {

  /**
   * Gets the extension a file name will end up with once it is stored.
   *
   * The name is normalised in line with core's file name sanitisation before
   * the extension is read.
   *
   * @param string $name
   *   A file name or URI.
   *
   * @return string
   *   The lowercase extension, or an empty string if there is none.
   *
   * @see \Drupal\system\EventSubscriber\SecurityFileUploadEventSubscriber::sanitizeName()
   */
  public static function getExtension(string $name): string {
    $name = rtrim(str_replace(chr(0), '', $name), ". \t\n\r\x0B");
    return strtolower(pathinfo($name, PATHINFO_EXTENSION));
  }

  /**
   * Checks whether a file name has a blocked extension.
   *
   * @param string $name
   *   A file name or URI.
   *
   * @return bool
   *   TRUE if the extension is blocked.
   */
  public static function isBlocked(string $name): bool {
    return in_array(static::getExtension($name), GovcmsFileConstraintInterface::BLOCKED_EXTENSIONS, TRUE);
  }

}
