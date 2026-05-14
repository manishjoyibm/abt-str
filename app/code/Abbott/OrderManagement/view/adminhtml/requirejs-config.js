let config = {
    config: {
        mixins: {
            'Avalara_AvaTax/js/view/address-validation-form': {
                'Abbott_OrderManagement/js/address-validation-form': true
            }
        }
    },
    map: {
        '*': {
            'Magento_Sales/order/create/scripts': 'Abbott_OrderManagement/js/order/create/scripts',
            'Magento_AdvancedCheckout/addbysku': 'Abbott_OrderManagement/js/addbysku'
        }
    }
};
