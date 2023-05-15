<?php

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\media\MediaInterface;

/**
 * Decorator on all EntityEmbedDisplays that adds alt and title overriding.
 */
class MediaImageDecorator implements EntityEmbedDisplayInterface {

  use StringTranslationTrait;

  /**
   * A string that signifies not to render the alt text.
   *
   * @const string
   */
  const EMPTY_STRING = '""';

  /**
   * The decorated EntityEmbedDisplay class.
   *
   * @var \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
   */
  private $decorated;

  /**
   * MediaImageDecorator constructor.
   *
   * @param \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface $decorated
   *   The decorated EntityEmbedDisplay plugin.
   */
  public function __construct(EntityEmbedDisplayInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * Passes through all unknown calls to the decorated object.
   */
  public function __call($method, $args) {
    return call_user_func_array([$this->decorated, $method], $args);
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account = NULL) {
    return $this->decorated->access($account);
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    return $this->decorated->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return $this->decorated->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return $this->decorated->calculateDependencies();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->decorated->getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->decorated->getPluginDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->decorated->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    return $this->decorated->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->decorated->buildConfigurationForm($form, $form_state);

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->decorated->getEntityFromContext();

    if ($image_field = $this->getMediaImageSourceField($entity)) {

      $settings = $entity->{$image_field}->getItemDefinition()->getSettings();
      $attributes = $this->getAttributeValues();

      $alt = isset($attributes['alt']) ? $attributes['alt'] : NULL;
      $title = isset($attributes['title']) ? $attributes['title'] : NULL;

      // Setting empty alt to double quotes. See ImageFieldFormatter.
      if ($settings['alt_field_required'] && $alt === '') {
        $alt = static::EMPTY_STRING;
      }

      if (!empty($settings['alt_field'])) {
        // Add support for editing the alternate and title text attributes.
        $form['alt'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Alternate text'),
          '#default_value' => $alt,
          '#description' => $this->t('This text will be used by screen readers, search engines, or when the image cannot be loaded.'),
          '#required_error' => $this->t('Alternative text is required.<br />(Only in rare cases should this be left empty. To create empty alternative text, enter <code>""</code> — two double quotes without any content).'),
          '#maxlength' => 512,
          '#placeholder' => $entity->{$image_field}->alt,
        ];
      }

      if (!empty($settings['title_field'])) {
        $form['title'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#default_value' => $title,
          '#description' => t('The title is used as a tool tip when the user hovers the mouse over the image.'),
          '#maxlength' => 1024,
          '#placeholder' => $entity->{$image_field}->title,
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->decorated->getEntityFromContext();
    if ($image_field = $this->getMediaImageSourceField($entity)) {
      $settings = $entity->{$image_field}->getItemDefinition()->getSettings();
      $values = $form_state->getValue(['attributes', 'data-entity-embed-display-settings']);

      if (!empty($settings['alt_field'])) {
        // When the alt attribute is set to two double quotes, transform it to
        // the empty string: two double quotes signify "empty alt attribute".
        // See ImagefieldFormatter.
        if (trim($values['alt']) === static::EMPTY_STRING) {
          $values['alt'] = static::EMPTY_STRING;
        }
        // If the alt text is unchanged from the values set on the
        // field, there's no need for the alt property to be set.
        elseif ($values['alt'] === $entity->{$image_field}->alt) {
          $values['alt'] = '';
        }

        $form_state->setValue(['attributes', 'alt'], $values['alt']);
        $form_state->unsetValue([
          'attributes',
          'data-entity-embed-display-settings',
          'alt',
        ]);
      }

      if (!empty($settings['title_field'])) {
        if (empty($values['title'])) {
          $values['title'] = '';
        }
        // If the title text is unchanged from the values set on the
        // field, there's no need for the title property to be set.
        elseif ($values['title'] === $entity->{$image_field}->title) {
          $values['title'] = '';
        }

        $form_state->setValue(['attributes', 'title'], $values['title']);
        $form_state->unsetValue([
          'attributes',
          'data-entity-embed-display-settings',
          'title',
        ]);
      }
    }
    $this->decorated->submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->decorated->build();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->decorated->getEntityFromContext();

    if ($image_field = $this->getMediaImageSourceField($entity)) {
      $settings = $entity->{$image_field}->getItemDefinition()->getSettings();

      if (!empty($settings['alt_field']) && $this->hasAttribute('alt')) {
        $entity->{$image_field}->alt = $this->getAttributeValue('alt');
        $entity->thumbnail->alt = $this->getAttributeValue('alt');
      }

      if (!empty($settings['title_field']) && $this->hasAttribute('title')) {
        $entity->{$image_field}->title = $this->getAttributeValue('title');
        $entity->thumbnail->title = $this->getAttributeValue('title');
      }
    }

    return $build;
  }

  /**
   * Get image field from source config.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Embedded entity.
   *
   * @return string|null
   *   String of image field name.
   */
  protected function getMediaImageSourceField(EntityInterface $entity) {
    if (!$entity instanceof MediaInterface) {
      return NULL;
    }

    $field_definition = $entity->getSource()
      ->getSourceFieldDefinition($entity->bundle->entity);
    $item_class = $field_definition->getItemDefinition()->getClass();
    if ($item_class == ImageItem::class || is_subclass_of($item_class, ImageItem::class)) {
      return $field_definition->getName();
    }
    return NULL;
  }

}
