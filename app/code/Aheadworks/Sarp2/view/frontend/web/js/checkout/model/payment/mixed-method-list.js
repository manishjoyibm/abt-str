/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'ko'
], function (ko) {
    'use strict';

    return ko.observableArray([
        'braintree',
        'braintree_paypal',
        'aw_bambora_apac',
        'stripe_payments',
        'authorizenet_acceptjs',
        'cashondelivery'
    ]);
});
