/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/action/cancel-coupon',
    'Magento_SalesRule/js/model/coupon'
], function ($, ko, Component, quote, setCouponCodeAction, cancelCouponAction, coupon) {
    'use strict';
    
    var totals = quote.getTotals(),
        couponCode = coupon.getCouponCode(),
        isApplied = coupon.getIsApplied();

    if (totals()) {
        couponCode(totals()['coupon_code']);
    }
    isApplied(couponCode() != null);

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/payment/discount'
        },
        couponCode: couponCode,

        /**
         * Applied flag
         */
        isApplied: isApplied,

        loadJsCustomAfterKoRender: function(){
            if(window.checkoutConfig.custom_message !== ''){ 
                $('.custom_message').empty().removeClass('checkout_success'); 
                $('.custom_message').append('<div class="dynamic_text">'+window.checkoutConfig.custom_message+'</div>').addClass('checkout_success');
                $('.checkout_success').prepend('<input type="checkbox" class="custom_check">');
            }
            let applyButton = $('button.action-apply');
            let discountInputVal = coupon.getCouponCode();
            if(discountInputVal.length === 0) {
                $(applyButton).prop('disabled', true);
            }
            $(document).on('change', '#checkout-payment-method-load input[name="payment[method]"]' , function() {
                let inputVal = $('.discount-input').val();
                if(inputVal.length === 0) {
                    $(applyButton).prop('disabled', true);
                }
            });
              $(document).on('keypress', '.discount-input' , function(e) {
                let inputVal = $(this).val();
                if(e.which === 13 && $.trim(inputVal).length === 0) {
                    e.preventDefault();
                }
              });

              let debounceTimerCoupon; 
                $(document).ajaxComplete(function(event, xhr,settings) {
                clearTimeout(debounceTimerCoupon);
                debounceTimerCoupon = setTimeout(function(){
                    let paymentDiv = $('.items.payment-methods').children().length; 
            if(paymentDiv > 0){
                    let inputValOnInput = $('.discount-input').val();
                    let applyButton = $('button.action-apply');
                    if($.trim(inputValOnInput).length > 0) {
                        $(applyButton).prop('disabled', false);
                    } else {
                        $(applyButton).prop('disabled', true);
                    }

                    $(document).on('input', '.discount-input' , function() {
                        let inputValOnInput = $(this).val();
                        if($.trim(inputValOnInput).length > 0) {
                            $(applyButton).prop('disabled', false);
                        } else {
                            $(applyButton).prop('disabled', true);
                        }
                      });

                }
                },2000);
                
                });

        },


        /**
         * Coupon code application procedure
         */
        apply: function () {
            if (this.validate()) {
                setCouponCodeAction(couponCode(), isApplied);           
            }
        },

        /**
         * Cancel using coupon
         */
        cancel: function () {
            if (this.validate()) {
                couponCode('');
               cancelCouponAction(isApplied);
            }
        },

        /**
         * Coupon form validation
         *
         * @returns {Boolean}
         */
        validate: function () {
            var form = '.checkout-payment-method .payment-method._active .payment-method-content #discount-form';
            $.validator.addMethod(
                "customRequiredvalidationrule",
                function(value, element) {        
                if(value) return true;
                else return false;
                },
                
                $.mage.__("Please enter Promotion Code.")
                );

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
