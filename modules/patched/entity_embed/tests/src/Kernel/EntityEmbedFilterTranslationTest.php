<?php

namespace Drupal\Tests\entity_embed\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests that entity embeds are translated based on host and `data-langcode`.
 *
 * @coversDefaultClass \Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter
 * @group entity_embed
 */
class EntityEmbedFilterTranslationTest extends EntityEmbedFilterTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    ConfigurableLanguage::createFromLangcode('pt-br')->save();
    // Reload the entity to ensure it is aware of the newly created language.
    $this->embeddedEntity = $this->container->get('entity_type.manager')
      ->getStorage($this->embeddedEntity->getEntityTypeId())
      ->load($this->embeddedEntity->id());

    $this->embeddedEntity->addTranslation('pt-br')
      ->setTitle('Embed em portugues')
      ->save();
  }

  /**
   * Tests that the expected embedded entity translation is selected.
   *
   * @dataProvider providerTranslationSituations
   */
  public function testTranslationSelection($text_langcode, array $additional_attributes, $expected_title_langcode) {
    $text = $this->createEmbedCode([
      'data-entity-type' => 'node',
      'data-entity-uuid' => static::EMBEDDED_ENTITY_UUID,
      'data-view-mode' => 'teaser',
      'data-entity-embed-display' => 'entity_reference:entity_reference_label',
      'data-entity-embed-settings' => '{"link":"0"}',
    ] + $additional_attributes);

    $result = $this->processText($text, $text_langcode, ['entity_embed']);
    $this->setRawContent($result->getProcessedText());

    $this->assertSame(
      $this->embeddedEntity->getTranslation($expected_title_langcode)->label(),
      (string) $this->cssSelect('div.embedded-entity')[0]
    );
    // Verify that the filtered text does not vary by translation-related cache
    // contexts: a particular translation of the embedded entity is selected
    // based on either the `data-langcode` attribute or the host entity's
    // language, neither of which should require a cache context to be
    // associated. (The host entity's language may itself be selected based on
    // some request context, but that is of no concern to this filter.)
    $this->assertSame($result->getCacheContexts(), ['user.permissions']);
  }

  /**
   * Data provider for testTranslationSelection().
   */
  public function providerTranslationSituations() {
    $embedded_entity_translation_languages = ['en', 'pt-br'];

    foreach (['en', 'pt-br', 'nl'] as $text_langcode) {
      // When no `data-langcode` attribute is specified, the text language
      // (which is set to the host entity's language) is respected. If that
      // translation does not exist, it falls back to the default translation of
      // the embedded entity.
      $match_or_fallback_langcode = in_array($text_langcode, $embedded_entity_translation_languages)
        ? $text_langcode
        : 'en';
      yield "text_langcode=$text_langcode (✅) ⇒ $match_or_fallback_langcode" => [
        $text_langcode,
        [],
        $match_or_fallback_langcode,
      ];

      // When the embedded entity has a translation for the language code in the
      // `data-langcode` attribute, that translation is used, regardless of the
      // language of the text (which is set to the language of the host entity).
      foreach ($embedded_entity_translation_languages as $data_langcode) {
        yield "text_langcode=$text_langcode (✅); data-langcode=$data_langcode (✅) ⇒ $data_langcode" => [
          $text_langcode,
          ['data-langcode' => $data_langcode],
          $data_langcode,
        ];
      }

      // When specifying a (valid) language code but the embedded entity has no
      // translation for that language, it falls back to the default translation
      // of the embedded entity.
      yield "text_langcode=$text_langcode (✅); data-langcode=nl (🚫) ⇒ en" => [
        $text_langcode,
        ['data-langcode' => 'nl'],
        'en',
      ];

      // When specifying a invalid language code, it falls back to the default
      // translation of the embedded entity.
      yield "text_langcode=$text_langcode (✅); data-langcode=non-existing-and-even-invalid-langcode (🚫) ⇒ en" => [
        $text_langcode,
        ['data-langcode' => 'non-existing-and-even-invalid-langcode'],
        'en',
      ];
    }
  }

}
