define([
    'jquery',
    'module'
], function ($,module) {
    'use strict';

    return function (validator) {
        var cfg = module.config ? module.config():{};
        var min = parseInt(cfg.minLength || 12, 10);

        $.validator.addMethod(
            'validate-admin-password',
            function (value) {
                return (value || '').length >= parseInt(min, 10);
            },
            function () {
                return $.mage.__('Please enter %1 or more characters, using both numeric and alphabetic.').replace('%1', min);
            }
        );

        return validator;
    };
});
