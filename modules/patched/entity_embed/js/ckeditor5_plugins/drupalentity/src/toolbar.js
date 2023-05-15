import { Plugin, icons } from 'ckeditor5/src/core';
import { isWidget, WidgetToolbarRepository } from 'ckeditor5/src/widget';
import { ButtonView } from "ckeditor5/src/ui";

export default class EntityEmbedToolbar extends Plugin {

  /**
   * @inheritdoc
   */
  static get requires() {
    return [WidgetToolbarRepository];
  }

  /**
   * @inheritdoc
   */
  init() {
    const editor = this.editor;
    const entityEmbedEditing = editor.plugins.get('EntityEmbedEditing');
    const options = editor.config.get('entityEmbed');
    const { dialogSettings = {} } = options;

    editor.ui.componentFactory.add('entityEmbedEdit', (locale) => {
      let buttonView = new ButtonView(locale);

      buttonView.set({
        label: editor.t('Edit'),
        icon: icons.pencil,
        tooltip: true,
      })

      this.listenTo(buttonView, 'execute', (eventInfo) => {
        let element = editor.model.document.selection.getSelectedElement();
        let libraryURL = Drupal.url('entity-embed/dialog/' + options.format + '/' + element.getAttribute('drupalEntityEmbedButton'));

        let existingValues = {};

        for (let [key, value] of element.getAttributes()) {
          let attribute = entityEmbedEditing.attrs[key]
          if (attribute) {
            existingValues[attribute] = value
          }
        }

        // Open a dialog to select entity to embed.
        this._openDialog(
          libraryURL,
          existingValues,
          ({ attributes }) => {
            editor.execute('insertEntityEmbed', attributes);
            editor.editing.view.focus();
          },
          dialogSettings,
        )
      });

      return buttonView;
    })
  }

  /**
   * @inheritdoc
   */
  afterInit() {
    const { editor } = this
    if (!editor.plugins.has('WidgetToolbarRepository')) {
      return;
    }
    const widgetToolbarRepository = editor.plugins.get('WidgetToolbarRepository')

    widgetToolbarRepository.register('entityEmbed', {
      items: ['entityEmbedEdit'],
      getRelatedElement(selection) {
        const viewElement = selection.getSelectedElement()
        if (!viewElement) {
          return null
        }
        if (!isWidget(viewElement)) {
          return null
        }
        if (!viewElement.getCustomProperty('drupalEntity')) {
          return null
        }

        return viewElement
      },
    })
  }

  /**
   * @param {string} url
   *   The URL that contains the contents of the dialog.
   * @param {object} existingValues
   *   Existing values that will be sent via POST to the url for the dialog
   *   contents.
   * @param {function} saveCallback
   *   A function to be called upon saving the dialog.
   * @param {object} dialogSettings
   *   An object containing settings to be passed to the jQuery UI.
   */
  _openDialog(url, existingValues, saveCallback, dialogSettings) {
    // Add a consistent dialog class.
    const classes = dialogSettings.dialogClass
      ? dialogSettings.dialogClass.split(' ')
      : [];
    classes.push('ui-dialog--narrow');
    dialogSettings.dialogClass = classes.join(' ');
    dialogSettings.autoResize =
      window.matchMedia('(min-width: 600px)').matches;
    dialogSettings.width = 'auto';

    const ckeditorAjaxDialog = Drupal.ajax({
      dialog: dialogSettings,
      dialogType: 'modal',
      selector: '.ckeditor5-dialog-loading-link',
      url,
      progress: { type: 'fullscreen' },
      submit: {
        editor_object: existingValues,
      },
    });
    ckeditorAjaxDialog.execute();

    // Store the save callback to be executed when this dialog is closed.
    Drupal.ckeditor5.saveCallback = saveCallback;
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'EntityEmbedToolbar';
  }

}
