/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'mage/storage',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/error-processor'
], function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
    'use strict';

    return {
        /**
         * Get shipping rates for specified address.
         * @param {Object} address
         */
        getRates: function (address) {
            var cache, serviceUrl, payload;
            var self = this;
            
            // Validate address has minimum required data before proceeding
            if (!address || !address.countryId) {
                console.warn('Shipping address missing required data, skipping rate estimation');
                shippingService.isLoading(false);
                return;
            }
            
            shippingService.isLoading(true);
            
            // Safely get cache key - if address doesn't have getCacheKey method or it fails, cache will be undefined
            try {
                cache = address.getCacheKey ? rateRegistry.get(address.getCacheKey()) : undefined;
            } catch (e) {
                console.warn('Failed to get cache key for address:', e);
                cache = undefined;
            }
            
            serviceUrl = resourceUrlManager.getUrlForEstimationShippingMethodsForNewAddress(quote);
            payload = JSON.stringify({
                    address: {
                        'street': address.street,
                        'city': address.city,
                        'region_id': address.regionId,
                        'region': address.region,
                        'country_id': address.countryId,
                        'postcode': address.postcode,
                        'email': address.email,
                        'customer_id': address.customerId,
                        'firstname': address.firstname,
                        'lastname': address.lastname,
                        'middlename': address.middlename,
                        'prefix': address.prefix,
                        'suffix': address.suffix,
                        'vat_id': address.vatId,
                        'company': address.company,
                        'telephone': address.telephone,
                        'fax': address.fax,
                        'custom_attributes': address.customAttributes,
                        'save_in_address_book': address.saveInAddressBook
                    }
                }
            );

            if (cache) {
                if(address.regionId !== undefined && address.regionId !== null && address.postcode != null){
                    shippingService.getRestrictedQuoteData(quote, address.regionId, address.street);
                }
                shippingService.setShippingRates(cache);
                shippingService.isLoading(false);
            } else {
                storage.post(
                    serviceUrl, payload, false
                ).done(function (result) {
                    rateRegistry.set(address.getCacheKey(), result);
                    if(result == "" && address.regionId !== undefined && address.regionId !== null && address.postcode != null){
                        shippingService.getRestrictedQuoteData(quote, address.regionId, address.street);
                    }
                    shippingService.setShippingRates(result);
                }).fail(function (response) {
                    shippingService.setShippingRates([]);
                    errorProcessor.process(response);
                }).always(function () {
                    shippingService.isLoading(false);
                });
            }
        }
    };
});
