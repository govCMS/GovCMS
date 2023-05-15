# Entity Embed Module

[Entity Embed](https://www.drupal.org/project/entity_embed) module
allows any entity to be embedded using a text editor.

## Requirements

* Drupal 8
* [Embed](https://www.drupal.org/project/embed) module

## Installation

Entity Embed can be installed via the
[standard Drupal installation process](http://drupal.org/node/895232).

## Configuration

* Install and enable [Embed](https://www.drupal.org/project/embed) module.
* Install and enable [Entity Embed](https://www.drupal.org/project/entity_embed)
  module.
* Go to the 'Text formats and editors' configuration page: `/admin/config/content/formats`,
  and for each text format/editor combo where you want to embed entities,
  do the following:
  * Enable the "Display embedded entities" filter for the desired text formats
    on the Text Formats configuration page.
  * Drag and drop the 'E' button into the Active toolbar.
  * If the text format uses the 'Limit allowed HTML tags and correct
    faulty HTML' filter, ensure the necessary tags and attributes were
    automatically whitelisted:
    ```<drupal-entity data-entity-type data-entity-uuid data-view-mode data-entity-embed-display data-entity-embed-display-settings data-align data-caption data-embed-button data-langcode alt title>```
    appears in the 'Allowed HTML tags' setting.
    *Warning: If you were using the module in very early pre-alpha
    stages you might need to add `data-entity-id` to the list of allowed
    attributes. Similarly, if you have been using the module in pre-beta stages,
    you need to white-list the `data-entity-embed-settings` attribute.*
  * If you're using both the 'Align images' and 'Caption images' filters make
    sure the 'Align images' filter is run before the 'Caption images' filter in
    the **Filter processing order** section. (Explanation: Due to the
    implementation details of the two filters it is important to execute them in
    the right sequence in order to obtain a sensible final markup. In practice
    this means that the alignment filter has to be run before the caption
    filter, otherwise the alignment class will appear inside the <figure> tag
    (instead of appearing on it) the caption filter produces.)

## Usage

* For example, create a new *Article* content.
* Click on the 'E' button in the text editor.
* Enter part of the title of the entity you're looking for and select
  one of the search results.
* If the entity you select is a node entity, for **Display as** you can choose
  one of the following options:
  * Entity ID
  * Label
  * Full content
  * RSS
  * Search index
  * Search result highlighting input
  * Teaser
* The last five options depend on the view modes you have on the entity.
* Optionally, choose to align left, center or right.
**Rendered Entity** was available before but now the view modes are
 available as entity embed display plugins.

## Embedding entities without WYSIWYG

Users should be embedding entities using the CKEditor WYSIWYG button as
described above. This section is more technical about the HTML markup
that is used to embed the actual entity.

### Example:
```html
<drupal-entity data-entity-type="node" data-entity-uuid="07bf3a2e-1941-4a44-9b02-2d1d7a41ec0e" data-entity-embed-display="entity_reference:entity_reference_entity_view" data-entity-embed-display-settings='{"view_mode":"teaser"}' />
```

## Entity Embed Display Plugins

Embedding entities uses an Entity Embed Display plugin, provided in the
`data-entity-embed-display` attribute. By default we provide four
different Entity Embed Display plugins out of the box:

- entity_reference:_formatter_id_: Renders the entity using a specific
  Entity Reference field formatter.
- entity_reference:_entity_reference_label_: Renders the entity using
  the "Label" formatter.
- file:_formatter_id_: Renders the entity using a specific File field
  formatter. This will only work if the entity is a file entity type.
- image:_formatter_id_: Renders the entity using a specific Image field
  formatter. This will only work if the entity is a file entity type,
  and the file is an image.  For the alt and title text to save, the `alt`
  and `title` attributes must be allowed on the `<drupal-entity>` HTML tag
  in the "Allowed HTML tags" for text formats that have the "Limit allowed
  HTML tags and correct faulty HTML" filter enabled.

Configuration for the Entity Embed Display plugin can be provided by
using a `data-entity-embed-display-settings` attribute, which contains a
JSON-encoded array value. Note that care must be used to use single
quotes around the attribute value since JSON-encoded arrays typically
contain double quotes.

The above examples render the entity using the
_entity_reference_entity_view_ formatter from the Entity Reference
module, using the _teaser_ view mode.
