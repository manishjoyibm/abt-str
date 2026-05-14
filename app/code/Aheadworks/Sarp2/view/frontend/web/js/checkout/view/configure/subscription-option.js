/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

require([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data'
], function ($, _, customerData) {
    'use strict';

    var addToCartFormSelector = '#product_addtocart_form',
        subscriptionOptionsListSelector = '[data-role=aw-sarp2-subscription-type]',
        productIdSelector = '[name=product]',
        cartData = customerData.get('cart');

    cartData.subscribe(function (updateCartData) {
        var form = $(addToCartFormSelector),
            productId = form.find(productIdSelector).val(),
            subscriptionOptions = form.find(subscriptionOptionsListSelector),
            itemData;

        if (productId && _.has(updateCartData, 'items')) {
            itemData = _.find(updateCartData['items'], function (itemCandidate) {
                return _.has(itemCandidate, 'product_id')
                    && itemCandidate['product_id'] == productId;
            });
            if (!_.isUndefined(itemData) && _.has(itemData, 'aw_sarp_subscription_type')) {
                subscriptionOptions.find('input[value=' + itemData['aw_sarp_subscription_type'] + ']')
                    .attr('checked', 'checked');
            }
        }
    });
});
