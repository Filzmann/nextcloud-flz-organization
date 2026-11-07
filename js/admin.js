/**
 * Organization Services Admin JavaScript
 */

(function(window, $, undefined) {
    'use strict';

    $(document).ready(function() {
        // Initialize form enhancements
        initFormHandlers();
    });

    function initFormHandlers() {
        // Add CSRF token to all forms if not present
        $('form').each(function() {
            if ($(this).find('input[name="requesttoken"]').length === 0) {
                var token = OC.requestToken;
                if (token) {
                    $(this).append('<input type="hidden" name="requesttoken" value="' + token + '" />');
                }
            }
        });

        // Add form validation feedback
        $('form').on('submit', function(e) {
            // Remove previous error classes
            $(this).find('.form-group').removeClass('has-error');
            $(this).find('.error-message').remove();
        });

        // Add keyboard navigation for better accessibility
        $('input, select, textarea').on('keydown', function(e) {
            if (e.key === 'Enter' && $(this).attr('type') !== 'textarea') {
                // Find next input or button
                var next = $(this).nextAll('input, select, textarea, button').first();
                if (next.length) {
                    next.focus();
                    if (next.is('button')) {
                        next.click();
                    }
                }
            }
        });
    }

    // Expose API for other scripts
    window.FlzOrganizationAdmin = {
        init: initFormHandlers
    };

})(window, jQuery);
