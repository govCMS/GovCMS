(function (window, document) {

  'use strict';

  var count = 1;

  // Get the .au-body class in ui-kit-accordion section.
  var groups = document.querySelectorAll('#kssref-ui-kit-accordion .au-body');

  for (var i = 0; i < groups.length; i++) {
    var group = groups[i];

    // Update the onclick attribute to get the right accordion-group number.
    var buttons = group.querySelectorAll('.au-btn');
    for (var k = 0; k < buttons.length; k++) {
      var button = buttons[k];

      var str = button.getAttribute('onclick');
      var newValue = str.replace(".accordion-group", ".accordion-group-" + count);
      button.setAttribute('onclick', newValue);
    }

    // Update the accordion-group to get number from au-body count.
    var el = group.querySelector('.accordion-group');
    var newValue = el.getAttribute('class') + '-' + count;
    el.setAttribute('class', newValue);

    var accordions = group.querySelectorAll('.au-accordion');

    for (var j = 0; j < accordions.length; j++) {
      var accordion = accordions[j];

      // Update href of accordion to be unique.
      var el = accordion.querySelector('.au-accordion__title');
      var newValue = el.getAttribute('href') + '-' + count;
      el.setAttribute('href', newValue);

      // Update aria-controls of accordion to be unique.
      newValue = el.getAttribute('aria-controls') + '-' + count;
      el.setAttribute('aria-controls', newValue);

      // Update id of accordion body to be unique.
      el = accordion.querySelector('.au-accordion__body');
      newValue = el.getAttribute('id') + '-' + count;
      el.setAttribute('id', newValue);
    }

    count++;
  }


})(window, document);
