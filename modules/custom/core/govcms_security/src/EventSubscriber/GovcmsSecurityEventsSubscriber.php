<?php

namespace Drupal\govcms_security\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\govcms_security\GovcmsFileConstraintInterface;

/**
 * GovCMS security event subscriber.
 */
class GovcmsSecurityEventsSubscriber implements EventSubscriberInterface {

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
    return $events;
  }

  /**
   * React to files being uploaded.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   HTTP request event.
   */
  public function onRequest(RequestEvent $event) {
    if ($event->isMainRequest()) {
      // File's names uploaded via a HTTP request,.
      $file_names = array_column($_FILES, 'name');

      array_walk_recursive($file_names, function ($name, $field) {
        if (is_string($name)) {
          // The file extension of the original name.
          $extension = pathinfo($name, PATHINFO_EXTENSION);
          if ($extension && is_string($extension)) {
            if (in_array(strtolower($extension), GovcmsFileConstraintInterface::BLOCKED_EXTENSIONS, TRUE)) {
              $message = sprintf('\'%s\' file is blocked from uploading ', $extension);
              // @todo Remove the uploaded file from the temporary folder as it is blocked
              // and do not need anymore.
              throw new AccessDeniedHttpException($message);
            }
          }
        }
      });
    }
  }

}
