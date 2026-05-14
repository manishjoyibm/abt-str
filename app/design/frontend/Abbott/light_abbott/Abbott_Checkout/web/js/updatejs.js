define(['jquery','mage/url'], function ($, url) {
    "use strict";

    function cartQtyupdate(itemId) {
        var form = $('form#form-validate');
        $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            showLoader: true,
            success: function (res) {
                var data = {"actionFrom":"magento"};
                $('#abbott-cart-action').attr('data-product', JSON.stringify(data));
                var parsedResponse = $.parseHTML(res);
                var result = $(parsedResponse).find("#form-validate");
                var sections = ['cart'];

                $("#form-validate").replaceWith(result);

                require(['Magento_Checkout/js/action/get-totals',
                    'Magento_Customer/js/customer-data'], function (getTotalsAction, customerData) {
                    // The mini cart reloading
                    customerData.reload(sections, true);

                    // The totals summary block reloading
                    var deferred = $.Deferred();
                    getTotalsAction([], deferred);

                    //Display error if found after jquery
                    var messages = $.cookieStorage.get('mage-messages');
                    if (!_.isEmpty(messages)) {
                        //customerData.set('messages', {messages: messages}); //commented error message from page header

                        $('.item-'+itemId+'-details .cart-item-msg').remove();
                        var productMessage = messages;
                        var productLevelUniqueMessage = productMessage.filter((value, index, self) => index === self.findIndex((t) => t.text === value.text));
                        $.each(productLevelUniqueMessage, function(index, value){
                            $('.item-'+itemId+'-details').append('<div class="cart-item-msg cart item message '+productLevelUniqueMessage[0].type+'"><div>'+productLevelUniqueMessage[0].text+'</div></div>');
                          });
                          setTimeout(function () {
                            $('.cart-item-msg').fadeOut('fast');
                        }, 5000);
                        $.cookieStorage.set('mage-messages', '');
                    }
                });
            },
            error: function (xhr, status, error) {
                var err = eval("(" + xhr.responseText + ")");
            }
        });
    }

    function cartUpdateMessage(){
        if (!$('.custom-qty')[0]) {
            location.reload();
        } else{
            var customurl = url.build('shoppingcart/index/index');
            $.ajax({
                url: customurl,
                type: "POST",
            }).done(function (data) {
               window.free_percentage = data.percentage;
               $('.free_message').text(data.html);
               $('#myBar').width(data.percentage);
               $('#myBar').css('background-color', data.color);
            });
        }
    }

    $(document).on('change', '.custom-qty input', function () {
        var str = $(this).attr("id");
        var arr = str.split("-");
        var dataId = arr[1];
        cartQtyupdate(dataId);
        setTimeout(cartUpdateMessage, 4000);
    });
    $(document).on('click', '.custom-qty a', function () {
        var dataId = $(this).attr("data-id");
        cartQtyupdate(dataId);
        setTimeout(cartUpdateMessage, 4000);
    });
    $('.qty_val').keypress(function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            var str = $(this).attr("id");
            var arr = str.split("-");
            var dataId = arr[1];
            cartQtyupdate(dataId);
            setTimeout(cartUpdateMessage, 4000);
            return false;
        }
    });
});
