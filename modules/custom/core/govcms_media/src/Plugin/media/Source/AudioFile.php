<?php

namespace Drupal\govcms_media\Plugin\media\Source;

use Drupal\govcms_media\FileInputExtensionMatchTrait;
use Drupal\govcms_media\InputMatchInterface;
use Drupal\media\Plugin\media\Source\AudioFile as BaseAudioFile;

/**
 * Input-matching version of the Audio File media source.
 */
class AudioFile extends BaseAudioFile implements InputMatchInterface {

  use FileInputExtensionMatchTrait;

}
