/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    './button-config',
    'Aheadworks_Sarp2/js/product/flag/is-subscription',
    'jquery/ui'
], function ($, payPalButtonConfig, isSubscriptionFlag) {
    'use strict';

    $.widget('mage.awSarp2ProductBraintreePaypalBtnAdapter', {
        options: {
            isSubscription: false,
            subscriptionOptionList: '[data-role=aw-sarp2-subscription-type]',
        },

        /**
         * @inheritdoc
         */
        _create: function () {
            if (this.options.isSubscription) {
                isSubscriptionFlag.setIsSubscription(true);
            }
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {},
                event;

            if (this.options.isSubscription) {
                event = 'updateSubscriptionOptionValue ' + this.options.subscriptionOptionList;
                handlers[event] = 'onSubscriptionOptionChange';
                this._on(handlers);
            }
        },

        /**
         * Subscription option change event handler
         *
         * @param {Event} event
         * @param {*} value
         */
        onSubscriptionOptionChange: function (event, value) {
            var configData = payPalButtonConfig();

            configData.flow = parseInt(value) === 0 ? 'checkout' : 'vault';
            payPalButtonConfig(configData);
        }
    });

    return $.mage.awSarp2ProductBraintreePaypalBtnAdapter;
});
