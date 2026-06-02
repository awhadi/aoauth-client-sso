(function($) {
    $(document).on('error', 'img[data-fallback-src], img[data-hide-on-error]', function() {
        var $image = $(this);
        var fallbackSrc = $image.data('fallback-src');

        if (fallbackSrc && $image.attr('src') !== fallbackSrc) {
            $image.attr('src', fallbackSrc);
            return;
        }

        if ($image.data('hide-on-error')) {
            $image.addClass('aoauth-is-hidden');
        }
    });

    function showMessage(message, isError, $scope) {
        $scope = $scope && $scope.length ? $scope : $('.aoauth-frontend-unlink').first();
        var $message = $scope.find('.aoauth-frontend-unlink-message').first();
        if (!$message.length) {
            $message = $('<div>', {
                class: 'aoauth-frontend-unlink-message',
                role: 'status'
            }).prependTo($scope);
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
                showMessage(response.data.message || translations.unlink_success || 'SSO account disconnected.', false, $button.closest('.aoauth-frontend-unlink'));
                $button.closest('.aoauth-frontend-connected').fadeOut(160);
                return;
            }

            showMessage((response && response.data && response.data.message) || translations.unlink_error || 'Could not disconnect SSO account.', true, $button.closest('.aoauth-frontend-unlink'));
            $button.prop('disabled', false);
        }).fail(function() {
            showMessage(translations.unlink_error || 'Could not disconnect SSO account.', true, $button.closest('.aoauth-frontend-unlink'));
            $button.prop('disabled', false);
        });
    });

    $(document).on('click', '.aoauth-clear-bot-verification-btn', function(e) {
        e.preventDefault();

        var $button = $(this);
        var settings = window.aoauth_account_unlink || {};
        var translations = settings.translations || {};
        var originalText = $button.text();

        $button.prop('disabled', true).text(translations.working || 'Working...');
        $.post(settings.ajaxurl, {
            action: 'aoauth_clear_current_bot_verifications',
            nonce: $button.data('nonce')
        }, function(response) {
            if (response && response.success) {
                showMessage(response.data.message || translations.clear_bot_success || 'Bot verification data cleared.', false, $button.closest('.aoauth-frontend-tool'));
            } else {
                showMessage((response && response.data && response.data.message) || translations.clear_bot_error || 'Could not clear bot verification data.', true, $button.closest('.aoauth-frontend-tool'));
            }
        }).fail(function() {
            showMessage(translations.clear_bot_error || 'Could not clear bot verification data.', true, $button.closest('.aoauth-frontend-tool'));
        }).always(function() {
            $button.prop('disabled', false).text(originalText);
        });
    });
})(jQuery);
