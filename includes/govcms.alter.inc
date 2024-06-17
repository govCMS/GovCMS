<?php

/**
 * @file
 * GovCMS alter helper functions.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Url;

/**
 * Implements hook_toolbar().
 */
function govcms_toolbar() {
  $items = [];

  $items['govcms_system_report'] = [
    '#type' => 'toolbar_item',
    'tab' => [
      '#type' => 'link',
      '#title' => t('GovCMS System Report'),
      '#url' => Url::fromRoute('govcms.admin.system_report'),
      '#options' => [
        'attributes' => [
          'title' => t('GovCMS System Report'),
          'class' => [
            'toolbar-icon',
            'toolbar-icon-system-admin-reports',
            'toolbar-item',
          ],
        ],
      ],
    ],
    '#weight' => 100,
  ];

  return $items;
}

/**
 * Implements hook_menu_links_discovered_alter().
 *
 * Alter menu links for the admin reports page.
 */
function govcms_menu_links_discovered_alter(array &$links): void {
  // Define the restricted routes.
  $restricted_routes = [
    'system.status',
    'update.status',
    'event_log_track.report_form',
  ];

  // Iterate over the discovered menu links.
  foreach ($links as $key => $link) {
    // Check if the menu link has a route name.
    if (isset($link['route_name']) && isset($link['parent']) && $link['parent'] === 'system.admin_reports') {
      // Remove the link if it matches any of the restricted routes.
      if (in_array($link['route_name'], $restricted_routes)) {
        unset($links[$key]);
      }
    }
  }
}

/**
 * Implements hook_form_FORM_ID_alter() for install_configure_form().
 *
 * Allows the profile to alter the site configuration form.
 */
function govcms_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Add a placeholder as example that one can choose an arbitrary site name.
  $form['site_information']['site_name']['#attributes']['placeholder'] = t('GovCMS');
  $form['site_information']['site_name']['#attributes']['value'] = t('GovCMS');
  $form['site_information']['site_mail']['#attributes']['value'] = 'no-reply@govcms.localhost';
  $form['admin_account']['account']['name']['#attributes']['value'] = 'admin';
  $form['admin_account']['account']['mail']['#attributes']['value'] = 'admin@govcms.localhost';
}

/**
 * Implements hook_page_attachments_alter().
 */
function govcms_page_attachments_alter(array &$page) {
  foreach ($page['#attached']['html_head'] as $key => $value) {
    if ($value[1] == 'system_meta_generator') {
      $page['#attached']['html_head'][$key][0]['#attributes']['content'] = 'Drupal 10 (http://drupal.org) + GovCMS (http://govcms.gov.au)';
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

/**
 * Implements hook_config_schema_info_alter().
 */
function govcms_config_schema_info_alter(&$definitions)
{
    if (isset($definitions['ckeditor5.plugin.ckeditor5_sourceEditing']))
    {
        unset($definitions['ckeditor5.plugin.ckeditor5_sourceEditing']['mapping']['allowed_tags']['sequence']['constraints']['SourceEditingRedundantTags']);
    }
}
