define([
    'jquery',
    'jquery/validate'
], function ($) {
    "use strict";

    return function (validator) {
        validator.addRule(
            'validate-abt-name', function (value) {
            return $.mage.isEmptyNoTrim(value) || /^[a-zA-Z\'\-\. ]+$/.test(value);
            }, $.mage.__('The field must contain only letters, spaces, apostrophes, hyphens and periods.'));
        validator.addRule(
            'validate-abt-company', function (value) {
            return $.mage.isEmptyNoTrim(value) || /^[a-zA-Z0-9\'\-\. ]+$/.test(value);
          }, $.mage.__('The field must contain only letters, numbers, spaces, apostrophes, hyphens and periods.'));
        validator.addRule(
            'validate-abt-mailing-address', function (value) {
            return $.mage.isEmptyNoTrim(value) || /^[a-zA-Z0-9\#\,\-\. ]+$/.test(value);
            }, $.mage.__('The field must contain only letters, numbers, spaces, hash, hyphen, comma and period.'));
        validator.addRule(
            'validate-abt-numeric-with-hyphen-spaces', function (value) {
            return $.mage.isEmptyNoTrim(value) || /^[0-9\ ]+$/.test(value);
            }, $.mage.__('The field must contain only numbers and spaces.'));
        validator.addRule(
            'validate-abt-zipcode', function (value) {
            return $.mage.isEmptyNoTrim(value) || /(^\d{5}$)|(^\d{5}-\d{4}$)/.test(value);
            }, $.mage.__('Enter a valid Zip code.'));

        return validator;
    };
});
