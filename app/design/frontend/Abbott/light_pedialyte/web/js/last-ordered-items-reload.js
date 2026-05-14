define([
    "jquery", "Magento_Customer/js/customer-data"
], function($, customerData) {
    "use strict";
    return function (config, element) {
            customerData
               .reload('*')
               .then(function() { 
               });
    };
});