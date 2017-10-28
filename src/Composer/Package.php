<?php
namespace govCMS\Core\Composer;
use govCMS\Core\IniEncoder;
use Composer\Package\Locker;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;
/**
 * Generates Drush make files for drupal.org's ancient packaging system.
 */
class Package {
  /**
   * The root Composer package (i.e., this composer.json).
   *
   * @var \Composer\Package\RootPackageInterface
   */
  protected $rootPackage;
  /**
   * The locker.
   *
   * @var \Composer\Package\Locker
   */
  protected $locker;
  /**
   * Package constructor.
   *
   * @param \Composer\Package\RootPackageInterface $root_package
   *   The root package (i.e., this composer.json).
   * @param \Composer\Package\Locker $locker
   *   The locker.
   */
  public function __construct(RootPackageInterface $root_package, Locker $locker) {
    $this->rootPackage = $root_package;
    $this->locker = $locker;
  }
  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    $composer = $event->getComposer();
    $handler = new static(
      $composer->getPackage(),
      $composer->getLocker()
    );
    $encoder = new IniEncoder();
    $make = $handler->make();
    $core = $handler->makeCore($make);
    file_put_contents('drupal-org-core.make', $encoder->encode($core));
    file_put_contents('drupal-org.make', $encoder->encode($make));
  }
  /**
   * Extracts a core-only make file from a complete make file.
   *
   * @param array $make
   *   The complete make file.
   *
   * @return array
   *   The core-only make file structure.
   */
  protected function makeCore(array &$make) {
    $project = $make['projects']['drupal'];
    unset($make['projects']['drupal']);
    return [
      'core' => $make['core'],
      'api' => $make['api'],
      'projects' => [
        'drupal' => $project,
      ],
    ];
  }
  /**
   * Generates a complete make file structure from the root package.
   *
   * @return array
   *   The complete make file structure.
   */
  protected function make() {
    $info = [
      'core' => '8.x',
      'api' => 2,
      'defaults' => [
        'projects' => [
          'subdir' => 'contrib',
        ],
      ],
      'projects' => [],
      'libraries' => [],
    ];
    $lock = $this->locker->getLockData();
    foreach ($lock['packages'] as $package) {
      list(, $name) = explode('/', $package['name'], 2);
      if ($this->isDrupalPackage($package)) {
        if ($package['type'] == 'drupal-core') {
          $name = 'drupal';
        }
        $info['projects'][$name] = $this->buildProject($package);
      }
      // Include any non-drupal libraries that exist in both .lock and .json.
      elseif ($this->isLibrary($package)) {
        $info['libraries'][$name] = $this->buildLibrary($package);
      }
      elseif ($this->isgovCMSTheme($package)) {
        $info['projects'][$name] = $this->buildProject($package);
      }
    }
    return $info;
  }
  /**
   * Builds a make structure for a library (i.e., not a Drupal project).
   *
   * @param array $package
   *   The Composer package definition.
   *
   * @return array
   *   The generated make structure.
   */
  protected function buildLibrary(array $package) {
    $info = [
      'type' => 'library',
    ];
    return $info + $this->buildPackage($package);
  }
  /**
   * Builds a make structure for a Drupal module, theme, profile, or core.
   *
   * @param array $package
   *   The Composer package definition.
   *
   * @return array
   *   The generated make structure.
   */
  protected function buildProject(array $package) {
    $info = [];
    switch ($package['type']) {
      case 'drupal-core':
      case 'drupal-theme':
      case 'drupal-module':
        $info['type'] = substr($package['type'], 7);
        break;
    }
    $info += $this->buildPackage($package);
    // Dev versions should use git branch + revision, otherwise a tag is used.
    if (strstr($package['version'], 'dev')) {
      // 'dev-' prefix indicates a branch-alias. Stripping the dev prefix from
      // the branch name is sufficient.
      // @see https://getcomposer.org/doc/articles/aliases.md
      if (strpos($package['version'], 'dev-') === 0) {
        $info['download']['branch'] = substr($package['version'], 4);
      }
      // Otherwise, leave as is. Version may already use '-dev' suffix.
      else {
        $info['download']['branch'] = $package['version'];
      }
      $info['download']['revision'] = $package['source']['reference'];
    }
    else {
      if ($package['type'] == 'drupal-core') {
        $version = $package['version'];
      }
      else {
        // Make tag versioning Drupal-friendly. 8.1.0-alpha1 => 8.x-1.0-alpha1.
        $version = sprintf(
          '%d.x-%s',
          $package['version']{0},
          substr($package['version'], 2)
        );
      }
      // Make the version Drush make-compatible: 1.x-13.0-beta2 --> 1.13-beta2
      $info['version'] = preg_replace(
        '/^([0-9]+)\.x-([0-9]+)\.[0-9]+(-.+)?/',
        '$1.$2$3',
        $version
      );
      unset($info['download']);
    }
    return $info;
  }
  /**
   * Builds a make structure for any kind of package.
   *
   * @param array $package
   *   The Composer package definition.
   *
   * @return array
   *   The generated make structure.
   */
  protected function buildPackage(array $package) {
    $info = [
      'download' => [
        'type' => 'git',
        'url' => $package['source']['url'],
        'branch' => $package['version'],
        'revision' => $package['source']['reference'],
      ],
    ];
    if (isset($package['extra']['patches_applied'])) {
      $info['patch'] = array_values($package['extra']['patches_applied']);
    }
    return $info;
  }
  /**
   * Determines if a package is a Drupal core, module, theme, or profile.
   *
   * @param array $package
   *   The package info.
   *
   * @return bool
   *   TRUE if the package is a Drupal core, module, theme, or profile;
   *   otherwise FALSE.
   */
  protected function isDrupalPackage(array $package) {
    $package_types = [
      'drupal-core',
      'drupal-module',
      'drupal-theme',
      'drupal-profile',
    ];
    return (
      strpos($package['name'], 'drupal/') === 0 &&
      in_array($package['type'], $package_types)
    );
  }
  /**
   * Determines if a package is an asset library.
   *
   * @param array $package
   *   The package info.
   *
   * @return bool
   *   TRUE if the package is an asset library, otherwise FALSE.
   */
  protected function isLibrary(array $package) {
    $package_types = [
      'drupal-library',
      'bower-asset',
      'npm-asset',
    ];
    return (
      in_array($package['type'], $package_types) &&
      array_key_exists($package['name'], $this->rootPackage->getRequires())
    );
  }
  protected function isgovCMSTheme (array $package) {
    $package_types = [
      'drupal-theme',
    ];
    return (
      in_array($package['type'], $package_types) &&
      array_key_exists($package['name'], $this->rootPackage->getRequires())
    );
  }
}