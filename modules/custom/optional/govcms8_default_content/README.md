# GovCMS 8 Default Content

This module automatically generates default content for GovCMS8.

It'll create basic pages such as accessibility, copyright, disclaimers, privacy and a home page.

It also create a test which can be used to showcase all the different paragraph types.

## How to Import Default Content

The content will be imported when the module is  **installed**.

If you **uninstall** the module the content will stay.

Two Drush commands were created to import and rollback content:

`govcms8:default-content-import`

`govcms8:default-content-rollback`

These commands are especially useful if you're debugging issues with imported content.

## Changed Imported Content

The default content is defined as arrays in `import` folder as `.inc` files.

Currently the module imports media types, nodes, paragraphs and taxonomy terms.

### Add new Page

If you want to add a new default page, open `govcms8_default_content.node.inc` and add the following example into the array.

```
    'disclaimers' => [
      'type' => 'govcms_standard_page',
      'title' => 'Disclaimers',
      'path' => '/disclaimers',
      'body' => '<p>Body content</p>',
      'menu' => [
        'title' => 'Disclaimers',
        'menu_name' => 'footer',
        'weight' => '15'
      ]
    ],
```

### Add Paragraphs to page

First, define your paragraphs in `govcms8_default_content.paragraph.inc` and then add the paragraph to a content type and/or paragraph type like the following:

```
    'disclaimers' => [
      'type' => 'govcms_standard_page',
      'field_components' => [
        'starter-demo-parliament-house',
        'starter-demo-information',
        'starter-demo-info',
        'starter-demo-hp-content-listing',
      ],
    ],
```

Add the machine name of the paragraph to a paragraph field.



