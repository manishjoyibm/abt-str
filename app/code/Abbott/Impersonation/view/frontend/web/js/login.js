define([
    'Magento_Customer/js/customer-data'
], function (customerData) {

    'use strict';

    return function (config) {
        if(customerData.reload(['customer'],true))
        {
            window.setTimeout(function(){
                window.location.href = config.redirectUrl;
            }, 5000);
        }
    };
});

