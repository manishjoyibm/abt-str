/**
 * Copyright 2019 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/confirm'
], function (_, $, $t, confirm) {
    'use strict';

    $.widget('mage.awSarp2StripeCardsChecker', {
        options: {
            elementSelector: '',
            confirmPopupClass: '',
            confirmPopupMessage: '',
            cardTokenPattern: '(cards\\/delete\\/).*(pm_\\w+)',
            cardTokenMatchIndex: 2,
            subscriptionTokens: {}
        },

        /**
         * @inheritdoc
         */
        _create: function () {
            this._bind();
        },

        /**
         * Event binding
         */
        _bind: function () {
            var handlers = {};

            handlers['click ' + this.options.elementSelector] = '_onLinkClick';
            this._on(handlers);
        },

        /**
         * Link click handler
         *
         * @param {Object} event
         * @private
         */
        _onLinkClick: function (event) {
            var self = this,
                link = event.currentTarget.href;

            if (this._isSubscription(link)) {
                event.preventDefault();

                confirm({
                    modalClass: this.options.confirmPopupClass,
                    content: this.options.confirmPopupMessage,
                    actions: {
                        confirm: function () {
                            self._redirectToUrl(link);
                        }
                    },
                    buttons: [
                        {
                            text: $.mage.__('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        },
                        {
                            text: $.mage.__('Continue'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }
                    ]
                });
            }
        },

        /**
         * Redirect to url
         *
         * @param {string} link
         * @private
         */
        _redirectToUrl: function (link) {
            window.location.href = link;
        },

        /**
         * Check if link has token used in subscription
         *
         * @param {string} link
         * @return {boolean}
         * @private
         */
        _isSubscription: function (link) {
            var regex = new RegExp(this.options.cardTokenPattern, 'g'),
                matches,
                cardToken;

            matches = regex.exec(link);
            if (_.has(matches, this.options.cardTokenMatchIndex)) {
                cardToken = matches[this.options.cardTokenMatchIndex];
                if (_.contains(this.options.subscriptionTokens, cardToken)) {
                    return true;
                }
            }
            return false;
        }
    });

    return $.mage.awSarp2StripeCardsChecker;
});
