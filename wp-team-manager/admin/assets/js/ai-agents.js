jQuery(document).ready(function($) {
    // Debug: Log initial state of all checkboxes
    console.log('=== WTM AI Agents Debug Info ===');
    $('.wtm-agent-card input[type="checkbox"]').each(function() {
        console.log('Checkbox:', $(this).attr('id'), 'Checked:', $(this).is(':checked'), 'Disabled:', $(this).is(':disabled'));
    });
    
    // Toggle agent settings visibility
    $('.wtm-agent-card input[type="checkbox"]').on('change', function(e) {
        const checkboxId = $(this).attr('id');
        console.log('Checkbox change event:', checkboxId);
        
        // Special handling for disabled Pro features
        if ($(this).is(':disabled')) {
            console.log('Checkbox is disabled (Pro feature), preventing action');
            e.preventDefault();
            
            // Show upgrade animation
            const $card = $(this).closest('.wtm-agent-card');
            const $upgradeBtn = $card.find('.wtm-upgrade-btn');
            if ($upgradeBtn.length) {
                $upgradeBtn.addClass('pulse-animation');
                setTimeout(() => $upgradeBtn.removeClass('pulse-animation'), 1000);
            }
            
            return false;
        }
        
        const $card = $(this).closest('.wtm-agent-card');
        const $settings = $card.find('.wtm-agent-settings');
        const $status = $card.find('.wtm-status-indicator');
        const $statusText = $card.find('.wtm-status-text');
        const $inputs = $settings.find('input, select, textarea');
        
        console.log('Toggle changed:', checkboxId, 'Checked:', $(this).is(':checked'));
        
        if ($(this).is(':checked')) {
            console.log('Enabling settings for:', checkboxId);
            $settings.addClass('active').show();
            $status.removeClass('error').addClass('active');
            $statusText.text('Active');
            $inputs.prop('disabled', false);
        } else {
            console.log('Disabling settings for:', checkboxId);
            $settings.removeClass('active').hide();
            $status.removeClass('active error');
            $statusText.text('Inactive');
            $inputs.prop('disabled', true);
        }
    });
    
    // Handle clicks on disabled checkboxes (for Pro features)
    $(document).on('click', '.wtm-agent-card input[type="checkbox"]:disabled', function(e) {
        console.log('Disabled checkbox clicked:', $(this).attr('id'));
        e.preventDefault();
        e.stopPropagation();
        
        // Show upgrade message for Pro features
        const $card = $(this).closest('.wtm-agent-card');
        const $upgradeBtn = $card.find('.wtm-upgrade-btn');
        if ($upgradeBtn.length) {
            $upgradeBtn.addClass('pulse-animation');
            setTimeout(() => $upgradeBtn.removeClass('pulse-animation'), 1000);
        }
        
        return false;
    });
    
    // Specific FAQ Bot toggle handler with enhanced debugging
    $('#faq-toggle').on('change click', function(e) {
        console.log('=== FAQ Bot Event ===');
        console.log('Event type:', e.type);
        console.log('FAQ Bot toggle - Checked:', $(this).is(':checked'), 'Disabled:', $(this).is(':disabled'));
        console.log('FAQ Bot element:', this);
        
        if ($(this).is(':disabled')) {
            console.log('FAQ Bot is disabled (Pro feature required)');
            e.preventDefault();
            e.stopPropagation();
            
            // Show upgrade animation
            const $card = $(this).closest('.wtm-agent-card');
            const $upgradeBtn = $card.find('.wtm-upgrade-btn');
            if ($upgradeBtn.length) {
                console.log('Triggering upgrade button animation');
                $upgradeBtn.addClass('pulse-animation');
                setTimeout(() => $upgradeBtn.removeClass('pulse-animation'), 1000);
            }
            
            return false;
        }
        
        const $card = $(this).closest('.wtm-agent-card');
        const $settings = $card.find('.wtm-agent-settings');
        const $status = $card.find('.wtm-status-indicator');
        const $statusText = $card.find('.wtm-status-text');
        const $inputs = $settings.find('input, select, textarea');
        
        console.log('FAQ Bot settings elements found:', {
            card: $card.length,
            settings: $settings.length,
            status: $status.length,
            statusText: $statusText.length,
            inputs: $inputs.length
        });
        
        if ($(this).is(':checked')) {
            console.log('Enabling FAQ Bot settings');
            $settings.addClass('active').show();
            $status.removeClass('error').addClass('active');
            $statusText.text('Active');
            $inputs.prop('disabled', false);
        } else {
            console.log('Disabling FAQ Bot settings');
            $settings.removeClass('active').hide();
            $status.removeClass('active error');
            $statusText.text('Inactive');
            $inputs.prop('disabled', true);
        }
    });
    
    // Debug: Test FAQ Bot toggle manually
    window.testFaqToggle = function() {
        console.log('=== Manual FAQ Bot Test ===');
        const $faqToggle = $('#faq-toggle');
        console.log('FAQ Toggle found:', $faqToggle.length);
        console.log('FAQ Toggle checked:', $faqToggle.is(':checked'));
        console.log('FAQ Toggle disabled:', $faqToggle.is(':disabled'));
        console.log('FAQ Toggle pro-active data:', $faqToggle.data('pro-active'));
        
        if ($faqToggle.length) {
            console.log('Triggering click on FAQ toggle');
            $faqToggle.trigger('click');
        } else {
            console.log('FAQ toggle not found!');
        }
    };
    
    // Initialize settings visibility on page load
    $('.wtm-agent-card input[type="checkbox"]').each(function() {
        const $card = $(this).closest('.wtm-agent-card');
        const $settings = $card.find('.wtm-agent-settings');
        const $status = $card.find('.wtm-status-indicator');
        const $statusText = $card.find('.wtm-status-text');
        const $inputs = $settings.find('input, select, textarea');
        
        console.log('Initializing:', $(this).attr('id'), 'Checked:', $(this).is(':checked'), 'Disabled:', $(this).is(':disabled'));
        
        if ($(this).is(':checked') && !$(this).is(':disabled')) {
            $settings.addClass('active').show();
            $status.removeClass('error').addClass('active');
            $statusText.text('Active');
            $inputs.prop('disabled', false);
        } else {
            $settings.removeClass('active').hide();
            $status.removeClass('active error');
            $statusText.text('Inactive');
            // Always disable inputs when toggle is off or disabled
            $inputs.prop('disabled', true);
        }
    });
    
    // Disable test buttons when required fields are empty
    function updateTestButtons() {
        // Telegram test button
        const telegramToken = $('input[name="wtm_telegram_bot_token"]').val();
        const telegramChatId = $('input[name="wtm_telegram_chat_id"]').val();
        const $telegramBtn = $('.wtm-test-btn');
        
        if (!telegramToken || !telegramChatId) {
            $telegramBtn.addClass('disabled').attr('onclick', 'return false;');
        } else {
            $telegramBtn.removeClass('disabled').removeAttr('onclick');
        }
    }
    
    // Update test buttons on field changes
    $('input[name="wtm_telegram_bot_token"], input[name="wtm_telegram_chat_id"]').on('input', updateTestButtons);
    
    // Initialize test button states
    updateTestButtons();
    
    // Validate required fields
    function validateAgentSettings() {
        let hasErrors = false;
        
        // Check Telegram settings
        const telegramEnabled = $('#telegram-toggle').is(':checked');
        if (telegramEnabled) {
            const botToken = $('input[name="wtm_telegram_bot_token"]').val();
            const chatId = $('input[name="wtm_telegram_chat_id"]').val();
            
            if (!botToken || !chatId) {
                hasErrors = true;
            }
        }
        
        // Check FAQ Bot settings
        const faqEnabled = $('#faq-toggle').is(':checked');
        const faqDisabled = $('#faq-toggle').is(':disabled');
        console.log('FAQ Bot validation - Enabled:', faqEnabled, 'Disabled:', faqDisabled);
        if (faqEnabled && !faqDisabled) {
            const apiKey = $('input[name="wtm_pro_openai_api_key"]').val();
            console.log('FAQ Bot API key check:', apiKey ? 'Key provided' : 'No key');
            
            if (!apiKey || !apiKey.startsWith('sk-')) {
                hasErrors = true;
                console.log('FAQ Bot validation failed - missing or invalid API key');
            }
        }
        
        // Check Profile Sync settings
        const syncEnabled = $('#sync-toggle').is(':checked');
        if (syncEnabled) {
            const webhookUrl = $('input[name="wtm_sync_webhook_url"]').val();
            
            if (!webhookUrl) {
                hasErrors = true;
            }
        }
        
        // Check Slack settings
        const slackEnabled = $('#slack-toggle').is(':checked');
        if (slackEnabled) {
            const webhookUrl = $('input[name="wtm_pro_slack_webhook_url"]').val();
            
            if (!webhookUrl) {
                hasErrors = true;
            }
        }
        
        return !hasErrors;
    }
    
    // Form submission validation
    $('form').on('submit', function(e) {
        console.log('Form submission - validating agent settings');
        if (!validateAgentSettings()) {
            console.log('Validation failed - but letting WordPress handle it');
            // Let WordPress handle the validation notices
            // This is just for client-side UX enhancement
        }
    });
    
    // Real-time field validation for Telegram
    $('input[name="wtm_telegram_bot_token"], input[name="wtm_telegram_chat_id"]').on('blur', function() {
        const $card = $(this).closest('.wtm-agent-card');
        const botToken = $card.find('input[name="wtm_telegram_bot_token"]').val();
        const chatId = $card.find('input[name="wtm_telegram_chat_id"]').val();
        const $status = $card.find('.wtm-status-indicator');
        
        if (botToken && chatId && $('#telegram-toggle').is(':checked')) {
            $status.removeClass('error').addClass('active');
        } else if ($('#telegram-toggle').is(':checked')) {
            $status.removeClass('active').addClass('error');
        }
        
        updateTestButtons();
    });
    
    // API Key validation for FAQ Bot
    $('input[name="wtm_pro_openai_api_key"]').on('blur input', function() {
        const $card = $(this).closest('.wtm-agent-card');
        const apiKey = $(this).val();
        const $status = $card.find('.wtm-status-indicator');
        const $toggle = $('#faq-toggle');
        
        console.log('FAQ Bot API key validation:', apiKey ? 'Key provided' : 'No key', 'Toggle checked:', $toggle.is(':checked'), 'Toggle disabled:', $toggle.is(':disabled'));
        
        if (apiKey && apiKey.startsWith('sk-') && $toggle.is(':checked') && !$toggle.is(':disabled')) {
            $status.removeClass('error').addClass('active');
        } else if ($toggle.is(':checked') && !$toggle.is(':disabled')) {
            $status.removeClass('active').addClass('error');
        }
    });
    
    // Webhook URL validation for Profile Sync
    $('input[name="wtm_sync_webhook_url"]').on('blur', function() {
        const $card = $(this).closest('.wtm-agent-card');
        const webhookUrl = $(this).val();
        const $status = $card.find('.wtm-status-indicator');
        
        if (webhookUrl && (webhookUrl.startsWith('http://') || webhookUrl.startsWith('https://')) && $('#sync-toggle').is(':checked')) {
            $status.removeClass('error').addClass('active');
        } else if ($('#sync-toggle').is(':checked')) {
            $status.removeClass('active').addClass('error');
        }
    });
    
    // Webhook URL validation for Slack
    $('input[name="wtm_pro_slack_webhook_url"]').on('blur', function() {
        const $card = $(this).closest('.wtm-agent-card');
        const webhookUrl = $(this).val();
        const $status = $card.find('.wtm-status-indicator');
        
        if (webhookUrl && webhookUrl.startsWith('https://hooks.slack.com/') && $('#slack-toggle').is(':checked')) {
            $status.removeClass('error').addClass('active');
        } else if ($('#slack-toggle').is(':checked')) {
            $status.removeClass('active').addClass('error');
        }
    });
    
    // Smooth scroll to error notices
    if ($('.notice.notice-error').length > 0) {
        $('html, body').animate({
            scrollTop: $('.notice.notice-error').first().offset().top - 100
        }, 500);
    }
    
    // Add visual feedback for form submission
    $('form').on('submit', function() {
        console.log('Form submission started');
        const $submitBtn = $(this).find('.button-primary');
        $submitBtn.prop('disabled', true).text('Saving...');
        
        // Re-enable after a delay (WordPress will redirect anyway)
        setTimeout(function() {
            $submitBtn.prop('disabled', false).text('Save AI Agent Settings');
        }, 3000);
    });
    
    // Debug: Add global function to check FAQ Bot state
    window.debugFaqBot = function() {
        console.log('=== FAQ Bot Debug ===');
        const $faqToggle = $('#faq-toggle');
        const $faqCard = $faqToggle.closest('.wtm-agent-card');
        const $faqSettings = $faqCard.find('.wtm-agent-settings');
        
        console.log('FAQ Toggle:', {
            exists: $faqToggle.length > 0,
            checked: $faqToggle.is(':checked'),
            disabled: $faqToggle.is(':disabled'),
            value: $faqToggle.val(),
            name: $faqToggle.attr('name')
        });
        
        console.log('FAQ Settings:', {
            exists: $faqSettings.length > 0,
            visible: $faqSettings.is(':visible'),
            hasActiveClass: $faqSettings.hasClass('active')
        });
        
        console.log('Pro Badge:', $faqCard.find('.wtm-pro-badge').length > 0);
        console.log('Upgrade Button:', $faqCard.find('.wtm-upgrade-btn').length > 0);
    };
    
    // Auto-run debug on page load
    setTimeout(function() {
        if (typeof window.debugFaqBot === 'function') {
            window.debugFaqBot();
        }
        
        // Test if FAQ toggle is working
        console.log('=== Testing FAQ Bot Toggle Functionality ===');
        const $faqToggle = $('#faq-toggle');
        if ($faqToggle.length && !$faqToggle.is(':disabled')) {
            console.log('FAQ Bot toggle is available and enabled');
        } else if ($faqToggle.length && $faqToggle.is(':disabled')) {
            console.log('FAQ Bot toggle is disabled (Pro feature required)');
        } else {
            console.log('FAQ Bot toggle not found!');
        }
    }, 1000);
    
    console.log('=== WTM AI Agents JavaScript Loaded ===');
    console.log('Available debug functions: debugFaqBot(), testFaqToggle(), toggleFaqBot(true/false)');
    
    // Add click handler to the toggle slider itself for better UX
    $(document).on('click', '.wtm-toggle-slider', function(e) {
        const $checkbox = $(this).siblings('input[type="checkbox"]');
        const checkboxId = $checkbox.attr('id');
        
        console.log('Toggle slider clicked for:', checkboxId);
        
        if ($checkbox.is(':disabled')) {
            console.log('Checkbox is disabled, showing upgrade prompt');
            e.preventDefault();
            
            // Show upgrade animation
            const $card = $(this).closest('.wtm-agent-card');
            const $upgradeBtn = $card.find('.wtm-upgrade-btn');
            if ($upgradeBtn.length) {
                $upgradeBtn.addClass('pulse-animation');
                setTimeout(() => $upgradeBtn.removeClass('pulse-animation'), 1000);
            }
            
            return false;
        }
        
        // Let the normal checkbox handling take over
        console.log('Triggering checkbox change for:', checkboxId);
        $checkbox.trigger('click');
    });
    
    // Alternative toggle method for testing
    window.toggleFaqBot = function(enable) {
        console.log('=== Toggle FAQ Bot ===', enable ? 'Enable' : 'Disable');
        const $faqToggle = $('#faq-toggle');
        
        if ($faqToggle.length) {
            if ($faqToggle.is(':disabled')) {
                console.log('FAQ Bot is disabled (Pro feature)');
                return false;
            }
            
            $faqToggle.prop('checked', enable).trigger('change');
            return true;
        }
        
        console.log('FAQ toggle not found!');
        return false;
    };
});