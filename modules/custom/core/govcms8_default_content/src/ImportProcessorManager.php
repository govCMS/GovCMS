<?php

namespace Drupal\govcms8_default_content;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the GovCMS8 Import Processor plugin manager.
 */
class ImportProcessorManager extends DefaultPluginManager {

  /**
   * Constructs a new ImportProcessorManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ImportProcessor', $namespaces, $module_handler, 'Drupal\govcms8_default_content\ImportProcessorInterface', 'Drupal\govcms8_default_content\Annotation\ImportProcessor');

    $this->alterInfo('govcms8_default_content_import_processor_info');
    $this->setCacheBackend($cache_backend, 'govcms8_default_content_import_processor_plugins');
  }

}
