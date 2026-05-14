/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

 define([], function () {
    'use strict';

     let scriptTagAdded = false;

     return {
        /**
         * Add script tag. Script tag should be added once
         */
        addReCaptchaScriptTag: function () {
            let element, scriptTag;

            if (!scriptTagAdded) {
                element = document.createElement('script');
                scriptTag = document.getElementsByTagName('script')[0];

                element.async = true;
                element.src = 'https://www.google.com/recaptcha/enterprise.js' +
                    '?onload=globalOnRecaptchaOnLoadCallback&render=explicit';

                scriptTag.parentNode.insertBefore(element, scriptTag);
                scriptTagAdded = true;
            }
        }
    };
});
