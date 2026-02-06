jQuery(document).ready(function($) {
    // Add enhanced search handling to shortcode generator
    function updateShortcode() {
        var shortcode = '[team_manager';
        
        // Get all form values
        var category = $('#category').val();
        var orderby = $('#orderby').val();
        var posts_per_page = $('#posts_per_page').val();
        var post__in = $('#post__in').val();
        var post__not_in = $('#post__not_in').val();
        var layout = $('#layout').val();
        var large_column = $('#large_column').val();
        var tablet_column = $('#tablet_column').val();
        var mobile_column = $('#mobile_column').val();
        var show_other_info = $('#show_other_info').val();
        var show_read_more = $('#show_read_more').val();
        var show_social = $('#show_social').val();
        var show_search = $('#show_search').val();
        var image_size = $('#image_size').val();
        var image_style = $('#image_style').val();
        var bg_color = $('#bg_color').val();
        var social_color = $('#social_color').val();
        
        // Build shortcode attributes
        if (category && category !== '0') shortcode += ' category="' + category + '"';
        if (orderby) shortcode += ' orderby="' + orderby + '"';
        if (posts_per_page && posts_per_page !== '0') shortcode += ' posts_per_page="' + posts_per_page + '"';
        if (post__in) shortcode += ' post__in="' + post__in + '"';
        if (post__not_in) shortcode += ' post__not_in="' + post__not_in + '"';
        if (layout) shortcode += ' layout="' + layout + '"';
        if (large_column) shortcode += ' large_column="' + large_column + '"';
        if (tablet_column) shortcode += ' tablet_column="' + tablet_column + '"';
        if (mobile_column) shortcode += ' mobile_column="' + mobile_column + '"';
        if (show_other_info) shortcode += ' show_other_info="' + show_other_info + '"';
        if (show_read_more) shortcode += ' show_read_more="' + show_read_more + '"';
        if (show_social) shortcode += ' show_social="' + show_social + '"';
        if (show_search === 'yes') shortcode += ' show_search="yes"';
        if (image_size) shortcode += ' image_size="' + image_size + '"';
        if (image_style) shortcode += ' image_style="' + image_style + '"';
        if (bg_color) shortcode += ' bg_color="' + bg_color + '"';
        if (social_color) shortcode += ' social_color="' + social_color + '"';
        
        shortcode += ']';
        
        $('#shortcode_output_box').text(shortcode);
        
        // Show Pro notice for enhanced search
        if (show_search === 'yes') {
            $('#shortcode_output_box').append('<br><small style="color: #ff6b6b;">Enhanced search requires Pro version</small>');
        }
    }
    
    // Update shortcode on form changes
    $('#tm_short_code input, #tm_short_code select').on('change keyup', updateShortcode);
    
    // Initial shortcode generation
    updateShortcode();
});