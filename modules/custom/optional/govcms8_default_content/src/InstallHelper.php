<?php

namespace Drupal\govcms8_default_content;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Defines a helper class for importing default content.
 *
 * @internal
 *   This code is only for use by the GovCMS8
 */
class InstallHelper implements ContainerInjectionInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * State.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a new InstallHelper object.
   *
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param \Drupal\Core\State\StateInterface $state
   *   State service.
   */
  public function __construct(AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler, StateInterface $state) {
    $this->aliasManager = $aliasManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path.alias_manager'),
      $container->get('entity_type.manager'),
      $container->get('module_handler'),
      $container->get('state')
    );
  }

  /**
   * Imports default contents.
   */
  public function importContent() {
    // $this->importParagraphs();
    $this->importMediaImages();
    $this->importTaxonomyTerms();
    $this->importPages();
  }

  /**
   * Imports pages.
   */
  protected function importPages() {
    $data = $this->loadDataArray('node');
    if (!empty($data)) {

      $uuids = [];

      foreach ($data as $item) {

        // Prepare content.
        $values = [
          'type' => $item['type'],
          'title' => $item['title'],
        ];
        // Fields mapping starts.
        // Set Body Field.
        if (!empty($item['body'])) {
          $body = $this->getBodyData($item['body']);
          if ($body !== FALSE) {
            $values['body'] = [
              'value' => $body,
              'format' => 'rich_text',
            ];
          }
        }

        if (!empty($item['summary'])) {
          $values['body']['summary'] = $item['summary'];
        }
        // Set node alias if exists.
        if (!empty($item['path'])) {
          $values['path'] = [
            'alias' => $item['path'],
            'pathauto' => 0,
          ];
        }

        $this->loadProcessorPlugins($values, $item, 'node');

        // Set article author.
        $values['uid'] = 1;

        // Create Node.
        $node = $this->entityTypeManager->getStorage('node')->create($values);
        $node->setPublished(TRUE);
        $node->set('moderation_state', "published");
        $node->save();

        // Create menu links.
        if (!empty($item['menu'])) {
          $item['menu']['link'] = [
            'uri' => 'internal:/node/' . $node->id(),
          ];
          $menu_link = $this->entityTypeManager->getStorage('menu_link_content')->create($item['menu']);
          $menu_link->save();
        }
        // Set page to front page is 'front_page' is equals TRUE.
        if (!empty($item['front_page']) && $item['front_page'] == TRUE) {
          \Drupal::service('config.factory')->getEditable('system.site')->set('page.front', '/node/' . $node->id())->save();
        }

        $uuids[$node->uuid()] = 'node';
      }
      $this->storeCreatedContentUuids($uuids);
    }
    return $this;
  }

  /**
   * Import media assets.
   */
  protected function importMediaImages() {
    $data = $this->loadDataArray('media_image');
    if (!empty($data)) {

      $uuids = [];

      foreach ($data as $item) {

        // Prepare content.
        $values = [
          'bundle' => $item['bundle'],
          'name' => $item['name'],
        ];

        $file = $this->saveFile($item['file']);

        if (!empty($file) && $file instanceof File) {
          $values['field_media_image'] = [
            'target_id' => $file->id(),
            'alt' => $item['alt'],
          ];
        }

        // Set author.
        $values['uid'] = 1;

        // Create Media.
        $entity = $this->entityTypeManager->getStorage('media')->create($values);
        $entity->save();
        $uuids[$entity->uuid()] = 'media';
      }
      $this->storeCreatedContentUuids($uuids);
    }
    return $this;
  }

  /**
   * Import taxonomy terms.
   */
  protected function importTaxonomyTerms() {
    $data = $this->loadDataArray('taxonomy');
    if (!empty($data)) {

      $uuids = [];

      foreach ($data as $item) {

        // Prepare content.
        $values = [
          'vid' => $item['vid'],
          'name' => $item['name'],
        ];

        // Set author.
        $values['uid'] = 1;

        // Create Media.
        $entity = $this->entityTypeManager->getStorage('taxonomy_term')->create($values);
        $entity->save();
        $uuids[$entity->uuid()] = 'taxonomy_term';
      }
      $this->storeCreatedContentUuids($uuids);
    }
    return $this;
  }

  /**
   * Import paragraphs.
   */
  public function importParagraphs(array $paragraphs) {

    $data = $this->loadDataArray('paragraph');
    $paragraph_items = [];
    $uuids = [];

    foreach ($paragraphs as $k => $paragraph_data) {
      $item = $data[$paragraph_data];
      if (!empty($item)) {

        // Prepare content.
        $values = [
          'type' => $item['type'],
        ];

        if (!empty($item['field_body'])) {
          $body = $this->getBodyData($item['field_body']);
          if ($body !== FALSE) {
            $values['field_body'] = [
              'value' => $body,
              'format' => 'rich_text',
            ];
          }
        }

        if (!empty($item['field_title'])) {
          $values['field_title'] = $item['field_title'];
        }

        if (!empty($item['field_heading'])) {
          $values['field_heading'] = $item['field_heading'];
        }

        $this->loadProcessorPlugins($values, $item, 'paragraph');

        // Set author.
        $values['uid'] = 1;

        // Create Media.
        $entity = $this->entityTypeManager->getStorage('paragraph')->create($values);
        $entity->save();
        $uuids[$entity->uuid()] = 'paragraph';
        $paragraph_items[] = $entity;
      }
    }

    $this->storeCreatedContentUuids($uuids);

    return $paragraph_items;
  }

  /**
   * Retrieves the body data from the array value or an HTML file.
   *
   * @param mixed $body
   *   Body field.
   *
   * @return mixed
   *   An array of data.
   */
  public function getBodyData($body) {
    $module_path = $this->moduleHandler->getModule('govcms8_default_content')->getPath();
    if (!empty($body)) {
      if (is_array($body) && !empty($body['file'])) {
        $file = $body['file'];
        $body_path = $module_path . '/import/html_body/' . $file;
        $body_html = file_get_contents($body_path);
        if ($body_html !== FALSE) {
          return $body_html;
        }
      }
      else {
        return $body;
      }
    }
  }

  /**
   * Helper method used to load the correct plugin.
   */
  public function loadProcessorPlugins(&$values, $item, $entity_type) {
    $manager = \Drupal::service('plugin.manager.import_processor');
    $import_processors = $manager->getDefinitions();
    foreach ($import_processors as $plugin_id => $import_processor) {
      if ($import_processor['type'] == $entity_type . ':' . $item['type']) {
        /** @var \Drupal\govcms8_default_content\ImportProcessorBase $processor */
        $processor = $manager->createInstance($plugin_id);
        $processor->setItem($item);
        $processor->process($values);
      }
    }
  }

  /**
   * Save a file during the media import.
   */
  public function saveFile($file_name) {
    $path = 'public://govcms8-demo';
    if (file_prepare_directory($path, FILE_CREATE_DIRECTORY)) {
      $source = DRUPAL_ROOT . '/' . drupal_get_path('module', 'govcms8_default_content') . '/import/images/' . $file_name;
      $data = file_get_contents($source);
      return file_save_data($data, "public://govcms8-demo/" . $file_name, FILE_EXISTS_RENAME);
    }
  }

  /**
   * Helper method used to get the correct include and return data array.
   *
   * @param string $type
   *   Data type.
   *
   * @return mixed
   *   An array of data.
   */
  public function loadDataArray($type) {
    module_load_include('inc', 'govcms8_default_content', 'import/govcms8_default_content.' . $type);
    $return = 'govcms8_default_content_default_' . $type;
    $data = $return();
    if (!empty($data)) {
      return $data;
    }
  }

  /**
   * Deletes any content imported by this module.
   *
   * @return $this
   */
  public function deleteImportedContent() {
    $uuids = $this->state->get('govcms8_default_content_uuids', []);
    $by_entity_type = array_reduce(array_keys($uuids), function ($carry, $uuid) use ($uuids) {
      $entity_type_id = $uuids[$uuid];
      $carry[$entity_type_id][] = $uuid;
      return $carry;
    }, []);
    foreach ($by_entity_type as $entity_type_id => $entity_uuids) {
      $storage = $this->entityTypeManager->getStorage($entity_type_id);
      $entities = $storage->loadByProperties(['uuid' => $entity_uuids]);
      $storage->delete($entities);
    }
    return $this;
  }

  /**
   * Looks up a user by name, if it is missing the user is created.
   *
   * @param string $name
   *   Username.
   *
   * @return int
   *   User ID.
   */
  protected function getUser($name) {
    $user_storage = $this->entityTypeManager->getStorage('user');
    $users = $user_storage->loadByProperties(['name' => $name]);;
    if (empty($users)) {
      // Creating user without any email/password.
      $user = $user_storage->create([
        'name' => $name,
        'status' => 1,
      ]);
      $user->enforceIsNew();
      $user->save();
      $this->storeCreatedContentUuids([$user->uuid() => 'user']);
      return $user->id();
    }
    $user = reset($users);
    return $user->id();
  }

  /**
   * Looks up a term by name, if it is missing the term is created.
   *
   * @param string $term_name
   *   Term name.
   * @param string $vocabulary_id
   *   Vocabulary ID.
   *
   * @return int
   *   Term ID.
   */
  protected function getTerm($term_name, $vocabulary_id = 'tags') {
    $term_name = trim($term_name);
    $term_storage = $this->entityTypeManager->getStorage('taxonomy_term');
    $terms = $term_storage->loadByProperties([
      'name' => $term_name,
      'vid' => $vocabulary_id,
    ]);
    if (!$terms) {
      $term = $term_storage->create([
        'name' => $term_name,
        'vid' => $vocabulary_id,
        'path' => ['alias' => '/' . Html::getClass($vocabulary_id) . '/' . Html::getClass($term_name)],
      ]);
      $term->save();
      $this->storeCreatedContentUuids([$term->uuid() => 'taxonomy_term']);
      return $term->id();
    }
    $term = reset($terms);
    return $term->id();
  }

  /**
   * Creates a file entity based on an image path.
   *
   * @param string $path
   *   Image path.
   *
   * @return int
   *   File ID.
   */
  protected function createFileEntity($path) {
    $uri = $this->fileUnmanagedCopy($path);
    $file = $this->entityTypeManager->getStorage('file')->create([
      'uri' => $uri,
      'status' => 1,
    ]);
    $file->save();
    $this->storeCreatedContentUuids([$file->uuid() => 'file']);
    return $file->id();
  }

  /**
   * Stores record of content entities created by this import.
   *
   * @param array $uuids
   *   Array of UUIDs where the key is the UUID and the value is the entity
   *   type.
   */
  protected function storeCreatedContentUuids(array $uuids) {
    $uuids = $this->state->get('govcms8_default_content_uuids', []) + $uuids;
    $this->state->set('govcms8_default_content_uuids', $uuids);
  }

  /**
   * Wrapper around file_unmanaged_copy().
   *
   * @param string $path
   *   Path to image.
   *
   * @return string|false
   *   The path to the new file, or FALSE in the event of an error.
   */
  protected function fileUnmanagedCopy($path) {
    $filename = basename($path);
    return file_unmanaged_copy($path, 'public://' . $filename, FILE_EXISTS_REPLACE);
  }

}
