define(['jquery', 'domReady!'], function ($) {
  'use strict';

  function needsAck() {
    return $('#abbott_adult_signature_ack').length > 0;
  }

  function isAcked() {
    return $('#abbott_adult_signature_ack').is(':checked');
  }

  function setSelect(value) {
    $('#abbott_adult_signature_required').val(value);   // 1 = Yes, 0 = No
  }

  // ---------------------------
  //  MAIN VALIDATION ON SUBMIT
  // ---------------------------
  function guard(e) {

    // CASE 1 — Checkbox is missing → FORCE select to NO
    if (!needsAck()) {
      setSelect("0"); // No
      return true;
    }

    // CASE 2 — Checkbox exists BUT NOT checked
    if (!isAcked()) {
      e.preventDefault();
      e.stopPropagation();

      // Show Magento-style error
      $('#abbott_adult_signature_ack-error')
          .text("This is a required field.")
          .show();

      $('#abbott_adult_signature_ack')
          .addClass('mage-error')
          .attr('aria-invalid', 'true');

      // Make sure select is also NO
      setSelect("0");

      return false;
    }

    // CASE 3 — Checkbox checked → OK, set YES
    setSelect("1");
  }

  // --------------------------------------
  //  CLICKING CHECKBOX UPDATES SELECT BOX
  // --------------------------------------
  $(document).on('change', '#abbott_adult_signature_ack', function () {

      if ($(this).is(':checked')) {
          setSelect("1");   // YES
      } else {
          setSelect("0");   // NO
      }

      // Clear error UI
      $('#abbott_adult_signature_ack-error').hide();
      $(this).removeClass('mage-error').attr('aria-invalid', 'false');
  });

  $(document).on('click', '#submit_order_top_button, #submit_order_button, .submit_order_bottom', guard);

});