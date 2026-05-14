/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modalToggle',
    'mage/translate'
], function ($, modalToggle) {
    'use strict';

    return function (config, deleteButton) {
        config.buttons = [
            {
                text: $.mage.__('No'),
                class: 'action secondary cancel'
            }, {
                text: $.mage.__('Yes'),
                class: 'action primary',

                /**
                 * Default action on button click
                 */
                click: function (event) { //eslint-disable-line no-unused-vars
                    $(deleteButton.form).submit();
                }
            }
        ];

        modalToggle(config, deleteButton);
    };
});
