define([
    'jquery'
], function ($) {
    'use strict';    

    $(document).ready(function () {
        $(document).on('click', "[name='region_id']", function () {             
            $(this).closest('form').find("input[name='postcode']").val('');
            $(this).closest('form').find("input[name='postcode']").keyup();
        });

        $(document).on('blur', "[name='street[0]']", function () {             
            $(this).closest('form').find("input[name='postcode']").val('');
            $(this).closest('form').find("input[name='postcode']").keyup();
        });

        $(document).on('blur', "[name='street[1]']", function () {             
            $(this).closest('form').find("input[name='postcode']").val('');
            $(this).closest('form').find("input[name='postcode']").keyup();
        });

        $(document).on('blur', "[name='street[2]']", function () {             
            $(this).closest('form').find("input[name='postcode']").val('');
            $(this).closest('form').find("input[name='postcode']").keyup();
        });
        
    });

    return function (targetModule) {
        targetModule.crazyPropertyAddedHere = 'yes';
        return targetModule;
    };
    
});