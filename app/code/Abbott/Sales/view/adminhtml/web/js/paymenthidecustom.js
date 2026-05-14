require(['jquery'], function ($) {
    
    $(document)
        .ready(function () {
            
            $("#order-billing_method_form, .order-billing-method-summary")
                .addClass("display-none");
            
            if ($('input:radio[name="order[shipping_method]"]')
                .attr("checked") == "checked") {
                $("#order-billing_method_form")
                    .addClass("display-block");
                $("#order-billing_method_form")
                    .removeClass("display-none");
            }
            $('input:radio[name="order[shipping_method]"]')
                .change(function () {
                    
                    $("#order-billing_method_form")
                        .addClass("display-block");
                    
                });
        });
});
