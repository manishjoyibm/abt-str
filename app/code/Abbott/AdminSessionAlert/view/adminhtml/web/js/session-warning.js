define(['jquery', 'Magento_Ui/js/modal/confirm','mage/url'], function ($, confirm, urlBuilder) {
    'use strict';

    return function (config, element) {
        var enabled = config.enabled;
        var sessionTimeout = config.sessionTimeout;
        var warningBefore = config.warningTimeout;
        var keepAliveUrl = config.pingUrl;

        //Exit if feature disabled.
        if(!enabled){
            return;
        }
        //Exit if session timeout is 0
        if(sessionTimeout === 0){
            return;
        }

        var logoutUrl = document.querySelector('.account-signout')?.getAttribute('href');
        if(!logoutUrl){
            var logoutUrl = document.querySelector('.tfa-logout-link')?.getAttribute('href');
        }
        var pageLoadTime = Math.floor(Date.now() / 1000);
        var modalShown = false;
        var countdownTimer = null;
        var currentModal = null;

        // Load session storage or reset it.
        if(sessionStorage.getItem('lastResetTime'))
        {
            pageLoadTime = parseInt(sessionStorage.getItem('lastResetTime'));
            resetTimer();

        }else {
            resetTimer();
        }

        // Listning from other tab changes/reload
        window.addEventListener('storage', function (event){
            if(event.key === 'session-keepalive-last-refresh'){
                var modalData = document.querySelector('.modal-popup.confirm');
                if(modalData){
                    modalData.remove();
                    document.body.classList.remove('_has-modal');
                    document.body.classList.remove('_show');
                    document.querySelector('.modals-overlay').remove();
                    modalData = null;
                }

                if(countdownTimer){
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                }
                modalShown = false;
                pageLoadTime = Math.floor(Date.now() / 1000);
                sessionStorage.setItem('lastResetTime', pageLoadTime);
            }

            //force logout to sync all tabs
             if(event.key === 'forceLogout'){
                window.location.href = logoutUrl;
             }
        });

        function formatTime(seconds) {
            var min = Math.floor(seconds / 60);
            var sec = seconds % 60;
            return min + ':'+(sec < 10 ? '0' + sec : sec);
        }

        function resetTimer() {
            pageLoadTime = Math.floor(Date.now() / 1000);
            sessionStorage.setItem('lastResetTime', pageLoadTime);
            modalShown = false;
            if(countdownTimer){
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
            localStorage.setItem('session-keepalive-last-refresh',Date.now());
        }

        $(document).ajaxComplete(function (){
            resetTimer();
        });

        window.addEventListener('pageshow', function (){
            resetTimer();
        });

        setInterval(()=>{
                    var now = Math.floor(Date.now() / 1000);
                    var elapsedTime = now - pageLoadTime + 5;
                    var triggerTime = (sessionTimeout - warningBefore);
                    if (elapsedTime >= triggerTime && !modalShown) {
                        modalShown = true;
                        var countdown = warningBefore;
                        var Instance = confirm({
                            title: 'Session Expiring',
                            content: '<div class="custom-session-alert"> Your session will expire in <strong id="session-timer">' + formatTime(countdown) + '</strong><br>Do you want to extend the session?</div>',
                            buttons: [{
                                text: 'Continue Session',
                                class: 'action-primary custom-button',
                                click: function (event) {
                                    var self = this;
                                    clearInterval(countdownTimer);
                                    $.ajax({
                                        url: keepAliveUrl,
                                        type: 'POST',
                                        data: {
                                            form_key: window.FORM_KEY
                                        },
                                        showLoader: false,
                                        success: function () {
                                            resetTimer();
                                            console.log('Session extended successfully..!');
                                            modalShown = false;
                                            self.closeModal();
                                        },
                                        error: function () {
                                            modalShown = false;
                                            self.closeModal();
                                        }
                                    });
                                }
                            },
                                {
                                    text: 'Logout',
                                    class: 'action-primary custom-button',
                                    click: function () {
                                        clearInterval(countdownTimer);
                                        modalShown = false;
                                        localStorage.setItem('forceLogout',Date.now())
                                        sessionStorage.removeItem('lastResetTime');
                                        window.location.href = logoutUrl;
                                    }
                                }],
                            opened: function () {
                                currentModal = this.modal;
                            }
                        });
                        var countdownEndTime = Date.now() + (warningBefore * 1000);
                        countdownTimer = setInterval(() => {
                            var remaining = Math.floor((countdownEndTime - Date.now()) / 1000);
                            if (remaining <= 0) {
                                clearInterval(countdownTimer);
                                localStorage.setItem('forceLogout',Date.now());
                                sessionStorage.removeItem('lastResetTime');
                                window.location.href = logoutUrl;
                            } else {
                                var formatted = formatTime(remaining);
                                var timerEI = document.getElementById('session-timer');
                                if (timerEI) {
                                    timerEI.textContent = formatted;
                                }
                            }
                        }, 1000);
                    }
                }, 1000);
    };
});