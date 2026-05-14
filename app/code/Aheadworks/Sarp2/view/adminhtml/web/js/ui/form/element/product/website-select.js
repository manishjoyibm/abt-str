/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/form/element/select'
], function (_, Select) {
    'use strict';

    return Select.extend({
        /**
         * @inheritdoc
         */
        _setClasses: function () {
            this._super();
            _.extend(this.additionalClasses, {_disabled: false});

            return this;
        }
    });
});
