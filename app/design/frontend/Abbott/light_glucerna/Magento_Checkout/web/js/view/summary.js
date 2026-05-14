define([
    'uiComponent',
    'Magento_Checkout/js/model/totals'
], function (Component, totals) {
    'use strict';
    var associatedData = window.checkoutConfig.associatedData;
    return Component.extend({
        isLoading: totals.isLoading,
        isTrial: associatedData ? parseInt(associatedData['allow_trial']) : 0,
        shakes: associatedData ? associatedData['actualShakes'] : 0,
        deliverySplit: associatedData ? associatedData['actualDeliverySplit'] : [],
        productNames: associatedData ? associatedData['productNames'] : [],
        checkIsTrial: function() {
          if(this.isTrial) {
            return true;
          }
          return false;
        },
        getItemShakes: function() {
          return this.shakes + " Shakes";
        },
        getProductChoosen: function() {
          var pName = "";
          var self = this;
          this.deliverySplit.forEach(function(item,index) {
            pName = pName + item + " " + self.productNames[index] + "<br>";
          });
          return pName;
        },
    });
});
