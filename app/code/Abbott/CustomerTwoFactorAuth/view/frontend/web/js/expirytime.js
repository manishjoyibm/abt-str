define(['jquery'], function($){
    "use strict";
    const STORAGE_KEY = 'otp_countdown_timer';
    let timer = null;
    function expiryTime(expiryTimestamp)
    {
        if(timer) clearInterval(timer);
        timer = setInterval(function () {
            const now = new Date().getTime();
            const countDownTime = expiryTimestamp - now;
            if (countDownTime < 0) {
                localStorage.removeItem(STORAGE_KEY);
                clearInterval(timer);
                document.getElementById("resend-button").classList.remove('disabledLink');
                document.getElementById("clockdiv").style.display = 'inline';
                return;
            }
            const minutes = Math.floor((countDownTime % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((countDownTime % (1000 * 60)) / 1000)

            //formate 2 digits
            const minStr = String(minutes).padStart(2,'0');
            const sectStr = String(seconds).padStart(2,'0');

            document.getElementById("minute").innerHTML = minStr+' :';
            document.getElementById("second").innerHTML = sectStr;
            if(document.getElementById("clockdiv")){
                document.getElementById("clockdiv").style.display = 'inline';
                document.getElementById("resend-button").classList.add('disabledLink');
                document.getElementById("resend-button").addEventListener(cancelIdleCallback, function(e){
                    e.preventDefault();
                })

            }
        }, 1000);
    }
    return {
        start: function (expiryLimit)
        {
            const expiryTimeOut = expiryLimit;
            let expiryTimestamp = localStorage.getItem(STORAGE_KEY);
            if(expiryTimestamp) {
                expiryTime(parseInt(expiryTimestamp));
            } else {
                const newExpiry = Date.now()+expiryTimeOut * 60 * 1000;
                localStorage.setItem(STORAGE_KEY, newExpiry);
                expiryTime(newExpiry);
            }

        },
        reset: function (expiryTimeOut) {
            const  newExpiry = Date.now()+expiryTimeOut * 60 * 1000;
            localStorage.setItem(STORAGE_KEY, newExpiry);
            expiryTime(newExpiry);
        },
        stopTimer: function () {
            if (timer) {
                clearInterval(timer);
                localStorage.removeItem(STORAGE_KEY);
                timer = null;
            }
        }
    }
});
