(function($) {
    $(document).ready(function() {
        function toggleSection($section, show) {
            if (show) {
                $section.stop(true, true).slideDown();
            } else {
                $section.stop(true, true).slideUp();
            }
        }

        function handleBotProtectionFields() {
            var botEnabled = $('#enable_bot_protection').is(':checked');
            var provider = $('#bot_protection_provider').val() || 'turnstile';

            toggleSection($('.aoauth-bot-protection-fields'), botEnabled);
            toggleSection($('.turnstile-fields'), botEnabled && provider === 'turnstile');
            toggleSection($('.recaptcha-fields'), botEnabled && provider === 'recaptcha');
        }

        $('#enable_bot_protection, #bot_protection_provider').on('change', function() {
            handleBotProtectionFields();
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
