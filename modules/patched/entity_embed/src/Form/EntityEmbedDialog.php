<?php

namespace Drupal\entity_embed\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\SetDialogTitleCommand;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\editor\Ajax\EditorDialogSave;
use Drupal\editor\EditorInterface;
use Drupal\embed\EmbedButtonInterface;
use Drupal\entity_browser\Events\Events;
use Drupal\entity_browser\Events\RegisterJSCallbacks;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager;
use Drupal\Component\Serialization\Json;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a form to embed entities by specifying data attributes.
 */
class EntityEmbedDialog extends FormBase {

  /**
   * The entity embed display manager.
   *
   * @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager
   */
  protected $entityEmbedDisplayManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The entity browser.
   *
   * @var \Drupal\entity_browser\EntityBrowserInterface
   */
  protected $entityBrowser;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The entity browser settings from the entity embed button.
   *
   * @var array
   */
  protected $entityBrowserSettings = [];

  /**
   * Constructs a EntityEmbedDialog object.
   *
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayManager $entity_embed_display_manager
   *   The Module Handler.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The Form Builder.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface
   *   The file URL generator.
   */
  public function __construct(EntityEmbedDisplayManager $entity_embed_display_manager, FormBuilderInterface $form_builder, EntityTypeManagerInterface $entity_type_manager, EventDispatcherInterface $event_dispatcher, EntityFieldManagerInterface $entity_field_manager, ModuleHandlerInterface $module_handler, FileUrlGeneratorInterface $file_url_generator) {
    $this->entityEmbedDisplayManager = $entity_embed_display_manager;
    $this->formBuilder = $form_builder;
    $this->entityTypeManager = $entity_type_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->entityFieldManager = $entity_field_manager;
    $this->moduleHandler = $module_handler;
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.entity_embed.display'),
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('event_dispatcher'),
      $container->get('entity_field.manager'),
      $container->get('module_handler'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_embed_dialog';
  }

  /**
   * Loads an entity (in the appropriate translation) given HTML attributes.
   *
   * @param string[] $attributes
   *   An array of HTML attributes, including at least `data-entity-type` and
   *   `data-entity-uuid`, and optionally `data-langcode`.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The requested entity, or NULL.
   */
  protected function loadEntityByAttributes(array $attributes) {
    $entity = $this->entityTypeManager->getStorage($attributes['data-entity-type'])
      ->loadByProperties(['uuid' => $attributes['data-entity-uuid']]);
    $entity = current($entity);
    if ($entity && $entity instanceof TranslatableInterface && !empty($attributes['data-langcode'])) {
      if ($entity->hasTranslation($attributes['data-langcode'])) {
        $entity = $entity->getTranslation($attributes['data-langcode']);
      }
    }

    return $entity;
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\editor\EditorInterface $editor
   *   The editor to which this dialog corresponds.
   * @param \Drupal\embed\EmbedButtonInterface $embed_button
   *   The URL button to which this dialog corresponds.
   */
  public function buildForm(array $form, FormStateInterface $form_state, EditorInterface $editor = NULL, EmbedButtonInterface $embed_button = NULL) {
    $values = $form_state->getValues();
    $input = $form_state->getUserInput();
    // Set embed button element in form state, so that it can be used later in
    // validateForm() function.
    $form_state->set('embed_button', $embed_button);
    $form_state->set('editor', $editor);
    // Initialize entity element with form attributes, if present.
    $entity_element = empty($values['attributes']) ? [] : $values['attributes'];
    $entity_element += empty($input['attributes']) ? [] : $input['attributes'];
    // The default values are set directly from \Drupal::request()->request,
    // provided by the editor plugin opening the dialog.
    if (!$form_state->get('entity_element')) {
      $form_state->set('entity_element', isset($input['editor_object']) ? $input['editor_object'] : []);
    }
    $entity_element += $form_state->get('entity_element');
    $entity_element += [
      'data-entity-type' => $embed_button->getTypeSetting('entity_type'),
      'data-entity-uuid' => '',
      'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
      'data-entity-embed-display-settings' => isset($form_state->get('entity_element')['data-entity-embed-settings']) ? $form_state->get('entity_element')['data-entity-embed-settings'] : [],
    ];
    $form_state->set('entity_element', $entity_element);
    $entity = $this->loadEntityByAttributes($entity_element);
    $form_state->set('entity', $entity ?: NULL);

    if (!$form_state->get('step')) {
      // If an entity has been selected, then always skip to the embed options.
      if ($form_state->get('entity')) {
        $form_state->set('step', 'embed');
      }
      else {
        $form_state->set('step', 'select');
      }
    }

    $form['#tree'] = TRUE;
    $form['#attached']['library'][] = 'editor/drupal.editor.dialog';
    $form['#attached']['library'][] = 'entity_embed/drupal.entity_embed.dialog';
    $form['#prefix'] = '<div id="entity-embed-dialog-form">';
    $form['#suffix'] = '</div>';
    $form['#attributes']['class'][] = 'entity-embed-dialog-step--' . $form_state->get('step');

    $this->loadEntityBrowser($form_state);

    if ($form_state->get('step') == 'select') {
      $form = $this->buildSelectStep($form, $form_state);
    }
    elseif ($form_state->get('step') == 'review') {
      $form = $this->buildReviewStep($form, $form_state);
    }
    elseif ($form_state->get('step') == 'embed') {
      $form = $this->buildEmbedStep($form, $form_state);
    }

    return $form;
  }

  /**
   * Form constructor for the entity selection step.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildSelectStep(array $form, FormStateInterface $form_state) {
    // Entity element is calculated on every AJAX request/submit.
    // See self::buildForm().
    $entity_element = $form_state->get('entity_element');
    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    $embed_button = $form_state->get('embed_button');
    $entity = $form_state->get('entity');

    $form['attributes']['data-entity-type'] = [
      '#type' => 'value',
      '#value' => $entity_element['data-entity-type'],
    ];

    $label = $this->t('Label');
    // Attempt to display a better label if we can by getting it from
    // the label field definition.
    $entity_type = $this->entityTypeManager->getDefinition($entity_element['data-entity-type']);
    if ($entity_type->entityClassImplements(FieldableEntityInterface::class) && $entity_type->hasKey('label')) {
      $field_definitions = $this->entityFieldManager->getBaseFieldDefinitions($entity_type->id());
      if (isset($field_definitions[$entity_type->getKey('label')])) {
        $label = $field_definitions[$entity_type->getKey('label')]->getLabel();
      }
    }

    $form['#title'] = $this->t('Select @type to embed', ['@type' => $entity_type->getSingularLabel()]);

    if ($this->entityBrowser) {
      $this->eventDispatcher->addListener(Events::REGISTER_JS_CALLBACKS, [$this, 'registerJSCallback']);
      $form['entity_browser'] = [
        '#type' => 'entity_browser',
        '#entity_browser' => $this->entityBrowser->id(),
        '#cardinality' => 1,
        '#entity_browser_validators' => [
          'entity_type' => ['type' => $entity_element['data-entity-type']],
        ],
      ];
    }
    else {
      $form['entity_id'] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => $entity_element['data-entity-type'],
        '#title' => $label,
        '#default_value' => $entity,
        '#required' => TRUE,
        '#description' => $this->t('Type label and pick the right one from suggestions. Note that the unique ID will be saved.'),
        '#maxlength' => 255,
      ];
      if ($bundles = $embed_button->getTypeSetting('bundles')) {
        $form['entity_id']['#selection_settings']['target_bundles'] = $bundles;
      }
    }

    if (!empty($entity_element['data-langcode'])) {
      $form['attributes']['data-langcode'] = [
        '#type' => 'hidden',
        '#value' => $entity_element['data-langcode'],
      ];
    }

    $form['attributes']['data-entity-uuid'] = [
      '#type' => 'value',
      '#title' => $entity_element['data-entity-uuid'],
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitSelectStep',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-next',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Form constructor for the entity review step.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildReviewStep(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $form_state->get('entity');

    $form['#title'] = $this->t('Review selected @type', ['@type' => $entity->getEntityType()->getSingularLabel()]);

    $form['selection'] = [
      '#markup' => $entity->label(),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Replace selection'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitAndShowSelect',
        'event' => 'click',
      ],
    ];

    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitAndShowEmbed',
        'event' => 'click',
      ],
      '#attributes' => [
        'class' => [
          'js-button-next',
        ],
      ],
    ];

    return $form;
  }

  /**
   * Form constructor for the entity embedding step.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildEmbedStep(array $form, FormStateInterface $form_state) {
    // Entity element is calculated on every AJAX request/submit.
    // See self::buildForm().
    $entity_element = $form_state->get('entity_element');
    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    $embed_button = $form_state->get('embed_button');
    /** @var \Drupal\editor\EditorInterface $editor */
    $editor = $form_state->get('editor');
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $form_state->get('entity');
    $values = $form_state->getValues();

    $form['#title'] = $this->t('Embed @type', ['@type' => $entity->getEntityType()->getSingularLabel()]);

    try {
      if ($entity->getEntityType()->hasLinkTemplate('canonical')) {
        $options = [
          'attributes' => [
            'target' => '_blank',
          ],
        ];
        $entity_label = $entity->toLink($entity->label(), 'canonical', $options)->toString();
      }
      elseif ($entity->getEntityTypeId() == 'file') {
          $entity_label = '<a href="' . $this->fileUrlGenerator->generateAbsoluteString($entity->getFileUri()) . '" target="_blank">' . $entity->label() . '</a>';
      }
      else {
        $entity_label = '<a href="' . $entity->toUrl()->toString() . '" target="_blank">' . $entity->label() . '</a>';
      }
    }
    catch (\Exception $e) {
      $entity_label = $entity->label();
    }

    $form['entity'] = [
      '#type' => 'item',
      '#title' => $this->t('Selected entity'),
      '#markup' => $entity_label,
    ];
    $form['attributes']['data-entity-type'] = [
      '#type' => 'hidden',
      '#value' => $entity_element['data-entity-type'],
    ];
    $form['attributes']['data-entity-uuid'] = [
      '#type' => 'hidden',
      '#value' => $entity_element['data-entity-uuid'],
    ];

    if (!empty($entity_element['data-langcode'])) {
      $form['attributes']['data-langcode'] = [
        '#type' => 'hidden',
        '#value' => $entity_element['data-langcode'],
      ];
    }

    // Build the list of allowed Entity Embed Display plugins.
    $display_plugin_options = $this->getDisplayPluginOptions($embed_button, $entity);

    // If the currently selected display is not in the available options,
    // use the first from the list instead. This can happen if an alter
    // hook customizes the list based on the entity.
    if (!isset($display_plugin_options[$entity_element['data-entity-embed-display']])) {
      $entity_element['data-entity-embed-display'] = key($display_plugin_options);
    }

    // The default Entity Embed Display plugin has been deprecated by the
    // rendered entity field formatter.
    if ($entity_element['data-entity-embed-display'] === 'default') {
      $entity_element['data-entity-embed-display'] = 'entity_reference:entity_reference_entity_view';
    }

    $form['attributes']['data-entity-embed-display'] = [
      '#type' => 'select',
      '#title' => $this->t('Display as'),
      '#options' => $display_plugin_options,
      '#default_value' => $entity_element['data-entity-embed-display'],
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updatePluginConfigurationForm',
        'wrapper' => 'data-entity-embed-display-settings-wrapper',
        'effect' => 'fade',
      ],
      // Hide the selection if only one option is available.
      '#access' => count($display_plugin_options) > 1,
    ];
    $form['attributes']['data-entity-embed-display-settings'] = [
      '#type' => 'container',
      '#prefix' => '<div id="data-entity-embed-display-settings-wrapper">',
      '#suffix' => '</div>',
    ];
    $form['attributes']['data-embed-button'] = [
      '#type' => 'value',
      '#value' => $embed_button->id(),
    ];
    $plugin_id = !empty($values['attributes']['data-entity-embed-display']) ? $values['attributes']['data-entity-embed-display'] : $entity_element['data-entity-embed-display'];
    if (!empty($plugin_id)) {
      if (empty($entity_element['data-entity-embed-display-settings'])) {
        $entity_element['data-entity-embed-display-settings'] = [];
      }
      elseif (is_string($entity_element['data-entity-embed-display-settings'])) {
        $entity_element['data-entity-embed-display-settings'] = Json::decode($entity_element['data-entity-embed-display-settings']) ?: [];
      }
      $display = $this->entityEmbedDisplayManager->createInstance($plugin_id, $entity_element['data-entity-embed-display-settings']);
      $display->setContextValue('entity', $entity);
      $display->setAttributes($entity_element);
      $form['attributes']['data-entity-embed-display-settings'] += $display->buildConfigurationForm($form, $form_state);
    }

    // When Drupal core's filter_align is being used, the text editor may
    // offer the ability to change the alignment.
    if ($editor->getFilterFormat()->filters('filter_align')->status) {
      $form['attributes']['data-align'] = [
        '#title' => $this->t('Align'),
        '#type' => 'radios',
        '#options' => [
          '' => $this->t('None'),
          'left' => $this->t('Left'),
          'center' => $this->t('Center'),
          'right' => $this->t('Right'),
        ],
        '#default_value' => isset($entity_element['data-align']) ? $entity_element['data-align'] : '',
        '#wrapper_attributes' => ['class' => ['container-inline']],
        '#attributes' => ['class' => ['container-inline']],
      ];
    }

    // When Drupal core's filter_caption is being used, the text editor may
    // offer the ability to add a caption.
    if ($editor->getFilterFormat()->filters('filter_caption')->status) {
      $form['attributes']['data-caption'] = [
        '#title' => $this->t('Caption'),
        '#type' => 'textarea',
        '#rows' => 3,
        '#default_value' => isset($entity_element['data-caption']) ? Html::decodeEntities($entity_element['data-caption']) : '',
        '#element_validate' => ['::escapeValue'],
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['back'] = [
      '#type' => 'submit',
      '#value' => $this->t('Back'),
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => !empty($this->entityBrowserSettings['display_review']) ? '::submitAndShowReview' : '::submitAndShowSelect',
        'event' => 'click',
      ],
    ];
    $form['actions']['save_modal'] = [
      '#type' => 'submit',
      '#value' => $this->t('Embed'),
      '#button_type' => 'primary',
      // No regular submit-handler. This form only works via JavaScript.
      '#submit' => [],
      '#ajax' => [
        'callback' => '::submitEmbedStep',
        'event' => 'click',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($form_state->get('step') == 'select') {
      $this->validateSelectStep($form, $form_state);
    }
    elseif ($form_state->get('step') == 'embed') {
      $this->validateEmbedStep($form, $form_state);
    }
  }

  /**
   * Form validation handler for the entity selection step.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateSelectStep(array $form, FormStateInterface $form_state) {
    if ($form_state->hasValue(['entity_browser', 'entities'])) {
      if (count($form_state->getValue(['entity_browser', 'entities'])) > 0) {
        $id = $form_state->getValue(['entity_browser', 'entities', 0])->id();
      }
      $element = $form['entity_browser'];
    }
    else {
      $id = trim($form_state->getValue(['entity_id']));
      $element = $form['entity_id'];
    }

    $entity_type = $form_state->getValue(['attributes', 'data-entity-type']);

    if (!isset($id)) {
      $form_state->setError($element, $this->t('No entity selected.'));
      return;
    }
    if ($entity = $this->entityTypeManager->getStorage($entity_type)->load($id)) {
      if (!$entity->access('view')) {
        $form_state->setError($element, $this->t('Unable to access @type entity @id.', ['@type' => $entity_type, '@id' => $id]));
      }
      else {
        if ($uuid = $entity->uuid()) {
          $form_state->setValueForElement($form['attributes']['data-entity-uuid'], $uuid);
        }
        else {
          $form_state->setError($element, $this->t('Cannot embed @type entity @id because it does not have a UUID.', ['@type' => $entity_type, '@id' => $id]));
        }

        // Ensure that at least one Entity Embed Display plugin is present
        // before proceeding to the next step. Raise an error otherwise.
        $embed_button = $form_state->get('embed_button');
        $display_plugin_options = $this->getDisplayPluginOptions($embed_button, $entity);
        // If no plugin is available after taking the intersection, raise error.
        // Also log an exception.
        if (empty($display_plugin_options)) {
          $form_state->setError($element, $this->t('No display options available for the selected %entity-type. Please select another %entity_type.', ['%entity_type' => $entity->getEntityType()->getLabel()]));
          $this->logger('entity_embed')->warning('No display options available for "@type:" entity "@id" while embedding using button "@button". Please ensure that at least one Entity Embed Display plugin is allowed for this embed button which is available for this entity.', [
            '@type' => $entity_type,
            '@id' => $entity->id(),
            '@button' => $embed_button->id(),
          ]);
        }
      }
    }
    else {
      $form_state->setError($element, $this->t('Unable to load @type entity @id.', ['@type' => $entity_type, '@id' => $id]));
    }
  }

  /**
   * Form validation handler for the entity embedding step.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateEmbedStep(array $form, FormStateInterface $form_state) {
    // Validate configuration forms for the Entity Embed Display plugin used.
    $entity_element = $form_state->getValue('attributes');
    $entity = $form_state->get('entity');
    $plugin_id = $entity_element['data-entity-embed-display'];
    $plugin_settings = !empty($entity_element['data-entity-embed-display-settings']) ? $entity_element['data-entity-embed-display-settings'] : [];
    $display = $this->entityEmbedDisplayManager->createInstance($plugin_id, $plugin_settings);
    $display->setContextValue('entity', $entity);
    $display->setAttributes($entity_element);
    $display->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**
   * Form submission handler to update the plugin configuration form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function updatePluginConfigurationForm(array &$form, FormStateInterface $form_state) {
    return $form['attributes']['data-entity-embed-display-settings'];
  }

  /**
   * Form submission handler to to another step of the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param string $step
   *   The next step name, such as 'select', 'review' or 'embed'.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitStep(array &$form, FormStateInterface $form_state, $step) {
    $response = new AjaxResponse();

    $form_state->set('step', $step);
    $form_state->setRebuild(TRUE);
    $rebuild_form = $this->formBuilder->rebuildForm('entity_embed_dialog', $form_state, $form);
    unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
    $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $rebuild_form));
    $response->addCommand(new SetDialogTitleCommand('', $rebuild_form['#title']));

    return $response;
  }

  /**
   * Form submission handler for the entity selection step.
   *
   * On success will send the user to the next step of the form to select the
   * embed display settings. On form errors, this will rebuild the form and
   * display the error messages.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitSelectStep(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Display errors in form, if any.
    if ($form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $form));
    }
    else {
      $form_state->set('step', !empty($this->entityBrowserSettings['display_review']) ? 'review' : 'embed');
      $form_state->setRebuild(TRUE);
      $rebuild_form = $this->formBuilder->rebuildForm('entity_embed_dialog', $form_state, $form);
      unset($rebuild_form['#prefix'], $rebuild_form['#suffix']);
      $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $rebuild_form));
      $response->addCommand(new SetDialogTitleCommand('', $rebuild_form['#title']));
    }

    return $response;
  }

  /**
   * Submit and show select step after submit.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitAndShowSelect(array &$form, FormStateInterface $form_state) {
    return $this->submitStep($form, $form_state, 'select');
  }

  /**
   * Submit and show review step after submit.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitAndShowReview(array &$form, FormStateInterface $form_state) {
    return $this->submitStep($form, $form_state, 'review');
  }

  /**
   * Submit and show embed step after submit.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitAndShowEmbed(array $form, FormStateInterface $form_state) {
    return $this->submitStep($form, $form_state, 'embed');
  }

  /**
   * Form submission handler for the entity embedding step.
   *
   * On success this will submit the command to save the embedded entity with
   * the configured display settings to the WYSIWYG element, and then close the
   * modal dialog. On form errors, this will rebuild the form and display the
   * error messages.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response.
   */
  public function submitEmbedStep(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Submit configuration form the selected Entity Embed Display plugin.
    $entity_element = $form_state->getValue('attributes');
    $entity = $this->loadEntityByAttributes($entity_element);
    $plugin_id = $entity_element['data-entity-embed-display'];
    $plugin_settings = !empty($entity_element['data-entity-embed-display-settings']) ? $entity_element['data-entity-embed-display-settings'] : [];
    $display = $this->entityEmbedDisplayManager->createInstance($plugin_id, $plugin_settings);
    $display->setContextValue('entity', $entity);
    $display->setAttributes($entity_element);
    $display->submitConfigurationForm($form, $form_state);

    $values = $form_state->getValues();
    // Display errors in form, if any.
    if ($form_state->hasAnyErrors()) {
      unset($form['#prefix'], $form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#entity-embed-dialog-form', $form));
    }
    else {
      // Serialize entity embed settings to JSON string.
      if (!empty($values['attributes']['data-entity-embed-display-settings'])) {
        $values['attributes']['data-entity-embed-display-settings'] = Json::encode($values['attributes']['data-entity-embed-display-settings']);
      }

      // Filter out empty attributes.
      $values['attributes'] = array_filter($values['attributes'], function ($value) {
        return (bool) mb_strlen((string) $value);
      });

      // Allow other modules to alter the values before getting submitted to the
      // WYSIWYG.
      $this->moduleHandler->alter('entity_embed_values', $values, $entity, $display);

      $response->addCommand(new EditorDialogSave($values));
      $response->addCommand(new CloseModalDialogCommand());
    }

    return $response;
  }

  /**
   * Form element validation handler; Escapes the value an element.
   *
   * This should be used for any element in the embed form which may contain
   * HTML that should be serialized as an attribute element on the embed.
   */
  public static function escapeValue($element, FormStateInterface $form_state) {
    if ($value = trim($element['#value'])) {
      $form_state->setValueForElement($element, Html::escape($value));
    }
  }

  /**
   * Returns the allowed display plugins given an embed button and an entity.
   *
   * @param \Drupal\embed\EmbedButtonInterface $embed_button
   *   The embed button.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return array
   *   List of allowed Entity Embed Display plugins.
   */
  public function getDisplayPluginOptions(EmbedButtonInterface $embed_button, EntityInterface $entity) {
    $plugins = $this->entityEmbedDisplayManager->getDefinitionOptionsForContext([
      'entity' => $entity,
      'entity_type' => $entity->getEntityTypeId(),
      'embed_button' => $embed_button,
    ]);

    return $plugins;
  }

  /**
   * Registers JS callbacks.
   *
   * Callbacks are responsible for getting entities from entity browser and
   * updating form values accordingly.
   */
  public function registerJSCallback(RegisterJSCallbacks $event) {
    if ($event->getBrowserID() == $this->entityBrowser->id()) {
      $event->registerCallback('Drupal.entityEmbedDialog.selectionCompleted');
    }
  }

  /**
   * Load the current entity browser and its settings from the form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  protected function loadEntityBrowser(FormStateInterface $form_state) {
    $this->entityBrowser = NULL;
    $this->entityBrowserSettings = [];

    /** @var \Drupal\embed\EmbedButtonInterface $embed_button */
    $embed_button = $form_state->get('embed_button');

    if ($embed_button && $entity_browser_id = $embed_button->getTypePlugin()->getConfigurationValue('entity_browser')) {
      $this->entityBrowser = $this->entityTypeManager->getStorage('entity_browser')->load($entity_browser_id);
      $this->entityBrowserSettings = $embed_button->getTypePlugin()->getConfigurationValue('entity_browser_settings');
    }
  }

}
