var config = {
  config: {
    mixins: {
      'Magento_Checkout/js/action/set-shipping-information': {
        'Abbott_AdultSignature/js/mixin/place-order-mixin': true
      },
      'Magento_Checkout/js/model/quote': {
                'Abbott_AdultSignature/js/mixin/quote-observe-state': true
            }
    }
  }
};