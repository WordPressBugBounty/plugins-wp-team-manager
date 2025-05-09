<?php
namespace DWL\Wtm\Classes;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://http://www.dynamicweblab.com/
 * @since      1.0.0
 *
 * @package    Wp_Team_Manager
 * @subpackage Wp_Team_Manager/public
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Team manager Shortcode generator class
 */
class Shortcodes{

  use \DWL\Wtm\Traits\Singleton;

  protected function init(){

    \add_shortcode( 'team_manager', [$this, 'shortcode_callback'] );
    \add_shortcode( 'dwl_create_team', [$this, 'create_team_callback'] );
  }


  /**
   * Handles the [team_manager] shortcode.
   *
   * @param array $atts Shortcode attributes.
   * @return string Shortcode output.
   */
public function shortcode_callback($atts) {
  ob_start();

  // Ensure security by sanitizing the attributes
  $settings = shortcode_atts(array(
      'orderby'        => 'menu_order',
      'layout'         => 'grid',
      'posts_per_page' => '-1',
      'post__in'       => '',
      'post__not_in'   => '',
      'show_other_info' => 'yes',
      'show_read_more'  => 'no',
      'show_social'     => 'yes',
      'image_style'    => 'boxed',
      'image_size'     => 'thumbnail',
      'category'       => '0',
      'large_column'   => '6',
      'tablet_column'  => '6',
      'mobile_column'  => '12',
      'bg_color'       => 'none',
      'social_color'   => '#4258d6'
  ), $atts);

  // Sanitize Inputs
  $settings = array_map('sanitize_text_field', $settings);

  // Validate Integer Inputs
  $settings['large_column']   = absint($settings['large_column']);
  $settings['tablet_column']  = absint($settings['tablet_column']);
  $settings['mobile_column']  = absint($settings['mobile_column']);

  // Security: Sanitize Array Inputs
  if (!empty($settings['post__in'])) {
      $settings['post__in'] = array_map('absint', explode(',', $settings['post__in']));
  } else {
      $settings['post__in'] = [];
  }

  if (!empty($settings['post__not_in'])) {
      $settings['post__not_in'] = array_map('absint', explode(',', $settings['post__not_in']));
  } else {
      $settings['post__not_in'] = [];
  }

  // Security: Sanitize taxonomy category
  if ($settings['category'] !== '0') {
      $settings['category'] = sanitize_title($settings['category']);
  }

  // Security: Check if links should open in new window
  $link_window = (get_option('tm_link_new_window') === 'True') ? 'target="_blank"' : '';

  // Security: Check if single template is disabled
  $disable_single_template = (get_option('single_team_member_view') === 'True');

  // Generate Unique Wrapper ID
  $shortcode_id = 'dwl-team-wrapper-' . esc_attr(uniqid());

  // Set Order Type
  $asc_desc = in_array($settings['orderby'], ['title', 'menu_order'], true) ? 'ASC' : 'DESC';

  // Get Current Page Number
  $_paged = is_front_page() ? 'page' : 'paged';
  $paged = max(1, absint(get_query_var($_paged)));

  // Wrapper Class
  $wrapper_class = ($settings['layout'] !== 'slider') ? 'wtm-row g-2 g-lg-3' : '';

  // WP_Query Arguments
  $args = [
      'post_type'      => 'team_manager',
      'post_status'    => 'publish',
      'posts_per_page' => $settings['posts_per_page'],
      'paged'          => $paged,
      'orderby'        => $settings['orderby'],
      'order'          => $asc_desc,
  ];

  if ($settings['category'] !== '0') {
      $args['tax_query'] = [[
          'taxonomy' => 'team_groups',
          'field'    => 'slug',
          'terms'    => $settings['category'],
      ]];
  }

  if (!empty($settings['post__in'])) {
      $args['post__in'] = $settings['post__in'];
  }

  if (!empty($settings['post__not_in'])) {
      $args['post__not_in'] = $settings['post__not_in'];
  }

  // Enqueue Required Assets
  wp_enqueue_style('wp-team-font-awesome');

  if ($settings['layout'] === 'slider') {
      wp_enqueue_style('wp-team-slick');
      wp_enqueue_style('wp-team-slick-theme');
      wp_enqueue_script('wp-team-slick');
      wp_enqueue_script('wp-team-script');
  }

  if (get_option('old_team_manager_style')) {
      wp_enqueue_style('wp-old-style');
  } else {
      wp_enqueue_style('wp-team-style');
  }

  // Get Team Data
  $team_data = Helper::get_team_data($args);

  ?>
  <style>
      <?php if (!empty($settings['social_color'])): ?>
      #<?php echo esc_attr($shortcode_id); ?> .team-member-socials a {
          background-color: <?php echo esc_attr($settings['social_color']); ?>;
      }
      #<?php echo esc_attr($shortcode_id); ?> .team-member-other-info .fas {
          color: <?php echo esc_attr($settings['social_color']); ?>;
      }
      <?php endif; ?>

      <?php if (!empty($settings['bg_color']) && $settings['bg_color'] !== 'none'): ?>
      #<?php echo esc_attr($shortcode_id); ?> .team-member-info-content {
          background-color: <?php echo esc_attr($settings['bg_color']); ?>;
      }
      <?php endif; ?>
  </style>

  <div id="<?php echo esc_attr($shortcode_id); ?>" class="dwl-team-wrapper wtm-container-fluid wtm-team-manager-shortcode">
      <div class="dwl-team-wrapper--main <?php echo esc_attr($wrapper_class); ?> dwl-team-layout-<?php echo esc_attr($settings['layout']); ?> dwl-team-image-style-<?php echo esc_attr($settings['image_style']); ?>">
          <?php Helper::show_html_output($settings['layout'], $team_data, $settings); ?>
      </div>
  </div>

  <?php
  return ob_get_clean();
}

