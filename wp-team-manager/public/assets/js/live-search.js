jQuery(document).ready(function($) {
    let searchTimeout;
    
    $(document).on('input', '.wtm-live-search', function() {
        const $input = $(this);
        const $container = $input.closest('.dwl-team-wrapper');
        const $loading = $input.siblings('.wtm-search-loading');
        const $results = $container.find('.dwl-team-wrapper--main');
        const searchTerm = $input.val().trim();
        
        clearTimeout(searchTimeout);
        
        if (searchTerm.length === 0) {
            // Reset to original results
            $container.find('.wtm-pagination-wrap, .dwl-team-load-more-wrap, .dwl-team-ajax-pagination-wrap').show();
            location.reload();
            return;
        }
        
        if (searchTerm.length < 2) {
            return;
        }
        
        searchTimeout = setTimeout(function() {
            $loading.show();
            
            $.ajax({
                url: wtm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wtm_live_search',
                    search: searchTerm,
                    layout: $input.data('layout'),
                    style: $input.data('style'),
                    nonce: wtm_ajax.nonce
                },
                success: function(response) {
                    $loading.hide();
                    if (response.success) {
                        $results.html(response.data);
                        $container.find('.wtm-pagination-wrap, .dwl-team-load-more-wrap, .dwl-team-ajax-pagination-wrap').hide();
                        if (typeof initTeamPopup === 'function') {
                            initTeamPopup();
                        }
                    }
                },
                error: function() {
                    $loading.hide();
                }
            });
        }, 300);
    });
});