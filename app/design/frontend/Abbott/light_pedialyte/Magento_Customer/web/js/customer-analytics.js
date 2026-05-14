define([
  'jquery','Magento_GoogleTagManager/js/google-tag-manager',
  'Magento_Customer/js/customer-data'
], function ($,gtmc,storage) {
    'use strict'; 
    return function () {
      window.dataLayer = window.dataLayer || [];
      
      $(document).on('click', '.login-container .form-login .primary button.action' , function() {
        var loginObj = {
          'event' : 'login',
          'method' : 'Login_Form'
        };
        window.dataLayer.push(loginObj);
      });
      $(document).on('click', '.form-create-account button.action' , function() {
        var registerObj = {
          'event' : 'sign_up',
          'method' : 'Signup_Form'
        };
        window.dataLayer.push(registerObj);
      });
    };
});