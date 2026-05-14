define([
  'jquery',
  'mage/storage',
  'Magento_Customer/js/customer-data',
  'Magento_Ui/js/modal/confirm',
  'mage/url'
], function ($, storage, customerData, confirm, urlBuilder) {
  'use strict';

  function ensureAcceptance() {
    // Always refresh 'cart' so we read latest server-side flags
    return $.when(customerData.reload(['cart'], true)).then(function () {
      var cartData = customerData.get('cart')();
      var sig = cartData && cartData.adult_signature ? cartData.adult_signature : {required:false,accepted:false};
      var config = window.checkoutConfig.adultSignature;
      
      return new Promise(function (resolve, reject) {
        
        if(!config.enabled || !config.hasAdultProduct) {
          resolve(true);
          return;
        }
        
        var selectedState = window.checkoutConfig.custom.state;
        var restrictedState = config.restricStates;
        
          if (!restrictedState.includes(selectedState)) {
              resolve(true);
              return;
          }
          
        confirm({
          title: $.mage.__('Adult Signature Required'),
          modalClass: 'modal-slide adultsignatureModel',
          content: (config.popupMessage)
            || $.mage.__('The purchase of one or more products in your cart requires an Adult Signature at delivery.'),
          actions: {
            confirm: function () {
              var acceptUrl = urlBuilder.build('abbott/adultsignature/accept');
              storage.post(acceptUrl).done(function () {
                customerData.reload(['cart'], true);
                resolve(true);
              }).fail(function () {
                reject(new Error('Could not set acceptance'));
              });
            },
            cancel: function () { reject(new Error('Not accepted')); }
          }

          // Do NOT pass custom `buttons` – let confirm wire the click handlers
        });
      });
    });
  }

  return function (originalAction) {
    return function () {
      var args = arguments, ctx = this;
      // return the chained promise so Magento advances to Payment after accept
      return ensureAcceptance().then(function () {
        return originalAction.apply(ctx, args);
      }).catch(function () {
        var dfd = $.Deferred(); dfd.reject(); return dfd.promise();
      });
    };
  };
});