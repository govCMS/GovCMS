<?php

namespace Drupal\govcms_media\Plugin\media\Source;

use Drupal\govcms_media\InputMatchInterface;
use Drupal\media\MediaTypeInterface;
use Drupal\media\Plugin\media\Source\OEmbed;

/**
 * Input-matching version of the OEmbed media source.
 */
class RemoteVideo extends OEmbed implements InputMatchInterface {

  /**
   * {@inheritdoc}
   */
  public function appliesTo($value, MediaTypeInterface $media_type) {
    if (is_string($value)) {
      try {
        return (bool) $this->urlResolver->getProviderByUrl($value);
      }
      catch (\Exception $e) {
      }
    }
    return FALSE;
  }

}
