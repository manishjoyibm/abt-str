define([
    'jquery',
    'mage/storage',
    'jquery/jquery-storageapi'
], function ($) {
    'use strict';
    var storage = $.initNamespaceStorage('subscription-profile-id').localStorage;
    return {
        setData: function (cacheKey,data) {
            storage.set(cacheKey, data);
        },
        getData: function (cacheKey) {
            return storage.get(cacheKey);
        },
        removeData: function (cacheKey) {
            storage.remove(cacheKey);
        }
    }
});
