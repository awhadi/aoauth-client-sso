(function($) {
    var turnstileWidgetIds = {};
    var activeRequests = {};
    var turnstileTimeouts = {};
    var externalScriptLoads = {};

    function translate(key, fallback) {
        if (typeof aoauth_public !== 'undefined' && aoauth_public.translations && aoauth_public.translations[key]) {
            return aoauth_public.translations[key];
        }
        return fallback;
    }

    function escapeHtml(text) {
        return $('<div>').text(text || '').html();
    }

    function loadExternalScript(key, url) {
        if (!url) {
            return $.Deferred().reject().promise();
        }

        if (externalScriptLoads[key]) {
            return externalScriptLoads[key];
        }

        externalScriptLoads[key] = $.Deferred(function(deferred) {
            $('<script>', {
                src: url,
                async: true,
                defer: true
            })
                .on('load', function() {
                    deferred.resolve();
                })
                .on('error', function() {
                    deferred.reject();
                })
                .appendTo('head');
        }).promise();

        return externalScriptLoads[key];
    }

    function ensureBotProtectionScript(type) {
        var protection = aoauth_public && aoauth_public.bot_protection ? aoauth_public.bot_protection : {};

        if (type === 'turnstile' && typeof turnstile !== 'undefined') {
            return $.Deferred().resolve().promise();
        }

        if (type === 'recaptcha' && typeof grecaptcha !== 'undefined') {
            return $.Deferred().resolve().promise();
        }

        return loadExternalScript(type, protection.script_url || '');
    }

    function isBotProtectionApiReady(type) {
        if (type === 'turnstile') {
            return typeof turnstile !== 'undefined';
        }

        if (type === 'recaptcha') {
            return typeof grecaptcha !== 'undefined';
        }

        return true;
    }

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

    function createFlowId() {
        return 'flow-' + Date.now().toString(36) + '-' + Math.random().toString(36).substr(2, 10);
    }

    function getUrlParam(url, key) {
        try {
            return new URL(url, window.location.href).searchParams.get(key) || '';
        } catch (e) {
            return '';
        }
    }

    function addUrlParam(url, key, value) {
        try {
            var parsedUrl = new URL(url, window.location.href);
            parsedUrl.searchParams.set(key, value);
            return parsedUrl.toString();
        } catch (e) {
            var separator = url.indexOf('?') === -1 ? '?' : '&';
            return url + separator + encodeURIComponent(key) + '=' + encodeURIComponent(value);
        }
    }

    function debugLog(level, message, context) {
        if (typeof aoauth_public === 'undefined' || !aoauth_public.debug_enabled) {
            return;
        }

        $.ajax({
            url: aoauth_public.ajaxurl,
            type: 'POST',
            data: {
                action: 'aoauth_client_debug_log',
                nonce: aoauth_public.nonce,
                source: 'public',
                level: level,
                message: message,
                context: JSON.stringify(context || {})
            }
        });
    }

    function placeInsideLoginForm() {
        var $insideBlock = $('.aoauth-login-buttons.aoauth-position-inside-form').first();
        var $loginForm = $('#loginform');

        if (!$insideBlock.length || !$loginForm.length || $insideBlock.closest('#loginform').length) {
            return;
        }

        var $submitRow = $loginForm.find('.submit').last();
        if ($submitRow.length) {
            $insideBlock.insertAfter($submitRow);
        } else {
            $insideBlock.appendTo($loginForm);
        }
    }
    
    function handleSSOClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $btn = $(this);
        var loginUrl = $btn.attr('href');
        var buttonId = 'aoauth-btn-' + Math.random().toString(36).substr(2, 9);
        var flowId = createFlowId();
        var provider = getUrlParam(loginUrl, 'provider');
        
        if ($btn.hasClass('aoauth-button-loading')) {
            return false;
        }
        
        if (typeof aoauth_public !== 'undefined' && 
            aoauth_public.bot_protection && 
            aoauth_public.bot_protection.type !== 'none') {
            
            var protectionType = aoauth_public.bot_protection.type;
            debugLog('info', 'SSO button clicked with bot protection', {
                flow_id: flowId,
                provider: provider,
                bot_protection: protectionType
            });
            
            if (!isBotProtectionApiReady(protectionType)) {
                showBeautifulLoader($btn);
                ensureBotProtectionScript(protectionType)
                        .done(function() {
                            hideBeautifulLoader($btn);
                            $btn.trigger('click');
                        })
                        .fail(function() {
                            debugLog('error', 'Bot protection API not loaded', {
                                flow_id: flowId,
                                provider: provider,
                                bot_protection: protectionType
                            });
                            hideBeautifulLoader($btn);
                            alert(translate('bot_protection_not_loaded', 'Bot protection not loaded. Please refresh the page.'));
                        });
                return false;
            }

            showBeautifulLoader($btn);
            showBotOverlay($btn);

            if (protectionType === 'turnstile') {
                var displayMode = aoauth_public.bot_protection.display_mode || 'invisible';
                var containerId = 'turnstile-container-' + buttonId;

                createTurnstileContainer(containerId, displayMode);
                
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
                        size: displayMode === 'invisible' ? 'invisible' : 'normal',
                        appearance: displayMode === 'invisible' ? 'execute' : 'always',
                        callback: function(token) {
                            debugLog('debug', 'Turnstile callback received', {
                                flow_id: flowId,
                                provider: provider,
                                display_mode: displayMode
                            });
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideVerificationRetry();
                            verifyToken(token, 'turnstile', loginUrl, $btn, containerId, buttonId, flowId, provider);
                        },
                        'error-callback': function(errorCode) {
                            debugLog('error', 'Turnstile error callback', {
                                flow_id: flowId,
                                provider: provider,
                                error_code: errorCode
                            });
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideBeautifulLoader($btn);
                            showVerificationRetry($btn);
                            var errorMsg = getTurnstileErrorMessage(errorCode);
                            updateVerificationMessage(errorMsg);
                            cleanupTurnstile(containerId, buttonId);
                        },
                        'expired-callback': function() {
                            debugLog('warning', 'Turnstile expired', {
                                flow_id: flowId,
                                provider: provider
                            });
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideBeautifulLoader($btn);
                            updateVerificationMessage(translate('verification_expired', 'Verification expired. Please try again.'));
                            showVerificationRetry($btn);
                            cleanupTurnstile(containerId, buttonId);
                        },
                        'timeout-callback': function() {
                            debugLog('warning', 'Turnstile timeout', {
                                flow_id: flowId,
                                provider: provider
                            });
                            clearTimeout(turnstileTimeouts[buttonId]);
                            hideBeautifulLoader($btn);
                            updateVerificationMessage(translate('verification_timed_out', 'Verification timed out. Please try again.'));
                            showVerificationRetry($btn);
                            cleanupTurnstile(containerId, buttonId);
                        }
                    });

                    if (displayMode === 'invisible' && typeof turnstile.execute === 'function') {
                        turnstile.execute(turnstileWidgetIds[buttonId]);
                    }
                    
                    // CRITICAL FIX: Set a timeout to detect if Turnstile never responds
                    // This handles cases where the widget gets stuck
                    turnstileTimeouts[buttonId] = setTimeout(function() {
                        if (turnstileWidgetIds[buttonId] && !activeRequests[loginUrl]) {
                            debugLog('warning', 'Turnstile widget stuck - forcing reset', {
                                flow_id: flowId,
                                provider: provider
                            });
                            try {
                                turnstile.reset(turnstileWidgetIds[buttonId]);
                            } catch(e) {}
                            hideBeautifulLoader($btn);
                            updateVerificationMessage(translate('verification_too_long', 'Verification is taking too long. Please try again.'));
                            showVerificationRetry($btn);
                            cleanupTurnstile(containerId, buttonId);
                        }
                    }, 30000);
                    
                } catch(err) {
                    debugLog('error', 'Turnstile render error', {
                        flow_id: flowId,
                        provider: provider,
                        error: err && err.message ? err.message : String(err)
                    });
                    clearTimeout(turnstileTimeouts[buttonId]);
                    hideBeautifulLoader($btn);
                    updateVerificationMessage(translate('bot_verification_error', 'Bot verification error. Please try again.'));
                    showVerificationRetry($btn);
                    cleanupTurnstile(containerId, buttonId);
                }
                
            } else if (protectionType === 'recaptcha') {
                setTimeout(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute(aoauth_public.bot_protection.site_key, {action: 'login'}).then(function(token) {
                            verifyToken(token, 'recaptcha', loginUrl, $btn, null, null, flowId, provider);
                        }).catch(function(err) {
                            debugLog('error', 'reCAPTCHA error', {
                                flow_id: flowId,
                                provider: provider,
                                error: err && err.message ? err.message : String(err)
                            });
                            hideBeautifulLoader($btn);
                            hideBotOverlay();
                            alert(translate('verification_error', 'Verification error. Please try again.'));
                        });
                    });
                }, 100);
            }
            
            return false;
        }
        
        showBeautifulLoader($btn);
        showRedirectOverlay();
        debugLog('info', 'SSO button clicked without bot protection', {
            flow_id: flowId,
            provider: provider
        });
        setTimeout(function() {
            window.location.href = addUrlParam(loginUrl, 'aoauth_flow_id', flowId);
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
            '<span class="aoauth-loader-text">' + escapeHtml(translate('authenticating', 'Authenticating...')) + '</span>' +
        '</span>';
        
        $buttonText.html(spinnerHtml);
    }
    
    function hideBeautifulLoader($btn) {
        $btn.removeClass('aoauth-button-loading');
        
        var originalText = $btn.data('original-text') || translate('sign_in', 'Sign in');
        $btn.find('.aoauth-button-text').html(originalText);
    }

    function shouldShowBotOverlay() {
        return typeof aoauth_public !== 'undefined' &&
            aoauth_public.bot_protection &&
            (aoauth_public.bot_protection.overlay_enabled || aoauth_public.bot_protection.display_mode !== 'invisible');
    }

    function showBotOverlay($btn) {
        if (!shouldShowBotOverlay()) {
            return;
        }

        showVerificationOverlay(aoauth_public.bot_protection.overlay_message || translate('verifying_secure_sign_in', 'Verifying secure sign-in...'), $btn);
    }

    function showRedirectOverlay() {
        if (typeof aoauth_public === 'undefined' || !aoauth_public.redirect_overlay_enabled) {
            return;
        }

        showVerificationOverlay(aoauth_public.redirect_overlay_message || translate('redirecting_secure_sign_in', 'Redirecting to secure sign-in...'));
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
                    $('<div>', { class: 'aoauth-verification-message' }),
                    $('<div>', { class: 'aoauth-verification-widget-slot' }),
                    $('<button>', {
                        type: 'button',
                        class: 'aoauth-verification-retry',
                        text: translate('try_again', 'Try again')
                    })
                ),
                $('<div>', { class: 'aoauth-verification-branding', 'aria-hidden': 'true' }).append(
                    $('<div>', { class: 'aoauth-verification-provider-mark' }),
                    $('<div>', { class: 'aoauth-verification-powered' })
                ),
                $('<div>', { class: 'aoauth-verification-plane' })
            );
            $('body').append($overlay);
        }

        var overlayConfig = aoauth_public.bot_protection || {};
        var overlayVariant = overlayConfig.overlay_variant || 'spotlight';
        var overlayTheme = overlayConfig.overlay_theme || 'modern';
        var overlayOpacity = parseInt(overlayConfig.overlay_opacity, 10) || 86;

        $overlay.find('.aoauth-verification-message').text(message);
        hideVerificationRetry();
        updateVerificationBranding($overlay, overlayConfig);
        $overlay
            .removeClass('aoauth-overlay-spotlight aoauth-overlay-panel aoauth-overlay-minimal aoauth-overlay-paper-plane aoauth-overlay-glass-shield aoauth-overlay-aurora aoauth-overlay-hyperspace aoauth-overlay-constellation aoauth-overlay-signal-grid aoauth-overlay-theme-simple aoauth-overlay-theme-modern aoauth-overlay-theme-rounded aoauth-overlay-theme-gradient aoauth-overlay-theme-outline aoauth-overlay-theme-icon-only aoauth-overlay-theme-icon-aurora aoauth-overlay-theme-icon-sunset aoauth-overlay-theme-icon-neon')
            .addClass('aoauth-overlay-' + overlayVariant)
            .addClass('aoauth-overlay-theme-' + overlayTheme)
            .toggleClass('aoauth-overlay-branding-hidden', !overlayConfig.overlay_branding_enabled);
        $overlay[0].style.setProperty('--aoauth-overlay-opacity', overlayOpacity / 100);

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

    function createTurnstileContainer(containerId, displayMode) {
        if ($('#' + containerId).length) {
            return;
        }

        var $container = $('<div>', {
            id: containerId,
            class: displayMode === 'invisible' ? 'aoauth-bot-widget-container' : 'aoauth-bot-widget-container-visible'
        });

        if (displayMode === 'invisible') {
            $container.appendTo('body');
            return;
        }

        var $slot = $('#aoauth-verification-overlay .aoauth-verification-widget-slot');
        if ($slot.length) {
            $slot.empty().append($container);
        } else {
            $container.appendTo('body');
        }
    }

    function updateVerificationMessage(message) {
        $('#aoauth-verification-overlay .aoauth-verification-message').text(message);
    }

    function showVerificationRetry($btn) {
        var $overlay = $('#aoauth-verification-overlay');
        $overlay.find('.aoauth-verification-retry')
            .addClass('is-visible')
            .off('click')
            .on('click', function() {
                hideBotOverlay();
                if ($btn && $btn.length) {
                    $btn.trigger('click');
                }
            });
    }

    function hideVerificationRetry() {
        $('#aoauth-verification-overlay .aoauth-verification-retry').removeClass('is-visible').off('click');
    }

    function updateVerificationBranding($overlay, overlayConfig) {
        var providerLabels = {
            turnstile: translate('verified_turnstile', 'Verified by Cloudflare Turnstile'),
            recaptcha: translate('verified_recaptcha', 'Verified by Google reCAPTCHA')
        };
        var providerLabel = providerLabels[overlayConfig.type] || translate('bot_verification_active', 'Bot verification active');
        var pluginLogo = overlayConfig.plugin_logo_url || '';

        $overlay.find('.aoauth-verification-provider-mark').text(providerLabel);
        var $powered = $overlay.find('.aoauth-verification-powered').empty();
        if (pluginLogo) {
            $('<img>', {
                src: pluginLogo,
                alt: ''
            }).appendTo($powered);
        }
        $('<span>', {
            text: translate('protected_with', 'Protected with aOAUTH Client SSO')
        }).appendTo($powered);
    }

    function hideBotOverlay() {
        $('#aoauth-verification-overlay').removeClass('is-visible');
        $('body').removeClass('aoauth-verification-active');
        hideVerificationRetry();
    }
    
    function getTurnstileErrorMessage(errorCode) {
        var messages = {
            'bad-request': translate('invalid_request', 'Invalid request. Please try again.'),
            'invalid-input-response': translate('invalid_verification_token', 'Invalid verification token. Please refresh and try again.'),
            'invalid-input-secret': translate('configuration_error', 'Configuration error. Please contact site administrator.'),
            'invalid-input-sitekey': translate('site_configuration_error', 'Site configuration error.'),
            'timeout-or-duplicate': translate('verification_timeout_duplicate', 'Verification timed out. Please refresh the page and try again.'),
            'internal-error': translate('internal_error', 'Internal error. Please try again.')
        };
        return messages[errorCode] || translate('verification_failed', 'Verification failed. Please try again.');
    }
    
    function verifyToken(token, type, loginUrl, $btn, containerId, buttonId, flowId, provider) {
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
                flow_id: flowId,
                provider: provider,
                nonce: aoauth_public.nonce
            },
            timeout: 30000,
            success: function(response) {
                delete activeRequests[loginUrl];
                if (response.success) {
                    if (response.data && response.data.verification) {
                        loginUrl += (loginUrl.indexOf('?') === -1 ? '?' : '&') + 'aoauth_bot_verification=' + encodeURIComponent(response.data.verification);
                    }
                    loginUrl = addUrlParam(loginUrl, 'aoauth_flow_id', flowId);
                    debugLog('info', 'Bot verification accepted by site', {
                        flow_id: flowId,
                        provider: provider,
                        bot_protection: type
                    });
                    
                    setTimeout(function() {
                        window.location.href = loginUrl;
                    }, 200);
                } else {
                    hideBeautifulLoader($btn);
                    updateVerificationMessage(response.data.message || translate('verification_failed', 'Verification failed. Please try again.'));
                    showVerificationRetry($btn);
                    if (containerId) cleanupTurnstile(containerId, buttonId);
                }
            },
            error: function(xhr, status, error) {
                delete activeRequests[loginUrl];
                debugLog('error', 'Bot verification AJAX error', {
                    flow_id: flowId,
                    provider: provider,
                    bot_protection: type,
                    status: status,
                    error: error,
                    response_status: xhr.status
                });
                hideBeautifulLoader($btn);
                updateVerificationMessage(translate('verification_error', 'Verification error. Please try again.'));
                showVerificationRetry($btn);
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
                debugLog('warning', 'Turnstile cleanup error', {
                    error: e && e.message ? e.message : String(e)
                });
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
        placeInsideLoginForm();
        if (typeof aoauth_public !== 'undefined' && aoauth_public.bot_protection && aoauth_public.bot_protection.type !== 'none') {
            ensureBotProtectionScript(aoauth_public.bot_protection.type);
        }
        $(document).off('click.aoauth').on('click.aoauth', '.aoauth-button', handleSSOClick);
    });
})(jQuery);
