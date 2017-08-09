<?php

namespace govCMS\Core\Composer;

use Composer\Script\Event;
use Composer\Util\ProcessExecutor;
use Composer\Package\PackageInterface;
use Symfony\Component\Yaml\Yaml;
use govCMS\Core\IniEncoder;

/**
 * Class Package
 *
 * @package govCMS\Core\Composer
 */
class Package {

  /**
   * @var
   */
  private static $composer;

  /**
   * @var
   */
  private static $executor;

  /**
   * @var
   */
  private static $encoder;

  /**
   * Package constructor.
   *
   * @param \Composer\Script\Event $event
   */
  private static function construct(Event $event) {
    static::$composer = $event->getComposer();
    static::$executor = new ProcessExecutor();
    static::$encoder = new IniEncoder();
  }

  /**
   * Build Drupal library.
   *
   * @param $make
   */
  private static function buildDrupalLibrary(&$make) {

  }

  /**
   * Build Drupal core.
   *
   * @param $make
   */
  private static function buildDrupalCore(&$make) {
    if (isset($make['projects']['drupal'])) {
      // Always use drupal.org's core repository, or patches will not apply.
      $make['projects']['drupal']['download']['url'] = 'https://git.drupal.org/project/drupal.git';
      $core = [
        'api' => 2,
        'core' => '8.x',
        'projects' => [
          'drupal' => [
            'type' => 'core',
            'version' => $make['projects']['drupal']['download']['tag'],
          ],
        ],
      ];
      if (isset($make['projects']['drupal']['patch'])) {
        $core['projects']['drupal']['patch'] = $make['projects']['drupal']['patch'];
      }
      file_put_contents('drupal-org-core.make', static::$encoder->encode($core));
      unset($make['projects']['drupal']);
    }
  }

  /**
   * Build Drupal projects.
   *
   * @param $make
   */
  private static function buildDrupalProject(&$make) {

  }

  /**
   * Build Drupal make files.
   *
   * @param $make
   */
  private static function buildDrupal($make) {
    static::buildDrupalCore($make);
  }

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    static::construct($event);

    // Convert the lock file to a make file using Drush's make-convert command.
    $bin_dir = static::$composer->getConfig()->get('bin-dir');
    $make = NULL;
    static::$executor->execute($bin_dir . '/drush make-convert composer.lock', $make);
    $make = Yaml::parse($make);

    // Build Drupal make files.
    static::buildDrupal($make);
  }

}
