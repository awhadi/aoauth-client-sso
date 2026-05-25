(function($) {
    function escapeHtml(text) {
        return $('<div />').text(text || '').html();
    }

    window.aoauthShowToast = function(message, type) {
        var toastType = type || 'info';
        var container = $('.aoauth-toast-container');

        if (container.length === 0) {
            container = $('<div class="aoauth-toast-container" aria-live="polite" aria-atomic="false"></div>');
            $('body').append(container);
        }

        var icons = {
            success: '✓',
            error: '!',
            info: 'i',
            warning: '!'
        };

        var toast = $(
            '<div class="aoauth-toast aoauth-toast-' + escapeHtml(toastType) + '" role="status" tabindex="0">' +
                '<span class="aoauth-toast-icon" aria-hidden="true">' + (icons[toastType] || icons.info) + '</span>' +
                '<span class="aoauth-toast-message">' + escapeHtml(message) + '</span>' +
            '</div>'
        );

        container.append(toast);

        var dismissToast = function() {
            toast.addClass('aoauth-toast-exiting');
            setTimeout(function() {
                toast.remove();
                if (container.children().length === 0) {
                    container.remove();
                }
            }, 300);
        };

        toast.on('click keydown', function(event) {
            if (event.type === 'click' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                dismissToast();
            }
        });

        setTimeout(dismissToast, 5000);
    };
})(jQuery);
