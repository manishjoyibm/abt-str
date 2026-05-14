let config = {
    config: {
        mixins: {
            'Magento_Ui/js/lib/validation/validator': {
                'Abbott_Checkout/js/validation-mixin': true
            }
        }
    },
    map: {
        "*": {
            nameValidation: "Abbott_Checkout/js/mage-validation",
            'Magento_Checkout/template/estimation.html': 'Abbott_Checkout/template/estimation.html',
            "PayPal_Braintree/js/view/payment/method-renderer/hosted-fields" : "Abbott_Checkout/js/view/payment/method-renderer/hosted-fields",
            "PayPal_Braintree/js/view/payment/method-renderer/cc-form" : "Abbott_Checkout/js/view/payment/method-renderer/cc-form",
            "PayPal_Braintree/template/payment/form" : "Abbott_Checkout/template/payment/form"
        }
    }
};
