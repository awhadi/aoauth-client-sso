(function($) {
    var turnstileWidgetIds = {};
    var activeRequests = {};
    var turnstileTimeouts = {};
    
    function handleSSOClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $btn = $(this);
        var loginUrl = $btn.attr('href');
        var buttonId = 'aoauth-btn-' + Math.random().toString(36).substr(2, 9);
        
        if ($btn.hasClass('aoauth-button-loading')) {
            return false;
        }
        
        if (typeof aoauth_public !== 'undefined' && 
            aoauth_public.bot_protection && 
            aoauth_public.bot_protection.type !== 'none') {
            
            var protectionType = aoauth_public.bot_protection.type;
            
            showBeautifulLoader($btn);
            showBotOverlay($btn);
            
            if (protectionType === 'turnstile') {
                if (typeof turnstile === 'undefined') {
                    console.error('Turnstile not loaded');
                    hideBeautifulLoader($btn);
                    alert('Bot protection not loaded. Please refresh the page.');
                    return false;
                }
                
                var containerId = 'turnstile-container-' + buttonId;
                
                if ($('#' + containerId).length === 0) {
                    $('<div>', {
                        id: containerId,
                        class: 'aoauth-bot-widget-container'
                    }).appendTo('body');
                }
                
                // CRITICAL FIX: Reset any existing widget first
                if (turnstileWidgetIds[buttonId]) {
                    try {
                        turnstile.reset(turnstileWidgetIds[buttonId]);
                    } catch(e) {}
                }
                
                // Clear any pending timeout
                if (turnstileTimeouts[buttonId]) {
                    clearTimeout(turnstileTimeouts[buttonId]);
                }
                
                try {
                    turnstileWidgetIds[buttonId] = turnstile.render('#' + containerId, {
                        sitekey: aoauth_public.bot_protection.site_key,
                        size: 'invisible',
                        execution: 'execute',
                        appearance: 'execute',
                        callback: function(token) {
                            console.log('Turnstile callback received');
                            clearTimeout(turnstileTimeouts[buttonId]);
                            verifyToken(token, 'turnstile', loginUrl, $btn, containerId, buttonId);
                        },
                        'error-callback': function(errorCode) {
                            console.error('Turnstile error:', errorCode);
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideBeautifulLoader($btn);
                            hideBotOverlay();
                            var errorMsg = getTurnstileErrorMessage(errorCode);
                            alert(errorMsg);
                            cleanupTurnstile(containerId, buttonId);
                        },
                        'expired-callback': function() {
                            console.log('Turnstile expired');
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideBeautifulLoader($btn);
                            hideBotOverlay();
                            alert('Verification expired. Please try again.');
                            cleanupTurnstile(containerId, buttonId);
                        },
                        'timeout-callback': function() {
                            console.log('Turnstile timeout');
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideBeautifulLoader($btn);
                            hideBotOverlay();
                            alert('Verification timed out. Please refresh the page and try again.');
                            cleanupTurnstile(containerId, buttonId);
                        }
                    });
                    
                    // CRITICAL FIX: Set a timeout to detect if Turnstile never responds
                    // This handles cases where the widget gets stuck
                    turnstileTimeouts[buttonId] = setTimeout(function() {
                        if (turnstileWidgetIds[buttonId] && !activeRequests[loginUrl]) {
                            console.log('Turnstile widget stuck - forcing reset');
                            try {
                                turnstile.reset(turnstileWidgetIds[buttonId]);
                            } catch(e) {}
                            hideBeautifulLoader($btn);
                            hideBotOverlay();
                            alert('Verification is taking too long. Please refresh the page and try again.');
                            cleanupTurnstile(containerId, buttonId);
                        }
                    }, 30000);
                    
                    requestAnimationFrame(function() {
                        turnstile.execute(turnstileWidgetIds[buttonId]);
                    });
                    
                } catch(err) {
                    console.error('Turnstile error:', err);
                    clearTimeout(turnstileTimeouts[buttonId]);
                    hideBeautifulLoader($btn);
                    hideBotOverlay();
                    alert('Bot verification error. Please try again.');
                    cleanupTurnstile(containerId, buttonId);
                }
                
            } else if (protectionType === 'recaptcha') {
                if (typeof grecaptcha === 'undefined') {
                    console.error('reCAPTCHA not loaded');
                    hideBeautifulLoader($btn);
                    hideBotOverlay();
                    alert('Bot protection not loaded. Please refresh the page.');
                    return false;
                }
                
                setTimeout(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute(aoauth_public.bot_protection.site_key, {action: 'login'}).then(function(token) {
                            verifyToken(token, 'recaptcha', loginUrl, $btn, null, null);
                        }).catch(function(err) {
                            console.error('reCAPTCHA error:', err);
                            hideBeautifulLoader($btn);
                            hideBotOverlay();
                            alert('Verification error. Please try again.');
                        });
                    });
                }, 100);
            }
            
            return false;
        }
        
        showBeautifulLoader($btn);
        showRedirectOverlay();
        setTimeout(function() {
            window.location.href = loginUrl;
        }, 100);
        return false;
    }
    
    function showBeautifulLoader($btn) {
        $btn.addClass('aoauth-button-loading');
        
        var $buttonText = $btn.find('.aoauth-button-text');
        var originalText = $buttonText.text();
        $btn.data('original-text', originalText);
        
        var spinnerHtml = '<span class="aoauth-loader">' +
            '<svg class="aoauth-loader-svg" width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">' +
                '<circle class="aoauth-loader-circle" cx="12" cy="12" r="10" fill="none" stroke-width="3"/>' +
                '<path class="aoauth-loader-path" d="M12,2 A10,10 0 0,1 22,12" fill="none" stroke-width="3"/>' +
            '</svg>' +
            '<span class="aoauth-loader-text">Authenticating...</span>' +
        '</span>';
        
        $buttonText.html(spinnerHtml);
    }
    
    function hideBeautifulLoader($btn) {
        $btn.removeClass('aoauth-button-loading');
        
        var originalText = $btn.data('original-text') || 'Sign in';
        $btn.find('.aoauth-button-text').html(originalText);
    }

    function shouldShowBotOverlay() {
        return typeof aoauth_public !== 'undefined' &&
            aoauth_public.bot_protection &&
            aoauth_public.bot_protection.overlay_enabled;
    }

    function showBotOverlay($btn) {
        if (!shouldShowBotOverlay()) {
            return;
        }

        showVerificationOverlay(aoauth_public.bot_protection.overlay_message || 'Verifying secure sign-in...', $btn);
    }

    function showRedirectOverlay() {
        if (typeof aoauth_public === 'undefined' || !aoauth_public.redirect_overlay_enabled) {
            return;
        }

        showVerificationOverlay(aoauth_public.redirect_overlay_message || 'Redirecting to secure sign-in...');
    }

    function showVerificationOverlay(message, $origin) {
        var overlayId = 'aoauth-verification-overlay';
        var $overlay = $('#' + overlayId);

        if (!$overlay.length) {
            $overlay = $('<div>', {
                id: overlayId,
                class: 'aoauth-verification-overlay',
                role: 'status',
                'aria-live': 'polite'
            }).append(
                $('<div>', { class: 'aoauth-verification-panel' }).append(
                    $('<div>', { class: 'aoauth-verification-ring' }),
                    $('<div>', { class: 'aoauth-verification-message' })
                )
            );
            $('body').append($overlay);
        }

        $overlay.find('.aoauth-verification-message').text(message);
        if ($origin && $origin.length) {
            var offset = $origin.offset();
            $overlay.css({
                '--aoauth-overlay-x': (offset.left + ($origin.outerWidth() / 2)) + 'px',
                '--aoauth-overlay-y': (offset.top + ($origin.outerHeight() / 2) - $(window).scrollTop()) + 'px'
            });
        } else {
            $overlay.css({
                '--aoauth-overlay-x': '50%',
                '--aoauth-overlay-y': '50%'
            });
        }
        $overlay.addClass('is-visible');
        $('body').addClass('aoauth-verification-active');
    }

    function hideBotOverlay() {
        $('#aoauth-verification-overlay').removeClass('is-visible');
        $('body').removeClass('aoauth-verification-active');
    }
    
    function getTurnstileErrorMessage(errorCode) {
        var messages = {
            'bad-request': 'Invalid request. Please try again.',
            'invalid-input-response': 'Invalid verification token. Please refresh and try again.',
            'invalid-input-secret': 'Configuration error. Please contact site administrator.',
            'invalid-input-sitekey': 'Site configuration error.',
            'timeout-or-duplicate': 'Verification timed out. Please refresh the page and try again.',
            'internal-error': 'Internal error. Please try again.'
        };
        return messages[errorCode] || 'Verification failed. Please try again.';
    }
    
    function verifyToken(token, type, loginUrl, $btn, containerId, buttonId) {
        var action = type === 'turnstile' ? 'aoauth_verify_turnstile' : 'aoauth_verify_recaptcha';
        
        if (activeRequests[loginUrl]) {
            return;
        }
        activeRequests[loginUrl] = true;
        
        $.ajax({
            url: aoauth_public.ajaxurl,
            type: 'POST',
            data: {
                action: action,
                token: token,
                nonce: aoauth_public.nonce
            },
            timeout: 30000,
            success: function(response) {
                delete activeRequests[loginUrl];
                if (response.success) {
                    if (response.data && response.data.verification) {
                        loginUrl += (loginUrl.indexOf('?') === -1 ? '?' : '&') + 'aoauth_bot_verification=' + encodeURIComponent(response.data.verification);
                    }
                    
                    setTimeout(function() {
                        window.location.href = loginUrl;
                    }, 200);
                } else {
                    hideBeautifulLoader($btn);
                    hideBotOverlay();
                    alert(response.data.message || 'Verification failed. Please try again.');
                    if (containerId) cleanupTurnstile(containerId, buttonId);
                }
            },
            error: function(xhr, status, error) {
                delete activeRequests[loginUrl];
                console.error('AJAX error:', error);
                hideBeautifulLoader($btn);
                hideBotOverlay();
                alert('Verification error. Please refresh and try again.');
                if (containerId) cleanupTurnstile(containerId, buttonId);
            }
        });
    }
    
    function cleanupTurnstile(containerId, buttonId) {
        if (turnstileTimeouts[buttonId]) {
            clearTimeout(turnstileTimeouts[buttonId]);
            delete turnstileTimeouts[buttonId];
        }
        
        if (buttonId && turnstileWidgetIds[buttonId] && typeof turnstile !== 'undefined') {
            try {
                turnstile.remove(turnstileWidgetIds[buttonId]);
            } catch(e) {
                console.log('Turnstile cleanup error:', e);
            }
            delete turnstileWidgetIds[buttonId];
        }
        
        if (containerId) {
            setTimeout(function() {
                $('#' + containerId).remove();
            }, 500);
        }
    }
    
    $(document).ready(function() {
        $(document).off('click.aoauth').on('click.aoauth', '.aoauth-button', handleSSOClick);
    });
})(jQuery);
