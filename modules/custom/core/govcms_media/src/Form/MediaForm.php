<?php

namespace Drupal\govcms_media\Form;

use Drupal\media\MediaForm as BaseMediaForm;

/**
 * Adds dynamic preview support to the media entity form.
 *
 * Code from \Drupal\lightning_media\Form\MediaForm in lightning_media 8.x-2.3
 * submodule.
 */
class MediaForm extends BaseMediaForm {

  use BulkCreationEntityFormTrait;

}
