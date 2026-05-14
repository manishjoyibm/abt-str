/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'awSarp2SubscriptionOptionStorage'
], function ($, _, optionStorage) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            /**
             * @inheritdoc
             * */
            _UpdatePrice: function () {
                var $widget = this,
                    optionPrices = optionStorage.get('option_prices'),
                    defaultOptionPrices = optionStorage.get('default_option_prices'),
                    subscriptionOptionList;

                optionStorage.set('selected_product_id', $widget._getSelectedProductId());

                if (optionPrices) {
                    if (!defaultOptionPrices) {
                        optionStorage.set('default_option_prices', $widget.options.jsonConfig.optionPrices);
                    }
                    $widget.options.jsonConfig.optionPrices = optionPrices;
                } else if (defaultOptionPrices) {
                    $widget.options.jsonConfig.optionPrices = defaultOptionPrices;
                }

                subscriptionOptionList = optionStorage.get('subscription_option_list');
                if (subscriptionOptionList) {
                    subscriptionOptionList.trigger('updateSubscriptionOptionValue')
                }

                return this._super();
            },

            /**
             * Get selected option product id
             *
             * @returns {*}
             * @private
             */
            _getSelectedProductId: function () {
                var $widget = this,
                    selectedOptions = _.object(_.keys($widget.optionsMap), {}),
                    selectedProductId;

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    var attributeId = $(this).attr('attribute-id');
                    selectedOptions[attributeId] = $(this).attr('option-selected');
                });

                selectedProductId = _.findKey($widget.options.jsonConfig.index, selectedOptions);

                return selectedProductId;
            }
        });

        return $.mage.SwatchRenderer;
    }
});
