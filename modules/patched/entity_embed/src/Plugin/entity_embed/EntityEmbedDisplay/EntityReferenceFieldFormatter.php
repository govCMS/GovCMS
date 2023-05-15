<?php

namespace Drupal\entity_embed\Plugin\entity_embed\EntityEmbedDisplay;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FormatterPluginManager;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\entity_embed\EntityEmbedDisplay\FieldFormatterEntityEmbedDisplayBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity Embed Display reusing entity reference field formatters.
 *
 * @see \Drupal\entity_embed\EntityEmbedDisplay\EntityEmbedDisplayInterface
 *
 * @EntityEmbedDisplay(
 *   id = "entity_reference",
 *   label = @Translation("Entity Reference"),
 *   deriver = "Drupal\entity_embed\Plugin\Derivative\FieldFormatterDeriver",
 *   field_type = "entity_reference",
 *   supports_image_alt_and_title = TRUE
 * )
 */
class EntityReferenceFieldFormatter extends FieldFormatterEntityEmbedDisplayBase implements TrustedCallbackInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->configFactory = $container->get('config.factory');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'disableContextualLinks',
      'disableQuickEdit',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldDefinition() {
    if (!isset($this->fieldDefinition)) {
      $this->fieldDefinition = parent::getFieldDefinition();
      $this->fieldDefinition->setSetting('target_type', $this->getEntityTypeFromContext());
    }
    return $this->fieldDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldValue() {
    return ['target_id' => $this->getContextValue('entity')->id()];
  }

  /**
   * {@inheritdoc}
   */
  protected function isApplicableFieldFormatter() {
    $access = parent::isApplicableFieldFormatter();

    // Don't bother checking if not allowed.
    if ($access->isAllowed()) {
      if ($this->getPluginId() === 'entity_reference:entity_reference_entity_view') {
        // This option disables entity_reference_entity_view plugin for content
        // entity types. If it is truthy then the plugin is enabled for all
        // entity types.
        $mode = $this->configFactory->get('entity_embed.settings')->get('rendered_entity_mode');
        if ($mode) {
          // Return *allowed* object.
          return $access;
        }

        // Only allow this if this is not a content entity type.
        $entity_type_id = $this->getEntityTypeFromContext();
        if ($entity_type_id) {
          $definition = $this->entityTypeManager->getDefinition($entity_type_id);
          return $access->andIf(AccessResult::allowedIf(!$definition->entityClassImplements(ContentEntityInterface::class)));
        }
      }
    }

    return $access;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();

    // Early return if this derived plugin is not using an EntityViewBuilder.
    // @see \Drupal\Core\Entity\EntityViewBuilder::getBuildDefaults()
    if (!isset($build['#view_mode'])) {
      return $build;
    }

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->getEntityFromContext();

    // There are a few concerns when rendering an embedded media entity:
    // - entity access checking happens not during rendering but during routing,
    //   and therefore we have to do it explicitly here for the embedded entity.
    $build['#access'] = $entity->access('view', NULL, TRUE);
    // - caching an embedded entity separately is unnecessary; the host entity
    //   is already render cached; plus specific values may be overridden (such
    //   as an `alt` attribute) which would mean this particular rendered
    //   representation is unique to the host entity and hence nonsensical to
    //   cache separately anyway.
    unset($build['#cache']['keys']);
    // - Contextual Links do not make sense for embedded entities; we only allow
    //   the host entity to be contextually managed.
    $build['#pre_render'][] = static::class . '::disableContextualLinks';
    // - Quick Edit does not make sense for embedded entities; we only allow the
    //   host entity to be edited in-place.
    $build['#pre_render'][] = static::class . '::disableQuickEdit';
    // - default styling may break captioned media embeds; attach asset library
    //   to ensure captions behave as intended.
    $build['#attached']['library'][] = 'entity_embed/caption';

    return $build;
  }

  /**
   * Disables Contextual Links for the embedded media by removing its property.
   *
   * @param array $build
   *   The render array for the embedded media.
   *
   * @return array
   *   The updated render array.
   *
   * @see \Drupal\Core\Entity\EntityViewBuilder::addContextualLinks()
   */
  public static function disableContextualLinks(array $build) {
    unset($build['#contextual_links']);
    return $build;
  }

  /**
   * Disables Quick Edit for the embedded media by removing its attributes.
   *
   * @param array $build
   *   The render array for the embedded media.
   *
   * @return array
   *   The updated render array.
   *
   * @see quickedit_entity_view_alter()
   */
  public static function disableQuickEdit(array $build) {
    unset($build['#attributes']['data-quickedit-entity-id']);
    return $build;
  }

}
