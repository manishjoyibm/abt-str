/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'underscore'
], function (_) {
    'use strict';

    var productTypes = ['simple', 'downloadable', 'virtual'];

    return {
        options: {
            tierPricesSelector: '.prices-tier'
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
            var priceCodes = ['oldPrice', 'basePrice', 'finalPrice'],
                selectedOptionPrices,
                result = {};

            _.each(priceCodes, function (priceCode) {
                if (_.has(priceOptions, subscriptionOptionId) && _.has(displayedPrices, priceCode)) {
                    selectedOptionPrices = priceOptions[subscriptionOptionId];
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
            var detailsData = _.has(detailsOptions, subscriptionOptionId)
                ? detailsOptions[subscriptionOptionId]
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
            return true;
        },

        /**
         * Check if need to display tier price
         *
         * @param {number} subscriptionOptionId
         * @param {Object} priceOptions
         * @returns {Boolean}
         */
        isNeedToDisplayTierPrice: function (subscriptionOptionId, priceOptions) {
            return true;
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
