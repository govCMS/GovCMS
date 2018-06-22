<?php

/**
 * @file
 * Store array of data which will be imported.
 */

/**
 * An array of govcms_standard_page pages.
 */
function govcms8_default_content_default_node() {
  return [
    'privacy-statement' => [
      'type' => 'govcms_standard_page',
      'title' => 'Privacy',
      'path' => '/privacy-statement',
      'body' => '<p>Thank you for visiting this GovCMS website.</p><p>The&nbsp;<a href="http://www.comlaw.gov.au/Series/C2004A03712"><em>Privacy Act 1988</em></a>&nbsp;(Privacy Act) regulates how personal information is handled. The Privacy Act defines personal information as:</p><blockquote><em>…information or an opinion, whether true or not, and whether recorded in a material form or not, about an identified individual, or an individual who is reasonably identifiable.</em></blockquote><p>The Office of the Australian Information Commissioner\'s website&nbsp;has a wide range of information about&nbsp;<a href="https://www.oaic.gov.au/privacy-law/privacy-act/">the Privacy Act</a>.</p>',
      'menu' => [
        'title' => 'Privacy',
        'menu_name' => 'footer',
        'weight' => '20'
      ]
    ],
    'disclaimers' => [
      'type' => 'govcms_standard_page',
      'title' => 'Disclaimers',
      'path' => '/disclaimers',
      'body' => '<p>Your use of this website does not:</p><ul><li>affect or reduce any obligation owed to you by any agency;</li><li>reduce any obligation you owe to any agency.</li></ul><p>This website is not a substitute for independent professional advice and you should obtain any appropriate professional advice relevant to your particular circumstances.</p>',
      'menu' => [
        'title' => 'Disclaimers',
        'menu_name' => 'footer',
        'weight' => '15'
      ]
    ],
    'copyright' => [
      'type' => 'govcms_standard_page',
      'title' => 'Copyright',
      'path' => '/copyright',
      'body' => '<p>Information on&nbsp;Australian Government intellectual property rules can be found on the website of the <a href="https://www.communications.gov.au/policy/policy-listing/australian-government-intellectual-property-rules">Department of Communications and the Arts</a>.</p><p>More information about this theme and it\'s licence can be <a href="https://github.com/govcms">found on the GovCMS GitHub</a>.</p>',
      'menu' => [
        'title' => 'Copyright',
        'menu_name' => 'footer',
        'weight' => '10'
      ]
    ],
    'accessibility' => [
      'type' => 'govcms_standard_page',
      'title' => 'Accessibility',
      'path' => '/accessibility',
      'body' => '<p>From <a href="https://en.wikipedia.org/wiki/Web_accessibility">Wikipedia</a>;&nbsp;</p><blockquote><p><em>Web accessibility&nbsp;refers to the&nbsp;<a href="https://en.wikipedia.org/wiki/Inclusion_(disability_rights)">inclusive practice</a>&nbsp;of removing barriers that prevent interaction with, or access to&nbsp;<a href="https://en.wikipedia.org/wiki/Website">websites</a>, by people with&nbsp;<a href="https://en.wikipedia.org/wiki/Disabilities">disabilities</a>. When sites are correctly designed, developed and edited, all users have equal access to information and functionality.</em></p></blockquote>',
      'menu' => [
        'title' => 'Accessibility',
        'menu_name' => 'footer',
        'weight' => '5'
      ]
    ],
    'about-us' => [
      'type' => 'govcms_standard_page',
      'title' => 'Welcome to GovCMS8',
      'path' => '/about-us',
      'body' => '<p>GovCMS is a content management systems maintained by the Australian Government Department of Finance and available to all levels of Australian government.</p><p>Built on Drupal, our&nbsp;aim is to simplify Government ICT and eliminate duplication of activities across agencies by providing a platform where they can easily design, develop and host websites. This reduces the technology and compliance burden on government agencies while providing a more cost-effective option for managing websites, allowing agencies to focus on providing high quality content and services to people.</p>',
      'menu' => [
        'title' => 'Welcome to GovCMS8',
        'menu_name' => 'footer',
        'weight' => '0'
      ]
    ],
    'standard-page-example' => [
      'type' => 'govcms_standard_page',
      'title' => 'Standard page - example',
      'path' => '/standard-page-example',
      'body' => 'Standard page content.',
      'field_thumbnail' => ['standard-page-image'],
      'field_featured_image' => ['featured-image'],
    ],
    // govcms_blog_article content.
    'blog-article-example' => [
      'type' => 'govcms_blog_article',
      'title' => 'Blog article – example',
      'path' => '/blog/example',
      'summary' => 'Use Blog articles to reach your target audience directly.',
      'body' => 'Use Blog articles to reach your target audience directly.',
      'field_thumbnail' => ['blog-article-image'],
      'field_featured_image' => ['featured-image'],
      'field_blog_article_categories' => ['blog-categories-govcms'],
    ],
    // govcms_event content.
    'event-example' => [
      'type' => 'govcms_event',
      'title' => 'Event - example',
      'path' => '/event/example',
      'summary' => 'Use Event to reach your target audience.',
      'body' => 'Use Event to reach your target audience.',
      'field_thumbnail' => ['event-image'],
      'field_featured_image' => ['featured-image'],
      'field_event_categories' => ['event-categories-presentations'],
      'field_event_location' => 'Canberra, Australian capital territory',
      'field_event_date' => [
        'value' => '2018-04-17T09:00:00',
        'end_value' => '2018-04-27T17:00:00',
      ],
    ],
    'event-current-example' => [
      'type' => 'govcms_event',
      'title' => 'Event Current - example',
      'path' => '/event/event-current-example',
      'summary' => 'Use Event to reach your target audience. (Current event)',
      'body' => 'Use Event to reach your target audience. (Current event)',
      'field_thumbnail' => ['event-current-image'],
      'field_featured_image' => ['featured-image'],
      'field_event_categories' => ['event-categories-presentations'],
      'field_event_location' => 'Canberra, Australian capital territory',
      'field_event_date' => [
        'value' => '2018-05-10T09:00:00',
        'end_value' => '2019-05-10T17:00:00',
      ],
    ],
    'event-future-example' => [
      'type' => 'govcms_event',
      'title' => 'Event Future - example',
      'path' => '/event/event-future-example',
      'summary' => 'Use Event to reach your target audience. (Future event)',
      'body' => 'Use Event to reach your target audience. (Future event)',
      'field_thumbnail' => ['event-future-image'],
      'field_featured_image' => ['featured-image'],
      'field_event_categories' => ['event-categories-presentations'],
      'field_event_location' => 'Canberra, Australian capital territory',
      'field_event_date' => [
        'value' => '2019-02-01T09:00:00',
        'end_value' => '2019-02-08T17:00:00',
      ],
    ],
    'event-past-example' => [
      'type' => 'govcms_event',
      'title' => 'Event Past - example',
      'path' => '/event/event-past-example',
      'summary' => 'Use Event to reach your target audience. (Past event)',
      'body' => 'Use Event to reach your target audience. (Past event)',
      'field_thumbnail' => ['event-past-image'],
      'field_featured_image' => ['featured-image'],
      'field_event_categories' => ['event-categories-presentations'],
      'field_event_location' => 'Canberra, Australian capital territory',
      'field_event_date' => [
        'value' => '2017-04-17T09:00:00',
        'end_value' => '2017-04-27T17:00:00',
      ],
    ],
    // govcms_foi content.
    'foi-request-example' => [
      'type' => 'govcms_foi',
      'title' => 'FOI Request - example ',
      'path' => '/freedom-of-information/example',
      'summary' => 'This as an example Freedom of Information Request',
      'body' => 'The broad objective of the Freedom of Information Act 1982 is to give the Australian community access to information held by the Government of the Commonwealth.',
      'field_thumbnail' => ['foi-image'],
      'field_foi_date_listed' => [
        'value' => '2018-04-04',
      ],
      'field_foi_proposed_deletion_date' => [
        'value' => '2019-04-04',
      ],
    ],
    // govcms_blog_article content.
    'news-and-media-example' => [
      'type' => 'govcms_news_and_media',
      'title' => 'News and Media - example',
      'path' => '/news-and-media/example',
      'summary' => 'Use News and Media to reach your target audience directly.',
      'body' => 'Use News and Media to reach your target audience directly.',
      'field_thumbnail' => ['news-and-media-image'],
      'field_featured_image' => ['featured-image'],
      'field_news_categories' => ['news-categories-govcms'],
      'field_media_release_type' => 'media_release',
    ],
    'homepage' => [
      'type' => 'govcms_standard_page',
      'title' => 'GovCMS8 Starter',
      'path' => '/homepage',
      'panelizer' => 'page_edgy',
      'front_page' => TRUE,
      'field_components' => [
        'starter-demo-parliament-house',
        'starter-demo-information',
        'starter-demo-info',
        'starter-demo-hp-content-listing',
      ],
    ],
    'test-page' => [
      'type' => 'govcms_standard_page',
      'title' => 'Test',
      'path' => '/test',
      'summary' => 'Test page demonstrating components, layouts, modifiers and styles.',
      'body' => '<span></span>',
      'panelizer' => 'page_edgy',
      'menu' => [
        'title' => 'Test',
        'menu_name' => 'main',
        'weight' => '10'
      ],
      'field_components' => [
        'test-test-page',
        'test-comp-view-mode-teaser',
        'test-comp-view-mode-teaser-small',
        'test-comp-view-mode-stack-simple',
        'test-comp-view-mode-stack-detail',
        'test-comp-view-mode-summary',
        'test-comp-view-mode-calendar-item',
        'test-comp-view-mode-search',
        'test-comp-il-3-stack-simple',
        'test-comp-il-3-stack-detail',
        'test-comp-node-list-3-stacks',
        'test-comp-node-list-4-stacks',
        'test-comp-node-list-6-stacks',
        'test-comp-content',
        'test-comp-content-default',
        'test-comp-content-alt',
        'test-comp-content-dark',
        'test-comp-content-dark-alt',
        'test-comp-content-style-default',
        'test-comp-content-style-hero',
        'test-comp-modifier-padding',
        'test-comp-modifier-background-image',
        'test-comp-modifier-relative-height',
        'test-comp-modifier-background-parallax',
        'test-comp-modifier-linear-gradient',
        'test-comp-modifier-colour-background',
        'test-comp-single-accordion',
        'test-comp-typography',
        'test-comp-accordion-group',
      ],
    ],
  ];
}
