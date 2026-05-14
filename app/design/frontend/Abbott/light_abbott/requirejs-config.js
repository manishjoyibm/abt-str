var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Magento_Checkout/js/model/checkout-data-resolver-mixin': true
            }
        }
    },
    map: {
        '*': {
            "Avalara_AvaTax/js/view/ReviewPayment" : "js/payment"
        }
    },
    paths: {
        'bootstrap.bundle': 'js/bootstrap.bundle.min'
    },
    shim: {
        '*': {
            deps: ['jquery']
        }
    }
};
