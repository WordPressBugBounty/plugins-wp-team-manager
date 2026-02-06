jQuery(document).ready(function ($) {
    'use strict';

    let migrationInProgress = false;
    let currentPlugin = null;

    // Migration form handling with AJAX
    $('#wtm-migration-form').on('submit', function (e) {
        e.preventDefault(); // Prevent default form submission

        const $form = $(this);
        const $button = $(e.originalEvent.submitter); // Get the clicked button
        const pluginKey = $button.val();
        const postCount = $button.data('count');

        // Prevent multiple simultaneous migrations
        if (migrationInProgress) {
            alert('Migration already in progress. Please wait...');
            return false;
        }

        // Show confirmation dialog
        const confirmMessage = wtmMigration.strings.confirm + '\n\n' +
            'Team Members: ' + postCount;

        if (!confirm(confirmMessage)) {
            return false;
        }

        // Start migration
        startMigration(pluginKey, $button);
    });

    /**
     * Start AJAX migration process
     */
    function startMigration(plugin, $button) {
        migrationInProgress = true;
        currentPlugin = plugin;

        // Disable all migrate buttons
        $('.wtm-migrate-btn').prop('disabled', true);

        // Show loading state on clicked button
        $button.addClass('loading');
        $button.find('.dashicons').removeClass('dashicons-migrate').addClass('dashicons-update');

        // Show progress container
        showMigrationProgress();

        // Initialize migration via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wtm_start_migration',
                nonce: wtmMigration.nonce,
                plugin: plugin
            },
            success: function (response) {
                if (response.success) {
                    updateProgressStatus(`Starting migration of ${response.data.total} posts...`);
                    // Start processing batches
                    processBatch(plugin, 0, response.data.total);
                } else {
                    showError(response.data.message || 'Failed to start migration');
                    resetMigrationState();
                }
            },
            error: function (xhr, status, error) {
                showError('AJAX error: ' + error);
                resetMigrationState();
            }
        });
    }

    /**
     * Process a batch of posts
     */
    function processBatch(plugin, offset, total) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wtm_migrate_batch',
                nonce: wtmMigration.nonce,
                plugin: plugin,
                offset: offset
            },
            success: function (response) {
                if (response.success) {
                    const data = response.data;

                    // Update progress
                    updateProgress(data.processed, data.total, data.migrated, data.failed);

                    // Show migrated titles
                    if (data.titles && data.titles.length > 0) {
                        appendMigratedTitles(data.titles);
                    }

                    // Check if complete
                    if (data.complete) {
                        completeMigration(data);
                    } else {
                        // Process next batch
                        processBatch(plugin, data.processed, total);
                    }
                } else {
                    showError(response.data.message || 'Migration batch failed');
                    resetMigrationState();
                }
            },
            error: function (xhr, status, error) {
                showError('AJAX error during batch processing: ' + error);
                resetMigrationState();
            }
        });
    }

    /**
     * Show migration progress container
     */
    function showMigrationProgress() {
        const $progress = $('#wtm-migration-progress');
        $progress.slideDown(300);

        // Reset progress
        $('#wtm-progress-fill').css('width', '0%');
        $('#wtm-migrated-list').empty();

        // Scroll to progress
        $('html, body').animate({
            scrollTop: $progress.offset().top - 100
        }, 500);
    }

    /**
     * Update progress bar and status
     */
    function updateProgress(processed, total, migrated, failed) {
        const percentage = Math.round((processed / total) * 100);

        // Update progress bar
        $('#wtm-progress-fill').css('width', percentage + '%');

        // Update status text
        const statusText = `Migrating: ${processed} / ${total} processed | ${migrated} migrated | ${failed} failed`;
        updateProgressStatus(statusText);
    }

    /**
     * Update progress status text
     */
    function updateProgressStatus(text) {
        $('#wtm-progress-status').text(text);
    }

    /**
     * Append migrated post titles to the list
     */
    function appendMigratedTitles(titles) {
        const $list = $('#wtm-migrated-list');

        // Create list if it doesn't exist
        if ($list.length === 0) {
            $('#wtm-migration-progress').append('<ul id="wtm-migrated-list" style="margin-top: 20px; max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 15px; border-radius: 8px;"></ul>');
        }

        titles.forEach(function (title) {
            $('#wtm-migrated-list').append(`<li style="padding: 5px 0; border-bottom: 1px solid #e5e5e5;">✓ ${title}</li>`);
        });

        // Auto-scroll to bottom
        const list = document.getElementById('wtm-migrated-list');
        if (list) {
            list.scrollTop = list.scrollHeight;
        }
    }

    /**
     * Complete migration
     */
    function completeMigration(data) {
        updateProgressStatus(`Migration complete! ${data.migrated} posts migrated successfully.`);

        // Show success message
        showSuccessNotice(data);

        // Reset state after delay
        setTimeout(function () {
            resetMigrationState();

            // Reload page to show updated counts
            location.reload();
        }, 3000);
    }

    /**
     * Show success notice
     */
    function showSuccessNotice(data) {
        const message = `Migration completed successfully! ${data.migrated} team members migrated.`;

        const $notice = $(`
            <div class="notice notice-success is-dismissible" style="margin: 20px 0;">
                <p><strong>${message}</strong></p>
            </div>
        `);

        $('.wtm-migration-header').after($notice);

        // Auto-dismiss after 5 seconds
        setTimeout(function () {
            $notice.fadeOut(500, function () {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Show error message
     */
    function showError(message) {
        const $error = $(`
            <div class="notice notice-error is-dismissible" style="margin: 20px 0;">
                <p><strong>Error:</strong> ${message}</p>
            </div>
        `);

        $('.wtm-migration-header').after($error);

        updateProgressStatus('Migration failed: ' + message);
    }

    /**
     * Reset migration state
     */
    function resetMigrationState() {
        migrationInProgress = false;
        currentPlugin = null;

        // Re-enable buttons
        $('.wtm-migrate-btn').prop('disabled', false).removeClass('loading');
        $('.wtm-migrate-btn .dashicons').removeClass('dashicons-update').addClass('dashicons-migrate');
    }

    // Enhanced button interactions
    $('.wtm-migrate-btn:not(.disabled)').on('mouseenter', function () {
        const $this = $(this);
        const count = $this.data('count');

        if (count) {
            $this.attr('title', `Migrate ${count} team members`);
        }
    });

    // Disabled button feedback
    $('.wtm-migrate-btn.disabled').on('click', function (e) {
        e.preventDefault();

        const $this = $(this);
        $this.addClass('shake');

        setTimeout(function () {
            $this.removeClass('shake');
        }, 500);

        showTooltip($this, 'No data available to migrate');
    });

    // Show tooltip function
    function showTooltip($element, message) {
        const $tooltip = $('<div class="wtm-tooltip">' + message + '</div>');

        $tooltip.css({
            position: 'absolute',
            background: '#333',
            color: 'white',
            padding: '8px 12px',
            borderRadius: '4px',
            fontSize: '12px',
            zIndex: 9999,
            whiteSpace: 'nowrap'
        });

        $('body').append($tooltip);

        const offset = $element.offset();
        $tooltip.css({
            top: offset.top - $tooltip.outerHeight() - 10,
            left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
        });

        setTimeout(function () {
            $tooltip.fadeOut(300, function () {
                $tooltip.remove();
            });
        }, 2000);
    }

    // Add animations CSS
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            .shake {
                animation: shake 0.5s ease-in-out;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .loading .dashicons-update {
                animation: spin 1s linear infinite;
            }
        `)
        .appendTo('head');

    // Stats animation on page load
    function animateStats() {
        $('.wtm-stat-number').each(function () {
            const $this = $(this);
            const finalValue = parseInt($this.text()) || 0;

            if (finalValue === 0) return;

            $this.text('0');

            $({ counter: 0 }).animate({ counter: finalValue }, {
                duration: 1500,
                easing: 'swing',
                step: function () {
                    $this.text(Math.ceil(this.counter));
                },
                complete: function () {
                    $this.text(finalValue);
                }
            });
        });
    }

    // Trigger stats animation when in viewport
    function checkStatsInView() {
        const $stats = $('.wtm-migration-stats');
        if ($stats.length === 0) return;

        const statsTop = $stats.offset().top;
        const statsBottom = statsTop + $stats.outerHeight();
        const viewportTop = $(window).scrollTop();
        const viewportBottom = viewportTop + $(window).height();

        if (statsBottom > viewportTop && statsTop < viewportBottom) {
            animateStats();
            $(window).off('scroll', checkStatsInView);
        }
    }

    // Initialize stats animation
    $(window).on('scroll', checkStatsInView);
    checkStatsInView(); // Check on page load

    // Cancel migration button handler
    $(document).on('click', '#wtm-cancel-migration', function () {
        const $button = $(this);
        const plugin = $button.data('plugin');

        if (!confirm('Are you sure you want to cancel this migration? Progress will be lost.')) {
            return;
        }

        $button.prop('disabled', true).text('Cancelling...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'wtm_cancel_migration',
                nonce: wtmMigration.nonce,
                plugin: plugin
            },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Failed to cancel migration: ' + (response.data.message || 'Unknown error'));
                    $button.prop('disabled', false).text('Cancel Migration');
                }
            },
            error: function () {
                alert('AJAX error while cancelling migration');
                $button.prop('disabled', false).text('Cancel Migration');
            }
        });
    });

    console.log('WTM Migration JavaScript (AJAX version) loaded successfully');
});