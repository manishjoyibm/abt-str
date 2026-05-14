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
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/customer-data'
], function (resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor, customerData) {
    'use strict';

    return {
        /**
         * Get shipping rates for specified address.
         * @param {Object} address
         */
        getRates: function (address, retryCount) {
            var cache, serviceUrl, payload, cartData, currentCartVersion;
            var self = this;
            retryCount = retryCount || 0;
            
            // Validate address has minimum required data before proceeding
            if (!address || !address.countryId) {
                console.warn('Shipping address missing required data, skipping rate estimation');
                shippingService.isLoading(false);
                return;
            }
            
            shippingService.isLoading(true);
            
            // Get current cart version to detect cart changes (e.g., coupon apply/remove)
            try {
                cartData = customerData.get('cart')();
                currentCartVersion = cartData && cartData['data_id'] ? cartData['data_id'] : null;
                
                // If cart data is not available yet (race condition), log warning
                if (!currentCartVersion) {
                    console.warn('Cart data not yet available, will fetch fresh shipping rates');
                }
            } catch (e) {
                console.warn('Failed to get cart data:', e);
                currentCartVersion = null;
            }
            
            // Safely get cache key - if address doesn't have getCacheKey method or it fails, cache will be undefined
            try {
                cache = address.getCacheKey ? rateRegistry.get(address.getCacheKey()) : undefined;
                
                // CRITICAL: If cart data is not available, don't use cache (prevents stale data on page load)
                if (cache && !currentCartVersion) {
                    console.log('Cart version unavailable, invalidating cache to ensure fresh data');
                    cache = undefined;
                }
                
                // Invalidate cache if cart version has changed (e.g., after coupon operations)
                if (cache && currentCartVersion) {
                    var cachedCartVersion = rateRegistry.get(address.getCacheKey() + '_cart_version');
                    if (cachedCartVersion && cachedCartVersion !== currentCartVersion) {
                        console.log('Cart version changed (was: ' + cachedCartVersion + ', now: ' + currentCartVersion + '), invalidating cache');
                        cache = undefined;
                    } else if (!cachedCartVersion) {
                        // If we have cache but no cart version stored, invalidate to be safe
                        console.log('No cart version in cache, invalidating to ensure consistency');
                        cache = undefined;
                    }
                }
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
                    try {
                        if (address.getCacheKey) {
                            rateRegistry.set(address.getCacheKey(), result);
                            // Store cart version with the cache to detect future changes
                            if (currentCartVersion) {
                                rateRegistry.set(address.getCacheKey() + '_cart_version', currentCartVersion);
                            }
                        }
                    } catch (e) {
                        console.warn('Failed to cache shipping rates:', e);
                    }
                    
                    if(result == "" && address.regionId !== undefined && address.regionId !== null && address.postcode != null){
                        shippingService.getRestrictedQuoteData(quote, address.regionId, address.street);
                    }
                    shippingService.setShippingRates(result);
                }).fail(function (response) {
                    // Retry once if this is the first attempt and cart data might not be ready
                    if (retryCount === 0 && !currentCartVersion) {
                        console.log('Shipping rate request failed and cart data was unavailable, retrying in 500ms...');
                        setTimeout(function() {
                            self.getRates(address, 1);
                        }, 500);
                    } else {
                        shippingService.setShippingRates([]);
                        errorProcessor.process(response);
                        shippingService.isLoading(false);
                    }
                }).always(function () {
                    // Only stop loading if we're not retrying
                    if (retryCount > 0 || currentCartVersion) {
                        shippingService.isLoading(false);
                    }
                });
            }
        }
    };
});
