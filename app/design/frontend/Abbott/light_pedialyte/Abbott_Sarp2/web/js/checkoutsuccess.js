define(["jquery", "domReady!"], function($,dom){
    if(window.valuesConfig.config != 0){
        var sku = window.orderItem.all_sku;
        var DoTag = window.valuesConfig.do_tag;
        var sourceUrl = window.valuesConfig.source_url;
        window.bk_async = function() {
        bk_addPageCtx('product', sku); 
        bk_addPageCtx('purchase_complete', true);
        BKTAG.doTag(DoTag, 1);
        };
        (function() {
            var scripts = document.getElementsByTagName('script')[0];
            var s = document.createElement('script');
            s.async = true;
            s.src = sourceUrl;
            scripts.parentNode.insertBefore(s, scripts);
        }());
    }
})