require(['jquery', 'jquery/ui'], function($){ 
  $( document ).ready(function() {
  //Set employee consent to frontend
    var empTermExistCondition = setInterval(function() {
     if ($('input[name=employee_terms]').length) { 
      clearInterval(empTermExistCondition);
      setEmployeeConsent();
     }
    }, 100);
    function setEmployeeConsent(){
      var res = $('.employee-consent .otnotice-section-content').html();
      if(res !== ''){
        $("input[name=employee_terms] + label").html(res); 
      }
    }
    
  //Set payment terms consent to frontend
    var payTermExistCondition = setInterval(function() {
      if ($('.checkout-agreement .label').length) { 
       clearInterval(payTermExistCondition);
       setPaymentConsent();
      }
     }, 100);
     function setPaymentConsent(){
       var res = $('.payment-terms-consent .otnotice-section-content').html();
       if(res !== ''){
         $(".checkout-agreement .label").html(res); 
       }
     }

     //Set subscription consent to frontend
    var subscriptionTermExistCondition = setInterval(function() {
     if ($('input[name=subscription_consent_terms]').length) { 
      clearInterval(subscriptionTermExistCondition);
      setSubscriptionConsent();
     }
    }, 100);
    function setSubscriptionConsent(){
      var res = $('.subscription-terms-consent .otnotice-section-content').html();
      if(res !== ''){
        $("input[name=subscription_consent_terms] + label").html(res); 
      }
    }
  }); 
}); 