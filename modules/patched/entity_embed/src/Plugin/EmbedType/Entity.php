<?php

namespace Drupal\entity_embed\Plugin\EmbedType;

use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginDependencyTrait;
use Drupal\embed\EmbedType\EmbedTypeBase;
use Drupal\entity_browser\EntityBrowserInterface;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity embed type.
 *
 * @EmbedType(
 *   id = "entity",
 *   label = @Translation("Entity")
 * )
 */
class Entity extends EmbedTypeBase implements ContainerFactoryPluginInterface {
  use PluginDependencyTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type repository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  protected $entityTypeRepository;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The Entity Embed Display plugin manager.
   *
   * @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
   */
  protected $displayPluginManager;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entity_type_repository
   *   The entity type repository service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $display_plugin_manager
   *   The plugin manager.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface
   *   The file URL generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityTypeRepositoryInterface $entity_type_repository, EntityTypeBundleInfoInterface $bundle_info, EntityEmbedDisplayManager $display_plugin_manager, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeRepository = $entity_type_repository;
    $this->entityTypeBundleInfo = $bundle_info;
    $this->displayPluginManager = $display_plugin_manager;
    $this->fileUrlGenerator = $file_url_generator;
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
      $container->get('entity_type.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('plugin.manager.entity_embed.display'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity_type' => 'node',
      'bundles' => [],
      'display_plugins' => [],
      'entity_browser' => '',
      'entity_browser_settings' => [
        'display_review' => 0,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $embed_button = $form_state->getTemporaryValue('embed_button');
    $entity_type_id = $this->getConfigurationValue('entity_type');

    $form['entity_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Entity type'),
      '#options' => $this->getEntityTypeOptions(),
      '#default_value' => $entity_type_id,
      '#description' => $this->t("The entity type this button will embed."),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => [$form_state->getFormObject(), 'updateTypeSettings'],
        'effect' => 'fade',
      ],
      '#disabled' => !$embed_button->isNew(),
    ];

