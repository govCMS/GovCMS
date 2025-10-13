<?php

namespace Drupal\govcms\Modules;

use Drupal\Core\Extension\ExtensionLifecycle;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Service description.
 */
class Lifecycle {

  // Deprecated modules.
  const DEPRECATED_MODULES = [];

  // Obsolete modules.
  const OBSOLETE_MODULES = [
    'ckeditor',
    'config_filter',
    'forum',
    'mailsystem',
    'panelizer',
    'swiftmailer',
    'tracker',
    'block_place',
    'aggregator',
    'block_inactive_users',
    'event_log_track_ui',
    'govcms8_foundations',
    'video_embed_field',
    'jquery_ui_accordion',
    'jquery_ui_resizable',
    'panels',
    'permissions_by_term',
    'permissions_by_entity',
    'restui',
    'clamav',
    'govcms8_layouts',
    'redirect_404',
    'field_layout',
    'graphql',
    'statistics',
    'purge_purger_http',
    'action',
    'help_topics',
    'tour',
    'webform_icheck',
    'webform_location_geocomplete',
    'webform_location_places',
    'webform_shortcuts',
    'webform_toggles',
    'panelizer_quickedit',
    'panels_ipe',
  ];

  /**
   * Constructs a new service.
   */
  public function __construct() {}

  /**
   * Updates module information based on its lifecycle status.
   *
   * @param array $info
   *   The module information array.
   * @param string $lifecycle
   *   The lifecycle status ('deprecated' or 'obsolete').
   */
  public function updateModuleInfo(array &$info, string $lifecycle): void {
    $info['name'] .= " [$lifecycle]";
    $info['package'] = "GovCMS [$lifecycle]";
    $info['lifecycle'] = $lifecycle;
    $info['lifecycle_link'] = 'https://github.com/GovCMS/GovCMS';
  }

  /**
   * Uninstalls modules marked as obsolete.
   *
   * @param array $modules
   *   The modules to uninstall.
   */
  public function uninstallObsoleteModules(array $modules): void {
    // Get the module installer service.
    $module_installer = \Drupal::service('module_installer');
    $module_handler = \Drupal::service('module_handler');

    foreach ($modules as $module) {
      // Check if the module is installed and marked as obsolete before attempting to uninstall.
      if ($module_handler->moduleExists($module)) {
        $moduleInfo = \Drupal::service('extension.list.module')->getExtensionInfo($module);

        if ($moduleInfo && isset($moduleInfo['lifecycle']) && $moduleInfo['lifecycle'] === ExtensionLifecycle::OBSOLETE) {
          $module_installer->uninstall([$module]);
        }
      }
    }
  }
}
