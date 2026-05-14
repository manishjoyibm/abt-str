define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'underscore',
        'Amasty_Orderattr/js/checkout-data',
        'uiRegistry',
        'Amasty_Orderattr/js/action/observe-shipping-method',
        'Amasty_Orderattr/js/form/relationRegistry'
    ],
    function (
        $,
        ko,
        Component,
        _,
        checkoutData,
        registry,
        observeShippingMethod,
        relationRegistry
    ) {
        return Component.extend({
            defaults: {
                paymentMethodParentName: 'checkout.steps.billing-step.payment.payments-list'
            },

            isVisible: ko.observable(false),

            relationRegistry: false,

            dependsToShow: [],

            initialize: function () {
                var self = this;
                this._super();

                if (!_.isUndefined(this.fields)) {
                    var fieldsetPath = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

                    _.each(this.fields, function (value) {
                        registry.async(fieldsetPath + '.' + value)(function (element) {
                            new observeShippingMethod(element).observeShippingMethods();
                        });
                    });
                }

                this.relationRegistry = relationRegistry;
                this.relationRegistry.clear();
                registry.async('amastyCheckoutProvider')(function (checkoutProvider) {
                    var scopeCheckoutData = checkoutData.getCheckoutData(self.amScope);

                    if (scopeCheckoutData ) {
                        checkoutProvider.set(
                            self.amScope,
                            $.extend(true, {}, checkoutProvider.get(self.amScope), scopeCheckoutData )
                        );
                    }
                    checkoutProvider.on(self.amScope, function (scopeData) {
                        checkoutData.setCheckoutData(self.amScope, scopeData);
                    });
                });
            },

            /**
             * If there are multiple payment methods available
             * then we need to display order attributes markup only once due to the input id duplication.
             *
             * @param {Object[]} parents - Array of parents Ui components
             * @returns {boolean}
             */
            shouldDisplay: function (parents) {
                const paymentMethodComponent = this.getPaymentMethodComponent(parents);

                return paymentMethodComponent?.getCode() === paymentMethodComponent?.isChecked();
            },

            /**
             * Get payment method component if order attributes placed inside it
             *
             * @param {Object[]} parents - Array of parents Ui components
             * @returns {Object|undefined} - Payment method component or undefined
             */
            getPaymentMethodComponent: function (parents) {
                return parents.find((parent) => {
                    return parent?.parentName === this.paymentMethodParentName
                        && typeof parent?.getCode === 'function'
                        && typeof parent?.isChecked === 'function';
                });
            },

            initElement: function (element) {
                this._super();
                new observeShippingMethod(element).observeShippingMethods();
            },
            /**
             * Magento 2.1.1 focusInvalid nonexistent fix
             */
            focusInvalidField: function () {
                var invalidField = _.find(this.delegate('isFieldInvalid'));

                if (!_.isUndefined(invalidField) && _.isFunction(invalidField.focused)) {
                    invalidField.focused(true);
                }

                return this;
            }
        });
    }
);
