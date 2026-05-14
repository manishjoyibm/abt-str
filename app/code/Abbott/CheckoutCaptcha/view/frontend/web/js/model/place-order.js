/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * @api
 */
define(
    [
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Customer/js/customer-data'
    ],
    function (storage, errorProcessor, fullScreenLoader, customerData) {
        'use strict';

        return function (serviceUrl, payload, messageContainer) {
            fullScreenLoader.startLoader();

            return storage.post(
                serviceUrl, JSON.stringify(payload)
            ).fail(
                function (response) {
                    var formBraintree = document.querySelector('.payment-form-braintree');
                    if (formBraintree != null) {
                        var attempts = localStorage.getItem("attempts") ? parseInt(localStorage.getItem("attempts")) : 0;
                        localStorage.setItem("attempts", ++attempts);
                    }

                    var formNode = document.querySelector('.payment-method-braintree');
        		    if (formNode != null) {
                      formNode.scrollIntoView(true);
                    }
                    errorProcessor.process(response, messageContainer);
            }
            ).success(
                function (response) {
                    var clearData = {
                        'selectedShippingAddress': null,
                        'shippingAddressFromData': null,
                        'newCustomerShippingAddress': null,
                        'selectedShippingRate': null,
                        'selectedPaymentMethod': null,
                        'selectedBillingAddress': null,
                        'billingAddressFromData': null,
                        'newCustomerBillingAddress': null
                    };

                    if (response.responseType !== 'error') {
                        customerData.set('checkout-data', clearData);
                        localStorage.removeItem("attempts");
                    }
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );
        };
    }
);
