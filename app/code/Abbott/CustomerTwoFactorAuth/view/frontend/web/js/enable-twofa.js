    define([
        'jquery',
        'Abbott_CustomerTwoFactorAuth/js/expirytime',
        'mage/validation'
    ],function($, expirytime){
        'use-strict';

    return function init(config){
    var expiryLimit = config.expiryLimit;
        const form = $('#form-validate');
        const otpLength = 6;
        $(document).ready(function(){
            $.ajax({
                url: config.sendUrl,
                type: 'POST',
                dataType: 'json',
                data: {form_key: window.FORM_KEY},
                success: function(response){
                    if(response.value === 1){
                        $('#email-message').html('<small>'+ 'A one-time auth code has been sent to your registered email' +'</small>');
                        expirytime.reset(expiryLimit);
                    }
                    else if(response.value === 2){
                        $('#email-message').html('<small>'+ response.message +'</small>');
                        document.getElementById('resend-button')?.classList.add('disabledLink');
                        expirytime.start(expiryLimit);
                    }
                    else if(response.value === 3){
                        document.getElementById('resend-button')?.classList.add('disabledLink');
                        $('#email-message').html('<small>'+ response.message +'</small>');
                    }
                    else{
                        $('#email-message').html('<small>'+ response.message +'</small>');
                        $('#resent-hide').show();
                    }
                },
                error: function (xhr, status, error){
                    $('#email-message').html('<small>'+ error +'</small>');
                }
            });
        });

        $('#resend-button').on('click', function(e) {
            e.preventDefault();
            $('#otp-token').removeClass('mage-error');
            form.find('.mage-error').remove();
            form.find('[aria-invalid="true"]').attr('aria-invalid', 'false');
            $.ajax({
                url: '/customer/loginsecurity/send',
                type: 'POST',
                dataType: 'json',
                showLoader: true,
                data: {form_key: window.FORM_KEY},
                success: function(response){
                    if(response.success == true){
                        $('#email-message').html('<small>'+ response.message +'</small>');
                        expirytime.reset(expiryLimit);
                    }else{
                        $('#email-message').html('<small>'+ response.message +'</small>');
                    }
                },
                error: function (xhr, status, error){
                    $('#email-message').html('<small>'+ error +'</small>');
                }
            });

        });

        var submitBtn = form.find('button[type="submit"]');
        form.mage('validation');
        $('#form-validate').on('submit', function(e) {
            e.preventDefault();
            var otp = $('#otp-token').val();
            if(!form.validation('isValid')){
                return false;
            }
            submitBtn.prop('disabled',true);
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: {
                    form_key: window.FORM_KEY,
                    otp_token: otp
                },
                showLoader: true,
                success: function(response){
                    if(response.value == 1) {
                        $('#email-message').html('<small class="error-msg">'+ response.message +'</small>');
                    } else if(response.value == 2) {
                        localStorage.removeItem('otp_countdown_timer');
                        localStorage.setItem('2fa-message', response.message);
                        window.location.href = response.url;
                    }
                },
                error: function (xhr, status, error){
                    $('#email-message').html('<small>'+ error +'</small>');
                },
                complete: function(){
                    submitBtn.prop('disabled',false);
                }
            });
        });

        $('#cancel').on('click', function(e) {
            e.preventDefault();
            if(localStorage.getItem('otp_countdown_timer')){
                localStorage.removeItem('otp_countdown_timer');
            }
            window.location.href = $(this).attr('href');
        });

        $("input[name = 'otp-token']").on('keyup',function(){
            let inputLen = $(this).val().length;
            if(inputLen === otpLength) {
                $('.save-otp').prop('disabled', false);
            } else {
                $('.save-otp').prop('disabled', true);
            }
        });
    }
});
