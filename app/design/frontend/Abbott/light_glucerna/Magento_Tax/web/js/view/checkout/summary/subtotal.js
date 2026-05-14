/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    var displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;
    var associatedData = window.checkoutConfig.associatedData;
    var allowTrial = associatedData? parseInt(associatedData['allow_trial']) : 0;

    return Component.extend({
        defaults: {
            displaySubtotalMode: displaySubtotalMode,
            template: 'Magento_Tax/checkout/summary/subtotal'
        },
        totals: quote.getTotals(),
        titleNew: allowTrial ? "Trial Price" : "Monthly Price",
        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals().subtotal;
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function () {
            return this.displaySubtotalMode == 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingTaxDisplayed: function () {
            return this.displaySubtotalMode == 'including'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*|String}
         */
        getValueInclTax: function () {
            var price = 0;

            if (this.totals()) {
                price = this.totals()['subtotal_incl_tax'];
            }

            return this.getFormattedPrice(price);
        }
    });
});
