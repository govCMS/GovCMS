/**
 * @file
 * Initializes modification based on provided configuration.
 */

(function (ParallaxBgModifier) {

  'use strict';

  ParallaxBgModifier.apply = function (context, selector, media, config) {

    var element = context.querySelector(selector);
    if (!element) {
      return;
    }
    
    var pluginConfig = {
      speed: (typeof config.speed !== 'undefined' ? config.speed : 0.5),
      zIndex: 0
    };

    toggle(element, media, pluginConfig);

    window.addEventListener('resize', function () {
      toggle(element, media, pluginConfig);
    });

  };

  function toggle(element, media, pluginConfig) {

    if (window.matchMedia(media).matches) {
      jarallax(element, pluginConfig);
    }
    else {
      jarallax(element, 'destroy');
    }

  }

})(window.ParallaxBgModifier = window.ParallaxBgModifier || {});
