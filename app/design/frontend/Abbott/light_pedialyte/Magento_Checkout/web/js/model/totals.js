/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
 define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function ($, ko, quote, customerData) {
    'use strict';

    var quoteItems = ko.observable(quote.totals().items),
        cartData = customerData.get('cart'),
        quoteSubtotal = parseFloat(quote.totals().subtotal),
        subtotalAmount = parseFloat(cartData().subtotalAmount);

    quote.totals.subscribe(function (newValue) {
        quoteItems(newValue.items);
    });

    if (quoteSubtotal !== subtotalAmount) {
        customerData.reload(['cart'], false);
    }

    return {
        totals: quote.totals,
        shippingMethod: quote.shippingMethod,
        isLoading: ko.observable(false),

        /**
         * @return {Function}
         */
        getItems: function () {
            return quoteItems;
        },

        /**
         * @param {*} code
         * @return {*}
         */
        getSegment: function (code) {
            if(window.is_subscription != 1 && window.location.pathname == '/checkout/'){ 
                var freeamount = window.freeamount;
                var shipping_amount = this.totals().shipping_amount;
                var shipping_dis_amount = this.totals().shipping_discount_amount;
                var subtotal_with_discount = this.totals().subtotal_with_discount;
                var subtotal = this.totals().subtotal;
                if(this.totals().coupon_code && shipping_amount == 0 && (this.shippingMethod() !== null)){
                    window.setfreeShip = 1;
                    $('.free_message').text('Congrats! You’ve received free shipping!');
                    $('#myBar').css('background-color','#267F4E');
                    $('#myBar').css('width','100%');
                }else if(subtotal >= freeamount){
                    window.setfreeShip = 1;
                    $('.free_message').text('Congrats! You qualify for FREE shipping!');
                    $('#myBar').css('background-color','#267F4E');
                    $('#myBar').css('width','100%');
                }else if(shipping_amount == 0 && subtotal == 0){
                    window.setfreeShip = 1;
                    $('.free_message').text('Congrats! You’ve received free shipping!');
                    $('#myBar').css('background-color','#267F4E');
                    $('#myBar').css('width','100%');
                }else if(subtotal < freeamount){
                    window.setfreeShip = 0;
                    var percentage = (subtotal * 100) / freeamount;
                    var amount = freeamount - subtotal;
                    if (amount != Math.floor(amount)) {
                        amount = amount.toFixed(2);
                     }
                    $('.free_message').text('You’re $'+amount+' away from FREE shipping!');
                    $('#myBar').css('background-color','#7F7F7F');
                    $('#myBar').css('width',Math.round(percentage)+'%');
                }
                else{
                    window.setfreeShip = 0;
                    $('.free_message').text(window.freetext);
                    $('#myBar').css('background-color',window.color);
                    $('#myBar').css('width',window.free_percentage+'%');
                }
            }
            
            var i, total;

            if (!this.totals()) {
                return null;
            }

            for (i in this.totals()['total_segments']) { //eslint-disable-line guard-for-in
                total = this.totals()['total_segments'][i];

                if (total.code == code) { //eslint-disable-line eqeqeq
                    return total;
                }
            }

            return null;
        }
    };
});
