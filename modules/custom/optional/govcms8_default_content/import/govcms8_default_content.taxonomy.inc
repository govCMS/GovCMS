<?php

/**
 * @file
 * Store array of data which will be imported.
 */

/**
 * An array of taxonomy terms.
 */
function govcms8_default_content_default_taxonomy() {
  return [
    'blog-categories-govcms' => [
      'vid' => 'blog_article_categories',
      'name' => 'GovCMS',
    ],
    'blog-categories-internet' => [
      'vid' => 'blog_article_categories',
      'name' => 'Internet',
    ],
    'blog-categories-open-data' => [
      'vid' => 'blog_article_categories',
      'name' => 'Open Data',
    ],
    'blog-categories-transport' => [
      'vid' => 'blog_article_categories',
      'name' => 'Transport',
    ],
    'event-categories-meetup' => [
      'vid' => 'event_categories',
      'name' => 'Meetup',
    ],
    'event-categories-presentations' => [
      'vid' => 'event_categories',
      'name' => 'Presentations',
    ],
    'news-categories-govcms' => [
      'vid' => 'news_categories',
      'name' => 'GovCMS',
    ],
  ];
}
