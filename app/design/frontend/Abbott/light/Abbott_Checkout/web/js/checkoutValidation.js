 define(['jquery'], function($) {
     $("body").on('click','.action.primary.checkout,.braintree-googlepay-button', function(e) {
        if($('.custom_message').length){ 
           if($('.custom_check').prop("checked") == false){
                $('.custom_checkout_error').remove();
                $('.checkout_success').after('<div class="mage-error custom_checkout_error" id="checkbox-error">This is a required field.</div>');
            } else{
                $('.custom_checkout_error').remove();
            }
        } 
     });
     $("body").on('change','.custom_check', function(e) {
        if(this.checked) {
            $('.custom_check').prop('checked', true);
        } else{
            $('.custom_check').prop('checked', false);
        }
    });
    $("body").on('click','.button.action.continue.primary', function(e) {
         $(".estimated-label").text("Total");
    });
});

