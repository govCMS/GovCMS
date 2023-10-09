<?php

namespace Drupal\govcms_security;

/**
 * Provides an interface for constrants on files uploaded.
 */
interface GovcmsFileConstraintInterface {

  /**
   * A list of blocked file extensions.
   */
  public const BLOCKED_EXTENSIONS = ['doc', 'xls', 'ppt', 'rtf'];

  /**
   * The regex used to extract the filename from the content disposition header.
   *
   * @var string
   */
  public const REQUEST_HEADER_FILENAME_REGEX = '@\bfilename(?<star>\*?)=\"(?<filename>.+)\"@';

}
