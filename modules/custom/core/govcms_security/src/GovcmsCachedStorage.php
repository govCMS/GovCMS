<?php

namespace Drupal\govcms_security;

use Drupal\Core\Config\CachedStorage;
use Drupal\Core\Config\StorageCacheInterface;
use Drupal\Core\Config\StorageInterface;

class GovcmsCachedStorage implements StorageInterface, StorageCacheInterface {

  /**
   * The inner service.
   * 
   * @var CachedStorage
   */
  protected $innerService;
  
  public function __construct(CachedStorage $inner) {
    $this->innerService = $inner;
    //parent::__construct($storage, $cache);
  }
  
  /**
   * {@inheritdoc}
   */
  public function read($name) {    
    $data = $this->innerService->read($name);
    $config_categories = explode('.', $name);
    
    // Override configurations.
    switch ($config_categories[0]) {
      // User configurations
      case 'user' :
        if (isset($data['is_admin'])) {
          // No role should be admin role.
          $data['is_admin'] = false;
        }
        break;
    }
    
    return $data;
  }
  
  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
    $data_to_return = $this->innerService->readMultiple($names);
    
    foreach ($names as $name) {
      $config_categories = explode('.', $name);
      
      // Override configurations.
      switch ($config_categories[0]) {
        case 'user' :
          if (isset($data_to_return[$name]['is_admin'])) {
            // No role should be admin role.
            $data_to_return[$name]['is_admin'] = false;
          }
          break;
      }
    }
    
    return $data_to_return;
  }
  
  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
    $config_categories = explode('.', $name);
    // Override configurations.
    switch ($config_categories[0]) {
      case 'user':
        if (isset($data['is_admin'])) {
          // No role should be admin role.
          $data['is_admin'] = false;
        }
        break;
    }
   
    return $this->innerService->write($name, $data);
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName() {
    return $this->innerService->getCollectionName();
  }
  
  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    return $this->innerService->deleteAll($prefix);
  }
  
  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    return $this->innerService->decode($raw);
  }
  
  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    return $this->innerService->delete($name);
  }
  
  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name) {
    return $this->innerService->rename($name, $new_name);
  }
  
  /**
   * {@inheritdoc}
   */
  public function resetListCache() {
    return $this->innerService->resetListCache();
  }
  
  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return $this->innerService->exists($name);
  }
  
  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    return $this->innerService->getAllCollectionNames();
  }
  
  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    return $this->innerService->listAll($prefix);
  }
  
  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    return $this->innerService->createCollection($collection);
  }
  
  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    return $this->innerService->encode($data);
  }
}

