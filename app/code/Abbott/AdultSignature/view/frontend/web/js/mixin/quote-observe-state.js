define([], function () {
    'use strict';

    return function (quote) {
        var ensureCheckoutConfigPath = function () {
            window.checkoutConfig = window.checkoutConfig || {};
            window.checkoutConfig.custom = window.checkoutConfig.custom || {};
        };

        var setStateInCheckoutConfig = function (addr) {
            if (!addr) { return; }

            var region = typeof addr.region === 'function' ? addr.region() : addr.region;
            var regionId = typeof addr.regionId === 'function' ? addr.regionId() : addr.regionId;
            var regionCode = typeof addr.regionCode === 'function' ? addr.regionCode() : addr.regionCode;

            ensureCheckoutConfigPath();
            window.checkoutConfig.custom.state = regionCode || null;
        };

        // initial and reactive
        setStateInCheckoutConfig(quote.shippingAddress());
        quote.shippingAddress.subscribe(setStateInCheckoutConfig);

        return quote;
    };
});