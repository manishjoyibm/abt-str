 require(['jquery'], function($){
    'use-strict';
            return function init(config){
            const message = localStorage.getItem('2fa-message');
            if(message) {
                const messageHtml = `<div aria-atomic="true" role="alert" class="message-success success message">
                        <div data-ui-id="custom-ajax-message">
                            ${message}
                        </div>
                    </div>`;
                $('.page.messages').prepend(messageHtml);
                localStorage.removeItem('2fa-message');
            }
        }
    });