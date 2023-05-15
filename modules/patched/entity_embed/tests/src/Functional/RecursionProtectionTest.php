<?php

namespace Drupal\Tests\entity_embed\Functional;

use Drupal\entity_embed\Plugin\Filter\EntityEmbedFilter;

/**
 * Tests recursive rendering protection.
 *
 * @group entity_embed
 *
 * @see \Drupal\Tests\entity_embed\Kernel\EntityEmbedFilterTest::testRecursionProtection
 */
class RecursionProtectionTest extends EntityEmbedTestBase {

  /**
   * Tests self embedding.
   */
  public function testSelfEmbedding() {
    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => "Pirate Chinchilla LLama",
      'body' => [
        'value' => 'temp',
        'format' => 'custom_format',
      ],
    ]);
    $node->save();
    $content = '<div class="pirate">Ahoy, Matey!</div> <drupal-entity data-entity-type="node" data-entity-uuid="' . $node->uuid() . '" data-entity-embed-display="view_mode:node.full"></drupal-entity>';
    $node->set('body', [
      'value' => $content,
      'format' => 'custom_format',
    ]);
    $node->save();
    $this->drupalGet('node/' . $node->id());
    $this->assertCount(EntityEmbedFilter::RECURSIVE_RENDER_LIMIT + 1, $this->getSession()->getPage()->findAll('xpath', '//div[@class="pirate"]'));
  }

  /**
   * Tests circular embedding.
   */
  public function testCircularEmbedding() {
    $node1 = $this->drupalCreateNode([
      'type' => 'article',
      'title' => "Grandpa",
      'body' => [
        'value' => 'temp',
        'format' => 'custom_format',
      ],
    ]);
    $node1->save();
    $node2 = $this->drupalCreateNode([
      'type' => 'article',
      'title' => "Son",
      'body' => [
        'value' => 'temp',
        'format' => 'custom_format',
      ],
    ]);
    $node2->save();
    $content = '<div class="node-2-embed">Embedded Son</div> <drupal-entity data-entity-type="node" data-entity-uuid="' . $node2->uuid() . '" data-view-mode="full"></drupal-entity>';
    $node1->set('body', [
      'value' => $content,
      'format' => 'custom_format',
    ]);
    $node1->save();
    $content = '<div class="node-1-embed">Embedded Son who is own grandpa</div> <drupal-entity data-entity-type="node" data-entity-uuid="' . $node1->uuid() . '" data-view-mode="full"></drupal-entity>';
    $node2->set('body', [
      'value' => $content,
      'format' => 'custom_format',
    ]);
    $node2->save();
    $this->drupalGet('node/' . $node1->id());
    $page = $this->getSession()->getPage();
    $this->assertCount(EntityEmbedFilter::RECURSIVE_RENDER_LIMIT, $page->findAll('xpath', '//div[@class="node-1-embed"]'));
    $this->assertCount(EntityEmbedFilter::RECURSIVE_RENDER_LIMIT + 1, $page->findAll('xpath', '//div[@class="node-2-embed"]'));

    $this->drupalGet('node/' . $node2->id());
    $page = $this->getSession()->getPage();
    $this->assertCount(EntityEmbedFilter::RECURSIVE_RENDER_LIMIT + 1, $page->findAll('xpath', '//div[@class="node-1-embed"]'));
    $this->assertCount(EntityEmbedFilter::RECURSIVE_RENDER_LIMIT, $page->findAll('xpath', '//div[@class="node-2-embed"]'));
  }

}
