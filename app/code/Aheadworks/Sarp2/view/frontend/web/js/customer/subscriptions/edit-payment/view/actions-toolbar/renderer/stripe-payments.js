/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/view/actions-toolbar/renderer/default',
    'Magento_Checkout/js/model/payment/additional-validators'
], function ($, Component, additionalValidators) {
    'use strict';

    return Component.extend({

        /**
         * @inheritdoc
         */
        validate: function (component) {
            return this._super(component) && additionalValidators.validate();
        },

        /**
         * @inheritdoc
         */
        savePaymentDetails: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }
            this._beforeAction().done(function () {
                self._getMethodRenderComponent().stripeJsPlaceOrder();
            });
        }
    });
});
