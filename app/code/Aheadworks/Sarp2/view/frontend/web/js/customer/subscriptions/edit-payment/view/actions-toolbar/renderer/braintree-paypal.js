/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery',
    'Aheadworks_Sarp2/js/customer/subscriptions/edit-payment/view/actions-toolbar/renderer/default',
], function ($, Component) {
    'use strict';

    return Component.extend({

        /**
         * @inheritdoc
         */
        isInContext: function () {
            return true;
        }
    });
});
