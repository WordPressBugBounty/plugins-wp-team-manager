/**
 * WP Team Manager - Unified Tools Tab Switching
 * 
 * Handles tab navigation for the unified tools/get help page
 */
jQuery(document).ready(function ($) {
    'use strict';

    // Tab switching functionality
    $('.wtm-tab-nav li').on('click', function () {
        const $tab = $(this);
        const tabId = $tab.data('tab');

        // Remove active class from all tabs and content
        $('.wtm-tab-nav li').removeClass('active');
        $('.wtm-tab-content').removeClass('active');

        // Add active class to clicked tab and corresponding content
        $tab.addClass('active');
        $('#' + tabId).addClass('active');

        // Store active tab in session storage for persistence
        try {
            sessionStorage.setItem('wtm_active_tab', tabId);
        } catch (e) {
            // Session storage not available, ignore
        }
    });

    // Restore last active tab on page load
    try {
        const lastActiveTab = sessionStorage.getItem('wtm_active_tab');
        if (lastActiveTab) {
            const $tab = $('.wtm-tab-nav li[data-tab="' + lastActiveTab + '"]');
            if ($tab.length) {
                $tab.trigger('click');
            }
        }
    } catch (e) {
        // Session storage not available, ignore
    }

    console.log('WTM Unified Tools JavaScript loaded');
});
