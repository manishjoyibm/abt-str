define([], function () {
    'use strict';

    /**
     * Magento initializer entry point.
     * Called automatically by data-mage-init.
     */
    return function () {

        window.checkoutConfig = window.checkoutConfig || {};
        window.checkoutConfig.payment = window.checkoutConfig.payment || {};

        var payment = window.checkoutConfig.payment;

        // Normalize Braintree enabled flag (isActive → enabled)
        if (payment.braintree && typeof payment.braintree.enabled === 'undefined') {
            payment.braintree.enabled = payment.braintree.isActive === true;
        }

        // Ensure Three-D-Secure config exists
        if (typeof payment.three_d_secure === 'undefined') {
            payment.three_d_secure = { enabled: false };
        }

        // Ensure ccform config exists
        if (typeof payment.ccform === 'undefined') {
            payment.ccform = {
                availableTypes: {},
                months: {},
                years: {},
                hasVerification: true,
                cvvImageUrl: ''
            };
        }
    };
});
