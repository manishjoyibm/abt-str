/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'uiRegistry',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/model/messages',
    'uiLayout',
    'Magento_Checkout/js/action/redirect-on-success'
], function (
    ko,
    $,
    Component,
    placeOrderAction,
    selectPaymentMethodAction,
    quote,
    customer,
    paymentService,
    checkoutData,
    checkoutDataResolver,
    registry,
    additionalValidators,
    Messages,
    layout,
    redirectOnSuccessAction
) {
    'use strict';

    return Component.extend({
        redirectAfterPlaceOrder: true,
        isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),

        /**
         * After place order callback
         */
        afterPlaceOrder: function () {
            // Override this function and put after place order logic here
        },

        /**
         * Initialize view.
         *
         * @return {exports}
         */
        initialize: function () {
            var billingAddressCode,
                billingAddressData,
                defaultAddressData;

            this._super().initChildren();
            quote.billingAddress.subscribe(function (address) {
                this.isPlaceOrderActionAllowed(address !== null);
            }, this);
            checkoutDataResolver.resolveBillingAddress();

            billingAddressCode = 'billingAddress' + this.getCode();
            registry.async('checkoutProvider')(function (checkoutProvider) {
                defaultAddressData = checkoutProvider.get(billingAddressCode);

                if (defaultAddressData === undefined) {
                    // Skip if payment does not have a billing address form
                    return;
                }
                billingAddressData = checkoutData.getBillingAddressFromData();

                if (billingAddressData) {
                    checkoutProvider.set(
                        billingAddressCode,
                        $.extend(true, {}, defaultAddressData, billingAddressData)
                    );
                }
                checkoutProvider.on(billingAddressCode, function (providerBillingAddressData) {
                    checkoutData.setBillingAddressFromData(providerBillingAddressData);
                }, billingAddressCode);
            });

            return this;
        },

        /**
         * Initialize child elements
         *
         * @returns {Component} Chainable.
         */
        initChildren: function () {
            this.messageContainer = new Messages();
            this.createMessagesComponent();

            return this;
        },

        /**
         * Create child message renderer component
         *
         * @returns {Component} Chainable.
         */
        createMessagesComponent: function () {

            var messagesComponent = {
                parent: this.name,
                name: this.name + '.messages',
                displayArea: 'messages',
                component: 'Magento_Ui/js/view/messages',
                config: {
                    messageContainer: this.messageContainer
                }
            };

            layout([messagesComponent]);

            return this;
        },

        /**
         * Place order.
         */
        placeOrder: function (data, event) {
            var self = this;
            self.pushToDataLayer();
            if (event) {
                event.preventDefault();
            }

            if($('.custom_message').length){ 
               if($('.custom_check').prop("checked") == false){
                    $('.custom_checkout_error').remove();
                    $('.checkout_success').after('<div class="mage-error custom_checkout_error" id="checkbox-error">This is a required field.</div>');
                    return false;
                } else{
                    $('.custom_checkout_error').remove();
                }
            } 

            if (this.validate() &&
                additionalValidators.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .done(
                        function () {
                            self.afterPlaceOrder();

                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }
                    ).always(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    );

                return true;
            }

            return false;
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            var messageContainer = this.messageContainer;
            if(checkoutData.getSelectedPaymentMethod() && checkoutData.getSelectedPaymentMethod().includes("vault") && this.vaultMessageContainer) {
                messageContainer = this.vaultMessageContainer;
            }
            return $.when(
                placeOrderAction(this.getData(), messageContainer)
            );
        },

        /**
         * @return {Boolean}
         */
        selectPaymentMethod: function () {
            $('.custom_checkout_error').remove();
            selectPaymentMethodAction(this.getData());
            checkoutData.setSelectedPaymentMethod(this.item.method);
            if(this.item.method.search('paypal') >= 0){
              this.pushToDataLayer();
            }
            return true;
        },

        pushToDataLayer: function () {
          if(window.dataLayer) {
            var result = window.dataLayer.filter(function(obj) {
              if(obj.event) {
                return obj.event === "checkout";
              }
            });
            if(result.length) {
              var actionField = {};
              actionField["step"] = 2;
              var paymentType = quote.paymentMethod() ? quote.paymentMethod().method : '';
              if(paymentType) {
                if(paymentType.search('paypal') >= 0) {
                  paymentType = 'Paypal';
                } else if(paymentType.search('braintree') >= 0) {
                  paymentType = 'Credit Card';
                }
              }
              result[0]["ecommerce"]["checkout"]["actionField"] = actionField;
              result[0]["ecommerce"]["checkout"]["actionField"]["option"] = paymentType;
              window.dataLayer.push(result[0]);
            }
          }
        },

        isChecked: ko.computed(function () {
            return quote.paymentMethod() ? quote.paymentMethod().method : null;
        }),

        isRadioButtonVisible: ko.computed(function () {
            return paymentService.getAvailablePaymentMethods().length !== 1;
        }),

        /**
         * Get payment method data
         */
        getData: function () {
            return {
                'method': this.item.method,
                'po_number': null,
                'additional_data': null
            };
        },

        /**
         * Get payment method type.
         */
        getTitle: function () {
            return this.item.title;
        },

        /**
         * Get payment method code.
         */
        getCode: function () {
            return this.item.method;
        },

        /**
         * @return {Boolean}
         */
        validate: function () {
            return true;
        },

        /**
         * @return {String}
         */
        getBillingAddressFormName: function () {
            return 'billing-address-form-' + this.item.method;
        },

        /**
         * Dispose billing address subscriptions
         */
        disposeSubscriptions: function () {
            // dispose all active subscriptions
            var billingAddressCode = 'billingAddress' + this.getCode();

            registry.async('checkoutProvider')(function (checkoutProvider) {
                checkoutProvider.off(billingAddressCode);
            });
        }
    });
});
