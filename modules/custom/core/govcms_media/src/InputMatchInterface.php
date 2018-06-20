<?php

namespace Drupal\govcms_media;

use Drupal\media\MediaTypeInterface;

/**
 * An interface for media type plugins to tell if they can handle mixed input.
 *
 * Code from \Drupal\lightning_media\InputMatchInterface
 * in lightning_media 8.x-2.3 submodule.
 */
interface InputMatchInterface {

  /**
   * Checks if this media type can handle a given input value.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\media\MediaTypeInterface $bundle
   *   The media bundle that is using this plugin.
   *
   * @return bool
   *   TRUE if the input can be handled by this plugin, FALSE otherwise.
   */
  public function appliesTo($value, MediaTypeInterface $bundle);

}
