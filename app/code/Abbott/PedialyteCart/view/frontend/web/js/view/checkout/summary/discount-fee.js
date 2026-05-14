define(
    [
        'ko',
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (ko, Component, quote, priceUtils, totals) {
        "use strict";
        var isPdlDiscountEnable = window.checkoutConfig.isPdlDiscountEnable;
        var pdlDiscountLabel = window.checkoutConfig.pdlDiscountLabel;

        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Abbott_PedialyteCart/checkout/summary/discount-fee'
            },
            totals: quote.getTotals(),
            canVisibleCustomDiscountBlock: isPdlDiscountEnable,
            getPdlDiscountLabel:ko.observable(pdlDiscountLabel),
            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,
            isDisplayed: function() {
                return this.isFullMode();
            },
            getValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = totals.getSegment('pdl_discount').value;
                }
                return this.getFormattedPrice(price);
            },
            getRawValue: function() {
                var price = 0;
                if (this.totals()) {
                  var segment = totals.getSegment('pdl_discount').value;
                  price = segment ? segment.value : 0;
                }
                return price;
            },
            getBaseValue: function() {
                var price = 0;
                if (this.totals()) {
                    price = this.totals().base_fee;
                }
                return priceUtils.formatPrice(price, quote.getBasePriceFormat());
            }
        });
    }
)
