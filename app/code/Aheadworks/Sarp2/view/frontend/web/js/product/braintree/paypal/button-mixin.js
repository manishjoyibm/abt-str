/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Aheadworks_Sarp2/js/product/flag/is-subscription',
    './button-config',
    'braintree',
    'braintreeDataCollector',
    'braintreePayPalCheckout',
    'PayPal_Braintree/js/form-builder',
    'https://www.paypalobjects.com/api/checkout.js'
], function (
    $,
    _,
    isSubscriptionFlag,
    payPalButtonConfig,
    braintree,
    braintreeDataCollector,
    braintreePayPalCheckout,
    formBuilder
) {
    'use strict';

    return function (button) {
        return isSubscriptionFlag.isSubscription()
            ? button.extend({
                payPalInstance: null,

                /**
                 * @inheritdoc
                 */
                initComponent: function () {
                    this.initPayPal();

                    return this;
                },

                /**
                 * @inheritdoc
                 */
                initObservable: function () {
                    this._super();
                    payPalButtonConfig.subscribe(this.reInitPayPal.bind(this));

                    return this;
                },

                /**
                 * Init PayPal
                 */
                initPayPal: function () {
                    var data = this._getPayPalBtnConfig();

                    braintree.create({
                        authorization: this.clientToken,
                    }, function (clientErr, clientInstance) {
                        if (clientErr) {
                            console.error('paypalCheckout error', clientErr);
                            return this.showError(
                                'PayPal Checkout could not be initialized. Please contact the store owner.'
                            );
                        }

                        braintreeDataCollector.create({
                            client: clientInstance,
                            paypal: true
                        }, function (err) {
                            if (err) {
                                return console.log(err);
                            }
                        });

                        braintreePayPalCheckout.create({
                            client: clientInstance
                        }, function (createErr, payPalCheckoutInstance) {
                            var actionSuccess = this.actionSuccess,
                                beforeSubmit = this.beforeSubmit,
                                mapShippingAddress = this._mapShippingAddress,
                                events = this.events;

                            if (createErr) {
                                console.error('paypalCheckout instantiation error', createErr);
                            } else {
                                this.payPalInstance = payPalCheckoutInstance;

                                paypal.Button.render({
                                    env: this.environment,
                                    style: this._getPayPalBtnStyle(),
                                    funding: this._getPayPalFunding(),
                                    locale: data.locale,

                                    payment: function () {
                                        return payPalCheckoutInstance.createPayment(data);
                                    },

                                    onCancel: function () {
                                        jQuery("#maincontent").trigger('processStop');
                                        if (_.isFunction(events.onCancel)) {
                                            events.onCancel();
                                        }
                                    },

                                    onError: function (err) {
                                        console.error('paypalCheckout button render error', err);
                                        jQuery("#maincontent").trigger('processStop');

                                        if (_.isFunction(events.onError)) {
                                            events.onError(err);
                                        }
                                    },

                                    onClick: function(data) {
                                        if (_.isFunction(events.onClick)) {
                                            events.onClick(data);
                                        }
                                    },

                                    onAuthorize: function (data) {
                                        return payPalCheckoutInstance.tokenizePayment(data)
                                            .then(function (payload) {
                                                if (_.isFunction(beforeSubmit) && !beforeSubmit(payload)) {
                                                    return false;
                                                }

                                                $("#maincontent").trigger('processStart');

                                                payload = mapShippingAddress(payload);
                                                formBuilder.build(
                                                    {
                                                        action: actionSuccess,
                                                        fields: {
                                                            result: JSON.stringify(payload)
                                                        }
                                                    }
                                                ).submit();
                                            });
                                    }
                                }, '#' + this.id);
                            }
                        }.bind(this));
                    }.bind(this));
                },

                /**
                 * Re-init PayPal
                 */
                reInitPayPal: function () {
                    if (!_.isNull(this.payPalInstance)) {
                        this.payPalInstance.teardown(function () {
                            $('#' + this.id).html('');
                            this.initPayPal();
                        }.bind(this));
                    } else {
                        this.initPayPal();
                    }
                },

                /**
                 * Get PayPal button config
                 *
                 * @returns {Object}
                 */
                _getPayPalBtnConfig: function () {
                    var configData = payPalButtonConfig(),
                        container = $('#' + this.id);

                    return _.extend({}, {
                        amount: container.data('amount'),
                        locale: container.data('locale'),
                        currency: container.data('currency'),
                        flow: 'checkout',
                        enableShippingAddress: true,
                        payee: {
                            email: this.payeeEmail
                        },
                        displayName: this.displayName,
                        offerCredit: this.offerCredit
                    }, _.object(
                        _.keys(configData),
                        _.values(configData)
                    ));
                },

                /**
                 * Get PayPal button style
                 *
                 * @returns {Object}
                 */
                _getPayPalBtnStyle: function () {
                    var style = {
                        color: this.color,
                        shape: this.shape,
                        layout: this.layout,
                        size: this.size
                    };

                    if (typeof this.fundingicons === 'boolean') {
                        style.fundingicons = this.fundingicons;
                    }
                    if (typeof this.branding === 'boolean') {
                        style.branding = this.branding;
                    }
                    if (typeof this.label === 'string') {
                        style.label = this.label;
                    }
                    if (typeof this.tagline === 'boolean') {
                        style.tagline = this.tagline;
                    }

                    return style;
                },

                /**
                 * Get PayPal Credit funding options
                 *
                 * @returns {Object}
                 */
                _getPayPalFunding: function () {
                    var funding = {
                        allowed: [],
                        disallowed: []
                    };

                    if (this.offerCredit === true) {
                        funding.allowed.push(paypal.FUNDING.CREDIT);
                    } else {
                        funding.disallowed.push(paypal.FUNDING.CREDIT);
                    }
                    if (this.disabledFunding.card === true) {
                        funding.disallowed.push(paypal.FUNDING.CARD);
                    }
                    if (this.disabledFunding.elv === true) {
                        funding.disallowed.push(paypal.FUNDING.ELV);
                    }

                    return funding;
                },

                /**
                 * Map shipping address
                 *
                 * @param {Object} payload
                 * @returns {Object}
                 */
                _mapShippingAddress: function (payload) {
                    var address = payload.details.shippingAddress;

                    payload.details.shippingAddress = {
                        streetAddress: address.line1,
                        locality: address.city,
                        postalCode: address.postalCode,
                        countryCodeAlpha2: address.countryCode,
                        email: payload.details.email,
                        firstname: payload.details.firstName,
                        lastname: payload.details.lastName,
                        telephone: !_.isUndefined(payload.details.phone)
                            ? payload.details.phone
                            : '',
                        region: !_.isUndefined(address.state)
                            ? address.state
                            : ''
                    };

                    return payload;
                }
            })
            : button;
    }
});
