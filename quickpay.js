(function ($) {
  'use strict';
  Drupal.behaviors.quickpayCardSelection = {
    attach: function (context, settings) {
      // Avoid slide animation if cards is hidden on load.
      if ($('#quickpay-method input:radio:checked').val() != 'selected') {
        $('#quickpay-cards').hide();
      }
      // Toggle the display as necessary when the radio is clicked.
      $('#quickpay-method input:radio').change(function () {
        if ($(this).val() == 'selected') {
          $('#quickpay-cards').slideDown();
        }
        else {
          $('#quickpay-cards').slideUp();
        }
      });
    }
  };
})(jQuery);
