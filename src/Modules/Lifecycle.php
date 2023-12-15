<?php

namespace Drupal\govcms\Modules;

/**
 * Service description.
 */
class Lifecycle {

  // Deprecated modules.
  const DEPRECATED_MODULES = [
    'swiftmailer',
  ];

  // Obsolete modules.
  const OBSOLETE_MODULES = [
    'config_filter',
    'panelizer',
  ];

  /**
   * Constructs a new service.
   */
  public function __construct() {}

  /**
   * Updates module information based on its lifecycle status.
   *
   * @param array $info
   *   The module information array.
   * @param string $lifecycle
   *   The lifecycle status ('deprecated' or 'obsolete').
   */
  public function updateModuleInfo(array &$info, string $lifecycle): void {
    $info['name'] .= " [$lifecycle]";
    $info['package'] = "GovCMS [$lifecycle]";
    $info['lifecycle'] = $lifecycle;
    $info['lifecycle_link'] = 'https://github.com/GovCMS/GovCMS';
  }

}
