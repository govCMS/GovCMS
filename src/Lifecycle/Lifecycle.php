<?php

namespace Drupal\govcms\Lifecycle;

use Drupal\Core\Extension\ExtensionLifecycle;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;

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
  ];

  // Deprecated themes.
  const DEPRECATED_THEMES = [];

  // Obsolete themes.
  const OBSOLETE_THEMES = [
    'bartik',
    'seven',
  ];

  /**
   * Constructs a new service.
   */
  public function __construct() {}

  /**
   * Retrieves the lifecycle status of all modules and themes.
   *
   * @return array An array with two keys, 'modules' and 'themes', each containing an array
   *               of 'deprecated' and 'obsolete' items.
   */
  public function getLifecycleStatus() {
    return [
      'modules' => [
        'deprecated' => self::DEPRECATED_MODULES,
        'obsolete' => self::OBSOLETE_MODULES,
      ],
      'themes' => [
        'deprecated' => self::DEPRECATED_THEMES,
        'obsolete' => self::OBSOLETE_THEMES,
      ]
    ];
  }

  /**
   * Updates extension information based on its lifecycle status.
   *
   * @param array $info
   *   The extension information array.
   * @param string $lifecycle
   *   The lifecycle status ('deprecated' or 'obsolete').
   */
  public function updateExtensionInfo(array &$info, string $lifecycle): void {
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

  /**
   * Uninstalls themes marked as obsolete.
   *
   * @param array $themes
   *   The themes to uninstall.
   */
  public function uninstallObsoleteThemes(array $themes): void {
    // Get the theme installer service.
    $theme_installer = \Drupal::service('theme_installer');
    $theme_handler = \Drupal::service('theme_handler');

    foreach ($themes as $theme) {
      // Check if the theme is installed and marked as obsolete before attempting to uninstall.
      if ($theme_handler->themeExists($theme)) {
        $themeInfo = \Drupal::service('extension.list.theme')->getExtensionInfo($theme);

        if ($themeInfo && isset($themeInfo['lifecycle']) && $themeInfo['lifecycle'] === ExtensionLifecycle::OBSOLETE) {
          $theme_installer->uninstall([$theme]);
        }
      }
    }
  }
}
