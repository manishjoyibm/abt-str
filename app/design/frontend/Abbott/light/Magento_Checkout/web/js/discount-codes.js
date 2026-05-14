define([
    'jquery',
    'jquery/validate',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';
    $.validator.addMethod(
        "customRequiredvalidationrule",
        function(value, element) {        
        if(value) return true;
        else return false;
        },
        
        $.mage.__("Please enter Promotion Code.")
        );

    $.widget('mage.discountCode', {
        options: {
        },

        /** @inheritdoc */
        _create: function () {
            this.couponCode = $(this.options.couponCodeSelector);
            this.removeCoupon = $(this.options.removeCouponSelector);
            let applyButton = this.options.applyButton;
            if($.trim(this.couponCode.val()).length == 0) {
                $(this.options.applyButton).prop('disabled', true);
            }
            this.couponCode.on('input', function() {
                let inputValOnInput = $(this).val();
                if($.trim(inputValOnInput).length > 0) {
                    $(applyButton).prop('disabled', false);
                } else {
                    $(applyButton).prop('disabled', true);
                }
            });

            this.couponCode.on('keypress', function(e) {
                let inputVal = $(this).val();
                if(e.which === 13 && $.trim(inputVal).length === 0) {
                    e.preventDefault();
                }
            });
            $(this.options.applyButton).on('click', $.proxy(function () {
                this.couponCode.attr('data-validate', "{'customRequiredvalidationrule':true}");
                this.removeCoupon.attr('value', '0');
                $(this.element).validation().trigger('submit');
            }, this));

            $(this.options.cancelButton).on('click', $.proxy(function () {
                this.couponCode.removeAttr('data-validate');
                this.removeCoupon.attr('value', '1');
                this.element.trigger('submit');
            }, this));
        }
    });

    return $.mage.discountCode;
});
