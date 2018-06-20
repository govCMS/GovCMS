<?php

namespace Drupal\govcms_media\Plugin\media\Source;

use Drupal\govcms_media\FileInputExtensionMatchTrait;
use Drupal\govcms_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\Image as BaseImage;

/**
 * Input-matching version of the Image media source.
 */
class Image extends BaseImage implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
