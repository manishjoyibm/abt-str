define([
    'uiComponent',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/cart/cache'
], function (Component, defaultTotal, cartCache) {
    'use strict';

    var associatedData = window.checkoutConfig.associatedData;
    var self;
    return Component.extend({
        isTrial: associatedData ? parseInt(associatedData['allow_trial']) : 0,
        associatedProductPrice: associatedData ? associatedData['associatedProductPrice'] : "",
        actualProductPrice: associatedData ? associatedData['actualProductPrice'] : "",
        associatedRenewDate: associatedData ? associatedData['associatedRenewDate'] : "",
        actualRenewDate: associatedData ? associatedData['actualRenewDate'] : "",
        deliverySplit: associatedData ? associatedData['associatedDeliverySplit'] : [],
        productNames: associatedData ? associatedData['productNames'] : [],
        defaults: {
            template: 'Magento_Checkout/subscriptioninfo'
        },
        initialize: function() {
          self = this;
          this._super();
          cartCache.set('totals',null);
          defaultTotal.estimateTotals();
        },
        shouldShowDiv: function() {
          if(this.isTrial) {
            return true;
          }
          return false;
        },
        getSubscriptionSummaryRenewPoductName: function() {
          var pName = "";
          this.deliverySplit.forEach(function(item,index) {
            pName = pName + item + " " + self.productNames[index] + "<br>";
          });
          return pName;
        },
        getSubscriptionSummaryRenewText: function() {
          return "Monthly subscription begins on " + this.associatedRenewDate;
        },
        getSubscriptionSummaryRenewPrice: function(){
          return "For " + this.associatedProductPrice + " + FREE shipping";
        },
        getSubscriptionRenewDate: function() {
          var text;
          if(this.isTrial){
            text =  "Subscription begins on " + this.associatedRenewDate + " for " + this.associatedProductPrice + "/month";
          } else {
            text =  "Renews on " + this.actualRenewDate +" for " + this.actualProductPrice + "/month";
          }
          return text;
        }
    });
});
