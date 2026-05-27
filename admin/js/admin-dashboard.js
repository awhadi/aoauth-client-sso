(function($) {

        // ============================================
    // ACCOUNT UNLINKING FUNCTIONALITY
    // ============================================
    
    /**
     * Show confirmation modal for unlinking
     */
    function aoauthShowUnlinkModal(options) {
        var modalHtml = `
            <div id="aoauth-unlink-modal" class="aoauth-modal aoauth-is-hidden">
                <div class="aoauth-modal-content">
                    <span class="aoauth-modal-close">&times;</span>
                    <h3>${escapeHtml(options.title)}</h3>
                    <p>${escapeHtml(options.message)}</p>
                    <div class="aoauth-unlink-warning-modal">
                        <p>⚠️ ${escapeHtml(options.warning || '')}</p>
                    </div>
                    <div class="aoauth-modal-buttons">
                        <button id="aoauth-confirm-unlink" class="aoauth-admin-button aoauth-admin-button-danger">${escapeHtml(options.confirmText || 'Yes, Disconnect')}</button>
                        <button id="aoauth-cancel-unlink" class="aoauth-admin-button aoauth-admin-button-secondary">${escapeHtml(options.cancelText || 'Cancel')}</button>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#aoauth-unlink-modal').remove();
        $('body').append(modalHtml);
        
        var $modal = $('#aoauth-unlink-modal');
        $modal.removeClass('aoauth-is-hidden');
        
        // Close handlers
        $modal.find('.aoauth-modal-close, #aoauth-cancel-unlink').on('click', function() {
            $modal.remove();
        });
        
        // Confirm handler
        $('#aoauth-confirm-unlink').on('click', function() {
            $modal.remove();
            options.onConfirm();
        });
        
        // Close on outside click
        $modal.on('click', function(e) {
            if ($(e.target).is($modal)) {
                $modal.remove();
            }
        });
    }
    
    /**
     * Unlink a single user account
     */
    function aoauthPerformUnlink(userId, provider, nonce, $button) {
        var originalHtml = $button.html();
        $button.prop('disabled', true).html('<span class="spinner is-active aoauth-inline-spinner"></span> Processing...');
        
        $.post(aoauth_admin.ajaxurl, {
            action: 'aoauth_unlink_account',
            user_id: userId,
            provider: provider,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                aoauthShowToast(response.data.message, 'success');
                // Update UI
                if ($button.hasClass('aoauth-unlink-profile-btn')) {
                    // On profile page - replace with "no connection" message
                    var $section = $button.closest('.aoauth-connected-info');
                    $section.html(`
                        <div class="aoauth-no-connection">
                            <p>
                                <span class="dashicons dashicons-admin-network"></span>
                                ${aoauth_admin.translations.no_provider}
                            </p>
                            <p class="description">
                                ${escapeHtml('To link an SSO provider, sign in using the provider on the login page.')}
                            </p>
                        </div>
                    `);
                } else {
                    // On users table - update the column
                    var $row = $button.closest('tr');
                    $row.find('.column-aoauth_sso').html('<span class="aoauth-no-provider">—</span>');
                    $row.find('.column-aoauth_actions').html('<span class="aoauth-no-action">—</span>');
                }
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                aoauthShowToast(response.data.message, 'error');
                $button.prop('disabled', false).html(originalHtml);
            }
        }).fail(function() {
            aoauthShowToast(aoauth_admin.translations.unlink_error, 'error');
            $button.prop('disabled', false).html(originalHtml);
        });
    }
    
    // Profile page unlink button handler
    $(document).on('click', '.aoauth-unlink-profile-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var userId = $btn.data('user-id');
        var provider = $btn.data('provider');
        var nonce = $btn.data('nonce');
        
        aoauthShowUnlinkModal({
            title: aoauth_admin.translations.confirm_unlink,
            message: 'Are you sure you want to disconnect your SSO account?',
            warning: 'After disconnecting, you will no longer be able to log in using this SSO provider.',
            confirmText: 'Yes, Disconnect',
            cancelText: 'Cancel',
            onConfirm: function() {
                aoauthPerformUnlink(userId, provider, nonce, $btn);
            }
        });
    });
    
    // Users table unlink button handler
    $(document).on('click', '.aoauth-unlink-user-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        
        if ($btn.prop('disabled')) {
            aoauthShowToast($btn.attr('title') || 'Action not allowed', 'error');
            return;
        }
        
        var userId = $btn.data('user-id');
        var provider = $btn.data('provider');
        var nonce = $btn.data('nonce');
        var userName = $btn.closest('tr').find('.username').text() || 'this user';
        
        aoauthShowUnlinkModal({
            title: aoauth_admin.translations.confirm_unlink,
            message: 'Are you sure you want to disconnect SSO for ' + userName + '?',
            warning: 'This user will no longer be able to log in using their SSO provider.',
            confirmText: 'Yes, Disconnect',
            cancelText: 'Cancel',
            onConfirm: function() {
                aoauthPerformUnlink(userId, provider, nonce, $btn);
            }
        });
    });
    
    // Bulk unlink handling (via checkboxes)
    $(document).on('click', '#doaction, #doaction2', function(e) {
        var action = $('#bulk-action-selector-top').val() || $('#bulk-action-selector-bottom').val();
        
        if (action !== 'aoauth_bulk_unlink') {
            return;
        }
        
        e.preventDefault();
        
        var selectedUsers = [];
        $('input[name="users[]"]:checked').each(function() {
            selectedUsers.push($(this).val());
        });
        
        if (selectedUsers.length === 0) {
            aoauthShowToast('Please select users to disconnect.', 'error');
            return false;
        }
        
        aoauthShowUnlinkModal({
            title: 'Disconnect SSO Accounts',
            message: 'Are you sure you want to disconnect SSO for ' + selectedUsers.length + ' selected user(s)?',
            warning: 'These users will no longer be able to log in using their SSO providers.',
            confirmText: 'Yes, Disconnect',
            cancelText: 'Cancel',
            onConfirm: function() {
                // Show loading state
                var $submitBtn = $(this);
                $submitBtn.prop('disabled', true).html('<span class="spinner is-active"></span> Processing...');
                
                $.post(aoauth_admin.ajaxurl, {
                    action: 'aoauth_bulk_unlink_accounts',
                    user_ids: selectedUsers,
                    nonce: aoauth_admin.nonce
                }, function(response) {
                    if (response.success) {
                        aoauthShowToast(response.data.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        aoauthShowToast(response.data.message, 'error');
                        $submitBtn.prop('disabled', false).html('Apply');
                    }
                }).fail(function() {
                    aoauthShowToast('Error processing bulk unlink request.', 'error');
                    $submitBtn.prop('disabled', false).html('Apply');
                });
            }
        });
        
        return false;
    });
    
    // Handle bulk action notice display after redirect
    if (window.location.href.indexOf('aoauth_bulk_unlinked') !== -1) {
        var urlParams = new URLSearchParams(window.location.search);
        var unlinked = urlParams.get('aoauth_bulk_unlinked');
        var failed = urlParams.get('aoauth_bulk_failed');
        
        if (unlinked && parseInt(unlinked) > 0) {
            aoauthShowToast('Successfully disconnected ' + unlinked + ' account(s).', 'success');
        }
        if (failed && parseInt(failed) > 0) {
            aoauthShowToast('Failed to disconnect ' + failed + ' account(s).', 'error');
        }
        
        // Clean URL
        var cleanUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, cleanUrl);
    }
    function escapeHtml(text) {
        return $('<div />').text(text).html();
    }
    
    $(document).ready(function() {
        // ============================================
        // THEME CAROUSEL NAVIGATION
        // ============================================
        function initThemeCarousel() {
            var container = $('.theme-carousel-container');
            var prevBtn = $('.theme-prev-btn');
            var nextBtn = $('.theme-next-btn');
            
            if (container.length === 0) return;
            
            function updateButtons() {
                if (prevBtn.length && nextBtn.length) {
                    var scrollLeft = container.scrollLeft();
                    var maxScroll = container[0].scrollWidth - container.innerWidth();
                    prevBtn.prop('disabled', scrollLeft <= 0);
                    nextBtn.prop('disabled', scrollLeft >= maxScroll - 1);
                }
            }
            
            prevBtn.on('click', function() {
                container.animate({ scrollLeft: '-=200' }, 300, updateButtons);
            });
            
            nextBtn.on('click', function() {
                container.animate({ scrollLeft: '+=200' }, 300, updateButtons);
            });
            
            container.on('scroll', updateButtons);
            setTimeout(updateButtons, 100);
            
            // Theme card click handler
            $('.aoauth-theme-card').on('click', function(e) {
                if (e.target.type === 'radio') return;
                
                var radio = $(this).find('input[type="radio"]');
                if (radio.length) {
                    radio.prop('checked', true);
                    radio.trigger('change');
                }
                
                $('.aoauth-theme-card').removeClass('active');
                $(this).addClass('active');
            });
        }
        initThemeCarousel();
        
        // ============================================
        // SAVE SETTINGS FORM
        // ============================================
        $('.aoauth-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            formData += '&action=aoauth_save_settings&nonce=' + aoauth_admin.nonce;
            
            $.post(aoauth_admin.ajaxurl, formData, function(response) {
                if (response.success) {
                    aoauthShowToast(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    aoauthShowToast(response.data.message, 'error');
                }
            }).fail(function() {
                aoauthShowToast('Error saving settings', 'error');
            });
        });
        
        // ============================================
        // TOGGLE PROVIDER
        // ============================================
        $(document).on('change', '.aoauth-toggle-provider', function() {
            var appId = $(this).data('app-id');
            var enabled = $(this).is(':checked') ? 1 : 0;
            var $toggle = $(this);
            
            $.post(aoauth_admin.ajaxurl, {
                action: 'aoauth_toggle_provider',
                app_id: appId,
                enabled: enabled,
                nonce: aoauth_admin.nonce
            }, function(response) {
                if (response.success) {
                    aoauthShowToast(response.data.message, 'success');
                } else {
                    aoauthShowToast(response.data.message, 'error');
                    $toggle.prop('checked', !enabled);
                }
            }).fail(function() {
                aoauthShowToast('Error toggling provider', 'error');
                $toggle.prop('checked', !enabled);
            });
        });
        
        // ============================================
        // DELETE APPLICATION
        // ============================================
        $(document).on('click', '.aoauth-delete-app-btn', function() {
            if (!confirm(aoauth_admin.translations.confirm_delete)) {
                return;
            }
            
            var appId = $(this).data('app-id');
            var card = $(this).closest('.aoauth-app-card');
            
            $.post(aoauth_admin.ajaxurl, {
                action: 'aoauth_delete_application',
                app_id: appId,
                nonce: aoauth_admin.nonce
            }, function(response) {
                if (response.success) {
                    card.fadeOut(function() {
                        $(this).remove();
                        if ($('.aoauth-app-card').length === 0) {
                            location.reload();
                        }
                    });
                    aoauthShowToast(response.data.message, 'success');
                } else {
                    aoauthShowToast(response.data.message, 'error');
                }
            }).fail(function() {
                aoauthShowToast('Error deleting application', 'error');
            });
        });
        
        // ============================================
        // REFRESH LOGS BUTTON
        // ============================================
        $('.aoauth-refresh-logs-btn').on('click', function() {
            location.reload();
        });
        
        // ============================================
        // CLEAR LOGS BUTTON (Logs Page)
        // ============================================
        $('.aoauth-clear-logs-btn').on('click', function() {
            if (!confirm(aoauth_admin.translations.confirm_clear_logs)) {
                return;
            }
            
            $.post(aoauth_admin.ajaxurl, {
                action: 'aoauth_clear_logs',
                nonce: aoauth_admin.nonce
            }, function(response) {
                if (response.success) {
                    aoauthShowToast(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    aoauthShowToast(response.data.message, 'error');
                }
            }).fail(function() {
                aoauthShowToast('Error clearing logs', 'error');
            });
        });
        
        // ============================================
        // EXPORT LOGS
        // ============================================
        $('.aoauth-export-logs-btn').on('click', function() {
            window.location.href = aoauth_admin.ajaxurl + '?action=aoauth_export_logs&nonce=' + aoauth_admin.nonce;
        });
        
        
        // ============================================
        // COPY CALLBACK URL
        // ============================================
        $(document).on('click', '.aoauth-copy-btn', function() {
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
        
        // ============================================
        // EXPORT CONFIG
        // ============================================
        $('#aoauth-export-config-btn').on('click', function() {
            var password = window.prompt('Optional: enter a backup password to include encrypted Client IDs, Client Secrets, and bot protection secret keys. Leave blank to download settings only; no secrets will be included.');
            if (password === null) {
                return;
            }

            if (password !== '') {
                var confirmation = window.prompt('Confirm the backup password. If this does not match, the settings download will be cancelled.');
                if (confirmation === null) {
                    return;
                }

                if (password !== confirmation) {
                    aoauthShowToast('Backup passwords do not match. Settings were not downloaded.', 'error');
                    return;
                }
            }

            var $form = $('<form>', {
                method: 'POST',
                action: aoauth_admin.ajaxurl
            }).append(
                $('<input>', { type: 'hidden', name: 'action', value: 'aoauth_export_config' }),
                $('<input>', { type: 'hidden', name: 'nonce', value: aoauth_admin.nonce }),
                $('<input>', { type: 'hidden', name: 'backup_password', value: password })
            );

            $('body').append($form);
            $form.trigger('submit');
            $form.remove();
        });
        
        // ============================================
        // IMPORT CONFIG
        // ============================================
        $('#aoauth-import-config-btn').on('click', function() {
            $('#aoauth-import-file').trigger('click');
        });
        
        $('#aoauth-import-file').on('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;
            
            var formData = new FormData();
            formData.append('action', 'aoauth_import_config');
            formData.append('nonce', aoauth_admin.nonce);
            formData.append('config_file', file);
            formData.append('backup_password', window.prompt('If this backup was exported with a password, enter it now. Leave blank for non-encrypted backups.') || '');
            $(this).val('');
            
            $.ajax({
                url: aoauth_admin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        aoauthShowToast(res.data.message, 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1200);
                    } else {
                        aoauthShowToast(res.data.message, 'error');
                    }
                },
                error: function() {
                    aoauthShowToast('Import failed', 'error');
                }
            });
        });
        
        // ============================================
        // FACTORY RESET WITH COUNTDOWN
        // ============================================
        var resetModal = $('#aoauth-factory-reset-modal');
        var countdownInterval;
        
        $('#aoauth-factory-reset-btn').on('click', function() {
            resetModal.removeClass('aoauth-is-hidden');
            var seconds = 10;
            var $confirmBtn = $('#aoauth-confirm-reset');
            var $countdownSpan = $('#aoauth-countdown');
            $confirmBtn.prop('disabled', true);
            $countdownSpan.text(seconds);
            
            if (countdownInterval) clearInterval(countdownInterval);
            countdownInterval = setInterval(function() {
                seconds--;
                $countdownSpan.text(seconds);
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    $confirmBtn.prop('disabled', false);
                }
            }, 1000);
        });
        
        $('#aoauth-confirm-reset').on('click', function() {
            if ($(this).prop('disabled')) return;
            
            $.post(aoauth_admin.ajaxurl, {
                action: 'aoauth_factory_reset',
                nonce: aoauth_admin.nonce
            }, function(response) {
                if (response.success) {
                    aoauthShowToast(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    aoauthShowToast(response.data.message, 'error');
                }
                resetModal.addClass('aoauth-is-hidden');
                if (countdownInterval) clearInterval(countdownInterval);
            }).fail(function() {
                aoauthShowToast('Factory reset failed', 'error');
                resetModal.addClass('aoauth-is-hidden');
                if (countdownInterval) clearInterval(countdownInterval);
            });
        });
        
        $('#aoauth-cancel-reset').on('click', function() {
            resetModal.addClass('aoauth-is-hidden');
            if (countdownInterval) clearInterval(countdownInterval);
        });
        
        // ============================================
        // CLEAR LOGS FROM SETTINGS PAGE
        // ============================================
        $('#aoauth-clear-logs-settings-btn').on('click', function() {
            if (!confirm(aoauth_admin.translations.confirm_clear_logs)) return;
            
            $.post(aoauth_admin.ajaxurl, {
                action: 'aoauth_clear_logs',
                nonce: aoauth_admin.nonce
            }, function(response) {
                if (response.success) {
                    aoauthShowToast(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    aoauthShowToast(response.data.message, 'error');
                }
            }).fail(function() {
                aoauthShowToast('Error clearing logs', 'error');
            });
        });

        $('.aoauth-maintenance-action').on('click', function() {
            var $button = $(this);
            var action = $button.data('action');
            var originalText = $button.text();

            $button.prop('disabled', true).text('Working...');
            $.post(aoauth_admin.ajaxurl, {
                action: action,
                nonce: aoauth_admin.nonce
            }, function(response) {
                if (response.success) {
                    aoauthShowToast(response.data.message, 'success');
                } else {
                    aoauthShowToast(response.data.message || 'Maintenance action failed.', 'error');
                }
            }).fail(function() {
                aoauthShowToast('Maintenance action failed.', 'error');
            }).always(function() {
                $button.prop('disabled', false).text(originalText);
            });
        });
        
        // ============================================
        // PAGINATED LOGS
        // ============================================
        function getLogFilters() {
            var filters = $('#aoauth-log-filters').serializeArray();
            var data = {};
            $.each(filters, function(index, item) {
                data[item.name] = item.value;
            });
            return data;
        }

        function loadLogsPage(page) {
            var data = $.extend(getLogFilters(), {
                action: 'aoauth_get_logs',
                page: page,
                limit: 50,
                nonce: aoauth_admin.nonce
            });

            $.post(aoauth_admin.ajaxurl, data, function(response) {
                if (response.success) {
                    var tbody = $('#aoauth-logs-tbody');
                    tbody.empty();
                    
                    $.each(response.data.logs, function(i, log) {
                        var eventType = log.event_type.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
                        var row = '<tr>' +
                            '<td>' + escapeHtml(eventType) + '</td>' +
                            '<td>' + escapeHtml(log.provider || '-') + '</td>' +
                            '<td><span class="aoauth-status-badge aoauth-status-' + log.status + '">' + escapeHtml(log.status.charAt(0).toUpperCase() + log.status.slice(1)) + '</span></td>' +
                            '<td>' + (log.username ? escapeHtml(log.username) : (log.user_id || '-')) + '</td>' +
                            '<td>' + escapeHtml(log.ip_address) + '</td>' +
                            '<td>' + escapeHtml(log.created_at) + '</td>' +
                            '</tr>';
                        tbody.append(row);
                    });
                    
                    renderPagination(response.data.current_page, response.data.pages);
                }
            }).fail(function() {
                aoauthShowToast('Error loading logs', 'error');
            });
        }
        
        function renderPagination(current, total) {
            var container = $('#aoauth-logs-pagination');
            if (total <= 1) {
                container.empty();
                return;
            }
            
            var html = '<div class="aoauth-pagination-links">';
            
            if (current > 1) {
                html += '<button class="aoauth-page-btn" data-page="' + (current - 1) + '">« Prev</button>';
            }
            
            for (var i = 1; i <= total; i++) {
                if (i === current) {
                    html += '<span class="aoauth-page-current">' + i + '</span>';
                } else if (Math.abs(i - current) <= 2 || i === 1 || i === total) {
                    html += '<button class="aoauth-page-btn" data-page="' + i + '">' + i + '</button>';
                } else if (i === current - 3 || i === current + 3) {
                    html += '<span class="aoauth-page-dots">...</span>';
                }
            }
            
            if (current < total) {
                html += '<button class="aoauth-page-btn" data-page="' + (current + 1) + '">Next »</button>';
            }
            
            html += '</div>';
            container.html(html);
            
            $('.aoauth-page-btn').on('click', function() {
                loadLogsPage($(this).data('page'));
            });
        }
        
        if ($('#aoauth-logs-pagination').length) {
            loadLogsPage(1);
        }

        $('#aoauth-log-filters').on('submit', function(e) {
            e.preventDefault();
            loadLogsPage(1);
        });
        
        // ============================================
        // GLOBAL AJAX ERROR HANDLER
        // ============================================
        $(document).ajaxError(function(event, jqXHR, settings, error) {
            if (settings.url === aoauth_admin.ajaxurl && settings.data && settings.data.indexOf('aoauth_') !== -1) {
                if (jqXHR.status === 403) {
                    aoauthShowToast('Security check failed. Please refresh the page and try again.', 'error');
                } else if (jqXHR.status === 500) {
                    aoauthShowToast('Server error occurred. Please try again.', 'error');
                }
            }
        });
        
        // ============================================
        // ENABLE/DISABLE LOGS TOGGLE WARNING
        // ============================================
        $('#enable_logs').on('change', function() {
            var target = $(this);
            var hasData = target.data('has-data') === 1;
            
            if (!target.is(':checked') && hasData) {
                var msg = aoauth_admin.translations.confirm_disable_logs;
                if (confirm(msg)) {
                    return true;
                } else {
                    target.prop('checked', true);
                    return false;
                }
            }
        });
        
        // ============================================
        // LOGS RETENTION PERIOD CHANGE HANDLER
        // ============================================
        $('#logs_retention_period').on('change', function() {
            aoauthShowToast('Log retention period updated. Changes will take effect on next cron job.', 'info');
        });
        
        // ============================================
        // PREVIEW BUTTON STYLES ON THEME SELECT
        // ============================================
        function updateLinkingPreview() {
            var selectedTheme = $('input[name="login_button_theme"]:checked').val() || 'modern';
            var title = $('#linking_page_title').val() || 'Link Your Account';
            var overlayVariant = $('#bot_overlay_variant').val() || 'spotlight';
            var showBranding = $('#bot_overlay_branding_enabled').is(':checked');

            $('.aoauth-linking-preview-wrap')
                .attr('data-preview-theme', selectedTheme)
                .attr('data-overlay-variant', overlayVariant)
                .toggleClass('aoauth-overlay-preview-branding-hidden', !showBranding);
            $('.aoauth-linking-preview-title').text(title);
        }

        $('input[name="login_button_theme"]').on('change', function() {
            var selectedTheme = $(this).val();
            $('.aoauth-theme-card').removeClass('active');
            $('.aoauth-theme-card[data-theme="' + selectedTheme + '"]').addClass('active');
            updateLinkingPreview();
        });

        $('#linking_page_title, #bot_overlay_variant, #bot_overlay_branding_enabled').on('input change', updateLinkingPreview);
        updateLinkingPreview();
    });
})(jQuery);
