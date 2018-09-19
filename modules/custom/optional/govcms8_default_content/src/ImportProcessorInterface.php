<?php

namespace Drupal\govcms8_default_content;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for GovCMS8 Import Processor plugins.
 */
interface ImportProcessorInterface extends PluginInspectionInterface {

  /**
   * Method used to customise $values array before passed to Drupal.
   */
  public function process(&$values);

}
