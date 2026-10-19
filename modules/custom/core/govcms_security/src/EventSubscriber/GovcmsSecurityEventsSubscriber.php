<?php

namespace Drupal\govcms_security\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\file\Validation\FileValidationEvent;
use Drupal\govcms_security\BlockedFileExtension;
use Drupal\govcms_security\GovcmsFileConstraintInterface;

/**
 * GovCMS security event subscriber.
 */
class GovcmsSecurityEventsSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    $events = [];
    // Subscribe to Symfony kernel request with default priority of 0.
    $events[KernelEvents::REQUEST][] = ['onRequest'];
    // Validate files after core has sanitised the file name.
    $events[FileValidationEvent::class][] = ['onFileValidation'];
    return $events;
  }

  /**
   * Rejects files with a blocked extension during file validation.
   *
   * This checks the file name that will be stored, before the file is moved
   * to its destination.
   *
   * @param \Drupal\file\Validation\FileValidationEvent $event
   *   The file validation event.
   */
  public function onFileValidation(FileValidationEvent $event) {
    $file = $event->file;
    foreach ([$file->getFilename(), $file->getFileUri()] as $name) {
      if (is_string($name) && BlockedFileExtension::isBlocked($name)) {
        $template = 'Files with the %extension extension are not allowed.';
        $parameters = ['%extension' => BlockedFileExtension::getExtension($name)];
        $event->violations->add(new ConstraintViolation(
          $this->t('Files with the %extension extension are not allowed.', $parameters),
          $template,
          $parameters,
          $file,
          'filename',
          $name,
        ));
        return;
      }
    }
  }

  /**
   * React to files being uploaded.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   HTTP request event.
   */
  public function onRequest(RequestEvent $event) {
    // Only need to check the main request.
    if ($event->isMainRequest()) {
      $current_request = $event->getRequest();
      // Content Disposition from the request's header.
      $content_disposition = $current_request->headers->get('content-disposition');
      $matches = [];

      // File upladed by API.
      if ($content_disposition) {
        if (preg_match(GovcmsFileConstraintInterface::REQUEST_HEADER_FILENAME_REGEX, $content_disposition, $matches)) {
          if (!empty($matches['filename'])) {
            // Validate the file name.
            $this->validateFile($matches['filename']);
          }
        }
      }

      // File uploaded by a form.
      $file_names = array_column($_FILES, 'name');
      // Search the array to find the filename element.
      array_walk_recursive($file_names, function ($file_name, $field) {
        if (is_string($file_name)) {
          // Validate the file name.
          $this->validateFile($file_name);
        }
      });
    }
  }

  /**
   * Validate uploading file.
   *
   * @param string $name
   *   The file name to validate.
   *
   * @return bool
   *   Return ture if the validation passed. Otherwise, it will throw an Access
   *   Denied Exception.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the file validation failed.
   */
  protected function validateFile($name) {
    // The file extension of the original name.
    $extension = BlockedFileExtension::getExtension($name);
    if ($extension) {
      if (BlockedFileExtension::isBlocked($name)) {
        $message = sprintf('\'%s\' file is blocked from uploading ', $extension);
        // @todo Remove the uploaded file from the temporary folder as it is blocked
        // and do not need anymore.
        throw new AccessDeniedHttpException($message);
      }
    }

    return TRUE;
  }

}
