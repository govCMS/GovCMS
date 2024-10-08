<?php

namespace Drupal\govcms\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Class ModuleInstalledEvent.
 */
class ModuleInstalledEvent extends Event {

  const MODULES_INSTALLED = 'govcms.modules_installed';

  public array $modules;

  /**
   * Constructs the object.
   *
   * @param array $modules
   */
  public function __construct(array $modules) {
    $this->modules = $modules;
  }

}
