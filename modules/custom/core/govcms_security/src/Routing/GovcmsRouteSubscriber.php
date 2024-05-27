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
   * Overrides user.login route with our custom login form.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   Route to be altered.
   */
  public function alterRoutes(RouteCollection $collection) {
    // Change path of user login to our overridden TFA login form.
    if ($route = $collection->get('block_inactive_users.settings_cancel_users')) {
        $route->setRequirement('_access', 'FALSE');
    }
    if ($route = $collection->get('block_inactive_users.confirm_cancel_users_form')) {
        $route->setRequirement('_access', 'FALSE');
    }
  }

}