/**
 * Generates the HTML output for a team layout based on provided attributes.
 *
 * @param array $atts Shortcode attributes.
 * 
 * @return string The generated HTML content for the team layout.
 *                Returns false if the 'id' attribute is not provided or is empty.
 */

  public function create_team_callback( $atts ) {
    ob_start();
    $default = shortcode_atts( array(
      'id' => '',
    ), $atts );

    if( null == $default['id'] || empty( $default['id'] ) ) {
        return false;
    }
    
    $post_id            = intval( $default['id'] );
    $all_settings       = get_post_meta( $post_id );
    
    $asc_desc           = isset( $all_settings['dwl_team_team_order'][0] ) ? $all_settings['dwl_team_team_order'][0]                                : 'ASC';
    $order_by           = isset( $all_settings['dwl_team_team_order_by'][0] ) ? $all_settings['dwl_team_team_order_by'][0]                          : 'name';
    $display_members    = isset( $all_settings['dwl_team_show_team_member_by_ids'][0] ) ? $all_settings['dwl_team_show_team_member_by_ids'][0]      : '';
    $remove_members     = isset( $all_settings['dwl_team_remove_team_members_by_ids'][0] ) ? $all_settings['dwl_team_remove_team_members_by_ids'][0]: '';
    $layout = isset( $all_settings['dwl_team_layout_option'][0] ) ? $all_settings['dwl_team_layout_option'][0] : 'grid';	
    $show_pagination = isset( $all_settings['dwl_team_show_pagination'][0] ) ? $all_settings['dwl_team_show_pagination'][0] : 'none';	
    
    $wrapper_calss = '';
    $social_size = ( false !== get_option('tm_social_size') ) ? get_option('tm_social_size') : 16;
    $shortcode_id = 'dwl-team-wrapper-'.$post_id;

	  $all_groups_member = isset( $all_settings['dwl_team_group_featured_cats'] ) ? $all_settings['dwl_team_group_featured_cats'][0]: [];
	
    $arrows = isset($all_settings['dwl_team_show_arrow'][0]) ? 'yes' == $all_settings['dwl_team_show_arrow'][0] ? true : false: false;
    $dot_nav = isset($all_settings['dwl_team_dot_nav'][0]) ? 'yes' == $all_settings['dwl_team_dot_nav'][0] ? true : false  : false;
    $autoplay = isset($all_settings['dwl_team_autoplay'][0]) ? 'yes' == $all_settings['dwl_team_autoplay'][0] ? true : false : false;
    $arrow_position = isset($all_settings['dwl_team_arrow_position'][0]) ? $all_settings['dwl_team_arrow_position'][0] : 'side';

    $desktop = isset( $all_settings['dwl_team_desktop'][0] ) ? $all_settings['dwl_team_desktop'][0] : 3;
    $tablet  = isset( $all_settings['dwl_team_tablet'][0] ) ? $all_settings['dwl_team_tablet'][0] : 2;
    $mobile  = isset( $all_settings['dwl_team_mobile'][0] ) ? $all_settings['dwl_team_mobile'][0] : 1;
   
    if( $layout != 'slider' ){
      $wrapper_calss = 'wtm-row g-2 g-lg-3';
    }

    $posts_per_page     = isset( $all_settings['dwl_team_show_total_members'][0] ) ? $all_settings['dwl_team_show_total_members'][0]: -1;
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $_paged   = is_front_page() ? "page" : "paged";
  
    $settings_json = esc_attr(json_encode($all_settings));

    $args = array(
      'post_type'      => 'team_manager',
      'post_status'    => 'publish',
      'posts_per_page' => $posts_per_page,
      'paged'          => $paged,
      'order'          => $asc_desc,
      'orderby'        => $order_by,
    );

    if( ! empty( $all_groups_member  ) ) {
      $feature_groups = unserialize( $all_groups_member );
      $args['tax_query'] = array(
              array(
                'taxonomy' => 'team_groups',
                'field' => 'slug',
                'terms' => $feature_groups,
              )
          );
    }
	
    if( ! empty( $display_members ) ){
      $post__in = explode(",", $display_members);
      $args['post__in'] = $post__in;
    }

    if( ! empty( $remove_members ) ) {
      $post_not_in = explode(",", $remove_members);
      $args['post__not_in'] = $post_not_in;
    }

    $team_data = Helper::get_team_data($args);

    wp_enqueue_style('wp-team-font-awesome');

    if( 'slider' == $layout ){
      wp_enqueue_style([
        'wp-team-slick',
        'wp-team-slick-theme'
      ]);
      wp_enqueue_script([
        'wp-team-slick',
        'wp-team-script'
      ]);
    }

    $old_team_manager_style = get_option( 'old_team_manager_style' );

    if( $old_team_manager_style ) {
      wp_enqueue_style( 'wp-old-style' );
    }else{
      wp_enqueue_style( 'wp-team-style' );
    }
    
    $side_arrow_class = ( $arrow_position == 'side' ) ? 'team-arrow-postion-side' : '';
    $teamplate_style  = isset($all_settings['dwl_team_grid_style_option'][0]) ? $all_settings['dwl_team_grid_style_option'][0] : 'style-1';
    $teamplate_layout = isset($all_settings['dwl_team_layout_option'][0]) ? $all_settings['dwl_team_layout_option'][0] : 'grid';
    $style_type       = isset($all_settings['dwl_team_' . $teamplate_layout . '_style_option'][0]) ? $all_settings['dwl_team_' . $teamplate_layout . '_style_option'][0] : 'style-1';

    $imageStyle =  isset($all_settings['dwl_team_image_style'])? $all_settings['dwl_team_image_style'] [0] : '';

    ?>
  <!-- <div class="dwl-team-search-container">
    <input type="text" id="dwl-team-search-<?php echo esc_attr( $post_id ); ?>" class="dwl-team-search" placeholder="Search team members..." data-post-id="<?php echo esc_attr( $post_id ); ?>">
  </div> -->
      <div id="dwl-team-wrapper-<?php echo esc_attr( $post_id ); ?>" class="dwl-team-wrapper wtm-container-fluid wtm-team-manager-shortcode-generator" data-settings="<?php echo $settings_json; ?>">
        <div class="dwl-team-wrapper--main dwl-team-layout-<?php echo esc_attr( $layout ) ?> dwl-team-wrapper-layout-<?php echo esc_attr( $layout ) ?> <?php echo esc_attr( $wrapper_calss ); ?> dwl-team-<?php echo esc_attr( $teamplate_layout ); ?>-<?php echo esc_attr( $teamplate_style ); ?> dwl-new-team-layout-<?php echo esc_attr( $layout ) ?> dwl-team-image-style-<?php echo esc_attr( $imageStyle );?> wp-team-arrow-<?php echo esc_attr($arrow_position); ?> <?php echo esc_attr($side_arrow_class);?>"
          data-arrows="<?php echo esc_attr($arrows); ?>" 
          data-dots="<?php echo esc_attr($dot_nav); ?>"  
          data-autoplay="<?php echo esc_attr($autoplay); ?>"
          data-desktop="<?php echo esc_attr(  $desktop )?>" 
          data-tablet="<?php echo esc_attr(  $tablet )?>" 
          data-mobile="<?php echo esc_attr(  $mobile )?>" 
        >  
        
          <?php Helper::renderTeamLayout( $teamplate_layout, $team_data, $style_type, $all_settings ); ?>
          
        </div>
        <?php if('none' !== $show_pagination && $layout !== 'slider'): ?>
        <?php echo wp_kses_post(Helper::get_pagination_markup(new \WP_Query($args), $posts_per_page)); ?>
        <?php endif; ?>
      </div>
    <?php

    wp_enqueue_script( ['wp-team-search'] );
    
      return ob_get_clean();
    }


}