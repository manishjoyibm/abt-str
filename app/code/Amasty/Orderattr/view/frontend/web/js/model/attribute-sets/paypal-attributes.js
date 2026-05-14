define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    var attributesTypes = ['amorder_attributes_fields'],
        formCode = 'amasty_checkout';

    if (quote.isVirtual()) {
        formCode = 'amasty_checkout_virtual';
    }

    return {
        'attributeTypes': attributesTypes,
        'formCode': formCode
    }
});
