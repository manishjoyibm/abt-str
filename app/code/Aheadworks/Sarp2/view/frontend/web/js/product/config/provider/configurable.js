/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'awSarp2SubscriptionOptionStorage'
], function ($, _, optionStorage) {
    'use strict';

    var productTypes = ['configurable'];

    return {
        options: {
            tierPricesSelector: '[data-role=tier-price-block]'
        },
        /**
         * Get product types
         *
         * @returns {Array}
         */
        getProductTypes: function () {
            return productTypes;
        },

        /**
         * Get regular prices update
         *
         * @param {number} subscriptionOptionId
         * @param {Object} displayedPrices
         * @param {Object} priceOptions
         * @returns {Object}
         */
        getRegularPrices: function (subscriptionOptionId, displayedPrices, priceOptions) {
            var priceCodes = ['oldPrice', 'basePrice','finalPrice'],
                selectedProductId = optionStorage.get('selected_product_id'),
                selectedOptionPrices,
                result = {};

            _.each(priceCodes, function (priceCode) {
                if (_.has(priceOptions, subscriptionOptionId) && _.has(displayedPrices, priceCode) && selectedProductId) {
                    selectedOptionPrices = priceOptions[subscriptionOptionId][selectedProductId];
                    result[priceCode] = {
                        amount: selectedOptionPrices[priceCode].amount - displayedPrices[priceCode].amount
                    };
                }
            });

            return result;
        },

        /**
         * Get option prices
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Object}
         */
        getOptionPrices: function (subscriptionOptionId, priceOptions) {
            return priceOptions[subscriptionOptionId];
        },

        /**
         * Get subscription details
         *
         * @param {Number} subscriptionOptionId
         * @param {Object} detailsOptions
         * @returns {Array}
         */
        getSubscriptionDetails: function (subscriptionOptionId, detailsOptions) {
            var selectedProductId = optionStorage.get('selected_product_id'),
                detailsData = _.has(detailsOptions, subscriptionOptionId) && selectedProductId
                ? detailsOptions[subscriptionOptionId][selectedProductId]
                : {};

            return _.toArray(detailsData);
        },

        /**
         * Check if need to display old price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayOldPrice: function (subscriptionOptionId, priceOptions) {
            var optionPrices = this.getOptionPrices(subscriptionOptionId, priceOptions),
                selectedProductId = optionStorage.get('selected_product_id'),
                prices;

            if (selectedProductId && !_.isEmpty(optionPrices[selectedProductId])) {
                prices = optionPrices[selectedProductId];
                return prices['finalPrice'].amount != prices['oldPrice'].amount;
            }

            return false;
        },

        /**
         * Check if need to display tier price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayTierPrice: function (subscriptionOptionId, priceOptions) {
            var optionPrices = this.getOptionPrices(subscriptionOptionId, priceOptions),
                selectedProductId = optionStorage.get('selected_product_id'),
                prices;

            if (selectedProductId && !_.isEmpty(optionPrices[selectedProductId])) {
                prices = optionPrices[selectedProductId];
                return !_.isEmpty(prices['tierPrices']);
            }

            return false;
        },

        /**
         * Get tier prices selector
         *
         * @returns {string}
         */
        getTierPricesSelector: function () {
            return this.options.tierPricesSelector;
        }
    };
});
