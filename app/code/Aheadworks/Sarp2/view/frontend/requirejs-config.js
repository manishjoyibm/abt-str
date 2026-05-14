/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

var config = {
    map: {
        '*': {
            awSarp2RegularPrice:                'Aheadworks_Sarp2/js/product/regular-price',
            awSarp2SubscriptionOptionList:      'Aheadworks_Sarp2/js/product/subscription-option-list',
            awSarp2SubscriptionOptionStorage:   'Aheadworks_Sarp2/js/product/storage',
            awSarp2ElementVisibility:           'Aheadworks_Sarp2/js/element-visibility',
            awSarp2ButtonControl:               'Aheadworks_Sarp2/js/button-control',
            awSarp2Calendar:                    'Aheadworks_Sarp2/js/widget/profile/calendar',
            awSarp2StripeCardsChecker:          'Aheadworks_Sarp2/js/customer/stripe-cards-checker'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/payment-service': {
                'Aheadworks_Sarp2/js/checkout/model/payment-service-mixin': true
            },
            'Magento_Checkout/js/model/quote': {
                'Aheadworks_Sarp2/js/checkout/model/quote-mixin': true
            },
            'PayPal_Braintree/js/view/payment/method-renderer/hosted-fields': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/braintree/hosted-fields-mixin': true
            },
            'PayPal_Braintree/js/view/payment/method-renderer/cc-form': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/braintree/cc-form-mixin': true
            },
            'PayPal_Braintree/js/view/payment/method-renderer/paypal': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/braintree/paypal-mixin': true
            },
            'Aheadworks_BamboraApac/js/view/payment/method-renderer/hosted-fields': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/bambora-apac/hosted-fields-mixin': true
            },
            'StripeIntegration_Payments/js/view/payment/method-renderer/stripe_payments': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/stripe-payments-mixin': true
            },
            'Magento_AuthorizenetAcceptjs/js/view/payment/method-renderer/authorizenet-accept': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/authorizenet-accept-mixin': true
            },
            'Magento_OfflinePayments/js/view/payment/method-renderer/cashondelivery-method': {
                'Aheadworks_Sarp2/js/checkout/view/payment-method/renderer/cashondelivery-mixin': true
            },
            'PayPal_Braintree/js/paypal/product-page': {
                'Aheadworks_Sarp2/js/product/braintree/paypal/button-mixin': true
            },
            'Magento_ConfigurableProduct/js/configurable': {
                'Aheadworks_Sarp2/js/product/configurable-mixin': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Aheadworks_Sarp2/js/product/swatch-renderer-mixin': true
            }
        }
    }
};
