jQuery(document).ready(function($) {
    
    // Load performance metrics on page load
    loadPerformanceMetrics();
    
    // Handle quick action buttons
    $('.wtm-quick-btn').on('click', function(e) {
        const $btn = $(this);
        const action = $btn.data('action');
        
        // Only prevent default for buttons with data-action (not links)
        if (!action) return;
        
        e.preventDefault();
        
        $btn.prop('disabled', true).addClass('updating-message');
        
        $.ajax({
            url: wtmDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wtm_quick_action',
                quick_action: action,
                nonce: wtmDashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message || wtmDashboard.strings.success, 'success');
                    
                    // Refresh metrics after cache clear
                    if (action === 'clear_cache') {
                        setTimeout(loadPerformanceMetrics, 1000);
                    }
                    
                    // Reload page after creating sample team
                    if (action === 'create_sample_team') {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showNotice(response.data || wtmDashboard.strings.error, 'error');
                }
            },
            error: function() {
                showNotice(wtmDashboard.strings.error, 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).removeClass('updating-message');
            }
        });
    });
    
    // Load performance metrics
    function loadPerformanceMetrics() {
        $.ajax({
            url: wtmDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wtm_get_performance_metrics',
                nonce: wtmDashboard.nonce
            },
            success: function(response) {
                if (response.success) {
                    const metrics = response.data;
                    
                    $('#wtm-load-time').text(metrics.load_time + 's');
                    $('#wtm-cache-rate').text(metrics.cache_hit_rate + '%');
                    $('#wtm-image-opt').text(metrics.image_optimization + '%');
                    
                    // Add color coding based on performance
                    updateMetricColors(metrics);
                }
            }
        });
    }
    
    // Update metric colors based on performance
    function updateMetricColors(metrics) {
        // Load time coloring
        const $loadTime = $('#wtm-load-time');
        if (metrics.load_time < 1) {
            $loadTime.css('color', '#10b981'); // Green
        } else if (metrics.load_time < 2) {
            $loadTime.css('color', '#f59e0b'); // Yellow
        } else {
            $loadTime.css('color', '#ef4444'); // Red
        }
        
        // Cache rate coloring
        const $cacheRate = $('#wtm-cache-rate');
        if (metrics.cache_hit_rate > 90) {
            $cacheRate.css('color', '#10b981');
        } else if (metrics.cache_hit_rate > 75) {
            $cacheRate.css('color', '#f59e0b');
        } else {
            $cacheRate.css('color', '#ef4444');
        }
        
        // Image optimization coloring
        const $imageOpt = $('#wtm-image-opt');
        if (metrics.image_optimization > 80) {
            $imageOpt.css('color', '#10b981');
        } else if (metrics.image_optimization > 60) {
            $imageOpt.css('color', '#f59e0b');
        } else {
            $imageOpt.css('color', '#ef4444');
        }
    }
    
    // Show admin notice
    function showNotice(message, type = 'success') {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);
        
        $('.wtm-dashboard').prepend($notice);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $notice.fadeOut(() => $notice.remove());
        }, 5000);
        
        // Handle manual dismiss
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(() => $notice.remove());
        });
    }
    
    // Refresh metrics every 30 seconds
    setInterval(loadPerformanceMetrics, 30000);
    
});