    if ($entity_type_id) {
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $form['bundles'] = [
        '#type' => 'checkboxes',
        '#title' => $entity_type->getBundleLabel() ?: $this->t('Bundles'),
        '#options' => $this->getEntityBundleOptions($entity_type),
        '#default_value' => $this->getConfigurationValue('bundles'),
        '#description' => $this->t('If none are selected, all are allowed.'),
      ];
      $form['bundles']['#access'] = !empty($form['bundles']['#options']);

      // Allow option to limit Entity Embed Display plugins.
      $form['display_plugins'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Allowed Entity Embed Display plugins'),
        '#options' => $this->displayPluginManager->getDefinitionOptionsForEntityType($entity_type_id),
        '#default_value' => $this->getConfigurationValue('display_plugins'),
        '#description' => $this->t('If none are selected, all are allowed. Note that these are the plugins which are allowed for this entity type, all of these might not be available for the selected entity.'),
      ];
      $form['display_plugins']['#access'] = !empty($form['display_plugins']['#options']);

      /** @var \Drupal\entity_browser\EntityBrowserInterface[] $browsers */
      if ($this->entityTypeManager->hasDefinition('entity_browser') && ($browsers = $this->entityTypeManager->getStorage('entity_browser')->loadMultiple())) {
        // Filter out unsupported displays & return array of ids and labels.
        $browsers = array_map(
          function ($item) {
            /** @var \Drupal\entity_browser\EntityBrowserInterface $item */
            return $item->label();
          },
          // Filter out both modal and standalone forms as they don't work.
          array_filter($browsers, function (EntityBrowserInterface $browser) {
            return !in_array($browser->getDisplay()->getPluginId(), ['modal', 'standalone'], TRUE);
          })
        );
        $options = ['_none' => $this->t('None (autocomplete)')] + $browsers;
        $form['entity_browser'] = [
          '#type' => 'select',
          '#title' => $this->t('Entity browser'),
          '#description' => $this->t('Entity browser to be used to select entities to be embedded. Only compatible browsers will be available to be chosen.'),
          '#options' => $options,
          '#default_value' => $this->getConfigurationValue('entity_browser'),
        ];
        $form['entity_browser_settings'] = [
          '#type' => 'details',
          '#title' => $this->t('Entity browser settings'),
          '#open' => TRUE,
          '#states' => [
            'invisible' => [
              ':input[name="type_settings[entity_browser]"]' => ['value' => '_none'],
            ],
          ],
        ];
        $form['entity_browser_settings']['display_review'] = [
          '#type' => 'checkbox',
          '#title' => 'Display the entity after selection',
          '#default_value' => $this->getConfigurationValue('entity_browser_settings')['display_review'],
        ];
      }
      else {
        $form['entity_browser'] = [
          '#type' => 'value',
          '#value' => '',
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Filter down the bundles and allowed Entity Embed Display plugins.
    $bundles = $form_state->getValue('bundles');
    $form_state->setValue('bundles', array_keys(array_filter($bundles)));
    $display_plugins = $form_state->getValue('display_plugins');
    $form_state->setValue('display_plugins', array_keys(array_filter($display_plugins)));
    $entity_browser = $form_state->getValue('entity_browser') == '_none' ? '' : $form_state->getValue('entity_browser');
    $form_state->setValue('entity_browser', $entity_browser);
    $form_state->setValue('entity_browser_settings', $form_state->getValue('entity_browser_settings', []));

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Builds a list of entity type options.
   *
   * Configuration entity types without a view builder are filtered out while
   * all other entity types are kept.
   *
   * @return array
   *   An array of entity type labels, keyed by entity type name.
   */
  protected function getEntityTypeOptions() {
    $options = $this->entityTypeRepository->getEntityTypeLabels(TRUE);

    foreach ($options as $group => $group_types) {
      foreach (array_keys($group_types) as $entity_type_id) {
        // Filter out entity types that do not have a view builder class.
        if (!$this->entityTypeManager->getDefinition($entity_type_id)->hasViewBuilderClass()) {
          unset($options[$group][$entity_type_id]);
        }
        // Filter out entity types that do not support UUIDs.
        elseif (!$this->entityTypeManager->getDefinition($entity_type_id)->hasKey('uuid')) {
          unset($options[$group][$entity_type_id]);
        }
        // Filter out entity types that will not have any Entity Embed Display
        // plugins.
        elseif (!$this->displayPluginManager->getDefinitionOptionsForEntityType($entity_type_id)) {
          unset($options[$group][$entity_type_id]);
        }
      }
    }

    return $options;
  }

  /**
   * Builds a list of entity type bundle options.
   *
   * Configuration entity types without a view builder are filtered out while
   * all other entity types are kept.
   *
   * @return array
   *   An array of bundle labels, keyed by bundle name.
   */
  protected function getEntityBundleOptions(EntityTypeInterface $entity_type) {
    $bundle_options = [];
    // If the entity has bundles, allow option to restrict to bundle(s).
    if ($entity_type->hasKey('bundle')) {
      foreach ($this->entityTypeBundleInfo->getBundleInfo($entity_type->id()) as $bundle_id => $bundle_info) {
        $bundle_options[$bundle_id] = $bundle_info['label'];
      }
      natsort($bundle_options);
    }
    return $bundle_options;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultIconUrl() {
    return $this->fileUrlGenerator->generateAbsoluteString(\Drupal::service('extension.list.module')->getPath('entity_embed') . '/js/plugins/drupalentity/entity.png');
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $this->addDependencies(parent::calculateDependencies());

    $entity_type_id = $this->getConfigurationValue('entity_type');
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $this->addDependency('module', $entity_type->getProvider());

    // Calculate bundle dependencies.
    foreach ($this->getConfigurationValue('bundles') as $bundle) {
      $bundle_dependency = $entity_type->getBundleConfigDependency($bundle);
      $this->addDependency($bundle_dependency['type'], $bundle_dependency['name']);
    }

    // Calculate display Entity Embed Display dependencies.
    foreach ($this->getConfigurationValue('display_plugins') as $display_plugin) {
      $instance = $this->displayPluginManager->createInstance($display_plugin);
      $this->calculatePluginDependencies($instance);
    }

    $entity_browser = $this->getConfigurationValue('entity_browser');
    if ($entity_browser && $this->entityTypeManager->hasDefinition('entity_browser')) {
      $browser = $this->entityTypeManager
        ->getStorage('entity_browser')
        ->load($entity_browser);
      if ($browser) {
        $this->addDependency($browser->getConfigDependencyKey(), $browser->getConfigDependencyName());
      }
    }

    return $this->dependencies;
  }

}
