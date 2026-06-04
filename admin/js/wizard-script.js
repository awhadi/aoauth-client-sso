(function($) {
    var currentStep = 1;
    var selectedProvider = null;
    var applicationConfig = {};
	    var testPassed = false;
	    var isEditMode = false;
	    var editAppId = '';
	    var editAppData = null;
	    var discoveredMetadata = {};
    
    function escapeHtml(text) {
        return $('<div />').text(text).html();
    }

    function debugLog(level, message, context) {
        if (typeof aoauth_admin === 'undefined' || !aoauth_admin.debug_enabled) {
            return;
        }

        $.ajax({
            url: aoauth_admin.ajaxurl,
            type: 'POST',
            data: {
                action: 'aoauth_client_debug_log',
                nonce: aoauth_admin.nonce,
                source: 'wizard',
                level: level,
                message: message,
                context: JSON.stringify(context || {})
            }
        });
    }
    
    function addScopeTag(scope) {
        var tag = $('<span class="aoauth-tag">' + escapeHtml(scope) + '<span class="aoauth-tag-remove">&times;</span></span>');
        $('#aoauth-scopes-list').append(tag);
    }
    
    function getProviderData(providerName) {
        var providers = aoauth_admin.providers;
        for (var i = 0; i < providers.length; i++) {
            if (providers[i].name === providerName) {
                return providers[i];
            }
        }
        return null;
    }
    
    function populateProviderFields(providerData) {
        $('#aoauth-provider-name').val(providerData.name);
        $('#aoauth-app-name').val(providerData.label);
        $('#aoauth-auth-endpoint').val(providerData.authorization_endpoint);
        $('#aoauth-token-endpoint').val(providerData.token_endpoint);
        $('#aoauth-userinfo-endpoint').val(providerData.userinfo_endpoint);
        
        $('#aoauth-scopes-list').empty();
        if (providerData.default_scopes) {
            providerData.default_scopes.forEach(function(scope) {
                addScopeTag(scope);
            });
        }
    }
    
    function collectConfigData() {
        var scopes = [];
        $('#aoauth-scopes-list .aoauth-tag').each(function() {
            var text = $(this).text().replace('×', '').trim();
            scopes.push(text);
        });
        
        applicationConfig = {
            provider_name: selectedProvider || $('#aoauth-provider-name').val(),
            app_name: $('#aoauth-app-name').val(),
            client_id: $('#aoauth-client-id').val(),
            client_secret: $('#aoauth-client-secret').val(),
            authorization_endpoint: $('#aoauth-auth-endpoint').val(),
            token_endpoint: $('#aoauth-token-endpoint').val(),
	            userinfo_endpoint: $('#aoauth-userinfo-endpoint').val(),
	            issuer: discoveredMetadata.issuer || '',
	            jwks_uri: discoveredMetadata.jwks_uri || '',
	            end_session_endpoint: discoveredMetadata.end_session_endpoint || '',
	            scopes: scopes,
            redirect_uri: $('#aoauth-callback-url').val(),
            discovery_url: $('#aoauth-discovery-url').val(),
            send_credentials_in_header: ($('input[name="credentials_location"]:checked').val() === 'header') ? 1 : 0,
            enabled: 0
        };
        
        if ($('#aoauth-enable-advanced-mapping').is(':checked')) {
            applicationConfig.enable_advanced_mapping = 1;
            applicationConfig.attribute_mapping = {};
            
            $('input[name^="attribute_mapping"]').each(function() {
                var match = $(this).attr('name').match(/\[(.*?)\]/);
                if (match && match[1]) {
                    applicationConfig.attribute_mapping[match[1]] = $(this).val();
                }
            });
            
            applicationConfig.role_mapping = {};
            var rolePath = $('input[name="role_mapping[attribute_path]"]').val();
            var roleRules = $('textarea[name="role_mapping[rules]"]').val();
            if (rolePath) applicationConfig.role_mapping.attribute_path = rolePath;
            if (roleRules) applicationConfig.role_mapping.rules = roleRules;
        }
        
        debugLog('debug', 'Configuration collected', applicationConfig);
        return applicationConfig;
    }
    
    function validateStep2() {
        if (!$('#aoauth-app-name').val()) {
            aoauthShowToast('Please enter an application name', 'error');
            return false;
        }
        
        if (!$('#aoauth-client-id').val()) {
            aoauthShowToast('Please enter a Client ID', 'error');
            return false;
        }
        
        if (!$('#aoauth-client-secret').val()) {
            aoauthShowToast('Please enter a Client Secret', 'error');
            return false;
        }
        
        if (!$('#aoauth-auth-endpoint').val()) {
            aoauthShowToast('Please enter an Authorization Endpoint', 'error');
            return false;
        }
        
        if (!$('#aoauth-token-endpoint').val()) {
            aoauthShowToast('Please enter a Token Endpoint', 'error');
            return false;
        }
        
        return true;
    }
    
    function updateSummary() {
        var summaryHtml = '<div class="aoauth-summary">';
        
        var displayFields = {
            'app_name': 'Application Name',
            'provider_name': 'Provider',
            'client_id': 'Client ID',
            'authorization_endpoint': 'Authorization Endpoint',
            'token_endpoint': 'Token Endpoint',
            'userinfo_endpoint': 'UserInfo Endpoint'
        };
        
        for (var key in displayFields) {
            if (applicationConfig[key]) {
                var value = applicationConfig[key];
                if (key === 'client_secret') {
                    value = '********';
                }
                
                summaryHtml += '<div class="aoauth-summary-item">';
                summaryHtml += '<span class="aoauth-summary-label">' + displayFields[key] + '</span>';
                summaryHtml += '<span class="aoauth-summary-value">' + escapeHtml(value) + '</span>';
                summaryHtml += '</div>';
            }
        }
        
        if (applicationConfig.scopes && applicationConfig.scopes.length > 0) {
            summaryHtml += '<div class="aoauth-summary-item">';
            summaryHtml += '<span class="aoauth-summary-label">Scopes</span>';
            summaryHtml += '<span class="aoauth-summary-value">' + escapeHtml(applicationConfig.scopes.join(', ')) + '</span>';
            summaryHtml += '</div>';
        }
        
        summaryHtml += '</div>';
        $('#aoauth-config-summary').html(summaryHtml);
    }
    
    function saveApplication(callback, isDraft) {
        var configToSave = $.extend(true, {}, applicationConfig);
        
        if (isDraft) {
            configToSave.draft = 1;
        } else {
            configToSave.draft = 0;
        }
        
        debugLog('info', 'Saving application', configToSave);
        
        $.ajax({
            url: aoauth_admin.ajaxurl,
            type: 'POST',
            data: {
                action: 'aoauth_save_application',
                app_data: configToSave,
                nonce: aoauth_admin.nonce
            },
            success: function(response) {
                debugLog('debug', 'Save response', {
                    success: !!response.success,
                    message: response.data && response.data.message ? response.data.message : ''
                });
                if (response.success) {
                    if (callback) callback(response);
                } else {
                    aoauthShowToast(response.data.message || 'Error saving application', 'error');
                }
            },
            error: function(xhr, status, error) {
                debugLog('error', 'Save error', {
                    status: status,
                    error: error,
                    response_status: xhr.status
                });
                aoauthShowToast('Connection error while saving', 'error');
            }
        });
    }
    
    function testConnection() {
        collectConfigData();
        
        var $testBtn = $('#aoauth-test-connection-btn');
        var $testStatus = $('#aoauth-test-status');
        var $testResult = $('#aoauth-test-result');
        var $finishBtn = $('#aoauth-finish-btn');
        
        $testBtn.prop('disabled', true).html('<span class="spinner is-active aoauth-inline-spinner"></span> Testing...');
        $testStatus.show();
        $testResult.removeClass('success error').html('').hide();
        
        debugLog('info', 'Testing connection with config', applicationConfig);
        
        $.ajax({
            url: aoauth_admin.ajaxurl,
            type: 'POST',
            data: {
                action: 'aoauth_test_connection',
                app_data: applicationConfig,
                nonce: aoauth_admin.nonce
            },
            success: function(response) {
                debugLog('debug', 'Test response', {
                    success: !!response.success,
                    message: response.data && response.data.message ? response.data.message : ''
                });
                $testBtn.prop('disabled', false).html('<span class="dashicons dashicons-yes-alt"></span> Test Connection');
                
                if (response.success) {
                    testPassed = true;
                    $finishBtn.prop('disabled', false);
                    $testResult.addClass('success').html(`
                        <div class="test-success-icon">✓</div>
                        <div class="test-success-message">${escapeHtml(response.data.message)}</div>
                    `).show();
                } else {
                    testPassed = false;
                    $finishBtn.prop('disabled', false); // Still allow saving even if test fails
                    $testResult.addClass('error').html(`
                        <div class="test-error-icon">✗</div>
                        <div class="test-error-message">${escapeHtml(response.data.message)}</div>
                        <div class="test-error-help">You can still save this configuration, but please verify your settings.</div>
                    `).show();
                }
            },
            error: function(xhr, status, error) {
                debugLog('error', 'Test error', {
                    status: status,
                    error: error,
                    response_status: xhr.status
                });
                $testBtn.prop('disabled', false).html('<span class="dashicons dashicons-yes-alt"></span> Test Connection');
                testPassed = false;
                $finishBtn.prop('disabled', false); // Still allow saving even if test fails
                
                var errorMsg = 'Connection error';
                if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    errorMsg = xhr.responseJSON.data.message;
                } else if (xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        if (parsed.data && parsed.data.message) errorMsg = parsed.data.message;
                    } catch(e) {}
                }
                
                $testResult.addClass('error').html(`
                    <div class="test-error-icon">✗</div>
                    <div class="test-error-message">${escapeHtml(errorMsg)}</div>
                    <div class="test-error-help">You can still save this configuration, but please verify your settings.</div>
                `).show();
                aoauthShowToast('Connection error: ' + errorMsg, 'warning');
            }
        });
    }
    
    function finishAndSave() {
        // ALWAYS save regardless of test status
        collectConfigData();
        applicationConfig.enabled = 1;
    
     saveApplication(function(response) {
         aoauthShowToast('Provider configuration saved and enabled! Redirecting...', 'success');
         setTimeout(function() {
             // CHANGED: redirect to providers page instead of aoauth-settings
             window.location.href = aoauth_admin.ajaxurl.replace('admin-ajax.php', 'admin.php?page=aoauth-providers');
         }, 2000);
     }, false);
    }
    
    function goToStep(step) {
        $('.aoauth-wizard-step').removeClass('active');
        $('.aoauth-wizard-step[data-step="' + step + '"]').addClass('active');
        
        $('.aoauth-progress-step').removeClass('active completed');
        $('.aoauth-progress-step').each(function() {
            var stepNum = parseInt($(this).data('step'));
            if (stepNum < step) {
                $(this).addClass('completed');
            } else if (stepNum === step) {
                $(this).addClass('active');
            }
        });
        
        currentStep = step;
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        isEditMode = aoauth_admin.edit_mode || false;
        editAppId = aoauth_admin.edit_app_id || '';
        editAppData = aoauth_admin.edit_app_data || null;
        
	        if (isEditMode && editAppData) {
	            selectedProvider = editAppData.provider_name;
	            applicationConfig = editAppData;
	            discoveredMetadata = {
	                issuer: editAppData.issuer || '',
	                jwks_uri: editAppData.jwks_uri || '',
	                end_session_endpoint: editAppData.end_session_endpoint || ''
	            };
	            testPassed = true;
            
            var providerData = getProviderData(selectedProvider);
            if (providerData) {
                populateProviderFields(providerData);
            }
            
            $('#aoauth-provider-name').val(editAppData.provider_name);
            $('#aoauth-app-name').val(editAppData.app_name);
            $('#aoauth-client-id').val(editAppData.client_id);
            $('#aoauth-client-secret').val(editAppData.client_secret);
            $('#aoauth-auth-endpoint').val(editAppData.authorization_endpoint);
            $('#aoauth-token-endpoint').val(editAppData.token_endpoint);
            $('#aoauth-userinfo-endpoint').val(editAppData.userinfo_endpoint);
            $('#aoauth-discovery-url').val(editAppData.discovery_url || '');
            
            if (editAppData.scopes && editAppData.scopes.length > 0) {
                $('#aoauth-scopes-list').empty();
                editAppData.scopes.forEach(function(scope) {
                    addScopeTag(scope);
                });
            }
            
            if (editAppData.enable_advanced_mapping) {
                $('#aoauth-enable-advanced-mapping').prop('checked', true);
                $('#aoauth-advanced-mapping').show();
                
                if (editAppData.attribute_mapping) {
                    for (var key in editAppData.attribute_mapping) {
                        $('input[name="attribute_mapping[' + key + ']"]').val(editAppData.attribute_mapping[key]);
                    }
                }
            }
            
            if (editAppData.send_credentials_in_header) {
                $('input[name="credentials_location"][value="header"]').prop('checked', true);
            } else {
                $('input[name="credentials_location"][value="body"]').prop('checked', true);
            }
            
            goToStep(2);
            
            $('.aoauth-wizard-skip').text('Back to Providers');
            $('#aoauth-finish-btn').prop('disabled', false);
        }
        
        $('#aoauth-wizard-close, #aoauth-wizard-skip').on('click', function(e) {
    e.preventDefault();
    // CHANGED: redirect to providers page
    window.location.href = aoauth_admin.ajaxurl.replace('admin-ajax.php', 'admin.php?page=aoauth-providers');
});
        
        $('#aoauth-provider-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            $('.aoauth-provider-card').each(function() {
                var label = $(this).data('label').toLowerCase();
                if (label.indexOf(searchTerm) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        $(document).on('click', '.aoauth-provider-card', function() {
            selectedProvider = $(this).data('provider');
            var providerData = getProviderData(selectedProvider);
            
            if (providerData) {
                populateProviderFields(providerData);
                goToStep(2);
            }
        });
        
        $('.aoauth-next-step-btn').on('click', function() {
            if (validateStep2()) {
                collectConfigData();
                updateSummary();
                testPassed = false;
                $('#aoauth-finish-btn').prop('disabled', false); // Always enable save button
                $('#aoauth-test-status').hide();
                goToStep(3);
            }
        });
        
        $('.aoauth-back-step-btn').on('click', function() {
            goToStep(2);
        });
        
        $('.aoauth-save-draft-btn').on('click', function() {
    collectConfigData();
    saveApplication(function(response) {
        aoauthShowToast('Draft saved successfully!', 'success');
        // REMOVED the redirect - stay on the wizard page
        // Just clear the spinner/loading state if any
        $('.aoauth-save-draft-btn').prop('disabled', false);
    }, true);
});
        
        $('#aoauth-test-connection-btn').on('click', function() {
            testConnection();
        });
        
        $('#aoauth-finish-btn').on('click', function() {
            // ALWAYS save - no test required
            finishAndSave();
        });
        
        $('.aoauth-copy-btn').on('click', function() {
            var targetId = $(this).data('target');
            var input = document.getElementById(targetId);
            if (input) {
                input.select();
                document.execCommand('copy');
                
                var btn = $(this);
                var originalHtml = btn.html();
                btn.html('<span class="dashicons dashicons-yes"></span>');
                aoauthShowToast('Copied to clipboard!', 'success');
                setTimeout(function() {
                    btn.html(originalHtml);
                }, 2000);
            }
        });
        
        $('#aoauth-discover-btn').on('click', function() {
            var discoveryUrl = $('#aoauth-discovery-url').val();
            
            if (!discoveryUrl) {
                aoauthShowToast('Please enter a discovery URL first', 'error');
                return;
            }
            
            var btn = $(this);
            btn.prop('disabled', true).text('Discovering...');
            
            $.ajax({
                url: aoauth_admin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'aoauth_discover_endpoints',
                    discovery_url: discoveryUrl,
                    nonce: aoauth_admin.nonce
                },
                success: function(response) {
                    btn.prop('disabled', false).text('Auto Discover');
                    
	                    if (response.success && response.data && response.data.endpoints) {
	                        var endpoints = response.data.endpoints;
	                        discoveredMetadata = {
	                            issuer: endpoints.issuer || '',
	                            jwks_uri: endpoints.jwks_uri || '',
	                            end_session_endpoint: endpoints.end_session_endpoint || ''
	                        };
                        
                        if (endpoints.authorization_endpoint) {
                            $('#aoauth-auth-endpoint').val(endpoints.authorization_endpoint);
                        }
                        
                        if (endpoints.token_endpoint) {
                            $('#aoauth-token-endpoint').val(endpoints.token_endpoint);
                        }
                        
                        if (endpoints.userinfo_endpoint) {
                            $('#aoauth-userinfo-endpoint').val(endpoints.userinfo_endpoint);
                        }
                        
                        aoauthShowToast(
                            endpoints.userinfo_endpoint ? 
                            'Discovery successful! All endpoints configured.' : 
                            'Discovery successful! Basic endpoints configured.',
                            'success'
                        );
                    } else {
                        var errorMsg = (response.data && response.data.message) ? 
                            response.data.message : 'Failed to discover endpoints';
                        aoauthShowToast(errorMsg, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    btn.prop('disabled', false).text('Auto Discover');
                    aoauthShowToast('Connection error: ' + error, 'error');
                }
            });
        });
        
        $('#aoauth-enable-advanced-mapping').on('change', function() {
            if ($(this).is(':checked')) {
                $('#aoauth-advanced-mapping').slideDown();
            } else {
                $('#aoauth-advanced-mapping').slideUp();
            }
        });
        
        $('#aoauth-scopes-input').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var value = $(this).val().trim();
                
                if (value) {
                    addScopeTag(value);
                    $(this).val('');
                }
            }
        });
        
        $(document).on('click', '.aoauth-tag-remove', function() {
            $(this).closest('.aoauth-tag').remove();
        });
        
        $('.aoauth-reset-mapping-btn').on('click', function() {
            if (selectedProvider && aoauth_admin.providers) {
                var providerData = getProviderData(selectedProvider);
                if (providerData) {
                    $('.aoauth-mapping-field').each(function() {
                        var defaultValue = $(this).data('default');
                        if (defaultValue) {
                            $(this).val(defaultValue);
                        }
                    });
                    aoauthShowToast('Mapping reset to default values', 'info');
                }
            }
        });
    });
})(jQuery);
