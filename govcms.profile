<?php

/**
 * @file
 * Enables modules and site configuration for the govCMS profile.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\contact\Entity\ContactForm;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function govcms_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Add a placeholder as example that one can choose an arbitrary site name.
  $form['site_information']['site_name']['#attributes']['placeholder'] = t('govCMS');
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function govcms_form_contact_message_contact_form_alter(&$form, FormStateInterface $form_state) {
  $form['actions']['preview']['#access'] = FALSE;
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function govcms_form_user_login_form_alter(&$form, &$form_state) {
  $form['#attributes']['autocomplete'] = 'off';
}

/**
 * Implements hook_page_attachments_alter().
 */
function govcms_page_attachments_alter(array &$page) {
  foreach ($page['#attached']['html_head'] as $key => $value) {
    if ($value[1] == 'system_meta_generator') {
      $page['#attached']['html_head'][$key][0]['#attributes']['content'] = 'Drupal 8 (http://drupal.org) + govCMS (http://govcms.gov.au)';
    }
  }
}

/**
 * Implements hook_system_breadcrumb_alter().
 */
function govcms_system_breadcrumb_alter(Breadcrumb $breadcrumb, RouteMatchInterface $route_match, array $context) {
  // Append the current page title to the breadcrumb for non-admin routes.
  if ($breadcrumb && !\Drupal::service('router.admin_context')->isAdminRoute()) {
    $title = \Drupal::service('title_resolver')->getTitle(\Drupal::request(), $route_match->getRouteObject());
    if (!empty($title)) {
      $breadcrumb->addLink(Link::createFromRoute($title, '<none>'));
    }
    $breadcrumb->addCacheContexts(['route']);
  }
}
