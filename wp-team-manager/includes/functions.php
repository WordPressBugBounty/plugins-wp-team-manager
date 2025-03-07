<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function wptm_custom_upload_mimes ( $existing_mimes=array() ) {
    // add your extension to the array
    $existing_mimes['vcf'] = 'text/x-vcard';
    return $existing_mimes;
}

// add VCF file type upload support

add_filter('upload_mimes', 'wptm_custom_upload_mimes');

/**
 * Add new feature image column
 *
 * @since 1.5
 *
 *
 */
function wptm_columns_head( $defaults ) {
    $defaults['featured_image'] = __('Featured Image', 'wp-team-manager');
    return $defaults;
}
 /**
 * Show feature image on the admin
 *
 * @since 1.5
 *
 *
 */
function wptm_columns_content($column_name, $post_ID) {
    if ($column_name === 'featured_image') {
        $image_url = get_the_post_thumbnail_url($post_ID, 'thumbnail');

        if (!empty($image_url)) {
            printf(
                '<img src="%s" alt="%s" style="max-width:150px; height:auto;" />',
                esc_url($image_url),
                esc_attr__('Feature Image', 'text-domain')
            );
        }
    }
}

add_filter('manage_team_manager_posts_columns', 'wptm_columns_head');
add_action('manage_team_manager_posts_custom_column', 'wptm_columns_content', 10, 2);

 /**
 * Show team member id on the admin section
 *
 * @since 1.5
 *
 *
 */

function wptm_posts_columns_id($defaults){
    $defaults['wps_post_id'] = __('ID', 'wp-team-manager');
    return $defaults;
}

function wptm_posts_custom_id_columns( $column_name, $id ){
  if( $column_name === 'wps_post_id' ){
          echo esc_html($id);
    }
}

add_filter('manage_team_manager_posts_columns', 'wptm_posts_columns_id', 5);
add_action('manage_team_manager_posts_custom_column', 'wptm_posts_custom_id_columns', 5, 2);


 /**
 * Get the custom template if is set
 * @todo Need to test out
 * @since 1.0
 */
 
 function wptm_get_template_hierarchy($template) {
    // Sanitize and normalize the template filename
    $template = sanitize_file_name($template);
    $template = rtrim($template, '.php') . '.php';

    // Define potential template locations
    $theme_template  = locate_template("team_template/{$template}");
    $plugin_template = TM_PATH . "/public/templates/{$template}";
    $fallback_template = TM_PATH . '/public/templates/single-team_manager.php';

    // Determine the final template path
    $file = $theme_template ?: $plugin_template;

    // Validate the resolved template path
    $real_file   = realpath($file);
    $theme_dir   = realpath(get_template_directory());
    $plugin_dir  = realpath(TM_PATH . '/public/templates/');

    if (!$real_file || (!str_starts_with($real_file, $theme_dir) && !str_starts_with($real_file, $plugin_dir))) {
        return $fallback_template; // Fallback if the resolved file is not valid
    }

    return apply_filters("team_manager_template_{$template}", $file);
}
 
/**
 * Returns template file
 * This will remove in future version
 * As its depricated, using hook instate
 * @since 1.6.1
 * @todo Need to remove
 */
 
add_filter( 'template_include', 'wptm_template_chooser');

function wptm_template_chooser( $template ) {
    // Early return if not singular or not team_manager post type
    if ( ! is_singular( 'team_manager' ) ) {
        return $template;
    }
    
    return wptm_get_template_hierarchy( 'single-team_manager' );
}

/**
 * Get team groups taxonomy terms as slug => name array
 * 
 * @return array Array of team group terms with slug as key and name as value
 * @since 1.0
 */
function wptm_get_taxonomy_terms() {
    // Attempt to retrieve cached terms
    $cached_terms = wp_cache_get('wptm_team_groups_terms', 'wptm');
    if ($cached_terms !== false) {
        return $cached_terms;
    }

    // Fetch terms with necessary fields
    $terms = get_terms([
        'taxonomy'   => 'team_groups',
        'hide_empty' => false,
        'fields'     => 'all',
    ]);

    // Return empty array if an error occurs
    if (is_wp_error($terms) || empty($terms)) {
        return [];
    }

    // Build an array with slug => name pairs
    $terms_array = [];
    foreach ($terms as $term) {
        $terms_array[$term->slug] = $term->name;
    }

    // Cache the results for an hour
    wp_cache_set('wptm_team_groups_terms', $terms_array, 'wptm', HOUR_IN_SECONDS);

    return $terms_array;
}
