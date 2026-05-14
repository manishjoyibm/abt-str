define(["jquery", "domReady!"], function($,dom){
   var percentage  =  window.free_percentage;
   var free_ship   =  window.setfreeShip;
   if(free_ship == 0){
     var i = 0;
        if (i == 0) {
        i = 1;
        var width = 1;
        var id = setInterval(frame, 1);
        function frame() {
          if (width >= percentage) {
            clearInterval(id);
            i = 0;
          } else {
            width++;
            $('#myBar').css('width',width+'%');
          }
          (percentage === 100) ? $('#myBar').css('background-color','#267F4E'): $('#myBar').css('background-color','#7F7F7F');
        }
      }
    }
})