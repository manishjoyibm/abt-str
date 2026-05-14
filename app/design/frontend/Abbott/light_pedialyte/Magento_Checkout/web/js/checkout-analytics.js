define([
  'jquery','Magento_GoogleTagManager/js/google-tag-manager-cart',
  'Magento_Customer/js/customer-data',
  'Magento_Checkout/js/model/quote'
], function ($,gtmc,storage,quote) {
    'use strict';
   
    $(document).on('change', '.checkout-agreements input[type="checkbox"],.custom_check', function(e) {
      let agreementId = $(this).attr('value');
      if($('.dynamic_text').length && $('.payment-method._active').attr('id') == 'payment-method-braintree-paypal'){ 
        if($('.custom_check').prop("checked") == true && $('#agreement_braintree_paypal_'+agreementId).prop("checked") == true){
          $("#braintree_paypal_placeholder").css({"opacity": "1", "pointer-events": "all"});   
          $('.custom_checkout_error').remove();       
        } else {
          $("#braintree_paypal_placeholder").css({"opacity": "0.3", "pointer-events": "none"});   
          $('.custom_checkout_error').remove();
          if($('.custom_check').prop("checked") == false)
          $('.checkout_success').after('<div class="mage-error custom_checkout_error" id="checkbox-error">This is a required field.</div>');     
        }
      } else{
        if($('#agreement_braintree_paypal_'+agreementId).prop("checked") == true){
          $("#braintree_paypal_placeholder").css({"opacity": "1", "pointer-events": "all"});   
          $('.custom_checkout_error').remove();       
        } else {
          $("#braintree_paypal_placeholder").css({"opacity": "0.3", "pointer-events": "none"});        
        }
      }
    }); 
    return function () {
      storage.reload(['directory-data']);
      storage.reload(['last-ordered-items']);
      window.dataLayer = window.dataLayer || [];
      var products = [];
      if(window.checkoutConfig){
        window.checkoutConfig.quoteItemData.forEach(function(item) {
          var product = {};
          product["id"] = item.sku;
          product["name"] = item.name;
          product["price"] = item.price;
          product["quantity"] = item.qty;
          product["brand"] = (item.product.brand == "null" || !item.product.brand) ? "" : item.product.brand;
          var variants = [];
          (item.product.case_of_product == "null" || !item.product.case_of_product) ? "" : variants.push(item.product.case_of_product);
          (item.product.product_flavor == "null" || !item.product.product_flavor) ? "" : variants.push(item.product.product_flavor);
          (item.product.product_form == "null" || !item.product.product_form) ? "" : variants.push(item.product.product_form);
          product["variant"] = (variants && variants.length) ? variants.join(" | ") : "NA" ;
          products.push(product);
        });

        var checkout = {};
        checkout["products"] = products;
        checkout["currencyCode"] = getCurrency();
        checkout["coupon"] = getCouponData();
        var ecommerce = {};
        ecommerce["checkout"] = checkout;
        var checkoutObj = {
          'event' : 'begin_checkout',
          'ecommerce' : ecommerce
        };
        window.dataLayer.push(checkoutObj);
      }

      $(window).on("load", function() {
          $(document).on('click', '#shipping-method-buttons-container button.action' , function() {
          let shippingAddressData = getShippingAddressData();
          let shippingMethodData = getShippingMethodData();
          let couponData = getCouponData();
          let currency = getCurrency();
          var shippingInfo = {
            shippingAddressData,
            shippingMethodData,
            products,
            couponData,
            currency
          };
          var shippingObj = {
            'event' : 'add_shipping_info',
            'shippingInfo' : shippingInfo
          };
          window.dataLayer.push(shippingObj);
                
        });
        let debounceTimer; 
        $(document).ajaxComplete(function(event, xhr,settings) {
         clearTimeout(debounceTimer);
         debounceTimer = setTimeout(function(){
          let paymentDiv = $('.items.payment-methods').children().length; 
            if(paymentDiv > 0){
                var selectedPM = $('#checkout-payment-method-load input[name="payment[method]"]:checked').val();
          if(selectedPM){
            let couponData = getCouponData();
            let currency = getCurrency();
            var pObj = {
              'event' : 'add_payment_info',
              'paymentMethod' : selectedPM,
              'coupon' : couponData,
              'currencyCode' : currency
            };
            window.dataLayer.push(pObj);
          }
            }
         },5000);
          
        });
    });
     
    function getShippingAddressData() {
      var shippingAddress = quote.shippingAddress();
      if(shippingAddress) {
            var shippingAddressInfo = {
              'postcode' : shippingAddress.postcode,
              'city' : shippingAddress.city,
              'region' : shippingAddress.region,
              'region_id' : shippingAddress.regionId,
              'country' : shippingAddress.countryId,
              'telephone' : shippingAddress.telephone
            };
            return shippingAddressInfo;
      }
      return '';
    }
    function getShippingMethodData() {
      var shippingMethod = quote.shippingMethod();
          
         if(shippingMethod){
          var selectedShippingMethod = {
            'amount' : shippingMethod && shippingMethod.amount,
            'carrier_code' : shippingMethod && shippingMethod.carrier_code,
            'carrier_title' : shippingMethod && shippingMethod.carrier_title,
            'method_code' : shippingMethod && shippingMethod.method_code,
            'method_title' : shippingMethod && shippingMethod.method_title
          };
          return selectedShippingMethod;
        }
        return '';
      }
    function getCouponData() {
      var coupon = {
        'couponCode' : window.checkoutConfig.totalsData.coupon_code,
        'discountAmount' : window.checkoutConfig.totalsData.discount_amount,
        'shippingDiscountAmount' : window.checkoutConfig.totalsData.shipping_discount_amount,
      };
      return coupon;
    }
    function getCurrency() {
      return window.checkoutConfig.totalsData.base_currency_code;
    }
    };
});