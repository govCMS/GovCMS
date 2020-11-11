(function ($, Drupal, drupalSettings) {
  /**
   * Add current or past class to events.
   */
  Drupal.behaviors.calendarItemCurrentDate = {
    attach: function attach(context) {
      $('time.calendar-date', context).once('calendar-date').each(function () {
        var $calendarDate = $(this);
        var dateTime = $calendarDate.attr('datetime');
        if (dateTime !== null) {
          // Set the time to 0, we only want to compare
          var today = new Date().setHours(0, 0, 0, 0);
          var eventDate = new Date(dateTime).setHours(0, 0, 0, 0);

          // If event date is less than today.
          if (eventDate < today) {
            $calendarDate.closest('.calendar-item').addClass('calendar-item--past');
          }
          // If event date is today.
          if (eventDate === today) {
            $calendarDate.closest('.calendar-item').addClass('calendar-item--current');
          }
        }
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
