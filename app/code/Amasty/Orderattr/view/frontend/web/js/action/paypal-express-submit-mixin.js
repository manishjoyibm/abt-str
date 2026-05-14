define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Amasty_Orderattr/js/model/attribute-sets/paypal-attributes',
    'Amasty_Orderattr/js/model/validate-and-save'
], function ($, quote, attributesForm, validateAndSave) {
    'use strict';

    var paypalExpressMixin = {
        validatePassed: false,

        _submitOrder: function () {
            var self = this;

            if (this.validatePassed) {
                return this._super();
            } else {
                validateAndSave(attributesForm).done(function() {
                    self.validatePassed = true;
                    return self._submitOrder();
                });
            }
        },

        /**
         * Update quote shipping method for correct update attribute fields.
         *
         * @private
         */
        _updateOrderSubmit: function (shouldDisable, fn) {
            var shippingMethod = $(this.options.shippingSubmitFormSelector)
                .find(this.options.shippingSelector).val().split('_');

            this._super(shouldDisable, fn);

            quote.shippingMethod({
                'carrier_code': shippingMethod[0],
                'method_code': shippingMethod[1]
            });
        }
    };

    return function (paypalExpressWidget) {
        $.widget('mage.orderReview', paypalExpressWidget, paypalExpressMixin);
        return $.mage.orderReview;
    };
});
