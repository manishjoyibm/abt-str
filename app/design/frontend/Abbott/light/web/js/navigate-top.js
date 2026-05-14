define(["jquery"], 
function($) {
;
(function(ABBOTT) {
    ABBOTT.navigateTop = (function() {});

      $(document).ready( function() {          
        $(function() {
            var $scrollTop = $('#scroll-top');
            var scrollVal = 30;
            
            /**
             * @function
             * @description Toggle class active on Scroll.
             */
            function toggleActive() {
              if (document.body.scrollTop > scrollVal || document.documentElement.scrollTop > scrollVal) {
                $scrollTop.addClass('active');
              } else {
                $scrollTop.removeClass('active');
              }
            }
      
            /**
             * @function
             * @description Scroll To the Top on Click
             */
            function scrollToTop() {
              $('html, body').animate({ scrollTop: '0' }, 1000 );
            }
      
            // Bind Events
            $(window).on('scroll', toggleActive);
            $scrollTop.on('click', scrollToTop);
          });
      });
  })(window.ABBOTT || (window.ABBOTT = {}));
});