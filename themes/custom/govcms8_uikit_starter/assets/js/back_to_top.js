/**
 * @file
 * JS for Back To Top link.
 */

(function ($) {

  Drupal.behaviors.govcms8_uikit_starter_BackToTop = {
    attach: function (context, settings) {
      var backToTop = $("#back-to-top button");

      // Toggle class on backToTop.
      $(function () {
        $(window).scroll(function () {
          if ($(this).scrollTop() > 250) {
            backToTop.addClass('is-visible');
          } else {
            backToTop.removeClass('is-visible');
          }
        });
      });

      // Scroll smoothly to top on click.
      backToTop.click(function (event) {
        $('body,html').animate({
          scrollTop: 0
        }, 800);
        event.preventDefault();
      });

    }
  };

})(jQuery);
