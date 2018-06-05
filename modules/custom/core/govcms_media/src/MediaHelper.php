<?php

namespace Drupal\govcms_media;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\govcms_media\Exception\IndeterminateBundleException;
use Drupal\media\MediaInterface;

/**
 * Provides helper methods for dealing with media entities.
 *
 * Code from \Drupal\lightning_media\MediaHelper
 * in lightning_media 8.x-2.3 submodule.
 */
class MediaHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * MediaHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns all file extensions accepted by bundles that use file fields.
   *
   * @param bool $check_access
   *   (optional) Whether to filter the bundles by create access for the current
   *   user. Defaults to FALSE.
   * @param string[] $bundles
   *   (optional) An array of bundle IDs from which to retrieve source field
   *   extensions. If omitted, all available bundles are allowed.
   *
   * @return string[]
   *   The file extensions accepted by all available bundles.
   */
  public function getFileExtensions($check_access = FALSE, array $bundles = []) {
    $extensions = '';

    // GovCMS Media overrides the media_bundle storage handler with a special
    // one that adds an optional second parameter to loadMultiple().
    $storage = $this->entityTypeManager
      ->getStorage('media_type');
    $media_types = $storage->loadMultiple($bundles ?: NULL, $check_access);

    /** @var \Drupal\media\MediaTypeInterface $media_type */
    foreach ($media_types as $media_type) {
      $field = $media_type->getSource()->getSourceFieldDefinition($media_type);

      // If the field is a FileItem or any of its descendants, we can consider
      // it a file field. This will automatically include things like image
      // fields, which extend file fields.
      if (is_a($field->getItemDefinition()->getClass(), FileItem::class, TRUE)) {
        $extensions .= $field->getSetting('file_extensions') . ' ';
      }
    }
    $extensions = preg_split('/,?\s+/', rtrim($extensions));
    return array_unique($extensions);
  }

  /**
   * Returns the first media bundle that can accept an input value.
   *
   * @param mixed $value
   *   The input value.
   * @param bool $check_access
   *   (optional) Whether to filter the bundles by create access for the current
   *   user. Defaults to TRUE.
   * @param string[] $bundles
   *   (optional) A set of media bundle IDs which might match the input. If
   *   omitted, all available bundles are checked.
   *
   * @return \Drupal\media\MediaTypeInterface
   *   A media bundle that can accept the input value.
   *
   * @throws \Drupal\govcms_media\Exception\IndeterminateBundleException
   *   If no bundle can be matched to the input value.
   */
  public function getBundleFromInput($value, $check_access = TRUE, array $bundles = []) {
    // GovCMS Media overrides the media_bundle storage handler with a special
    // one that adds an optional second parameter to loadMultiple().
    $media_types = $this->entityTypeManager
      ->getStorage('media_type')
      ->loadMultiple($bundles ?: NULL, $check_access);
    ksort($media_types);

    /** @var \Drupal\media\MediaTypeInterface $media_type */
    foreach ($media_types as $media_type) {
      $source = $media_type->getSource();

      if ($source instanceof InputMatchInterface && $source->appliesTo($value, $media_type)) {
        return $media_type;
      }
    }
    throw new IndeterminateBundleException($value);
  }

  /**
   * Creates a media entity from an input value.
   *
   * @param mixed $value
   *   The input value.
   * @param string[] $bundles
   *   (optional) A set of media bundle IDs which might match the input value.
   *   If omitted, all bundles to which the user has create access are checked.
   *
   * @return \Drupal\media\MediaInterface
   *   The unsaved media entity.
   */
  public function createFromInput($value, array $bundles = []) {
    /** @var \Drupal\media\MediaInterface $entity */
    $entity = $this->entityTypeManager
      ->getStorage('media')
      ->create([
        'bundle' => $this->getBundleFromInput($value, TRUE, $bundles)->id(),
      ]);

    $field = static::getSourceField($entity);
    if ($field) {
      $field->setValue($value);
    }
    return $entity;
  }

  /**
   * Attaches a file entity to a media entity.
   *
   * @param \Drupal\media\MediaInterface $entity
   *   The media entity.
   * @param \Drupal\file\FileInterface $file
   *   The file entity.
   * @param int $replace
   *   (optional) What to do if the file already exists. Can be any of the
   *   constants accepted by file_move().
   *
   * @return \Drupal\file\FileInterface|false
   *   The final file entity (unsaved), or FALSE if an error occurred.
   */
  public static function useFile(MediaInterface $entity, FileInterface $file, $replace = FILE_EXISTS_RENAME) {
    $field = static::getSourceField($entity);
    $field->setValue($file);

    $destination = '';
    $destination .= static::prepareFileDestination($entity);
    if (substr($destination, -1) != '/') {
      $destination .= '/';
    }
    $destination .= $file->getFilename();

    if ($destination == $file->getFileUri()) {
      return $file;
    }
    else {
      $file = file_move($file, $destination, $replace);

      if ($file) {
        $field->setValue($file);
        return $file;
      }
      else {
        return FALSE;
      }
    }
  }

  /**
   * Prepares the destination directory for a file attached to a media entity.
   *
   * @param \Drupal\media\MediaInterface $entity
   *   The media entity.
   *
   * @return string
   *   The destination directory URI.
   *
   * @throws \RuntimeException
   *   If the destination directory is not writable.
   */
  public static function prepareFileDestination(MediaInterface $entity) {
    /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $item */
    $item = static::getSourceField($entity)->first();

    $dir = $item->getUploadLocation();
    $is_ready = file_prepare_directory($dir, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);

    if ($is_ready) {
      return $dir;
    }
    else {
      throw new \RuntimeException('Could not prepare ' . $dir . ' for writing');
    }
  }

  /**
   * Indicates if the media entity's type plugin supports dynamic previews.
   *
   * @param \Drupal\media\MediaInterface $entity
   *   The media entity.
   *
   * @return bool
   *   TRUE if dynamic previews are supported, FALSE otherwise.
   */
  public static function isPreviewable(MediaInterface $entity) {
    $plugin_definition = $entity->getSource()->getPluginDefinition();

    return isset($plugin_definition['preview']);
  }

  /**
   * Returns the media entity's source field item list.
   *
   * @param \Drupal\media\MediaInterface $entity
   *   The media entity.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The media entity's source field item list, or NULL if the media type
   *   plugin does not define a source field.
   */
  public static function getSourceField(MediaInterface $entity) {
    $field = $entity->getSource()->getSourceFieldDefinition($entity->bundle->entity);

    return $field
      ? $entity->get($field->getName())
      : NULL;
  }

}
