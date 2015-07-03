(function ($) {
  'use strict';
  Drupal.behaviors.quickpayCardSelection = {
    attach: function (context, settings) {
      // Avoid slide animation if cards is hidden on load.
      if ($('#quickpay-method input:radio:checked').val() != 'selected') {
        $('#quickpay-cards').closest('fieldset').hide();
      }
      // Toggle the display as necessary when the radio is clicked.
      $('#quickpay-method input:radio').change(function () {
        if ($(this).val() == 'selected') {
          $('#quickpay-cards').closest('fieldset').slideDown();
        }
        else {
          $('#quickpay-cards').closest('fieldset').slideUp();
        }
      });
    }
  };
})(jQuery);
