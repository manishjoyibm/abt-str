define([
    'jquery','Magento_GoogleTagManager/js/google-tag-manager-cart'
], function ($,gtmc) {
    'use strict';

    return function (data) {
      window.dataLayer = window.dataLayer || [];
      if(data.removeData.length) {
        dataLayer.push({
            "event": "removeFromCart",
            "ecommerce": {
              "remove":{
                "products": data.removeData
              }
            }
        });
      }
    };
});
