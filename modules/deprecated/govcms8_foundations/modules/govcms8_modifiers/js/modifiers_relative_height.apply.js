/**
 * @file
 * Initializes modification based on provided configuration.
 */

(function (RelativeHeightModifier) {

  'use strict';

  RelativeHeightModifier.apply = function (context, selector, media, config) {

    var element = context.querySelector(selector);
    if (!element) {
      return;
    }

    setHeight(element, media, config.ratio);

    window.addEventListener('resize', function () {
      setHeight(element, media, config.ratio);
    });

  };

  function setHeight(element, media, ratio) {

    if (window.matchMedia(media).matches) {

      if (ratio.indexOf('%') !== -1) {
        element.style.height = (window.innerHeight * parseFloat(ratio) / 100) + 'px';
      }
      else {
        element.style.height = (element.offsetWidth / parseFloat(ratio)) + 'px';
      }
    }
    else {
      element.style.height = '';
    }

  }

})(window.RelativeHeightModifier = window.RelativeHeightModifier || {});
