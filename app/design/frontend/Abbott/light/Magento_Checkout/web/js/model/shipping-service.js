/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'mage/storage',
    'jquery',
    'mage/url'
], function (ko, checkoutDataResolver, storage, $, urlBuilder) {
    'use strict';

    var shippingRates = ko.observableArray([]);

    return {
        isLoading: ko.observable(false),

        /**
         * Set shipping rates
         *
         * @param {*} ratesData
         */
        setShippingRates: function (ratesData) {
            shippingRates(ratesData);
            shippingRates.valueHasMutated();
            checkoutDataResolver.resolveShippingRates(ratesData);
        },

        /**
         * Get shipping rates
         *
         * @returns {*}
         */
        getShippingRates: function () {
            return shippingRates;
        },
         
        getRestrictedQuoteData: function (quoteData, regionId, street) {
            var url = urlBuilder.build('shippingrestriction/validate/validatequote');
            var payload = JSON.stringify({'regionId': regionId, 'street': street});
            storage.post(
                url, payload, false
            ).done(function (result) {                
                if (result) {
                    $('.no-quotes-block').html(result);
                }
            }).fail(function (response) {
                alert('Fail to fetch Shipping Options');                
            }).always(function () {
                
            });
             
        }
    };
});
