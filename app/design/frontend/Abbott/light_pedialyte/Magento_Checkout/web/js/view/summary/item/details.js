define([
    'uiComponent'
], function (Component) {
    'use strict';
    
    var quoteItemData = window.checkoutConfig.quoteItemData;
    
    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },
        
        quoteItemData: quoteItemData,
        
        /**
         * @param {Object} quoteItem
         * @return {String}
         */
        getValue: function (quoteItem) {
            return quoteItem.name;
        },
        
        getSize: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            if (item.size_attr){
                return 'Size: '+item.size_attr;
            } else{
                return '';
            }
        },

        getFlavour: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            if (item.flavour_attr){
                return 'Flavor: '+item.flavour_attr;
            } else{
                return '';
            }
        },

        getBackorder: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            if (item.backorder){
                return item.backorder;
            } else{
                return '';
            }
        },
        
        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        }
    });
});
