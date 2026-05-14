/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var providers = [],
        currentProvider = null,
        productType,
        eventHandlers = {},
        isInitialized = false;

    /**
     * Get regular price provider
     *
     * @returns {Object|Boolean}
     */
    function getProvider () {
        if (_.isNull(currentProvider)) {
            currentProvider = _.find(providers, function (candidate) {
                var types = candidate.getProductTypes(),
                    found = _.find(types, function (type) {
                        return type == productType;
                    });

                return !_.isUndefined(found);
            });
            if (_.isUndefined(currentProvider)) {
                currentProvider = false;
            }
        }
        return currentProvider;
    }

    return {
        config: {},

        /**
         * Component constructor
         *
         * @param {Object} configData
         */
        'Aheadworks_Sarp2/js/product/config/provider': function (configData) {
            this.config = configData.config;
            productType = this.config.productType;
            this.setIfInitialized();
        },

        /**
         * Register config provider
         *
         * @param {Object} provider
         */
        register: function (provider) {
            providers.push(provider);
            currentProvider = null;
        },

        /**
         * Get regular prices update
         *
         * @param {jQuery} priceHolder
         * @param {Number} subscriptionOptionId
         * @return {Object}
         */
        getRegularPrices: function (priceHolder, subscriptionOptionId) {
            return getProvider()
                ? getProvider().getRegularPrices(
                    subscriptionOptionId,
                    priceHolder.priceBox('option').prices,
                    this.config.regularPrices.options
                )
                : {};
        },

        /**
         * Get option prices
         *
         * @param {Number} subscriptionOptionId
         * @return {Object}
         */
        getOptionPrices: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getOptionPrices(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : {};
        },

        /**
         * Get subscription details
         *
         * @param {Number} subscriptionOptionId
         * @returns {Array}
         */
        getSubscriptionDetails: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().getSubscriptionDetails(
                    subscriptionOptionId,
                    this.config.subscriptionDetails
                )
                : [];
        },

        /**
         * Get current product id
         *
         * @returns {Number}
         */
        getProductId: function () {
            return this.config.productId;
        },

        /**
         * Check if need to display old price
         *
         * @param {Number} subscriptionOptionId
         * @returns {Boolean}
         */
        isNeedToDisplayOldPrice: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().isNeedToDisplayOldPrice(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : false;
        },

        /**
         * Check if need to display tier price
         *
         * @param {Number} subscriptionOptionId
         * @returns {Boolean}
         */
        isNeedToDisplayTierPrice: function (subscriptionOptionId) {
            return getProvider()
                ? getProvider().isNeedToDisplayTierPrice(
                    subscriptionOptionId,
                    this.config.regularPrices.options
                )
                : false;
        },

        /**
         * Get tier prices selector
         *
         * @returns {string}
         */
        getTierPricesSelector: function () {
            return getProvider()
                ? getProvider().getTierPricesSelector()
                : '';
        },

        /**
         * Check if provider is initialized and set corresponding flag
         */
        setIfInitialized: function () {
            isInitialized = getProvider() !== false
                && !_.isEmpty(this.config)
                && !_.isUndefined(productType);
            if (isInitialized) {
                this._triggerEvent('initialize');
            }
        },

        /**
         * Get initialized flag
         *
         * @returns {boolean}
         */
        isInitialized: function () {
            return isInitialized;
        },

        /**
         * Add event handler
         *
         * @param {string} eventType
         * @param {Function} callback
         */
        on: function (eventType, callback) {
            if (!_.has(eventHandlers, eventType)) {
                eventHandlers[eventType] = [];
            }
            eventHandlers[eventType].push(callback);
        },

        /**
         * Trigger event
         *
         * @param {string} eventType
         */
        _triggerEvent: function (eventType) {
            if (_.has(eventHandlers, eventType)) {
                _.each(eventHandlers[eventType], function (callback) {
                    callback();
                });
            }
        }
    };
});
