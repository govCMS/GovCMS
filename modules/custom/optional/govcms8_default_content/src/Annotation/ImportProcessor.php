<?php

namespace Drupal\govcms8_default_content\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a GovCMS8 Import Processor item annotation object.
 *
 * @see \Drupal\govcms8_default_content\ImportProcessorManager
 * @see plugin_api
 *
 * @Annotation
 */
class ImportProcessor extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * Entity type.
   *
   * @var string
   */
  public $type;

}
