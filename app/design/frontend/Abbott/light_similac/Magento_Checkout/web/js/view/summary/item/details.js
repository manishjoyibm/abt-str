define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },

        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        getItemSizeWeight: function(item) {
          var quoteItemData = window.checkoutConfig.quoteItemData;
          var sizeOrWeight = '';
          quoteItemData.forEach(function(quoteItem) {
            if(item["item_id"] == quoteItem["item_id"]) {
              sizeOrWeight = quoteItem.product ? quoteItem.product["size_or_weight"] : '';
            }
          });
          return sizeOrWeight;
        }
    });
});
