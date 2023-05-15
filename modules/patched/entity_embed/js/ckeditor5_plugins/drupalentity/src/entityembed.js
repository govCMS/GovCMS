import EntityEmbedEditing from './editing';
import EntityEmbedToolbar from './toolbar';
import EntityEmbedUI from './ui';
import { Plugin } from 'ckeditor5/src/core';

export default class EntityEmbed extends Plugin {

  static get requires() {
    return [EntityEmbedEditing, EntityEmbedUI, EntityEmbedToolbar];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'EntityEmbed';
  }

}
