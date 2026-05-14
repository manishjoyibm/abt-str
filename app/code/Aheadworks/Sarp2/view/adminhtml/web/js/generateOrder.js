define([
    'jquery',
    'Aheadworks_Sarp2/js/profileStorage'
], function ($, profileStorage) {
    'use strict';
    window.generateOrdersById = function (profileUrl,profileId) {
        var profieStorageVal = profileStorage.getData(profileId);
        if (!profieStorageVal) {
            profileStorage.setData(profileId,1);
        } else {
            profileStorage.setData(profileId,2);
        }
        profieStorageVal = profileStorage.getData(profileId);
        if (profieStorageVal == 2) {
            profileStorage.removeData(profileId);
        }
        var profileUpdatedUrl = profileUrl + "counter/" + profieStorageVal;
        window.location.href = profileUpdatedUrl;
    }

});
