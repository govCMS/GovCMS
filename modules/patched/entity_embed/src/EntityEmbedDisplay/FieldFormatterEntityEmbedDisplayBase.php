<?php

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\PluginDependencyTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\entity_embed\Entity\EntityEmbedFakeEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for field formatter display plugins.
 */
abstract class FieldFormatterEntityEmbedDisplayBase extends EntityEmbedDisplayBase {
  use PluginDependencyTrait;

  /**
   * The field formatter plugin manager.
   *
   * @var \Drupal\Core\Field\FormatterPluginManager
   */
  protected $formatterPluginManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The field definition.
   *
   * @var \Drupal\Core\Field\BaseFieldDefinition
   */
  protected $fieldDefinition;

  /**
   * The field formatter.
   *
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $fieldFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->formatterPluginManager = $container->get('plugin.manager.field.formatter');
    $instance->typedDataManager = $container->get('typed_data_manager');
    return $instance;
  }

  /**
   * Get the FieldDefinition object required to render this field's formatter.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   The field definition.
   *
   * @see \Drupal\entity_embed\FieldFormatterEntityEmbedDisplayBase::build()
   */
  public function getFieldDefinition() {
    if (!isset($this->fieldDefinition)) {
      $field_type = $this->getPluginDefinition()['field_type'];
      $this->fieldDefinition = BaseFieldDefinition::create($field_type);
      // Ensure the field name is unique for each Entity Embed Display plugin
      // instance.
      static $index = 0;
      $this->fieldDefinition->setName('_entity_embed_' . $index++);
    }
    return $this->fieldDefinition;
  }

  /**
   * Get the field value required to pass into the field formatter.
   *
   * @return mixed
   *   The field value.
   */
  abstract public function getFieldValue();

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    return parent::access($account)->andIf($this->isApplicableFieldFormatter());
  }

  /**
   * Checks if the field formatter is applicable.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Returns the access result.
   */
  protected function isApplicableFieldFormatter() {
    $definition = $this->formatterPluginManager()->getDefinition($this->getFieldFormatterId());
    return AccessResult::allowedIf($definition['class']::isApplicable($this->getFieldDefinition()));
  }

  /**
   * Returns the field formatter id.
   *
   * @return string|null
   *   Returns field formatter id or null.
   */
  public function getFieldFormatterId() {
    return $this->getDerivativeId();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Create a temporary entity to which our fake field value can be
    // added.
    $fakeEntity = EntityEmbedFakeEntity::create(['type' => '_entity_embed']);

    $definition = $this->getFieldDefinition();

    /* @var \Drupal\Core\Field\FieldItemListInterface $items $items */
    // Create a field item list object, 1 is the value, array('target_id' => 1)
    // would work too, or multiple values. 1 is passed down from the list to the
    // field item, which knows that an integer is the ID.
    $items = $this->typedDataManager->create(
      $definition,
      $this->getFieldValue($definition),
      $definition->getName(),
      $fakeEntity->getTypedData()
    );

    // Prepare, expects an array of items, keyed by parent entity ID.
    $formatter = $this->getFieldFormatter();
    $formatter->prepareView([$fakeEntity->id() => $items]);
    $build = $formatter->viewElements($items, $this->getLangcode());
    // For some reason $build[0]['#printed'] is TRUE, which means it will fail
    // to render later. So for now we manually fix that.
    // @todo Investigate why this is needed.
    show($build[0]);
    return $build[0];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return $this->formatterPluginManager()->getDefaultSettings($this->getFieldFormatterId());
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $this->getFieldFormatter()->settingsForm($form, $form_state);
  }

  /**
   * Constructs a field formatter.
   *
   * @return \Drupal\Core\Field\FormatterInterface
   *   The formatter object.
   */
  public function getFieldFormatter() {
    if (!isset($this->fieldFormatter)) {
      $display = [
        'type' => $this->getFieldFormatterId(),
        'settings' => $this->getConfiguration(),
        'label' => 'hidden',
      ];

      // Create the formatter plugin. Will use the default formatter for that
      // field type if none is passed.
      $this->fieldFormatter = $this->formatterPluginManager->getInstance(
        [
          'field_definition' => $this->getFieldDefinition(),
          'view_mode' => '_entity_embed',
          'configuration' => $display,
        ]
      );
    }

    return $this->fieldFormatter;
  }

  /**
   * Creates a new faux-field definition.
   *
   * @param string $type
   *   The type of the field.
   *
   * @return \Drupal\Core\Field\BaseFieldDefinition
   *   A new field definition.
   */
  protected function createFieldDefinition($type) {
    $definition = BaseFieldDefinition::create($type);
    static $index = 0;
    $definition->setName('_entity_embed_' . $index++);
    return $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $this->addDependencies(parent::calculateDependencies());

    $definition = $this->formatterPluginManager()->getDefinition($this->getFieldFormatterId());
    $this->addDependency('module', $definition['provider']);
    // @todo Investigate why this does not work currently.
    // $this->calculatePluginDependencies($this->getFieldFormatter());
    return $this->dependencies;
  }

  /**
   * Gets the field formatter plugin manager.
   *
   * This is needed for the defaultConfiguration() method since that gets
   * invoked via the constructor, and our services are injected after the
   * constructor are called.
   *
   * @return \Drupal\Core\Field\FormatterPluginManager
   */
  public function formatterPluginManager() {
    if (!isset($this->formatterPluginManager)) {
      $this->formatterPluginManager = \Drupal::service('plugin.manager.field.formatter');
    }
    return $this->formatterPluginManager;
  }

}
