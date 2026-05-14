define(["jquery"], function($) {
   'use strict';
   return function(address) {
       $.widget('abbott.address', address,{
           _deleteAddress: function (e) {
                var self = this;
                if (typeof $(e.target).parent().data('address') !== 'undefined') {
                    window.location = self.options.deleteUrlPrefix + $(e.target).parent().data('address') +
                                    '/form_key/' + $.mage.cookies.get('form_key');
                } else {
                    window.location = self.options.deleteUrlPrefix + $(e.target).data('address') +
                                    '/form_key/' + $.mage.cookies.get('form_key');
                }
                return false;
            }
       });
       return $.abbott.address;
   };
});