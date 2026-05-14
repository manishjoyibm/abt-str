define([
    'uiRegistry',
    'underscore'
], function (registry, _) {
    'use strict';

    function getCustomScope(attributeType) {
        if (attributeType.indexOf('.') !== -1) {
            return attributeType.substr(attributeType.indexOf('.') + 1);
        }

        return attributeType;
    }

    return function (attributesTypes, hideError = false) {
        let amastyCheckoutProvider = registry.get('amastyCheckoutProvider'),
            focused = false,
            result = {};

        amastyCheckoutProvider.set('params.invalid', false);

        _.each(attributesTypes, function (attributeType) {
            let customScope = getCustomScope(attributeType),
                container;

            result = _.extend(result, amastyCheckoutProvider.get(attributeType));
            amastyCheckoutProvider.trigger(customScope + '.data.validate');

            if (!hideError && !focused && amastyCheckoutProvider.get('params.invalid')) {
                container = registry.filter("index = " + attributeType + 'Container');
                if (container.length) {
                    container[0].focusInvalidField();
                }
                focused = true;
            }
        }, this);

        if (amastyCheckoutProvider.get('params.invalid')) {
            if (hideError) {
                //set current value as initialValue
                amastyCheckoutProvider.trigger('data.overload');
                //set initialValue to value and reset errors
                amastyCheckoutProvider.trigger('data.reset');
            }

            return false;
        } else {
            return result;
        }
    }
});
