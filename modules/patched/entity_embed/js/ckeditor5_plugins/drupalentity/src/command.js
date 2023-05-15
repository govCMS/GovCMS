import {Command} from 'ckeditor5/src/core';

export default class InsertEntityEmbedCommand extends Command {

  execute(attributes) {
    const { model } = this.editor;
    const entityEmbedEditing = this.editor.plugins.get('EntityEmbedEditing');

    // Create object that contains supported data-attributes in view data by
    // flipping `EntityEmbedEditing.attrs` object (i.e. keys from object become
    // values and values from object become keys).
    const dataAttributeMapping = Object.entries(entityEmbedEditing.attrs).reduce(
      (result, [key, value]) => {
        result[value] = key;
        return result;
      },
      {},
    );

    // \Drupal\entity_embed\Form\EntityEmbedDialog returns data in keyed by
    // data-attributes used in view data. This converts data-attribute keys to
    // keys used in model.
    const modelAttributes = Object.keys(attributes).reduce(
      (result, attribute) => {
        if (dataAttributeMapping[attribute]) {
          result[dataAttributeMapping[attribute]] = attributes[attribute];
        }
        return result;
      },
      {},
    );

    model.change((writer) => {
      model.insertContent(createEntityEmbed(writer, modelAttributes));
    });
  }

  refresh() {
    const model = this.editor.model;
    const selection = model.document.selection;
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'drupalEntity',
    );
    this.isEnabled = allowedIn !== null;
  };

}

function createEntityEmbed(writer, attributes) {
  return writer.createElement('drupalEntity', attributes);
}
