<?php

namespace Drupal\entity_embed\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\entity_embed\EntityEmbedBuilderInterface;
use Drupal\entity_embed\Exception\EntityNotFoundException;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\embed\DomHelperTrait;

/**
 * Provides a filter to display embedded entities based on data attributes.
 *
 * @Filter(
 *   id = "entity_embed",
 *   title = @Translation("Display embedded entities"),
 *   description = @Translation("Embeds entities using data attributes: data-entity-type, data-entity-uuid, and data-view-mode. Should usually run as the last filter, since it does not contain user input."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = 100,
 * )
 */
class EntityEmbedFilter extends FilterBase implements ContainerFactoryPluginInterface {

  use DomHelperTrait;

  /**
   * The number of times this formatter allows rendering the same entity.
   *
   * @var int
   */
  const RECURSIVE_RENDER_LIMIT = 20;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity embed builder service.
   *
   * @var \Drupal\entity_embed\EntityEmbedBuilderInterface
   */
  protected $builder;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * An array of counters for the recursive rendering protection.
   *
   * Each counter takes into account all the relevant information about the
   * field and the referenced entity that is being rendered.
   *
   * @var array
   *
   * @see \Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter::$recursiveRenderDepth
   */
  protected static $recursiveRenderDepth = [];

  /**
   * Constructs a EntityEmbedFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\entity_embed\EntityEmbedBuilderInterface $builder
   *   The entity embed builder service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface
   *   The file URL generator.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, RendererInterface $renderer, EntityEmbedBuilderInterface $builder, LoggerChannelFactoryInterface $logger_factory, FileUrlGeneratorInterface $file_url_generator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
    $this->builder = $builder;
    $this->loggerFactory = $logger_factory;
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
      $container->get('renderer'),
      $container->get('entity_embed.builder'),
      $container->get('logger.factory'),
      $container->get('file_url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type') !== FALSE && (strpos($text, 'data-entity-embed-display') !== FALSE || strpos($text, 'data-view-mode') !== FALSE)) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//drupal-entity[@data-entity-type and (@data-entity-uuid or @data-entity-id) and (@data-entity-embed-display or @data-view-mode)]') as $node) {
        /** @var \DOMElement $node */
        $entity_type = $node->getAttribute('data-entity-type');
        $entity = NULL;
        $entity_output = '';

        // data-entity-embed-settings is deprecated, make sure we convert it to
        // data-entity-embed-display-settings.
        if (($settings = $node->getAttribute('data-entity-embed-settings')) && !$node->hasAttribute('data-entity-embed-display-settings')) {
          $node->setAttribute('data-entity-embed-display-settings', $settings);
          $node->removeAttribute('data-entity-embed-settings');
        }

        $entity = NULL;
        try {
          // Load the entity either by UUID (preferred) or ID.
          $id = NULL;
          if ($id = $node->getAttribute('data-entity-uuid')) {
            $entity = $this->entityTypeManager->getStorage($entity_type)
              ->loadByProperties(['uuid' => $id]);
            $entity = current($entity);
          }
          else {
            $id = $node->getAttribute('data-entity-id');
            $entity = $this->entityTypeManager->getStorage($entity_type)->load($id);
          }
          if (!$entity instanceof EntityInterface) {
            $missing_text = $this->t('Missing @type.', ['@type' => $this->entityTypeManager->getDefinition($entity_type)->getSingularLabel()]);
            $entity_output = '<img src="' . $this->fileUrlGenerator->generateString('core/modules/media/images/icons/no-thumbnail.png') . '" width="180" height="180" alt="' . $missing_text . '" title="' . $missing_text . '"/>';
            throw new EntityNotFoundException(sprintf('Unable to load embedded %s entity %s.', $entity_type, $id));
          }
        }
        catch (EntityNotFoundException $e) {
          watchdog_exception('entity_embed', $e);
        }

        if ($entity instanceof EntityInterface) {
          // If a UUID was not used, but is available, add it to the HTML.
          if (!$node->getAttribute('data-entity-uuid') && $uuid = $entity->uuid()) {
            $node->setAttribute('data-entity-uuid', $uuid);
          }

          $context = $this->getNodeAttributesAsArray($node);
          $context += ['data-langcode' => $langcode];

          // Due to render caching and delayed calls, filtering happens later
          // in the rendering process through a '#pre_render' callback, so we
          // need to generate a counter that takes into account all the
          // relevant information about this field and the referenced entity
          // that is being rendered.
          // @see \Drupal\filter\Element\ProcessedText::preRenderText()
          $recursive_render_id = $entity->uuid() . json_encode($context);
          if (isset(static::$recursiveRenderDepth[$recursive_render_id])) {
            static::$recursiveRenderDepth[$recursive_render_id]++;
          }
          else {
            static::$recursiveRenderDepth[$recursive_render_id] = 1;
          }

          // Protect ourselves from recursive rendering.
          if (static::$recursiveRenderDepth[$recursive_render_id] > static::RECURSIVE_RENDER_LIMIT) {
            $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering embedded entity %entity_type: %entity_id. Aborting rendering.', [
              '%entity_type' => $entity->getEntityTypeId(),
              '%entity_id' => $entity->id(),
            ]);
            $entity_output = '';
          }
          else {
            $build = $this->builder->buildEntityEmbed($entity, $context);
            // We need to render the embedded entity:
            // - without replacing placeholders, so that the placeholders are
            //   only replaced at the last possible moment. Hence we cannot use
            //   either renderPlain() or renderRoot(), so we must use render().
            // - without bubbling beyond this filter, because filters must
            //   ensure that the bubbleable metadata for the changes they make
            //   when filtering text makes it onto the FilterProcessResult
            //   object that they return ($result). To prevent that bubbling, we
            //   must wrap the call to render() in a render context.
            $entity_output = $this->renderer->executeInRenderContext(new RenderContext(), function () use (&$build) {
              return $this->renderer->render($build);
            });
            $result = $result->merge(BubbleableMetadata::createFromRenderArray($build));
          }
        }

        $this->replaceNodeContent($node, $entity_output);
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t('
        <p>You can embed entities. Additional properties can be added to the embed tag like data-caption and data-align if supported. Example:</p>
        <code>&lt;drupal-entity data-entity-type="node" data-entity-uuid="07bf3a2e-1941-4a44-9b02-2d1d7a41ec0e" data-view-mode="teaser" /&gt;</code>');
    }
    else {
      return $this->t('You can embed entities.');
    }
  }

}
