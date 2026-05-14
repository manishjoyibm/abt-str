require(['jquery'], function ($) {
    'use strict';

    /**
     * Sends CSP violation data using jQuery AJAX.
     * @param {Object} data - The CSP violation details.
     */
    async function sendCspViolation(data)
    {
        try {
            await $.ajax({
                url: '/abbottcsp/report/storefront',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function(response) {
                    if(response.success) {
                        console.log('[CSP Logger] Violation logged successfully: ', response);
                    } else {
                        console.log('[CSP Logger] Failed to log violation: ', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.log('[CSP Logger] Failed to log violation: ', error);
                }
            });
        } catch (error) {
            console.log('[CSP Logger] Failed to log violation:', error);
        }
    }

    /**
     * Handles the securitypolicyviolation event.
     * @param {SecurityPolicyViolationEvent} event
     */
    function handleCspViolation(event)
    {
        const violation = {
            violatedDirective: event.violatedDirective,
            blockedUri: event.blockedURI,
            documentUri: event.documentURI,
            sourceFile: event.sourceFile,
            lineNumber: event.lineNumber,
            columnNumber: event.columnNumber
        };

        console.warn('[CSP Logger] Violation detected:', violation);
        sendCspViolation(violation);
    }

    // Register the event listener
    if (typeof window !== 'undefined' && window.addEventListener) {
        window.addEventListener('securitypolicyviolation', handleCspViolation);
    }
});
