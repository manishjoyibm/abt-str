/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'ko',
    'underscore',
    './price-input'
], function (ko, _, PriceInput) {
    'use strict';

    return PriceInput.extend({
        defaults: {
            elementTmpl: 'Aheadworks_Sarp2/ui/form/element/product/price-input-with-auto',
            visibleValue: '',
            isUseAuto: '',
            isUseAutoDisabled: false,
            autoValue: 0,
            isAutoValueFloat: true,
            listens: {
                'isUseAuto': 'toggleUseAuto'
            }
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            this._super()
                .observe(['autoValue', 'isUseAuto', 'isUseAutoDisabled']);

            this.visibleValue = ko.pureComputed({

                /**
                 * Read callback
                 *
                 * @returns {*}
                 */
                read: function () {
                    return this.isUseAuto() ? this.autoValue() : this.value();
                },

                /**
                 * Write callback
                 *
                 * @param {*} value
                 */
                write: function (value) {
                    this.value(value);
                },

                owner: this
            });

            return this;
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            this._super();

            if (!this.value()) {
                this.isUseAuto(true);
            }

            return this;
        },

        /**
         * Toggle use auto value flag
         *
         * @param {Boolean} state
         */
        toggleUseAuto: function (state) {
            this.disabled(state);
            if (state) {
                this.value('');
            } else {
                this.value(this.isAutoValueFloat ? this.autoValue() : '');
                this.error(false);
            }
        }
    });
});
