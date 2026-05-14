/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define(
    [
        'underscore',
        'Magento_Checkout/js/model/quote',
        'Aheadworks_Sarp2/js/checkout/model/payment/place-order'
    ],
    function (_, quote, placeMixedOrderAction) {
        'use strict';

        return function (renderer) {
            return renderer.extend({

                /**
                 * @inheritdoc
                 */
                placeOrder: function () {
                    if (quote.totals()['grand_total'] > 0) {
                        return this._super();
                    }

                    return quote.isAwSarp2QuoteMixed() || quote.isAwSarp2QuoteSubscription()
                        ? placeMixedOrderAction(this)
                        : this._super();
                },

                /**
                 * @inheritdoc
                 */
                getPayPalConfig: function () {
                    var config = this._super();

                    if (_.has(config, 'paypal')) {
                        if (_.has(config.paypal, 'singleUse')) {
                            config.paypal.singleUse = false;
                        } else {
                            config.paypal.flow = 'checkout';
                        }
                    } else {
                        config.flow = 'vault';
                    }

                    return config;
                },

                /**
                 * @inheritdoc
                 */
                isSkipOrderReview: function () {
                    return true;
                },

                isActiveVault: function () {
                    return true;
                }
            });
        }
    }
);
