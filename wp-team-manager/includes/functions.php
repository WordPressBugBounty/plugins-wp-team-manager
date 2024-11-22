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
function wptm_columns_content( $column_name, $post_ID ) {
    if ( $column_name == 'featured_image' ) {
		$post_featured_image = get_the_post_thumbnail_url($post_ID,'thumbnail');
        if ( $post_featured_image ) {
            echo '<img src="' . esc_url( $post_featured_image ) . '" alt="Feature Image" style="max-width:150px" />';
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
 *
 * @since 1.0
 */
 
function wptm_get_template_hierarchy( $template ) {
 
    // Get the template slug
    $template_slug = rtrim( $template, '.php' );
    $template = $template_slug . '.php';
 
    // Check if a custom template exists in the theme folder, if not, load the plugin template file
    if ( $theme_file = locate_template( array( 'team_template/' . $template ) ) ) {
        $file = $theme_file;
    }
    else {
        $file = TM_PATH . '/public/templates/' . $template;
    }
 
    return apply_filters( 'team_manager_template_' . $template, $file );
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
 
    // Post ID
    $post_id = get_the_ID();
 
    // For all other CPT
    if ( get_post_type( $post_id ) != 'team_manager' ) {
        return $template;
    }
 
    //Use team_manager template
    if ( is_singular('team_manager') ) {
        
        return wptm_get_template_hierarchy( 'single-team_manager' );
    }
 
    return $template;
    
}

/**
 * Get all team_groups for meta value
 * @param $term_slug
 *
 * @return array|bool
 */

function wptm_get_taxonomy_terms( ) {
    $args = array(
        'taxonomy'   => 'team_groups',
        'hide_empty' => false,
    );
    $wc_pcd_terms = get_terms( $args );
    $wc_pcd_terms_array = [];
    foreach ( $wc_pcd_terms as $term ) {
        $wc_pcd_terms_array[ $term->slug ] = $term->name;
    }
    
    return $wc_pcd_terms_array;
}
