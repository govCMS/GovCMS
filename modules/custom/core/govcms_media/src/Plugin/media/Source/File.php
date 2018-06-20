<?php

namespace Drupal\govcms_media\Plugin\media\Source;

use Drupal\govcms_media\FileInputExtensionMatchTrait;
use Drupal\govcms_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\File as BaseFile;

/**
 * Input-matching version of the File media source.
 */
class File extends BaseFile implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
