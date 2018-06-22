<?php

namespace Drupal\govcms8_default_content;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for GovCMS8 Import Processor plugins.
 */
abstract class ImportProcessorBase extends PluginBase implements ImportProcessorInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  protected $item = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entityTypeManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * Get item property.
   *
   * @return array
   *   An array of the item that needs to be imported.
   */
  public function getItem() {
    return $this->item;
  }

  /**
   * Set item property.
   *
   * @param array $item
   *   An array of the item that needs to be imported.
   */
  public function setItem(array $item) {
    $this->item = $item;
  }

  /**
   * Load entity by property.
   *
   * @param string $entity_type
   *   Entity type.
   * @param array $values
   *   An array of properties you want to filter by.
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed
   *   An arary of entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function loadEntitiesByProperties($entity_type, array $values) {
    $entities = $this->entityTypeManager->getStorage($entity_type)->loadByProperties($values);

    if (!empty($entities)) {
      return $entities;
    }
  }

  /**
   * Maps a base field.
   *
   * @param array $values
   *   The values array which is imported.
   * @param string $field_name
   *   The name of the field.
   */
  public function mapBasicField(array &$values, $field_name) {
    $item = $this->item;
    if (!empty($item[$field_name])) {
      $values[$field_name] = $item[$field_name];
    }
  }

  /**
   * Maps a paragraph field.
   *
   * @param array $paragraphs
   *   An array of paragraph machine names.
   *
   * @return array
   *   An array of entities.
   */
  public function mapParagraphField(array $paragraphs) {
    $entities = \Drupal::classResolver()->getInstanceFromDefinition(InstallHelper::class)->importParagraphs($paragraphs);
    if (!empty($entities)) {
      return $entities;
    }
    return [];
  }

  /**
   * Maps a media field.
   *
   * @param string $field_name
   *   Field name.
   *
   * @return array
   *   Returns media entities.
   */
  public function mapMediaField($field_name) {
    $media = $this->populateMediaField($field_name);
    if (!empty($media)) {
      return $media;
    }
    return [];
  }

  /**
   * Finds a media asset by name.
   *
   * @param string $field_name
   *   Field name.
   *
   * @return array
   *   Returns media entities.
   */
  public function populateMediaField($field_name) {
    $item = $this->item;
    $assets = [];
    if (!empty($item[$field_name])) {
      $data = $this->loadDataArray('media_image');

      foreach ($item[$field_name] as $k => $value) {
        if (!empty($data[$value])) {
          $name = $data[$value]['name'];
          $entities = $this->loadEntitiesByProperties('media', ['name' => $name]);

          foreach ($entities as $entity) {
            $assets[] = ['target_id' => $entity->id()];
          }
        }
      }
      return $assets;
    }
  }

  /**
   * Maps a taxonomy field.
   *
   * @param string $field_name
   *   Field name.
   * @param string $vid
   *   Taxonomy vid.
   *
   * @return array
   *   Returns media entities.
   */
  public function mapTaxonomyTermField($field_name, $vid) {
    $entities = $this->populateTaxonomyTermField($field_name, $vid);
    if (!empty($entities)) {
      return $entities;
    }
    return [];
  }

  /**
   * Finds taxonomy terms by name and vid.
   *
   * @param string $field_name
   *   Field name.
   * @param string $vid
   *   Taxonomy vid.
   *
   * @return array
   *   Returns media entities.
   */
  public function populateTaxonomyTermField($field_name, $vid) {
    $item = $this->item;
    $terms = [];
    if (!empty($item[$field_name])) {
      $data = $this->loadDataArray('taxonomy');

      foreach ($item[$field_name] as $k => $value) {
        if (!empty($data[$value])) {
          $name = $data[$value]['name'];
          $entities = $this->loadEntitiesByProperties('taxonomy_term', ['name' => $name, 'vid' => $vid]);

          foreach ($entities as $entity) {
            $terms[] = ['target_id' => $entity->id()];
          }
        }
      }
      return $terms;
    }
  }

  /**
   * Maps an entities reference field.
   *
   * @param string $field_name
   *   Field name.
   *
   * @return array
   *   Returns entities.
   */
  public function mapEntityReferenceField($field_name) {
    $entities = $this->populateEntityReferenceField($field_name);
    if (!empty($entities)) {
      return $entities;
    }
    return [];
  }

  /**
   * Finds nodes by machine name.
   *
   * @param string $field_name
   *   Field name.
   *
   * @return array
   *   Returns entities.
   */
  public function populateEntityReferenceField($field_name) {
    $item = $this->item;
    $node = [];
    if (!empty($item[$field_name])) {
      $data = $this->loadDataArray('node');

      foreach ($item[$field_name] as $k => $value) {
        if (!empty($data[$value])) {
          $title = $data[$value]['title'];
          $entities = $this->loadEntitiesByProperties('node', ['title' => $title]);

          foreach ($entities as $entity) {
            $node[] = ['target_id' => $entity->id()];
          }
        }
      }
    }
    return $node;
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

}
