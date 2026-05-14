/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Aheadworks_Sarp2/js/product/config/provider',
    'mage/template',
    'awSarp2SubscriptionOptionStorage',
    './saving-estimation'
], function (
    $,
    _,
    configProvider,
    mageTemplate,
    optionStorage,
    savingEstimation // todo: M2SARP-331
) {
    'use strict';

    var value;

    $.widget('mage.awSarp2SubscriptionOptionList', {
        options: {
            defaultValue: 0,
            details: '[data-role=aw-sarp2-subscription-details]',
            detailsList: '[data-role=aw-sarp2-subscription-details-list]',
            detailsListItemTemplate: '[data-role=details-item-template]',
            priceHolder: '[data-role=priceBox]',
            configurableOldPriceClass: false,
            oldPriceSelector: '.old-price'
        },

        /**
         * @inheritdoc
         * */
        _create: function () {
            value = this.options.defaultValue;
            this._bind();
        },

        /**
         * @inheritdoc
         */
        _init: function () {
            var initialChecked = this.element.find('input:checked');

            this.options.priceHolder += '[data-product-id=' + configProvider.getProductId() + ']';

            if (initialChecked.length > 0) {
                value = initialChecked.val();
            }
            if (configProvider.isInitialized()) {
                this._triggerUpdateValue();
            } else {
                configProvider.on('initialize', $.proxy(this._triggerUpdateValue, this));
            }

            optionStorage.set('subscription_option_list', this.element);
        },

        /**
         * Event binding
         */
        _bind: function () {
            this._on({
                'change input': 'onInputValueChange',
                'updateSubscriptionOptionValue': 'onUpdateValue'
            });
        },

        /**
         * On input value change
         *
         * @param {Event} event
         */
        onInputValueChange: function (event) {
            value = $(event.currentTarget).val();
            this._triggerUpdateValue();
        },

        /**
         * Set value
         *
         * @param {Number} newValue
         */
        setValue: function (newValue) {
            value = newValue;
            this._triggerUpdateValue();
        },

        /**
         * On update value event handler
         */
        onUpdateValue: function () {
            var details = this.element.find(this.options.details),
                subscriptionDetails;

            if (value == 0) {
                optionStorage.remove('option_prices');
                this.showAdditionalPrices();
                details.slideUp('slow').animate({
                    opacity: 0
                }, {
                    queue: false,
                    duration: 'slow'
                });
            } else {
                optionStorage.set('option_prices', configProvider.getOptionPrices(value));
                this.hideAdditionalPrices();
                subscriptionDetails = configProvider.getSubscriptionDetails(value);
                if (!_.isEmpty(subscriptionDetails)) {
                    this._refreshDetailsList(subscriptionDetails);
                    details.css('opacity', 0).slideDown('slow').animate({
                        opacity: 1
                    }, {
                        queue: false,
                        duration: 'slow'
                    });
                }
            }
        },

        /**
         * Refresh subscription details list
         *
         * @param {Array} details
         */
        _refreshDetailsList: function (details) {
            var detailsList = this.element.find(this.options.detailsList),
                templateSelector = this.options.details + ' ' + this.options.detailsListItemTemplate;

            detailsList.html('');
            _.each(details, function (data) {
                var template = mageTemplate(templateSelector),
                    item = $(template(data));

                item.appendTo(detailsList);
            });
        },

        /**
         * Trigger update value event
         */
        _triggerUpdateValue: function () {
            this.element.trigger('updateSubscriptionOptionValue', value);
        },

        /**
         * Show additional prices
         */
        showAdditionalPrices: function () {
            var priceHolder = $(this.options.priceHolder),
                oldPriceBox = $(this.options.oldPriceSelector, priceHolder),
                tierPricesBox = $(configProvider.getTierPricesSelector());

            if (oldPriceBox.length && configProvider.isNeedToDisplayOldPrice(value)) {
                oldPriceBox.show();
                if (this.options.configurableOldPriceClass) {
                    oldPriceBox.addClass('sly-old-price');
                }
            }
            if (tierPricesBox.length  && configProvider.isNeedToDisplayTierPrice(value)) {
                tierPricesBox.show();
            }
        },

        /**
         * Hide additional prices
         */
        hideAdditionalPrices: function () {
            var oldPriceBox = $(this.options.oldPriceSelector, $(this.options.priceHolder)),
                tierPricesBox = $(configProvider.getTierPricesSelector());

            if (oldPriceBox.length) {
                oldPriceBox.hide();
                if (oldPriceBox.hasClass('sly-old-price')) {
                    oldPriceBox.removeClass('sly-old-price');
                    this.options.configurableOldPriceClass = true;
                }
            }
            if (tierPricesBox.length) {
                tierPricesBox.hide();
            }
        }
    });

    return $.mage.awSarp2SubscriptionOptionList;
});
