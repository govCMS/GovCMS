<?php

namespace Drupal\govcms_media\Form;

use Drupal\media\MediaForm as BaseMediaForm;

/**
 * Adds dynamic preview support to the media entity form.
 */
class MediaForm extends BaseMediaForm {

  use BulkCreationEntityFormTrait;

}
