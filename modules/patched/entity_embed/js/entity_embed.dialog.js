/**
 * @file
 * Provides JavaScript additions to entity embed dialog.
 *
 * This file provides popup windows for previewing embedded entities from the
 * embed dialog.
 */

(function ($, Drupal, once) {

  "use strict";

  /**
   * Attach behaviors to links for entities.
   */
  Drupal.behaviors.entityEmbedPreviewEntities = {
    attach: function (context) {
      $(context).find('form.entity-embed-dialog .form-item-entity a').on('click', Drupal.entityEmbedDialog.openInNewWindow);
    },
    detach: function (context) {
      $(context).find('form.entity-embed-dialog .form-item-entity a').off('click', Drupal.entityEmbedDialog.openInNewWindow);
    }
  };

  /**
   * Behaviors for the entityEmbedDialog iframe.
   */
  Drupal.behaviors.entityEmbedDialog = {
    attach: function (context, settings) {
      $(once('js-entity-embed-dialog', 'body')).on('entityBrowserIFrameAppend', function () {
        $('.entity-select-dialog').trigger('resize');
        // Hide the next button, the click is triggered by Drupal.entityEmbedDialog.selectionCompleted.
        $('#drupal-modal').parent().find('.js-button-next').addClass('visually-hidden');
      });
    }
  };

  /**
   * Entity Embed dialog utility functions.
   */
  Drupal.entityEmbedDialog = Drupal.entityEmbedDialog || {
    /**
     * Open links to entities within forms in a new window.
     */
    openInNewWindow: function (event) {
      event.preventDefault();
      $(this).attr('target', '_blank');
      window.open(this.href, 'entityPreview', 'toolbar=0,scrollbars=1,location=1,statusbar=1,menubar=0,resizable=1');
    },
    selectionCompleted: function(event, uuid, entities) {
      $('.entity-select-dialog .js-button-next').click();
    }
  };

})(jQuery, Drupal, once);
