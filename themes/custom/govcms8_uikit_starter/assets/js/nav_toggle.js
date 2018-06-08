/**
 * @file
 * JS for Navigation Toggle.
 */

(function ($) {

  Drupal.behaviors.govcms8_uikit_starter_NavToggle = {
    attach: function (context, settings) {

      $('.nav-toggle').on('click', function (event) {
        $('.menu__main').toggleClass('open');
        event.preventDefault();
      });

    }
  };

})(jQuery);
