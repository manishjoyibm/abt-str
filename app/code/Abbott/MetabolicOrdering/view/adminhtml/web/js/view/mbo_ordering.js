define(['jquery'], function($) {      
    $(document).on('click','.update_button,.add_button,.action-add',function(e){
        setTimeout(function() {
           if($('.message-error').is(":visible")){
                $('#submit_order_top_button,.submit_order_bottom').prop('disabled', true);
            } else{
                $('#submit_order_top_button,.submit_order_bottom').prop('disabled', false);
            }
        }, 2000);
    });        
});