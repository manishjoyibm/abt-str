define([
    'uiComponent',
    'ko',
    'Magento_Checkout/js/model/payment/additional-validators',
    'mage/translate'
], function (Component, ko, additionalValidators, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Abbott_SubscriptionConsent/checkout/consent'
        },

        initialize: function () {
            this._super();

            var self = this;

            // Whether the user has checked the box
            this.isChecked = ko.observable(false);

            // Inline error visibility
            this.showInlineError = ko.observable(false);

            // Config from checkoutConfig
            this.config = (window.checkoutConfig && window.checkoutConfig.abbott_subscription_consent)
                ? window.checkoutConfig.abbott_subscription_consent
                : { enabled: false, content: '', error_message: '' };

            // Only render when feature is enabled
            this.isVisible = ko.pureComputed(function () {
                return !!(self.config && self.config.enabled === true);
            });

            // Provide a single source of truth for the error text
            this.errorText = ko.pureComputed(function () {
                return (self.config && self.config.error_message)
                    ? self.config.error_message
                    : $t('This is a required field.');
            });

            // Clear inline error when user checks
            this.isChecked.subscribe(function (val) {
                if (val) {
                    self.showInlineError(false);
                }
            });

            // Register a validator that blocks place order and shows inline error
            additionalValidators.registerValidator({
                validate: function () {
                    if (self.isVisible() && !self.isChecked()) {
                        self.showInlineError(true);
                        return false;
                    }
                    self.showInlineError(false);
                    return true;
                }
            });

            return this;
        },

        getContent: function () {
            return (this.config && this.config.content) ? this.config.content : '';
        }
    });
});