(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Handle layout option selection
        $(document).on('click', '.wtm-layout-option', function() {
            if ($(this).hasClass('pro-disabled')) {
                return false;
            }
            
            const $wrapper = $(this).closest('.wtm-advanced-layout-wrapper');
            const value = $(this).data('value');
            
            // Remove previous selection
            $wrapper.find('.wtm-layout-option').removeClass('selected');
            
            // Add selection to clicked option
            $(this).addClass('selected');
            
            // Trigger change event for Elementor
            const $control = $wrapper.closest('.elementor-control');
            const controlName = $control.data('setting');
            
            if (controlName) {
                elementor.settings.page.model.set(controlName, value);
            }
        });
        
        // Initialize selected state
        $(document).on('elementor:init', function() {
            $('.wtm-advanced-layout-wrapper').each(function() {
                const $wrapper = $(this);
                const $control = $wrapper.closest('.elementor-control');
                const controlName = $control.data('setting');
                
                if (controlName) {
                    const currentValue = elementor.settings.page.model.get(controlName);
                    $wrapper.find(`[data-value="${currentValue}"]`).addClass('selected');
                }
            });
        });
    });
    
})(jQuery);