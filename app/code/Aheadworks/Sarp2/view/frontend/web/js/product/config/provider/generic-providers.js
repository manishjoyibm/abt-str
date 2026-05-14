/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'Aheadworks_Sarp2/js/product/config/provider',
    './generic',
    './configurable'
], function (provider, genericProvider, configurableProvider) {
    'use strict';

    return {

        /**
         * Component constructor
         */
        'Aheadworks_Sarp2/js/product/config/provider/generic-providers': function () {
            provider.register(genericProvider);
            provider.register(configurableProvider);
            provider.setIfInitialized();
        }
    };
});
