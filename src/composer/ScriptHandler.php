<?php

namespace GovCMS\composer;

use Composer\Semver\Comparator;
use Symfony\Component\Filesystem\Filesystem;
use Composer\EventDispatcher\Event;
use DrupalFinder\DrupalFinder;

/**
 * GovCMS Composer Script Handler.
 *
 * @package DrupalProject\composer
 */
class ScriptHandler {

  /**
   * Get the Drupal root directory.
   *
   * @param string $project_root
   *   Project root.
   *
   * @return string
   *   Drupal root path.
   */
  protected static function getDrupalRoot($project_root) {
    $fs = new Filesystem();
    $drupalFinder = new DrupalFinder();
    $drupalFinder->locateRoot(getcwd());
    $drupalRoot = $drupalFinder->getDrupalRoot();
    if (!$fs->exists($drupalRoot . '/core')) {
      return $project_root . '/app';
    }
    else {
      return $drupalRoot;
    }
  }


  /**
   * Create required files.
   *
   * @param  Event  $event
   *   Event of create required files.
   */
  public static function createRequiredFiles(Event $event) {
    $fs = new Filesystem();
    $drupal_root = static::getDrupalRoot(getcwd());

    $dirs = [
      'modules',
      'profiles',
      'themes',
      'libraries',
    ];

    // Required for unit testing.
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupal_root . '/' . $dir)) {
        $fs->mkdir($drupal_root . '/' . $dir);
        $fs->touch($drupal_root . '/' . $dir . '/.gitkeep');
      }
    }

    // Prepare the settings file for installation.
    if (!$fs->exists($drupal_root . '/sites/default/settings.php') and $fs->exists($drupal_root . '/sites/default/default.settings.php')) {
      $fs->copy($drupal_root . '/sites/default/default.settings.php', $drupal_root . '/sites/default/settings.php');
      $fs->chmod($drupal_root . '/sites/default/settings.php', 0666);
      $event->getIO()->write("Create a sites/default/settings.php file with chmod 0666");
    }
    // Prepare the services file for installation.
    if (!$fs->exists($drupal_root . '/sites/default/services.yml') and $fs->exists($drupal_root . '/sites/default/default.services.yml')) {
      $fs->copy($drupal_root . '/sites/default/default.services.yml', $drupal_root . '/sites/default/services.yml');
      $fs->chmod($drupal_root . '/sites/default/services.yml', 0666);
      $event->getIO()->write("Create a sites/default/services.yml file with chmod 0666");
    }
    // Create the files directory with chmod 0777.
    if (!$fs->exists($drupal_root . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($drupal_root . '/sites/default/files', 0777);
      umask($oldmask);
      $event->getIO()->write("Create a sites/default/files directory with chmod 0777");
    }
  }

  /**
   * Checks if the installed version of Composer is compatible.
   *
   * @param  Event  $event
   *   Event of create required files.
   */
  public static function checkComposerVersion(Event $event) {
    $composer = $event->getComposer();
    $io = $event->getIO();
    $version = $composer::VERSION;
    // The dev-channel of composer uses the git revision as version number,
    // try to the branch alias instead.
    if (preg_match('/^[0-9a-f]{40}$/i', $version)) {
      $version = $composer::BRANCH_ALIAS_VERSION;
    }
    // If Composer is installed through git we have no easy way to determine if
    // it is new enough, just display a warning.
    if ($version === '@package_version@' || $version === '@package_branch_alias_version@') {
      $io->writeError('<warning>You are running a development version of Composer. If you experience problems, please update Composer to the latest stable version.</warning>');
    }
    elseif (Comparator::lessThan($version, '1.0.0')) {
      $io->writeError('<error>Drupal-project requires Composer version 1.0.0 or higher. Please update your Composer before continuing</error>.');
      exit(1);
    }
  }

}
