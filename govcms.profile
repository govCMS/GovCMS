<?php

/**
 * @file
 * Enables modules and site configuration for the GovCMS profile.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function govcms_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Add a placeholder as example that one can choose an arbitrary site name.
  $form['site_information']['site_name']['#attributes']['placeholder'] = t('GovCMS');
}

/**
 * Implements hook_page_attachments_alter().
 */
function govcms_page_attachments_alter(array &$page) {
  foreach ($page['#attached']['html_head'] as $key => $value) {
    if ($value[1] == 'system_meta_generator') {
      $page['#attached']['html_head'][$key][0]['#attributes']['content'] = 'Drupal 9 (http://drupal.org) + GovCMS (http://govcms.gov.au)';
    }
  }
}

/**
 * Implements hook_system_breadcrumb_alter().
 */
function govcms_system_breadcrumb_alter(Breadcrumb $breadcrumb, RouteMatchInterface $route_match, array $context) {
  // Append the current page title to the breadcrumb for non-admin routes.
  if (!empty($route_match->getRouteObject()) && $breadcrumb && !\Drupal::service('router.admin_context')->isAdminRoute()) {
    $title = \Drupal::service('title_resolver')->getTitle(\Drupal::request(), $route_match->getRouteObject());
    if (!empty($title)) {
      $breadcrumb->addLink(Link::createFromRoute($title, '<none>'));
    }
    $breadcrumb->addCacheContexts(['route']);
  }
}

/**
 * Implements hook_field_widget_form_alter().
 *
 * @deprecated in drupal:9.2.0 and is removed from drupal:10.0.0. Use
 *   hook_field_widget_single_element_form_alter instead.
 *
 * @see https://www.drupal.org/node/3180429
 */
function govcms_field_widget_form_alter(&$element, FormStateInterface $form_state, $context) {
  // In Drupal 7 `hook_field_widget_form_alter` could be implemented by the
  // theme, but in Drupal 8 this hook changed to being module-only. This hook
  // can be critical for improving the editor experience for things like
  // paragraphs. Implementing themes can access the plugin ID via
  // $context['widget']->getPluginId().
  \Drupal::theme()->alter([
    'field_widget_form',
  ], $element, $form_state, $context);
}

/**
 * Implements hook_field_widget_multivalue_form_alter().
 *
 * @deprecated in drupal:9.2.0 and is removed from drupal:10.0.0. Use
 *   hook_field_widget_complete_form_alter instead.
 *
 * @see https://www.drupal.org/node/3180429
 */
function govcms_field_widget_multivalue_form_alter(&$elements, FormStateInterface $form_state, $context) {
  // In Drupal 7 `hook_field_widget_multivalue_form_alter` could be implemented
  // by the theme, but in Drupal 8 this hook changed to being module-only.
  // This hook can be critical for improving the editor experience for things
  // like paragraphs. Implementing themes can access the plugin ID via
  // $context['widget']->getPluginId().
  \Drupal::theme()->alter([
    'field_widget_multivalue_form',
  ], $elements, $form_state, $context);
}

/**
 * Implements hook_field_widget_single_element_form_alter().
 *
 * Introduced in Drupal 9.2.x.
 * Replaces hook_field_widget_form_alter().
 *
 * @see \Drupal\Core\Field\WidgetBase::formSingleElement()
 */
function govcms_field_widget_single_element_form_alter(array &$element, FormStateInterface $form_state, array $context) {
  // In Drupal 7 `hook_field_widget_form_alter` could be implemented
  // by the theme, but in Drupal 8 this hook changed to being module-only.
  // This hook can be critical for improving the editor experience for things
  // like paragraphs. Implementing themes can access the plugin ID via
  // $context['widget']->getPluginId().
  \Drupal::theme()->alter([
    'field_widget_single_element_form',
    'field_widget_single_element_' . $context['widget']->getPluginId() . '_form',
  ], $element, $form_state, $context);
}

/**
 * Implements hook_field_widget_complete_form_alter().
 *
 * Introduced in Drupal 9.2.x.
 * Replaces hook_field_widget_multivalue_form_alter().
 *
 * @see \Drupal\Core\Field\WidgetBase::form()
 */
function govcms_field_widget_complete_form_alter(array &$field_widget_complete_form, FormStateInterface $form_state, array $context) {
  // In Drupal 7 `hook_field_widget_multivalue_form_alter` could be implemented
  // by the theme, but in Drupal 8 this hook changed to being module-only.
  // This hook can be critical for improving the editor experience for things
  // like paragraphs. Implementing themes can access the plugin ID via
  // $context['widget']->getPluginId().
  \Drupal::theme()->alter([
    'field_widget_complete_form',
    'field_widget_complete_' . $context['widget']->getPluginId() . '_form',
  ], $field_widget_complete_form, $form_state, $context);
}
