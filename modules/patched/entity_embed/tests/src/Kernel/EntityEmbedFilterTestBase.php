<?php

namespace Drupal\Tests\entity_embed\Kernel;

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\RenderContext;
use Drupal\filter\FilterPluginCollection;
use Drupal\filter\FilterProcessResult;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Base class for Entity Embed filter tests.
 */
abstract class EntityEmbedFilterTestBase extends KernelTestBase {

  use NodeCreationTrait {
    createNode as drupalCreateNode;
  }
  use UserCreationTrait {
    createUser as drupalCreateUser;
    createRole as drupalCreateRole;
  }
  use ContentTypeCreationTrait {
    createContentType as drupalCreateContentType;
  }

  /**
   * The UUID to use for the embedded entity.
   *
   * @var string
   */
  const EMBEDDED_ENTITY_UUID = 'e7a3e1fe-b69b-417e-8ee4-c80cb7640e63';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'embed',
    'entity_embed',
    'field',
    'filter',
    'node',
    'system',
    'text',
    'user',
  ];

  /**
   * The sample Node entity to embed.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $embeddedEntity;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('node', 'node_access');
    $this->installSchema('system', 'sequences');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig('filter');
    $this->installConfig('node');

    // Create a user with required permissions. Ensure that we don't use user 1
    // because that user is treated in special ways by access control handlers.
    $admin_user = $this->drupalCreateUser([]);
    $user = $this->drupalCreateUser([
      'access content',
    ]);
    $this->container->set('current_user', $user);

    // Create a sample node to be embedded.
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    $this->embeddedEntity = $this->drupalCreateNode([
      'title' => 'Embed Test Node',
      'uuid' => static::EMBEDDED_ENTITY_UUID,
    ]);
  }

  /**
   * Gets an embed code with given attributes.
   *
   * @param array $attributes
   *   The attributes to add.
   *
   * @return string
   *   A string containing a drupal-entity dom element.
   *
   * @see assertEntityEmbedFilterHasRun()
   */
  protected function createEmbedCode(array $attributes) {
    $dom = Html::load('<drupal-entity>This placeholder should not be rendered.</drupal-entity>');
    $xpath = new \DOMXPath($dom);
    $drupal_entity = $xpath->query('//drupal-entity')[0];
    foreach ($attributes as $attribute => $value) {
      $drupal_entity->setAttribute($attribute, $value);
    }
    return Html::serialize($dom);
  }

  /**
   * Applies the `@Filter=entity_embed` filter to text, pipes to raw content.
   *
   * @param string $text
   *   The text string to be filtered.
   * @param string $langcode
   *   The language code of the text to be filtered.
   *
   * @return \Drupal\filter\FilterProcessResult
   *   The filtered text, wrapped in a FilterProcessResult object, and possibly
   *   with associated assets, cacheability metadata and placeholders.
   *
   * @see \Drupal\Tests\entity_embed\Kernel\EntityEmbedFilterTestBase::createEmbedCode()
   * @see \Drupal\KernelTests\AssertContentTrait::setRawContent()
   */
  protected function applyFilter($text, $langcode = 'en') {
    $this->assertStringContainsString('<drupal-entity', $text);
    $this->assertStringContainsString('This placeholder should not be rendered.', $text);
    $filter_result = $this->processText($text, $langcode);
    $output = $filter_result->getProcessedText();
    $this->assertStringNotContainsString('<drupal-entity', $output);
    $this->assertStringNotContainsString('This placeholder should not be rendered.', $output);
    $this->setRawContent($output);
    return $filter_result;
  }

  /**
   * Assert that the SimpleXMLElement object has the given attributes.
   *
   * @param \SimpleXMLElement $element
   *   The SimpleXMLElement object to check.
   * @param array $attributes
   *   An array of attributes.
   */
  protected function assertHasAttributes(\SimpleXMLElement $element, array $attributes) {
    foreach ($attributes as $attribute => $value) {
      $this->assertSame((string) $value, (string) $element[$attribute]);
    }
  }

  /**
   * Processes text through the provided filters.
   *
   * @param string $text
   *   The text string to be filtered.
   * @param string $langcode
   *   The language code of the text to be filtered.
   * @param string[] $filter_ids
   *   (optional) The filter plugin IDs to apply to the given text, in the order
   *   they are being requested to be executed.
   *
   * @return \Drupal\filter\FilterProcessResult
   *   The filtered text, wrapped in a FilterProcessResult object, and possibly
   *   with associated assets, cacheability metadata and placeholders.
   *
   * @see \Drupal\filter\Element\ProcessedText::preRenderText()
   */
  protected function processText($text, $langcode = 'und', array $filter_ids = ['entity_embed']) {
    $manager = $this->container->get('plugin.manager.filter');
    $bag = new FilterPluginCollection($manager, []);
    $filters = [];
    foreach ($filter_ids as $filter_id) {
      $filters[] = $bag->get($filter_id);
    }

    $render_context = new RenderContext();
    /** @var \Drupal\filter\FilterProcessResult $filter_result */
    $filter_result = $this->container->get('renderer')->executeInRenderContext($render_context, function () use ($text, $filters, $langcode) {
      $metadata = new BubbleableMetadata();
      foreach ($filters as $filter) {
        /** @var \Drupal\filter\FilterProcessResult $result */
        $result = $filter->process($text, $langcode);
        $metadata = $metadata->merge($result);
        $text = $result->getProcessedText();
      }
      return (new FilterProcessResult($text))->merge($metadata);
    });
    if (!$render_context->isEmpty()) {
      $filter_result = $filter_result->merge($render_context->pop());
    }
    return $filter_result;
  }

}
