<?php

/**
 * @file
 * Store array of data which will be imported.
 */

/**
 * An array of paragraphs.
 */
function govcms8_default_content_default_paragraph() {
  return [
    'starter-demo-information' => [
      'type' => 'content',
      'field_title' => 'GovCMS Information',
      'field_body' => '<p>Built on Drupal, our&nbsp;aim is to simplify Government ICT and eliminate duplication of activities across agencies by providing a platform where they can easily design, develop and host websites. This reduces the technology and compliance burden on government agencies while providing a more cost-effective option for managing websites, allowing agencies to focus on providing high quality content and services to people.</p>',
      'field_c_colour_classes' => 'color--dark',
      'field_c_style_classes' => 'style--hero',
      'field_read_more' => [
        'uri' => 'internal:/about-us',
        'title' => 'About GovCMS',
      ],
      'field_modifiers' =>
        ['starter-demo-information--mod-color-background-modifier'],
    ],
    'starter-demo-information--mod-color-background-modifier' => [
      'type' => 'color_background_modifier',
      'field_mod_bc_bg_color_val' => '#135E70',
      'field_mod_bc_bg_h_color_val' => '#0d414d',
      'field_mod_transition_duration' => '0.30',
    ],
    'starter-demo-info' => [
      'type' => 'content',
      'field_title' => 'GovCMS Starter Info',
      'field_body' => '<p>GovCMS8 Starter provides site builders with the&nbsp;scaffolding they need to make an Australian Government website out of the box.&nbsp;</p><p>The theme makes use of components from&nbsp;the&nbsp;<a href="https://designsystem.gov.au">Australian Government Design System</a>&nbsp;built by the&nbsp;<a href="https://www.dta.gov.au">Digital Transformation Agency</a>.&nbsp;</p>',
      'field_c_colour_classes' => 'color--alt',
      'field_c_style_classes' => 'style--hero',
    ],
    'starter-demo-parliament-house' => [
      'type' => 'content',
      'field_title' => 'Welcome to GovCMS',
      'field_heading' => 'Welcome to GovCMS',
      'field_body' => '<p>GovCMS is a content management systems maintained by the Australian Government Department of Finance and available to all levels of Australian government.</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>',
      'field_c_colour_classes' => 'color--dark',
      'field_c_style_classes' => 'style--hero',
      'field_modifiers' =>
        ['starter-demo-parliament-house--mod-background-image-modifier',
          'starter-demo-parliament-house--mod-relative-height-modifier',
        ],
    ],
    'starter-demo-parliament-house--mod-background-image-modifier' => [
      'type' => 'parallax_bg_modifier',
      'field_mod_parallax' => ['australian-parliament-house'],
      'field_mod_parallax_speed' => '0.10',
    ],
    'starter-demo-parliament-house--mod-relative-height-modifier' => [
      'type' => 'relative_height_modifier',
      'field_mod_relative_height' => '3',
    ],
    'starter-demo-hp-content-listing' => [
      'type' => 'node_list',
      'field_title' => 'HP content list',
      'field_heading' => 'Content example (demo)',
      'field_body' => '<p>Node List of example content, rendered with Stack Simple view mode in equal columns layout.</p>',
      'field_nl_view_mode' => 'stack_simple',
      'field_nl_layout_classes' => 'list-layout--equal',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'event-example',
          'news-and-media-example',
          'foi-request-example',
          'standard-page-example',
        ],
      'field_modifiers' =>
        ['starter-demo-hp-content-listing--mod-padding-modifier'],
    ],
    'starter-demo-hp-content-listing--mod-padding-modifier' => [
      'type' => 'padding_modifier',
      'field_mod_padding_t_size' => '2.00',
      'field_mod_padding_t_units' => 'em',
      'field_mod_padding_r_size' => '2.00',
      'field_mod_padding_r_units' => 'em',
      'field_mod_padding_b_size' => '2.00',
      'field_mod_padding_b_units' => 'em',
      'field_mod_padding_l_size' => '2.00',
      'field_mod_padding_l_units' => 'em',
    ],
    'test-test-page' => [
      'type' => 'content',
      'field_title' => 'Test page',
      'field_body' => '<h2>GovCMS Test page</h2>',
      'field_c_colour_classes' => 'color--dark',
      'field_modifiers' => ['test-test-page--mod-relative-height-modifier',
        'test-test-page--mod-linear-gradient-modifier',
      ],
    ],
    'test-test-page--mod-relative-height-modifier' => [
      'type' => 'relative_height_modifier',
      'field_mod_relative_height' => '3',
      'field_mod_vertical_align' => 'middle',
      'field_mod_media_query' => '(min-width:768px)',
    ],
    'test-test-page--mod-linear-gradient-modifier' => [
      'type' => 'custom_linear_gradient_modifier',
      'field_mod_cl_gradient_colors' => [
        '#135E70',
        '#0A343F',
      ],
      'field_mod_cl_gradient_direction' => '90.00',
    ],
    'test-comp-view-mode-teaser' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Teaser',
      'field_heading' => 'View mode – Teaser',
      'field_body' => 'Node List of example content, rendered with Teaser view mode in a single column.',
      'field_nl_view_mode' => 'teaser',
      'field_nl_layout_classes' => 'list-layout--12',
      'field_nl_nodes' => ['blog-article-example', 'event-example'],
      'field_read_more' => [
        'uri' => 'internal:/events',
        'title' => 'Read more',
      ],
    ],
    'test-comp-view-mode-teaser-small' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Teaser small',
      'field_heading' => 'View mode – Teaser small',
      'field_body' => 'Node List of example content, rendered with Teaser Small view mode in a single column.',
      'field_nl_view_mode' => 'teaser_small',
      'field_nl_layout_classes' => 'list-layout--12',
      'field_nl_nodes' => ['blog-article-example', 'event-example'],
      'field_read_more' => [
        'uri' => 'internal:/events',
        'title' => 'Read more',
      ],
    ],
    'test-comp-view-mode-stack-simple' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Stack Simple',
      'field_heading' => 'View mode – Stack Simple',
      'field_body' => 'Node List of example content, rendered with Stack Simple view mode in three columns.',
      'field_nl_view_mode' => 'stack_simple',
      'field_nl_layout_classes' => 'list-layout--4-4-4',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'event-example',
          'news-and-media-example',
          'foi-request-example',
          'standard-page-example',
        ],
    ],
    'test-comp-view-mode-stack-detail' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Stack Detail',
      'field_heading' => 'View mode – Stack Detail',
      'field_body' => 'Node List of example content, rendered with Stack Detail view mode in a three columns.',
      'field_nl_view_mode' => 'stack_detail',
      'field_nl_layout_classes' => 'list-layout--3-3-3-3',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'event-example',
          'news-and-media-example',
          'standard-page-example',
        ],
    ],
    'test-comp-view-mode-summary' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Summary',
      'field_heading' => 'View mode – Summary',
      'field_body' => 'Node List of example content, rendered with Summary view mode in a single column.',
      'field_nl_view_mode' => 'summary',
      'field_nl_layout_classes' => 'list-layout--12',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'event-example',
          'standard-page-example',
        ],
    ],
    'test-comp-view-mode-calendar-item' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Calendar Item',
      'field_heading' => 'View mode – Calendar Item',
      'field_body' => 'Node List of example content, rendered with Calendar Item view mode in a single column.',
      'field_nl_view_mode' => 'calendar_item',
      'field_nl_layout_classes' => 'list-layout--12',
      'field_nl_nodes' =>
        [
          'event-example',
          'event-past-example',
          'event-current-example',
          'event-future-example',
          'blog-article-example',
          'foi-request-example',
          'news-and-media-example',
          'standard-page-example',
        ],
    ],
    'test-comp-view-mode-search' => [
      'type' => 'node_list',
      'field_title' => 'View mode – Search',
      'field_heading' => 'View mode – Search',
      'field_body' => 'Node List of example content, rendered with Search view mode in a single column.',
      'field_nl_view_mode' => 'search',
      'field_nl_layout_classes' => 'list-layout--12',
      'field_nl_nodes' => ['blog-article-example', 'event-example',
        'standard-page-example',
      ],
    ],
    'test-comp-il-3-stack-simple' => [
      'type' => 'item_list',
      'field_title' => 'Component: Item List: 3 Stack Simple',
      'field_heading' => 'Component: Item List: 3 Stack Simple',
      'field_body' => 'Item List paragraph bundle contains a list of Items which can model any object',
      'field_il_view_mode' => 'stack_simple',
      'field_il_layout_classes' => 'list-layout--4-4-4',
      'field_il_items' =>
        [
          'test-comp-il-3-stack-simple--item-1',
          'test-comp-il-3-stack-simple--item-2',
          'test-comp-il-3-stack-simple--item-3',
        ],
    ],
    'test-comp-il-3-stack-simple--item-1' => [
      'type' => 'item',
      'field_title' => 'First item',
      'field_heading' => 'First item',
      'field_item_info' => 'First item info',
      'field_item_summary' => 'Capitalize on low hanging fruit to identify a ballpark value added activity to beta test. Override the digital divide with additional clickthroughs from DevOps. Nanotechnology immersion along the information highway will close the loop on focusing solely on the bottom line.',
      'field_item_image' => ['item-image-1'],
      'field_item_url' => [
        'uri' => 'internal:/copyright',
      ],
    ],
    'test-comp-il-3-stack-simple--item-2' => [
      'type' => 'item',
      'field_title' => 'Second item',
      'field_heading' => 'Second item',
      'field_item_info' => 'Second item info',
      'field_item_summary' => 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition. Organically grow the holistic world view of disruptive innovation via workplace diversity and empowerment',
      'field_item_image' => ['item-image-2'],
      'field_item_url' => [
        'uri' => 'https://drupal.org',
      ],
    ],
    'test-comp-il-3-stack-simple--item-3' => [
      'type' => 'item',
      'field_title' => 'Third item',
      'field_heading' => 'Third item',
      'field_item_info' => 'Third item info',
      'field_item_summary' => 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition. Organically grow the holistic world view of disruptive innovation via workplace diversity and empowerment',
      'field_item_image' => ['item-image-3'],
    ],
    'test-comp-il-3-stack-detail' => [
      'type' => 'item_list',
      'field_title' => 'Component: Item List: 3 Stack Detail',
      'field_heading' => 'Component: Item List: 3 Stack Detail',
      'field_body' => 'Item List paragraph bundle contains a list of Items which can model any object.',
      'field_il_view_mode' => 'stack_detail',
      'field_il_layout_classes' => 'list-layout--4-4-4',
      'field_il_items' =>
        [
          'test-comp-il-3-stack-detail--item-1',
          'test-comp-il-3-stack-detail--item-2',
          'test-comp-il-3-stack-detail--item-3',
        ],
    ],
    'test-comp-il-3-stack-detail--item-1' => [
      'type' => 'item',
      'field_title' => 'First item',
      'field_heading' => 'First item',
      'field_item_info' => 'First item info',
      'field_item_summary' => 'First item content.',
      'field_item_image' => ['item-image-3'],
      'field_item_url' => [
        'uri' => 'internal:/privacy-statement',
      ],
    ],
    'test-comp-il-3-stack-detail--item-2' => [
      'type' => 'item',
      'field_title' => 'Second item',
      'field_heading' => 'Second item',
      'field_item_info' => 'Second item info',
      'field_item_summary' => 'Second item content.',
      'field_item_image' => ['item-image-2'],
    ],
    'test-comp-il-3-stack-detail--item-3' => [
      'type' => 'item',
      'field_title' => 'Third item',
      'field_heading' => 'Third item',
      'field_item_info' => 'Third item info',
      'field_item_summary' => 'Third item content.',
      'field_item_image' => ['item-image-1'],
    ],
    'test-comp-node-list-3-stacks' => [
      'type' => 'node_list',
      'field_title' => 'Component: Node list: 3 Stacks',
      'field_heading' => 'Component: Node list: 3 Stacks',
      'field_body' => 'Node List paragraph bundle, rendered with Stack Simple view mode in 3 columns on Dark Alt background with Hero style.',
      'field_nl_view_mode' => 'stack_detail',
      'field_nl_layout_classes' => 'list-layout--4-4-4',
      'field_nl_colour_classes' => 'color--dark-alt',
      'field_nl_style_classes' => 'style--hero',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'event-example',
          'news-and-media-example',
        ],
    ],
    'test-comp-node-list-4-stacks' => [
      'type' => 'node_list',
      'field_title' => 'Component: Node list: 4 Stacks',
      'field_heading' => 'Component: Node list: 4 Stacks',
      'field_body' => 'Node List paragraph bundle, rendered with Stack Detail view mode in 4 columns.',
      'field_nl_view_mode' => 'stack_detail',
      'field_nl_layout_classes' => 'list-layout--3-3-3-3',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'news-and-media-example',
          'event-example',
          'news-and-media-example',
        ],
    ],
    'test-comp-node-list-6-stacks' => [
      'type' => 'node_list',
      'field_title' => 'Component: Node list: 6 Stacks',
      'field_heading' => 'Component: Node list: 6 Stacks',
      'field_body' => 'Node List paragraph bundle, rendered with Stack Simple view mode in 6 columns.',
      'field_nl_view_mode' => 'stack_simple',
      'field_nl_layout_classes' => 'list-layout--2-2-2-2-2-2',
      'field_nl_nodes' =>
        [
          'blog-article-example',
          'event-example',
          'news-and-media-example',
          'standard-page-example',
          'foi-request-example',
          'blog-article-example',
          'news-and-media-example',
          'event-example',
        ],
    ],
    'test-comp-content' => [
      'type' => 'content',
      'field_title' => 'Component: Content',
      'field_heading' => 'Component: Content',
      'field_body' => '<p>Content paragraph bundle contains arbitrary HTML.</p><p>Bring to the table win-win survival strategies to ensure proactive domination. At the end of the day, going forward, a new normal that has evolved from generation X is on the runway heading towards a streamlined cloud solution. User generated content in real-time will have multiple touchpoints for offshoring</p>',
    ],
    'test-comp-content-default' => [
      'type' => 'content',
      'field_title' => 'Content Colour Selector: Default',
      'field_heading' => 'Content Colour Selector: Default',
      'field_body' => 'Content component with default colour selected.',
    ],
    'test-comp-content-alt' => [
      'type' => 'content',
      'field_title' => 'Content Colour Selector: Alt',
      'field_heading' => 'Content Colour Selector: Alt',
      'field_body' => 'Content component with alt colour selected.',
      'field_c_colour_classes' => 'color--alt',
    ],
    'test-comp-content-dark' => [
      'type' => 'content',
      'field_title' => 'Content Colour Selector: Dark',
      'field_heading' => 'Content Colour Selector: Dark',
      'field_body' => 'Content component with dark colour selected.',
      'field_c_colour_classes' => 'color--dark',
    ],
    'test-comp-content-dark-alt' => [
      'type' => 'content',
      'field_title' => 'Content Colour Selector: Dark Alt',
      'field_heading' => 'Content Colour Selector: Dark Alt',
      'field_body' => 'Content component with dark alt colour selected.',
      'field_c_colour_classes' => 'color--dark-alt',
    ],
    'test-comp-content-style-default' => [
      'type' => 'content',
      'field_title' => 'Content Style Selector: Default',
      'field_heading' => 'Content Style Selector: Default',
      'field_body' => 'Content component with default style selected.',
    ],
    'test-comp-content-style-hero' => [
      'type' => 'content',
      'field_title' => 'Content Style Selector: Hero',
      'field_heading' => 'Content Style Selector: Hero',
      'field_body' => 'Content component with hero style selected. (Dark Colour Selector added for clarity).',
      'field_c_colour_classes' => 'color--dark',
      'field_c_style_classes' => 'style--hero',
    ],
    'test-comp-modifier-padding' => [
      'type' => 'content',
      'field_title' => 'Modifier: Padding',
      'field_heading' => 'Modifier: Padding',
      'field_body' => '<p>Content component with Padding modifier (and Colours modifier).</p><p>Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition. Organically grow the holistic world view of disruptive innovation via workplace diversity and empowerment</p>',
      'field_modifiers' =>
        [
          'test-comp-modifier-padding--mod-padding-modifier',
          'test-comp-modifier-padding--mod-color-background-modifier',
        ],
    ],
    'test-comp-modifier-padding--mod-padding-modifier' => [
      'type' => 'padding_modifier',
      'field_mod_padding_t_size' => '2.00',
      'field_mod_padding_t_units' => 'em',
      'field_mod_padding_r_size' => '2.00',
      'field_mod_padding_r_units' => 'em',
      'field_mod_padding_b_size' => '2.00',
      'field_mod_padding_b_units' => 'em',
      'field_mod_padding_l_size' => '2.00',
      'field_mod_padding_l_units' => 'em',
    ],
    'test-comp-modifier-padding--mod-color-background-modifier' => [
      'type' => 'color_background_modifier',
      'field_mod_bc_bg_color_val' => '#F5F5F5',
    ],
    'test-comp-modifier-background-image' => [
      'type' => 'content',
      'field_title' => 'Modifier: Background Image',
      'field_heading' => 'Modifier: Background Image',
      'field_body' => '<p dir="ltr">Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier. Content component with Background image modifier.</p>',
      'field_modifiers' =>
        [
          'test-comp-modifier-background-image--mod-background-image-modifier',
        ],
    ],
    'test-comp-modifier-background-image--mod-background-image-modifier' => [
      'type' => 'image_bg_modifier',
      'field_mod_image' => ['australian-parliament-house'],
      'field_mod_bgi_color_val' => '#f5f5f5',
      'field_mod_image_style' => 'stretch',
      'field_mod_image_position' => 'center-top',
    ],
    'test-comp-modifier-relative-height' => [
      'type' => 'content',
      'field_title' => 'Modifier: Relative Height',
      'field_heading' => 'Modifier: Relative Height',
      'field_body' => '<p dir="ltr">Content component with Relative Height modifier (and Colours modifier).</p><p dir="ltr">Set to 4:1 ratio.</p>',
      'field_modifiers' => ['test-comp-modifier-relative-height--mod-relative-height-modifier'],
    ],
    'test-comp-modifier-relative-height--mod-relative-height-modifier' => [
      'type' => 'relative_height_modifier',
      'field_mod_relative_height' => '4',
      'field_mod_vertical_align' => 'middle',
      'field_mod_media_query' => '(min-width:768px)',
    ],
    'test-comp-modifier-background-parallax' => [
      'type' => 'content',
      'field_title' => 'Modifier: Background Parallax',
      'field_heading' => 'Modifier: Background Parallax',
      'field_body' => 'Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier. Content component with Background Parallax modifier.',
      'field_c_colour_classes' => 'color--dark',
      'field_modifiers' =>
        [
          'test-comp-modifier-background-parallax--mod-background-image-modifier',
        ],
    ],
    'test-comp-modifier-background-parallax--mod-background-image-modifier' => [
      'type' => 'parallax_bg_modifier',
      'field_mod_parallax' => ['australian-parliament-house'],
      'field_mod_bgp_color_val' => '#EBEBEB',
      'field_mod_parallax_speed' => '0.45',
    ],
    'test-comp-modifier-linear-gradient' => [
      'type' => 'content',
      'field_title' => 'Modifier: Linear Gradient',
      'field_heading' => 'Modifier: Linear Gradient',
      'field_body' => 'Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier. Content component with Linear Gradient modifier.',
      'field_c_colour_classes' => 'color--dark',
      'field_modifiers' =>
        [
          'test-comp-modifier-linear-gradient--mod-linear-gradient-modifier',
        ],
    ],
    'test-comp-modifier-linear-gradient--mod-linear-gradient-modifier' => [
      'type' => 'custom_linear_gradient_modifier',
      'field_mod_cl_gradient_colors' => [
        '#135E70',
        '#0A343F',
      ],
      'field_mod_cl_gradient_direction' => '90.00',
    ],
    'test-comp-modifier-colour-background' => [
      'type' => 'content',
      'field_title' => 'Modifier: Colour Background ',
      'field_heading' => 'Modifier: Colour Background ',
      'field_body' => 'Content component with Colour Background modifier. Relative Height modifier added to get the size.',
      'field_modifiers' =>
        ['test-comp-modifier-colour-background--mod-color-background-modifier',
          'test-comp-modifier-colour-background--mod-relative-height-modifier',
        ],
    ],
    'test-comp-modifier-colour-background--mod-color-background-modifier' => [
      'type' => 'color_background_modifier',
      'field_mod_bc_bg_color_val' => '#F9DBE5',
      'field_mod_bc_bg_h_color_val' => '#FEF8F9',
      'field_mod_transition_duration' => '1.00',
    ],
    'test-comp-modifier-colour-background--mod-relative-height-modifier' => [
      'type' => 'relative_height_modifier',
      'field_mod_relative_height' => '4',
      'field_mod_vertical_align' => 'middle',
      'field_mod_media_query' => '(min-width:768px)',
    ],
    'test-comp-single-accordion' => [
      'type' => 'accordion',
      'field_title' => 'Accordion item title (Fast, Closed)',
      'field_heading' => 'Accordion item heading (Fast, Closed)',
      'field_accordion_body' => 'Accordion item body',
      'field_accordion_speed' => 'fast',
      'field_accordion_orientation' => 'closed',
    ],
    'test-comp-accordion-group' => [
      'type' => 'accordion_group',
      'field_title' => 'Accordion group',
      'field_heading' => 'Accordion group',
      'field_body' => 'Accordion group content',
      'field_show_open_all' => TRUE,
      'field_show_close_all' => TRUE,
      'field_show_toggle_all' => TRUE,
      'field_ag_style_classes' => 'style--hero',
      'field_accordions' =>
        [
          'test-comp-accordion-group--item-1',
          'test-comp-accordion-group--item-2',
          'test-comp-accordion-group--item-3',
        ],
    ],
    'test-comp-accordion-group--item-1' => [
      'type' => 'accordion',
      'field_title' => 'Accordion item title (Slow, Closed)',
      'field_heading' => 'Accordion item heading (Slow, Closed)',
      'field_accordion_body' => 'Accordion item body',
      'field_accordion_speed' => 'slow',
      'field_accordion_orientation' => 'closed',
    ],
    'test-comp-accordion-group--item-2' => [
      'type' => 'accordion',
      'field_title' => 'Accordion item title 2 (Fast, Open)',
      'field_heading' => 'Accordion item heading 2 (Fast, Open)',
      'field_accordion_body' => 'Accordion item body 2',
      'field_accordion_speed' => 'fast',
      'field_accordion_orientation' => 'open',
    ],
    'test-comp-accordion-group--item-3' => [
      'type' => 'accordion',
      'field_title' => 'Accordion item title 3 (Fast, Closed)',
      'field_heading' => 'Accordion item heading 3 (Fast, Closed)',
      'field_accordion_body' => 'Accordion item body 3',
      'field_accordion_speed' => 'fast',
      'field_accordion_orientation' => 'closed',
    ],
    'test-comp-typography' => [
      'type' => 'content',
      'field_title' => 'Typography',
      'field_heading' => 'Typography – basic HTML elements',
      'field_body' => [
        'file' => 'test-comp-typography.html'
      ],
    ],
  ];
}
