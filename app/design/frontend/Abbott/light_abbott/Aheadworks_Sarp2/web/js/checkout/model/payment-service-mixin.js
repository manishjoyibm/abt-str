define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'Aheadworks_Sarp2/js/checkout/model/payment/mixed-method-list',
    'Magento_Checkout/js/action/select-payment-method'
], function (
    _,
    quote,
    methodList,
    mixedPaymentMethodList,
    selectPaymentMethodAction
) {
    'use strict';

    /**
     * Check if free payment method
     *
     * @param {Object} paymentMethod
     * @returns {boolean}
     */
    function isFreePaymentMethod (paymentMethod) {
        return paymentMethod.method == 'free';
    }

    /**
     * Check if mixed payment method
     *
     * @param {Object} paymentMethod
     * @returns {boolean}
     */
    function isMixedPaymentMethod (paymentMethod) {
        var found = _.find(mixedPaymentMethodList(), function (methodCode) {
            return paymentMethod.method == methodCode;
        });

        return !_.isUndefined(found);
    }

    return function (paymentService) {
        return _.extend(paymentService, {

            /**
             * @inheritdoc
             */
            setPaymentMethods: function (methods) {
                var grandTotal = quote.totals()['grand_total'],
                    filteredMethods,
                    methodIsAvailable,
                    methodNames;

                this.isFreeAvailable = !!_.find(methods, isFreePaymentMethod);
                filteredMethods = methods;
                if (grandTotal <= 0) {
                    filteredMethods = _.filter(methods, isFreePaymentMethod);
                    methods = filteredMethods;
                }

                if (filteredMethods.length === 1) {
                    selectPaymentMethodAction(filteredMethods[0]);
                } else if (quote.paymentMethod()) {
                    methodIsAvailable = methods.some(function (item) {
                        return item.method === quote.paymentMethod().method;
                    });
                    if (!methodIsAvailable) {
			if(typeof quote.paymentMethod().method !== 'undefined' && quote.paymentMethod().method !== "free") {
				return selectPaymentMethodAction(quote.paymentMethod());
			}
		    }
                }

                var availablePaymentMethods = filteredMethods;
                if (availablePaymentMethods.length > 0) {
                    availablePaymentMethods.some(function (payment) {
                        if (payment.method == "braintree") {
                            selectPaymentMethodAction(payment);
                        }
                    });
                }

                methodNames = _.pluck(methods, 'method');
                _.map(methodList(), function (existingMethod) {
                    var existingMethodIndex = methodNames.indexOf(existingMethod.method);

                    if (existingMethodIndex !== -1) {
                        methods[existingMethodIndex] = existingMethod;
                    }
                });

                methodList(methods);
            },

            /**
             * @inheritdoc
             */
            getAvailablePaymentMethods: function () {
                var allMethods = methodList().slice(),
                    grandTotal = quote.totals()['grand_total'];

                if (grandTotal == undefined) {
                    return _.reject(allMethods, isFreePaymentMethod);
                }    

                if (grandTotal > 0) {
                    return _.reject(allMethods, isFreePaymentMethod);
                } else {
                    return _.filter(allMethods, isFreePaymentMethod);
                }
            }
        });
    }
});
