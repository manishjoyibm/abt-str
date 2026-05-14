/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Aheadworks_Sarp2/js/product/config/provider',
    './saving-estimation', // todo: M2SARP-331
    'jquery/ui',
    'Magento_Catalog/js/price-box'
], function ($, _, configProvider, savingEstimation) {
    'use strict';

    $.widget('mage.awSarp2RegularPrice', {
        options: {
            priceHolder: '[data-role=priceBox]',
            subscriptionType: '[data-role=aw-sarp2-subscription-type]'
        },

        /**
         * @inheritdoc
         * */
        _create: function () {
            this._bind();
            this.options.priceHolder += '[data-product-id=' + configProvider.getProductId() + ']';
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['updateSubscriptionOptionValue ' + this.options.subscriptionType] = 'onSubscriptionTypeChanged';
            this._on(handlers);
        },

        /**
         * Subscription type change event handler
         *
         * @param {Event} event
         * @param {Number} optionId
         */
        onSubscriptionTypeChanged: function (event, optionId) {
            this._updatePrice(optionId);
        },

        /**
         * Update price
         *
         * @param {Number} subscriptionOptionId
         */
        _updatePrice: function (subscriptionOptionId) {
            var priceHolder = $(this.options.priceHolder),
                prices = configProvider.getRegularPrices(
                    priceHolder,
                    subscriptionOptionId
                );

            if (!_.isEmpty(prices)) {
                savingEstimation.disable();
                priceHolder.trigger('updatePrice', {prices: prices});
                savingEstimation.enable();
            }
        }
    });

    return $.mage.awSarp2RegularPrice;
});
