<?php

namespace Drupal\govcms_media\Plugin\media\Source;

use Drupal\govcms_media\FileInputExtensionMatchTrait;
use Drupal\govcms_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\VideoFile as BaseVideoFile;

/**
 * Input-matching version of the Video File media source.
 */
class VideoFile extends BaseVideoFile implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
