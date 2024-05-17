<?php

namespace Drupal\govcms_security\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 *
 * Class GovcmsRouteSubscriber.
 *
 * @package Drupal\govcms_security\Routing
 */
class GovcmsRouteSubscriber extends RouteSubscriberBase {

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array {
        $events[RoutingEvents::ALTER] = ['onAlterRoutes', -220];
        return $events;
    }

  /**
   * Overrides user.login route with our custom login form.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   Route to be altered.
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change path of user login to our overridden TFA login form.
    if ($route = $collection->get('user.login')) {
      $route->setDefault('_form', '\Drupal\govcms_security\Form\GovcmsLoginForm');
    }
  }

}

