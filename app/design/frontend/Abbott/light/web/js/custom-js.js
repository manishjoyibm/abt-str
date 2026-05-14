define(['jquery'],function($){
    $("#myiFrame").on("load", function() {
        let head = $("#myiFrame").contents().find("head");
        let css = '<style>/********* Put your styles here **********</style>';
        $(head).append(css);
      });
});