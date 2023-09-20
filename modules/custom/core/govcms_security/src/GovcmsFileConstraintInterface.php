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

}
