<?php

namespace Drupal\Tests\entity_embed\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\filter\FilterProcessResult;

/**
 * @coversDefaultClass \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter
 * @group entity_embed
 */
class EntityEmbedFilterTest extends EntityEmbedFilterTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    // @see entity_embed_test_entity_access()
    // @see entity_embed_test_entity_view_alter()
    'entity_embed_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('system');
  }

  /**
   * Ensures entities are rendered with correct data attributes.
   *
   * @dataProvider providerTestBasics
   */
  public function testBasics(array $embed_attributes, $expected_view_mode, array $expected_attributes) {
    $content = $this->createEmbedCode($embed_attributes);

    $result = $this->applyFilter($content);

    $this->assertCount(1, $this->cssSelect('div.embedded-entity > [data-entity-embed-test-view-mode="' . $expected_view_mode . '"]'));
    $this->assertHasAttributes($this->cssSelect('div.embedded-entity')[0], $expected_attributes);
    $this->assertSame([
      'config:filter.format.plain_text',
      'foo:1',
      'node:1',
      'node_view',
      'user:2',
      'user_view',
    ], $this->getCacheTags($result));
    $this->assertSame(['timezone', 'user.permissions'], $this->getCacheContexts($result));
    $this->assertSame(Cache::PERMANENT, $result->getCacheMaxAge());
    $this->assertSame(['library'], array_keys($result->getAttachments()));
    $this->assertSame(['entity_embed/caption'], $result->getAttachments()['library']);
  }

  private function getCacheTags(FilterProcessResult $result): array {
    $cache_tags = $result->getCacheTags();
    sort($cache_tags);
    return $cache_tags;
  }

  private function getCacheContexts(FilterProcessResult $result): array {
    $cache_contexts = $result->getCacheContexts();
    sort($cache_contexts);
    return $cache_contexts;
  }

  /**
   * Data provider for testBasics().
   */
  public function providerTestBasics() {
    return [
      'data-entity-uuid + data-view-mode=teaser' => [
        [
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-view-mode' => 'teaser',
        ],
        'teaser',
        [
          'data-entity-type' => 'node',
          'data-view-mode' => 'teaser',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-langcode' => 'en',
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
        ],
      ],
      'data-entity-uuid + data-view-mode=full' => [
        [
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-view-mode' => 'full',
        ],
        'full',
        [
          'data-entity-type' => 'node',
          'data-view-mode' => 'full',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-langcode' => 'en',
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"full"}',
        ],
      ],
      'data-entity-uuid + data-view-mode=default' => [
        [
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-view-mode' => 'default',
        ],
        'default',
        [
          'data-entity-type' => 'node',
          'data-view-mode' => 'default',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-langcode' => 'en',
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"default"}',
        ],
      ],
      'data-entity-uuid + data-entity-embed-display' => [
        [
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"full"}',
        ],
        'full',
        [
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"full"}',
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-langcode' => 'en',
        ],
      ],
      'data-entity-uuid + data-entity-embed-display + data-view-mode ⇒ data-entity-embed-display wins' => [
        [
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-entity-embed-display' => 'default',
          'data-entity-embed-display-settings' => '{"view_mode":"full"}',
          'data-view-mode' => 'some-invalid-view-mode',
        ],
        'full',
        [
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"full"}',
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-view-mode' => 'some-invalid-view-mode',
          'data-langcode' => 'en',
        ],
      ],
      'custom attributes are retained' => [
        [
          'data-foo' => 'bar',
          'foo' => 'bar',
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-view-mode' => 'teaser',
        ],
        'teaser',
        [
          'data-foo' => 'bar',
          'foo' => 'bar',
          'data-entity-type' => 'node',
          'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
          'data-view-mode' => 'teaser',
          'data-langcode' => 'en',
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
        ],
      ],
    ];
  }

  /**
   * Tests that entity access is respected by embedding an unpublished entity.
   *
   * @dataProvider providerAccessUnpublished
   */
  public function testAccessUnpublished($allowed_to_view_unpublished, $expected_rendered, CacheableMetadata $expected_cacheability, array $expected_attachments) {
    // Unpublish the embedded entity so we can test variations in behavior.
    $this->embeddedEntity->setUnpublished()->save();

    // Are we testing as a user who is allowed to view the embedded entity?
    if ($allowed_to_view_unpublished) {
      $this->container->get('current_user')
        ->addRole($this->drupalCreateRole(['view own unpublished content']));
    }

    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-view-mode' => 'teaser',
    ]);
    $result = $this->applyFilter($content);

    if (!$expected_rendered) {
      $this->assertEmpty($this->getRawContent());
    }
    else {
      $this->assertCount(1, $this->cssSelect('div.embedded-entity > [data-entity-embed-test-view-mode="teaser"]'));
    }

    // Expected bubbleable metadata.
    $this->assertSame($expected_cacheability->getCacheTags(), $this->getCacheTags($result));
    $this->assertSame($expected_cacheability->getCacheContexts(), $this->getCacheContexts($result));
    $this->assertSame($expected_cacheability->getCacheMaxAge(), $result->getCacheMaxAge());
    $this->assertSame($expected_attachments, $result->getAttachments());
  }

  /**
   * Data provider for testAccessUnpublished().
   */
  public function providerAccessUnpublished() {
    return [
      'user cannot access embedded entity' => [
        FALSE,
        FALSE,
        (new CacheableMetadata())
          ->setCacheTags(['foo:1', 'node:1'])
          ->setCacheContexts(['user.permissions'])
          ->setCacheMaxAge(Cache::PERMANENT),
        [],
      ],
      'user can access embedded entity' => [
        TRUE,
        TRUE,
        (new CacheableMetadata())
          ->setCacheTags([
            'config:filter.format.plain_text',
            'foo:1',
            'node:1',
            'node_view',
            'user:2',
            'user_view',
          ])
          ->setCacheContexts(['timezone', 'user', 'user.permissions'])
          ->setCacheMaxAge(Cache::PERMANENT),
        ['library' => ['entity_embed/caption']],
      ],
    ];
  }

  /**
   * Tests the indicator for missing entities.
   *
   * @dataProvider providerMissingEntityIndicator
   */
  public function testMissingEntityIndicator($entity_type_id, $uuid, $expected_missing_text) {
    $content = $this->createEmbedCode([
      'data-entity-type' => $entity_type_id,
      'data-entity-uuid' => $uuid,
      'data-view-mode' => 'default',
    ]);

    // If the UUID being used in the embed is that of the sample entity, first
    // assert that it currently results in a functional embed, then delete it.
    if ($uuid === static::EMBEDDED_ENTITY_UUID) {
      $this->applyFilter($content);
      $this->assertCount(1, $this->cssSelect('div.embedded-entity > [data-entity-embed-test-view-mode="default"]'));
      $this->embeddedEntity->delete();
    }

    $this->applyFilter($content);
    $this->assertCount(0, $this->cssSelect('div.embedded-entity > [data-entity-embed-test-view-mode="default"]'));
    $this->assertCount(0, $this->cssSelect('div.embedded-entity'));
    /** @var \SimpleXMLElement[] $deleted_embed_warning */
    $deleted_embed_warning = $this->cssSelect('img');
    $this->assertNotEmpty($deleted_embed_warning);
    $src = \Drupal::service('file_url_generator')->generateString('core/modules/media/images/icons/no-thumbnail.png');
    $this->assertHasAttributes($deleted_embed_warning[0], [
      'alt' => $expected_missing_text,
      'src' => $src,
      'title' => $expected_missing_text,
    ]);
  }

  /**
   * Data provider for testMissingEntityIndicator().
   */
  public function providerMissingEntityIndicator() {
    return [
      'node; valid UUID but for a deleted entity' => [
        'node',
        static::EMBEDDED_ENTITY_UUID,
        'Missing content item.',
      ],
      'node; invalid UUID' => [
        'node',
        'invalidUUID',
        'Missing content item.',
      ],
      'user; invalid UUID' => [
        'user',
        'invalidUUID',
        'Missing user.',
      ],
    ];
  }

  /**
   * Tests that only <drupal-entity> tags are processed.
   *
   * @see \Drupal\Tests\entity_embed\FunctionalJavascript\MediaImageTest::testOnlyDrupalEntityTagProcessed()
   */
  public function testOnlyDrupalEntityTagProcessed() {
    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => $this->embeddedEntity->uuid(),
      'data-view-mode' => 'teaser',
    ]);
    $content = str_replace('drupal-entity', 'entity-embed', $content);

    $filter_result = $this->processText($content, 'en', ['entity_embed']);
    // If input equals output, the filter didn't change anything.
    $this->assertSame($content, $filter_result->getProcessedText());
  }

  /**
   * Tests recursive rendering protection.
   */
  public function testRecursionProtection() {
    $text = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-view-mode' => 'default',
    ]);

    // Render and verify the presence of the embedded entity 20 times.
    for ($i = 0; $i < 20; $i++) {
      $this->applyFilter($text);
      $this->assertCount(1, $this->cssSelect('div.embedded-entity > [data-entity-embed-test-view-mode="default"]'));
    }

    // Render a 21st time, this is exceeding the recursion limit. The entity
    // embed markup will be stripped.
    $this->applyFilter($text);
    $this->assertEmpty($this->getRawContent());
  }

  /**
   * @covers \Drupal\filter\Plugin\Filter\FilterAlign
   * @covers \Drupal\filter\Plugin\Filter\FilterCaption
   * @dataProvider providerFilterIntegration
   */
  public function testFilterIntegration(array $filter_ids, array $additional_attributes, $verification_selector, $expected_verification_success, array $expected_asset_libraries, $prefix = '', $suffix = '') {
    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-view-mode' => 'teaser',
    ] + $additional_attributes);
    $content = $prefix . $content . $suffix;

    $result = $this->processText($content, 'en', $filter_ids);
    $this->setRawContent($result->getProcessedText());
    $this->assertCount($expected_verification_success ? 1 : 0, $this->cssSelect($verification_selector));
    $this->assertHasAttributes($this->cssSelect('div.embedded-entity')[0], [
      'data-entity-type' => 'node',
      'data-view-mode' => 'teaser',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-langcode' => 'en',
      'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
      'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
    ]);
    $this->assertSame([
      'config:filter.format.plain_text',
      'foo:1',
      'node:1',
      'node_view',
      'user:2',
      'user_view',
    ], $this->getCacheTags($result));
    $this->assertSame(['timezone', 'user.permissions'], $this->getCacheContexts($result));
    $this->assertSame(Cache::PERMANENT, $result->getCacheMaxAge());
    $this->assertSame(['library'], array_keys($result->getAttachments()));
    $this->assertSame($expected_asset_libraries, $result->getAttachments()['library']);
  }

  /**
   * Data provider for testFilterIntegration().
   */
  public function providerFilterIntegration() {
    $default_asset_libraries = ['entity_embed/caption'];

    $caption_additional_attributes = ['data-caption' => 'Yo.'];
    $caption_verification_selector = 'figure > figcaption';
    $caption_test_cases = [
      '`data-caption`; only `entity_embed` ⇒ caption absent' => [
        ['entity_embed'],
        $caption_additional_attributes,
        $caption_verification_selector,
        FALSE,
        $default_asset_libraries,
      ],
      '`data-caption`; `filter_caption` + `entity_embed` ⇒ caption present' => [
        ['filter_caption', 'entity_embed'],
        $caption_additional_attributes,
        $caption_verification_selector,
        TRUE,
        ['filter/caption', 'entity_embed/caption'],
      ],
      '`<a>` + `data-caption`; `filter_caption` + `entity_embed` ⇒ caption present, link preserved' => [
        ['filter_caption', 'entity_embed'],
        $caption_additional_attributes,
        'figure > a[href="https://www.drupal.org"] + figcaption',
        TRUE,
        ['filter/caption', 'entity_embed/caption'],
        '<a href="https://www.drupal.org">',
        '</a>',
      ],
    ];

    $align_additional_attributes = ['data-align' => 'center'];
    $align_verification_selector = 'div.embedded-entity.align-center';
    $align_test_cases = [
      '`data-align`; `entity_embed` ⇒ alignment absent' => [
        ['entity_embed'],
        $align_additional_attributes,
        $align_verification_selector,
        FALSE,
        $default_asset_libraries,
      ],
      '`data-align`; `filter_align` + `entity_embed` ⇒ alignment present' => [
        ['filter_align', 'entity_embed'],
        $align_additional_attributes,
        $align_verification_selector,
        TRUE,
        $default_asset_libraries,
      ],
      '`<a>` + `data-align`; `filter_align` + `entity_embed` ⇒ alignment present, link preserved' => [
        ['filter_align', 'entity_embed'],
        $align_additional_attributes,
        'a[href="https://www.drupal.org"] > div.embedded-entity.align-center',
        TRUE,
        $default_asset_libraries,
        '<a href="https://www.drupal.org">',
        '</a>',
      ],
    ];

    $caption_and_align_test_cases = [
      '`data-caption` + `data-align`; `filter_align` + `filter_caption` + `entity_embed` ⇒ aligned caption present' => [
        ['filter_align', 'filter_caption', 'entity_embed'],
        $align_additional_attributes + $caption_additional_attributes,
        'figure.align-center > figcaption',
        TRUE,
        ['filter/caption', 'entity_embed/caption'],
      ],
      '`<a>` + `data-caption` + `data-align`; `filter_align` + `filter_caption` + `entity_embed` ⇒ aligned caption present, link preserved' => [
        ['filter_align', 'filter_caption', 'entity_embed'],
        $align_additional_attributes + $caption_additional_attributes,
        'figure.align-center > a[href="https://www.drupal.org"] + figcaption',
        TRUE,
        ['filter/caption', 'entity_embed/caption'],
        '<a href="https://www.drupal.org">',
        '</a>',
      ],
    ];

    return $caption_test_cases + $align_test_cases + $caption_and_align_test_cases;
  }

}
