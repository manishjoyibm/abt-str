/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    var isEnabled = true;

    return {
        config: {},

        /**
         * Enable saving estimation
         */
        enable: function () {
            isEnabled = true;
        },

        /**
         * Disable saving estimation
         */
        disable: function () {
            isEnabled = false;
        }

        // todo: M2SARP-331
    };
});
