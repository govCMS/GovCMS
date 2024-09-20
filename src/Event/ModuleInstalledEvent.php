<?php

namespace Drupal\govcms\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Class ModuleInstalledEvent.
 */
class ModuleInstalledEvent extends Event {

  public const MODULES_INSTALLED = 'govcms.modules_installed';

  /**
   * Constructs the object.
   *
   * @param array $modules
   */
  public function __construct(public array $modules)
  {
  }

}
