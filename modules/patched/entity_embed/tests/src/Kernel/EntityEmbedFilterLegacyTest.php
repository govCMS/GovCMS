<?php

namespace Drupal\Tests\entity_embed\Kernel;

/**
 * @coversDefaultClass \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter
 * @group entity_embed
 * @group legacy
 */
class EntityEmbedFilterLegacyTest extends EntityEmbedFilterTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig('system');
  }

  /**
   * Tests BC for `data-entity-uuid`'s predecessor, `data-entity-id`.
   */
  public function testEntityIdBackwardsCompatibility() {
    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-id' => 1,
      'data-view-mode' => 'teaser',
    ]);
    $this->applyFilter($content);
    $this->assertHasAttributes($this->cssSelect('div.embedded-entity')[0], [
      'data-entity-type' => 'node',
      'data-entity-id' => 1,
      'data-view-mode' => 'teaser',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-langcode' => 'en',
      'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
      'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
    ]);
  }

  /**
   * Verifies `data-entity-id` is ignored when `data-entity-uuid` is present.
   */
  public function testEntityIdIgnoredIfEntityUuidPresent() {
    $nonsensical_id = $this->randomMachineName();
    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-entity-id' => $nonsensical_id,
      'data-view-mode' => 'teaser',
    ]);
    $this->applyFilter($content);
    $this->assertHasAttributes($this->cssSelect('div.embedded-entity')[0], [
      'data-entity-type' => 'node',
      'data-entity-id' => $nonsensical_id,
      'data-view-mode' => 'teaser',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-langcode' => 'en',
      'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
      'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
    ]);
  }

  /**
   * Tests BC for `data-entity-embed-display-settings`'s predecessor.
   */
  public function testEntityEmbedSettingsBackwardsCompatibility() {
    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-entity-embed-display' => 'entity_reference:entity_reference_label',
      'data-entity-embed-settings' => '{"link":"0"}',
    ]);
    $this->applyFilter($content);
    $this->assertCount(0, $this->cssSelect('div.embedded-entity a'));
    $this->assertSame($this->embeddedEntity->label(), (string) $this->cssSelect('div.embedded-entity')[0]);
  }

  /**
   * Tests BC for `data-entity-embed-display="default"`.
   */
  public function testEntityEmbedDisplayDefaultBackwardsCompatibility() {
    $content = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-entity-embed-display' => 'default',
      'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
    ]);
    $this->applyFilter($content);
    $this->assertHasAttributes($this->cssSelect('div.embedded-entity')[0], [
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
      'data-entity-embed-display-settings' => '{"view_mode":"teaser"}',
      'data-langcode' => 'en',
    ]);
  }

}
