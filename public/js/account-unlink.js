(function($) {
    function showMessage(message, isError) {
        var $message = $('.aoauth-frontend-unlink-message');
        if (!$message.length) {
            $message = $('<div>', {
                class: 'aoauth-frontend-unlink-message',
                role: 'status'
            }).prependTo('.aoauth-frontend-unlink');
        }

        $message
            .toggleClass('is-error', !!isError)
            .text(message);
    }

    $(document).on('click', '.aoauth-frontend-unlink-btn', function(e) {
        e.preventDefault();

        var $button = $(this);
        var settings = window.aoauth_account_unlink || {};
        var translations = settings.translations || {};

        if (!window.confirm(translations.confirm_unlink || 'Disconnect this SSO account?')) {
            return;
        }

        $button.prop('disabled', true);

        $.post(settings.ajaxurl, {
            action: 'aoauth_unlink_account',
            user_id: $button.data('user-id'),
            provider: $button.data('provider'),
            nonce: $button.data('nonce')
        }, function(response) {
            if (response && response.success) {
                showMessage(response.data.message || translations.unlink_success || 'SSO account disconnected.', false);
                $button.closest('.aoauth-frontend-connected').fadeOut(160);
                return;
            }

            showMessage((response && response.data && response.data.message) || translations.unlink_error || 'Could not disconnect SSO account.', true);
            $button.prop('disabled', false);
        }).fail(function() {
            showMessage(translations.unlink_error || 'Could not disconnect SSO account.', true);
            $button.prop('disabled', false);
        });
    });
})(jQuery);
