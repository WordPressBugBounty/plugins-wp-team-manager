jQuery(document).ready(function($) {
    
    // Check for automatic step progression on page load
    checkForStepProgression();
    
    // Handle complete step button
    $('.wtm-complete-step').on('click', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const step = $btn.data('step');
        
        $btn.prop('disabled', true).text('Completing...');
        
        $.ajax({
            url: wtmOnboarding.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wtm_complete_onboarding_step',
                step: step,
                nonce: wtmOnboarding.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.completed) {
                        // Onboarding completed
                        $('.wtm-onboarding-notice').fadeOut(300, function() {
                            $(this).remove();
                        });
                        
                        // Show completion message
                        showCompletionMessage();
                    } else {
                        // Move to next step
                        location.reload();
                    }
                }
            },
            error: function() {
                $btn.prop('disabled', false).text('Mark as Complete');
            }
        });
    });
    
    // Handle skip onboarding
    $('.wtm-skip-onboarding').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to skip the setup guide?')) {
            return;
        }
        
        $.ajax({
            url: wtmOnboarding.ajaxUrl,
            type: 'POST',
            data: {
                action: 'wtm_skip_onboarding',
                nonce: wtmOnboarding.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.wtm-onboarding-notice').fadeOut(300, function() {
                        $(this).remove();
                    });
                }
            }
        });
    });
    
    // Show completion message
    function showCompletionMessage() {
        const $message = $(`
            <div class="notice notice-success is-dismissible wtm-completion-notice">
                <p><strong>🎉 Setup Complete!</strong> Your Team Manager is ready to use. You can now add team members and display them on your website.</p>
            </div>
        `);
        
        $('.wrap').prepend($message);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $message.fadeOut(() => $message.remove());
        }, 5000);
    }
    
    // Check for automatic step progression
    function checkForStepProgression() {
        // Check if we're on a team member edit page and step was auto-advanced
        const urlParams = new URLSearchParams(window.location.search);
        const postType = urlParams.get('post_type');
        const action = urlParams.get('action');
        
        if (postType === 'team_manager' || action === 'edit') {
            // Check if step was recently advanced
            const stepAdvanced = sessionStorage.getItem('wtm_step_advanced');
            if (stepAdvanced) {
                sessionStorage.removeItem('wtm_step_advanced');
                showStepAdvancedMessage();
            }
        }
    }
    
    // Show step advanced message
    function showStepAdvancedMessage() {
        const $message = $(`
            <div class="notice notice-success is-dismissible wtm-step-advanced-notice">
                <p><strong>🎉 Great job!</strong> You've created your first team member. The setup guide has automatically advanced to the next step.</p>
            </div>
        `);
        
        $('.wrap').prepend($message);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $message.fadeOut(() => $message.remove());
        }, 5000);
    }
    
    // Animate progress bar on load
    setTimeout(() => {
        $('.wtm-progress-fill').css('transition', 'width 1s ease');
    }, 100);
    
});