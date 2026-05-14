/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/checkout-data'
], function ($, _, VaultComponent, globalMessageList, fullScreenLoader, selectPaymentMethod, checkoutData) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'PayPal_Braintree/payment/paypal/vault',
            additionalData: {}
        },

        /**
         * @returns
         */
        selectPaymentMethod: function () {
            selectPaymentMethod(
                {
                    method: this.getId(),
                    code: this.getCode()
                }
            );
            checkoutData.setSelectedPaymentMethod(this.getCode());

            return true;
        },

        /**
         * Get PayPal payer email
         * @returns {String}
         */
        getPayerEmail: function () {
            return this.details.payerEmail;
        },

        /**
         * Get type of payment
         * @returns {String}
         */
        getPaymentIcon: function () {
            return window.checkoutConfig.payment['braintree_paypal'].paymentIcon;
        },

        /**
         * Place order
         */
        beforePlaceOrder: function () {
            this.getPaymentMethodNonce();
        },

        /**
         * Send request to get payment method nonce
         */
        getPaymentMethodNonce: function () {
            var self = this;

            fullScreenLoader.startLoader();
            $.getJSON(self.nonceUrl, {
                'public_hash': self.publicHash
            })
                .done(function (response) {
                    fullScreenLoader.stopLoader();
                    self.additionalData['payment_method_nonce'] = response.paymentMethodNonce;
                    self.placeOrder();
                })
                .fail(function (response) {
                    var error = JSON.parse(response.responseText);

                    fullScreenLoader.stopLoader();
                    globalMessageList.addErrorMessage({
                        message: error.message
                    });
                });
        },

        /**
         * Get payment method data
         * @returns {Object}
         */
        getData: function () {
            var data = {
                'method': this.code,
                'additional_data': {
                    'public_hash': this.publicHash
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            return data;
        }
    });
});
