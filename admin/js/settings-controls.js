(function($) {
    $(document).ready(function() {
        function toggleSection($section, show) {
            if (show) {
                $section.stop(true, true).slideDown();
            } else {
                $section.stop(true, true).slideUp();
            }
        }

        function handleBotProtectionFields(triggerId) {
            var turnstileEnabled = $('#enable_turnstile').is(':checked');
            var recaptchaEnabled = $('#enable_recaptcha').is(':checked');

            if (turnstileEnabled && recaptchaEnabled) {
                if (triggerId === 'enable_turnstile') {
                    $('#enable_recaptcha').prop('checked', false);
                    recaptchaEnabled = false;
                } else {
                    $('#enable_turnstile').prop('checked', false);
                    turnstileEnabled = false;
                }
            }

            toggleSection($('.turnstile-fields'), turnstileEnabled);
            toggleSection($('.recaptcha-fields'), recaptchaEnabled);
        }

        $('#enable_turnstile, #enable_recaptcha').on('change', function() {
            handleBotProtectionFields(this.id);
        });

        handleBotProtectionFields();

        $('#allow_account_linking').on('change', function() {
            toggleSection($('#account-linking-security-row'), $(this).is(':checked'));
        });

        $('#auto_create_users').on('change', function() {
            toggleSection($('#default-role-row'), $(this).is(':checked'));
        });
    });
})(jQuery);
