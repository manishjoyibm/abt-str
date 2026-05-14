/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Customer/js/model/address-list',
    'mage/translate',
    'Magento_Customer/js/model/customer'
], function (Component, addressList, $t, customer) {
    'use strict';

    var newAddressOption = {
            /**
             * Get new address label
             * @returns {String}
             */
            getAddressInline: function () {
                return $t('New Address');
            },
            customerAddressId: null
        },
        addressOptions = addressList().filter(function (address) {
            return address.getType() === 'customer-address';
        });

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/billing-address',
            selectedAddress: null,
            isNewAddressSelected: false,
            addressOptions: addressOptions,
            exports: {
                selectedAddress: '${ $.parentName }:selectedAddress'
            }
        },

        /**
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();
            this.addressOptions.push(newAddressOption);

            return this;
        },

        /**
         * @return {exports.initObservable}
         */
        initObservable: function () {
            this._super()
                .observe('selectedAddress isNewAddressSelected')
                .observe({
                    isNewAddressSelected: !customer.isLoggedIn() || !addressOptions.length
                });

            return this;
        },

        /**
         * @param {Object} address
         * @return {*}
         */
        addressOptionsText: function (address) {
            return address.getAddressInline();
        },

        /**
         * @param {Object} address
         */
        onAddressChange: function (address) { 
            this.isNewAddressSelected(address === newAddressOption);
            jQuery(".field input").removeAttr('placeholder');
            jQuery(".field.billingAddressbraintreefirstname input, .field.billingAddressbraintreelastname input").removeAttr('placeholder');
            jQuery(document).on("focus", ".field input", function () {
                jQuery(this).removeAttr('placeholder');
            });
            jQuery(document).on("focusout", ".field input", function () {
                jQuery(this).removeAttr('placeholder');
            });
            jQuery(document).on("blur", ".field input", function () {
                jQuery(this).removeAttr('placeholder');
            });
            jQuery(".field.billingAddressbraintreefirstname, .field.billingAddressbraintreelastname, .field.billingAddressbraintreecity, .field.billingAddressbraintreepostcode, .field.billingAddressbraintreetelephone").addClass("fet");
            jQuery(".field.street .field._required").addClass("fet");
            jQuery(".field.billingAddressbraintreeregion_id, .field.billingAddressbraintreecountry_id").addClass("fet select");
            if (jQuery(".field.billingAddressbraintreefirstname input").val() != "") {
                jQuery(".field.billingAddressbraintreefirstname input").parent(".control").parent(".fet").addClass("has-content");
            } else {
                jQuery(".field.billingAddressbraintreefirstname input").parent(".control").parent(".fet").removeClass("has-content");
                jQuery(".field.billingAddressbraintreefirstname input").parent(".control").parent(".fet").find("label").removeClass("labelfocus");
            }
            if (jQuery(".field.billingAddressbraintreelastname input").val() != "") {
                jQuery(".field.billingAddressbraintreelastname input").parent(".control").parent(".fet").addClass("has-content");
            } else {
                jQuery(".field.billingAddressbraintreelastname input").parent(".control").parent(".fet").removeClass("has-content");
                jQuery(".field.billingAddressbraintreelastname input").parent(".control").parent(".fet").find("label").removeClass("labelfocus");
            }
        }
    });
});
