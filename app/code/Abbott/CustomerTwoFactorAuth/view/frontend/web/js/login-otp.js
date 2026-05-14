define([
  'jquery',
  'Magento_Ui/js/modal/modal',
  'Abbott_CustomerTwoFactorAuth/js/expirytime',
  'loader',              // augments $.fn.loader
  'mage/storage',
  'mage/validation',
  'mage/translate',
  'mage/cookies',
  'domReady!'
], function ($, modal, expirytime, loader, storage) {
  'use strict';

  return function (config) {
    // -------------------- State & constants --------------------
    const OTP_LENGTH = 6;
    const SEND_OTP_URL = 'rest/V1/sendAndSaveOtp';
    const VERIFY_OTP_URL = 'rest/V1/verifyOtp';
    const GET_ENABLED_URL = 'rest/V1/customerTwoFa';
    const expiryLimit = config.expiryLimit;

    // Use either #login-form or form.form-login (first one found)
    const $loginForm = $('#login-form, form.form-login').first();
    const $otpForm   = $('#otp-form');
    const $modal     = $('#modal');

    const $otpInput      = $('#otp');
    const $verifyBtn     = $('.verify-otp-btn');
    const $verifyWrapper = $('.verify-otp-wrapper');
    const $resendLink    = $('.resend-link-request');

    const $msgBox    = $('.otp-message-container');
    const $expiryBox = $('.otp-expiry-message-container');
    const $errorBox  = $('.otp-error-message-container');

    let allowNativeSubmit = false;   // gate: blocks any submit until true
    let otpValidated = false;

    const __ = $.mage.__;

    // -------------------- Utilities --------------------
    function getFormKey() {
      return $.mage.cookies.get('form_key') || '';
    }

    function ensureFormKey($form) {
      if (!$form || !$form.length) return;
      let $fk = $form.find('input[name="form_key"]');
      if (!$fk.length) {
        $fk = $('<input/>', {type: 'hidden', name: 'form_key'}).prependTo($form);
      }
      $fk.val(getFormKey());
    }

    function nativeSubmit($form) {
      ensureFormKey($form);
      allowNativeSubmit = true;    // let it pass our global guard
      $form[0].submit();           // native submit (no jQuery events)
    }

    function setBox($el, text, cssClass) {
      if (!$el || !$el.length) return;
      $el.removeClass('display-none').empty();
      if (!text) { $el.addClass('display-none'); return; }
      const p = document.createElement('p');
      if (cssClass) p.classList.add(cssClass);
      p.textContent = text;        // safe (no HTML injection)
      $el.append(p);
    }

    function showMessage(text, cls) { setBox($msgBox, text, cls); }
    function showExpiry(text, cls)  { setBox($expiryBox, text, cls); }
    function showError(text, cls)   { setBox($errorBox, text, cls); }

    function getEmail() {
      return ($('[name="login[username]"]').val() || $('#email').val() || '').trim();
    }

    function getPassword() {
      const $form = $('form.form-login');  // default class
            const pass  = $('[name="login[password]"]').val() || document.getElementById('pass').value || $form.find('input[name="login[password]"]').val();
            return pass;
    }

    function postJSON(url, payload) {
      return storage.post(url, JSON.stringify(payload), true, 'application/json');
    }

    function withLoader(promise) {
      $modal.loader('show');
      return promise.always(function () {
        $modal.loader('hide');
      });
    }

    // -------------------- Modal init --------------------
    $modal.loader({ icon: config.loaderIcon });

    const popup = modal({
      type: 'popup',
      responsive: true,
      title: config.modalTitle,
      buttons: [{
        text: __('Cancel'),
        class: '',
        click: function () { this.closeModal(); }
      }]
    }, $modal);

    $modal.on('modalclosed', function () {
      $modal.loader('hide');
      $('input[name="otp"]').val('');
      showMessage('');
      showExpiry('');
      showError('');
    });

    // -------------------- API flows --------------------
    function getTwoFaEnabled() {
      const payload = { request: { email: getEmail(), pass: getPassword() } };
      return postJSON(GET_ENABLED_URL, payload)
        .then(function (response) {
          // Treat anything except explicit false as "enabled"
          return response !== false;
        })
        .fail(function () {
          // Conservative on failure: assume enabled so we don't submit/reload early
          showError(__('Two-factor check failed. Please try again.'), 'error');
          return $.Deferred().resolve(true);
        });
    }

    function sendOtp() {
      const payload = { request: { email: getEmail() } };

      // Reset OTP field validation markers
      $('#otp').removeClass('mage-error');
      $otpForm.find('.mage-error').remove();
      $otpForm.find('[aria-invalid="true"]').attr('aria-invalid', 'false');

      return withLoader(
        postJSON(SEND_OTP_URL, payload)
          .done(function (response) {
            $otpInput.val('');

            if (response.value === '3') {
              document.getElementById('resend-button')?.classList.add('disabledLink');
              showMessage(__(response.message), 'error');
              showExpiry('');
              showError('');
              document.getElementById("otp").disabled=true;
            } else if (response.limit === response.attempt && response.value === '1') {
              expirytime.start(expiryLimit);
              showMessage(__(response.message), 'success');
              showExpiry(__(response.expiry_message));
              showError('');
            } else if (response.value === '2') {
              showMessage(__(response.message));
              showExpiry(__(response.expiry_message));
              showError('');
              expirytime.start(expiryLimit);
            } else {
              showMessage(__(response.message), 'success');
              showExpiry(__(response.expiry_message));
              showError('');
                expirytime.start(expiryLimit);
            }

            $verifyWrapper.removeClass('display-none');
          })
          .fail(function () {
            document.getElementById('resend-button')?.classList.remove('disabledLink');
            showError(__('Unable to send the request. Please try again later.'), 'error');
          })
      );
    }

    function verifyOtp() {
      if (!$otpForm.validation('isValid')) return $.Deferred().reject();

      const payload = { request: { email: getEmail(), otp: $otpInput.val() } };

      return withLoader(
        postJSON(VERIFY_OTP_URL, payload)
          .done(function (response) {
            // Accept array [true, msg], object { ... }, or truthy
            const ok = Array.isArray(response) ? response[0] !== false : !!response;

            if (ok) {
              otpValidated = true;
              
                // Ensure OTP countdown is cleared and storage removed on success
               try { expirytime.stopTimer(); } catch (e) { /* ignore */ }

              popup.closeModal();
              nativeSubmit($loginForm);
            } else {
              const msg = Array.isArray(response) ? response[1]
                        : (response && response.message) || __('Invalid OTP');
              showError(__(msg), 'error');
            }
          })
          .fail(function () {
            showError(__('Unable to send the request. Please try again later.'), 'error');
          })
      );
    }

    // -------------------- HARD GUARDS against premature submits --------------------
    // 1) Capture-phase submit guard: blocks any submit until we allow it.
    (function attachGlobalSubmitGuard() {
      document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form) return;

        // Match both common Magento login forms
        if (form.matches('#login-form') || form.matches('form.form-login')) {
          if (!allowNativeSubmit) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();
          }
        }
      }, true); // capture phase, runs before jQuery/others
    })();

    // 2) Trap Enter key in the form until we allow submission
    if ($loginForm && $loginForm.length) {
      $loginForm.off('keydown.otp').on('keydown.otp', 'input,select,textarea', function (e) {
        if ((e.key === 'Enter' || e.keyCode === 13) && !allowNativeSubmit) {
          e.preventDefault();
          e.stopImmediatePropagation();
        }
      });

      // 3) Trap submit button clicks until allowed
      $loginForm.off('click.otp').on('click.otp', 'button[type="submit"], input[type="submit"]', function (e) {
        if (!allowNativeSubmit) {
          e.preventDefault();
          e.stopImmediatePropagation();
          $loginForm.trigger('submit');
        }
      });
    }

    // -------------------- Event wiring --------------------
    function checkLogin(){
        // Login submit flow
    if ($loginForm && $loginForm.length) {
      $loginForm.off('submit.otp').on('submit.otp', function (e) {

        
        // Decide OTP path by backend setting
        getTwoFaEnabled().then(function (enabled) {
          if (enabled && !otpValidated) {
            $modal.removeClass('display-none');
            popup.openModal();
            sendOtp();
          } else {
            nativeSubmit($loginForm);
          }
        });

        if (allowNativeSubmit) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        $loginForm.validation();
        if (!$loginForm.validation('isValid')) return false;

        // If modal already visible and not verified, block submit
        const modalOpen = $modal.is(':visible') && !$modal.hasClass('display-none');
        if (modalOpen && !otpValidated) return false;

        return false;
      });
    }
    }

    //Login submit
    $('#login-form #send2').on('click', function(){
      checkLogin();
    })


    // Resend link
    if ($resendLink && $resendLink.length) {
      $resendLink.off('click.otp').on('click.otp', function (e) {
        e.preventDefault();
        sendOtp();
      });
    }

    // OTP validation + verify click
    $otpForm.validation();
    if ($verifyBtn && $verifyBtn.length) {
      $verifyBtn.off('click.otp').on('click.otp', function () {
        verifyOtp();
      });
    }

    // Enable verify button only when OTP length is exact
    $('input[name="otp"]').off('keyup.otp').on('keyup.otp', function () {
      const len = $(this).val().length;
      $verifyBtn.prop('disabled', len !== OTP_LENGTH);
    });
  };
});
