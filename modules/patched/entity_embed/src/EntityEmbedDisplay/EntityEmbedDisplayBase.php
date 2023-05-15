<?php

namespace Drupal\entity_embed\EntityEmbedDisplay;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base Entity Embed Display implementation.
 *
 * @see \Drupal\entity_embed\Annotation\EntityEmbedDisplay
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
 * @see plugin_api
 *
 * @ingroup entity_embed_api
 */
abstract class EntityEmbedDisplayBase extends PluginBase implements ContainerFactoryPluginInterface, EntityEmbedDisplayInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The context for the plugin.
   *
   * @var array
   */
  public $context = [];

  /**
   * The attributes on the embedded entity.
   *
   * @var array
   */
  public $attributes = [];

  /**
   * Constructs an EntityEmbedDisplayBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    // @todo Add a hook_entity_embed_display_access()?
    // Check that the plugin's registered entity types matches the current
    // entity type.
    return AccessResult::allowedIf($this->isValidEntityType())
      // @see \Drupal\Core\Entity\EntityTypeManager
      ->addCacheTags(['entity_types']);
  }

  /**
   * Validates that this display plugin applies to the current entity type.
   *
   * This checks the plugin annotation's 'entity_types' value, which should be
   * an array of entity types that this plugin can process, or FALSE if the
   * plugin applies to all entity types.
   *
   * @return bool
   *   TRUE if the plugin can display the current entity type, or FALSE
   *   otherwise.
   */
  protected function isValidEntityType() {
    // First, determine whether or not the entity type id is valid. Return FALSE
    // if the specified id is not valid.
    $entity_type = $this->getEntityTypeFromContext();
    if (!$this->entityTypeManager->getDefinition($entity_type)) {
      return FALSE;
    }

    $definition = $this->getPluginDefinition();
    if ($definition['entity_types'] === FALSE) {
      return TRUE;
    }
    else {
      return in_array($entity_type, $definition['entity_types']);
    }
  }

  /**
   * {@inheritdoc}
   */
  abstract public function build();

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $this->configuration = array_intersect_key($form_state->getValues(), $this->defaultConfiguration());
    }
  }

  /**
   * Gets a configuration value.
   *
   * @param string $name
   *   The name of the plugin configuration value.
   * @param mixed $default
   *   The default value to return if the configuration value does not exist.
   *
   * @return mixed
   *   The currently set configuration value, or the value of $default if the
   *   configuration value is not set.
   */
  public function getConfigurationValue($name, $default = NULL) {
    $configuration = $this->getConfiguration();
    return array_key_exists($name, $configuration) ? $configuration[$name] : $default;
  }

  /**
   * Sets the value for a defined context.
   *
   * @param string $name
   *   The name of the context in the plugin definition.
   * @param mixed $value
   *   The value to set the context to. The value has to validate against the
   *   provided context definition.
   */
  public function setContextValue($name, $value) {
    $this->context[$name] = $value;
  }

  /**
   * Gets the values for all defined contexts.
   *
   * @return array
   *   An array of set context values, keyed by context name.
   */
  public function getContextValues() {
    return $this->context;
  }

  /**
   * Gets the value for a defined context.
   *
   * @param string $name
   *   The name of the context in the plugin configuration.
   *
   * @return mixed
   *   The currently set context value.
   */
  public function getContextValue($name) {
    return !empty($this->context[$name]) ? $this->context[$name] : NULL;
  }

  /**
   * Returns whether or not value is set for a defined context.
   *
   * @param string $name
   *   The name of the context in the plugin configuration.
   *
   * @return bool
   *   True if context value exists, false otherwise.
   */
  public function hasContextValue($name) {
    return array_key_exists($name, $this->context);
  }

  /**
   * Gets the entity type from the current context.
   *
   * @return string
   *   The entity type id.
   */
  public function getEntityTypeFromContext() {
    if ($this->hasContextValue('entity')) {
      return $this->getContextValue('entity')->getEntityTypeId();
    }
    else {
      return $this->getContextValue('entity_type');
    }
  }

  /**
   * Gets the entity from the current context.
   *
   * @todo Where does this come from? The value must come from somewhere, yet
   * this does not implement any context-related interfaces. This is an *input*,
   * so we need cache contexts and possibly cache tags to reflect where this
   * came from. We need that for *everything* that this class does that relies
   * on this, plus any of its subclasses. Right now, this is effectively a
   * global that breaks cacheability metadata.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity from the current context.
   */
  public function getEntityFromContext() {
    if ($this->hasContextValue('entity')) {
      return $this->getContextValue('entity');
    }
  }

  /**
   * Sets the values for all attributes.
   *
   * @param array $attributes
   *   An array of attributes, keyed by attribute name.
   */
  public function setAttributes(array $attributes) {
    $this->attributes = $attributes;
  }

  /**
   * Gets the values for all attributes.
   *
   * @return array
   *   An array of set attribute values, keyed by attribute name.
   */
  public function getAttributeValues() {
    return $this->attributes;
  }

  /**
   * Gets the value for an attribute.
   *
   * @param string $name
   *   The name of the attribute.
   * @param mixed $default
   *   The default value to return if the attribute value does not exist.
   *
   * @return mixed
   *   The currently set attribute value.
   */
  public function getAttributeValue($name, $default = NULL) {
    $attributes = $this->getAttributeValues();
    return array_key_exists($name, $attributes) ? $attributes[$name] : $default;
  }

  /**
   * Checks if an attribute is set.
   *
   * @param string $name
   *   The name of the attribute.
   *
   * @return bool
   *   Returns TRUE if value is set.
   */
  public function hasAttribute($name) {
    return array_key_exists($name, $this->getAttributeValues());
  }

  /**
   * Gets the current language code.
   *
   * @return string
   *   The langcode present in the 'data-langcode', if present, or the current
   *   langcode from the language manager, otherwise.
   */
  public function getLangcode() {
    $langcode = $this->getAttributeValue('data-langcode');
    if (empty($langcode)) {
      $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    }
    return $langcode;
  }

}
