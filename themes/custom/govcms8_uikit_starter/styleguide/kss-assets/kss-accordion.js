(function (window, document) {

  'use strict';

  var count = 1;

  var groups = document.querySelectorAll('#kssref-ui-kit-accordion .au-body');
  console.log(groups);

  for (var i = 0; i < groups.length; i++) {
    var group = groups[i];
    console.log(count);
    console.log(group);

    var buttons = group.querySelectorAll('.au-btn');
    for (var k = 0; k < buttons.length; k++) {
      var button = buttons[k];

      var str = button.getAttribute('onclick');
      var newValue = str.replace(".accordion-group", ".accordion-group-" + count);
      console.log(newValue);
      button.setAttribute('onclick', newValue);
    }

    var el = group.querySelector('.accordion-group');
    var newValue = el.getAttribute('class') + '-' + count;
    console.log(newValue);
    el.setAttribute('class', newValue);

    var accordions = group.querySelectorAll('.au-accordion');

    for (var j = 0; j < accordions.length; j++) {
      var accordion = accordions[j];
      console.log(accordion);

      var el = accordion.querySelector('.au-accordion__title');
      var newValue = el.getAttribute('href') + '-' + count;
      console.log(newValue);
      el.setAttribute('href', newValue);

      newValue = el.getAttribute('aria-controls') + '-' + count;
      console.log(newValue);
      el.setAttribute('aria-controls', newValue);

      el = accordion.querySelector('.au-accordion__body');
      newValue = el.getAttribute('id') + '-' + count;
      console.log(newValue);
      el.setAttribute('id', newValue);
    }

    count++;
  }


})(window, document);
