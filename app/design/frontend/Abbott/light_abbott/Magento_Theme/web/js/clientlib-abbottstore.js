define(['jquery','domReady!'], function ($) {
    "use strict";

    return function (data) {
        window.datasrc = data.src;
        let debounceTimer = 0;
        $(document).ready(function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                let headerDiv = $('#header-set').children().length;
                if (headerDiv > 0) {
                    let script = document.createElement('script');
                    script.src = window.datasrc;
                    document.body.appendChild(script);
                }
            }, 500);
        });
    };
});
