/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/cart/cache',
    'Magento_Customer/js/customer-data'
], function (quote, defaultProcessor, totalsDefaultProvider, shippingService, cartCache, customerData) {
    'use strict';

    var rateProcessors = {},
        totalsProcessors = {},

        /**
         * Estimate totals for shipping address and update shipping rates.
         */
        estimateTotalsAndUpdateRates = function () {
            var type = quote.shippingAddress().getType();

            if (
                quote.isVirtual() ||
                window.checkoutConfig.activeCarriers && window.checkoutConfig.activeCarriers.length === 0
            ) {
                // update totals block when estimated address was set
                totalsProcessors['default'] = totalsDefaultProvider;
                totalsProcessors[type] ?
                    totalsProcessors[type].estimateTotals(quote.shippingAddress()) :
                    totalsProcessors['default'].estimateTotals(quote.shippingAddress());
            } else {
                // check if user data not changed -> load rates from cache
                if (!cartCache.isChanged('address', quote.shippingAddress()) &&
                    !cartCache.isChanged('cartVersion', customerData.get('cart')()['data_id']) &&
                    cartCache.get('rates')
                ) {
                    shippingService.setShippingRates(cartCache.get('rates'));

                    return;
                }

                // update rates list when estimated address was set
                rateProcessors['default'] = defaultProcessor;
                rateProcessors[type] ?
                    rateProcessors[type].getRates(quote.shippingAddress()) :
                    rateProcessors['default'].getRates(quote.shippingAddress());

                // save rates to cache after load
                shippingService.getShippingRates().subscribe(function (rates) {
                    cartCache.set('rates', rates);
                });
            }
        },

        /**
         * Estimate totals for shipping address.
         */
        estimateTotalsShipping = function () {
            var items = customerData.get('cart')().items;
            console.log(items);
            if(items != undefined)
            {
                if (items.length > 0) {
                    totalsDefaultProvider.estimateTotals(quote.shippingAddress());
                }
            }
            return;
        },

        /**
         * Estimate totals for billing address.
         */
        estimateTotalsBilling = function () {
            var type = quote.billingAddress().getType();

            if (quote.isVirtual()) {
                // update totals block when estimated address was set
                totalsProcessors['default'] = totalsDefaultProvider;
                totalsProcessors[type] ?
                    totalsProcessors[type].estimateTotals(quote.billingAddress()) :
                    totalsProcessors['default'].estimateTotals(quote.billingAddress());
            }
        };
    quote.shippingMethod.subscribe(estimateTotalsShipping);
    quote.billingAddress.subscribe(estimateTotalsBilling);
    customerData.get('cart').subscribe(estimateTotalsShipping);
});
