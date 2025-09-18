<?php
namespace DWL\Wtm\Classes;
use DWL\Wtm\Classes\Helper;
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

  // Whitelist risky attributes to prevent bad values reaching WP_Query or output
  // Allowed values are opinionated; adjust if you add new templates/sizes
  $allowed_orderby   = [ 'menu_order', 'title', 'date', 'modified', 'rand' ];
  $allowed_layouts   = [ 'grid', 'list', 'slider' ];
  $allowed_img_style = [ 'boxed', 'rounded', 'circle', 'card' ];
 
  if ( ! in_array( $settings['orderby'], $allowed_orderby, true ) ) {
      $settings['orderby'] = 'menu_order';
  }
  if ( ! in_array( $settings['layout'], $allowed_layouts, true ) ) {
      $settings['layout'] = 'grid';
  }
  if ( ! in_array( $settings['image_style'], $allowed_img_style, true ) ) {
      $settings['image_style'] = 'boxed';
  }
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
  $asc_desc = in_array( $settings['orderby'], [ 'title', 'menu_order' ], true ) ? 'ASC' : 'DESC';
  $is_random = ( 'rand' === $settings['orderby'] );

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
      // Only set order if not random to avoid unnecessary SQL
      'order'          => $is_random ? '' : $asc_desc,
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

    $post_id = absint( $default['id'] );
    if ( ! $post_id ) {
        return '';
    }
    
    $all_settings       = get_post_meta( $post_id );

    // Read settings via centralized helper (post meta only; no shortcode overrides)
    $layout          = Helper::get_team_setting( $post_id, 'dwl_team_layout_option', 'grid', 'string' );
    $pagination_type = Helper::get_team_setting( $post_id, 'dwl_team_pagination_type', 'none', 'string' );


    // Filter UI settings (shortcode meta)
    $filter_enable   = Helper::get_team_setting( $post_id, 'dwl_team_filter_enable', 'false', 'string' );
    $filter_taxonomy = Helper::get_team_setting( $post_id, 'dwl_team_filter_taxonomy', 'team_groups', 'string' );
    // Whitelist allowed taxonomies (avoid arbitrary input)
    $allowed_tax = apply_filters( 'wtm_allowed_filter_taxonomies', [ 'team_groups', 'team_department', 'team_genders', 'team_designation' ] );
    if ( ! in_array( $filter_taxonomy, $allowed_tax, true ) ) {
        $filter_taxonomy = 'team_groups';
    }

    // Legacy back-compat: if old `show_pagination` is enabled and layout is not slider, force numbers
    $show_pagination_legacy = Helper::get_team_setting( $post_id, 'dwl_team_show_pagination', '', 'string' );
    if ( 'slider' !== $layout && ( '1' === $show_pagination_legacy || 'yes' === $show_pagination_legacy ) ) {
        $pagination_type = 'numbers';
    }

    $asc_desc   = strtoupper( Helper::get_team_setting( $post_id, 'dwl_team_team_order', 'ASC', 'string' ) );
    $asc_desc   = in_array( $asc_desc, [ 'ASC', 'DESC' ], true ) ? $asc_desc : 'ASC';

    $order_by   = Helper::get_team_setting( $post_id, 'dwl_team_team_order_by', 'title', 'string' );
    $allowed_orderby = [ 'menu_order', 'title', 'date', 'modified', 'rand' ];
    if ( ! in_array( $order_by, $allowed_orderby, true ) ) {
        $order_by = 'title';
    }
    $is_random = ( 'rand' === $order_by );

    $display_members = Helper::get_team_setting( $post_id, 'dwl_team_show_team_member_by_ids', [], 'csv_ints' );
    $remove_members  = Helper::get_team_setting( $post_id, 'dwl_team_remove_team_members_by_ids', [], 'csv_ints' );

    $arrows         = Helper::get_team_setting( $post_id, 'dwl_team_show_arrow', false, 'bool' );
    $dot_nav        = Helper::get_team_setting( $post_id, 'dwl_team_dot_nav', false, 'bool' );
    $autoplay       = Helper::get_team_setting( $post_id, 'dwl_team_autoplay', false, 'bool' );
    $arrow_position = Helper::get_team_setting( $post_id, 'dwl_team_arrow_position', 'side', 'string' );

    $desktop = Helper::get_team_setting( $post_id, 'dwl_team_desktop', 3, 'int' );
    $tablet  = Helper::get_team_setting( $post_id, 'dwl_team_tablet', 2, 'int' );
    $mobile  = Helper::get_team_setting( $post_id, 'dwl_team_mobile', 1, 'int' );

    $posts_per_page = Helper::get_team_setting( $post_id, 'dwl_team_show_total_members', -1, 'int' );
    if ( 0 === $posts_per_page ) { $posts_per_page = -1; }

    $all_groups_member = Helper::get_team_setting( $post_id, 'dwl_team_group_featured_cats', [], 'array' );

    $wrapper_calss = '';
    $social_size = ( false !== get_option('tm_social_size') ) ? get_option('tm_social_size') : 16;
    $shortcode_id = 'dwl-team-wrapper-'.$post_id;

    // New Query & Filtering fields via Helper
    $tax_relation    = Helper::get_team_setting( $post_id, 'dwl_team_tax_relation', 'OR', 'string' );
    $tax_relation    = in_array( $tax_relation, [ 'AND', 'OR' ], true ) ? $tax_relation : 'OR';
    $tax_include_ids = Helper::get_team_setting( $post_id, 'dwl_team_tax_include', [], 'csv_ints' );
    $tax_exclude_ids = Helper::get_team_setting( $post_id, 'dwl_team_tax_exclude', [], 'csv_ints' );
    $keyword         = Helper::get_team_setting( $post_id, 'dwl_team_keyword', '', 'string' );
    $date_from       = Helper::get_team_setting( $post_id, 'dwl_team_date_from', '', 'string' );
    $date_to         = Helper::get_team_setting( $post_id, 'dwl_team_date_to', '', 'string' );

    // Validate date strings (YYYY-MM-DD); ignore if invalid
    $date_from_valid = ( $date_from && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_from ) ) ? $date_from : '';
    $date_to_valid   = ( $date_to && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_to ) ) ? $date_to : '';

    if( $layout !== 'slider' ){
      $wrapper_calss = 'wtm-row g-2 g-lg-3';
    }

    $_paged = is_front_page() ? 'page' : 'paged';
    $paged  = max( 1, absint( get_query_var( $_paged ) ) );
  
    // Build a frontend-safe settings payload: strip style/theming & internal keys
    $frontend_settings = [];
    foreach ( (array) $all_settings as $key => $val ) {
        // Skip theming keys and WP internal edit markers
        if ( 0 === strpos( $key, 'dwl_team_theme_' ) || in_array( $key, [ '_edit_lock', '_edit_last' ], true ) ) {
            continue;
        }
        // Many meta values are arrays; reduce to first scalar value
        if ( is_array( $val ) ) {
            $frontend_settings[ $key ] = count( $val ) ? $val[0] : '';
        } else {
            $frontend_settings[ $key ] = $val;
        }
    }

    // JSON for data-settings (escape for attribute context)
    $settings_json = esc_attr( wp_json_encode( $frontend_settings ) );

    // --- Accessibility & SEO (global) ---
    $a11y_enable       = (int) get_option( 'tm_a11y_enable', 1 );
    $a11y_region_label = (string) get_option( 'tm_a11y_region_label', __( 'Team members', 'wp-team-manager' ) );
    $a11y_focus_ring   = (int) get_option( 'tm_a11y_focus_ring', 1 );
    $a11y_list_roles   = (int) get_option( 'tm_a11y_list_roles', 1 );

    // Pagination mode logic
    $pagination_mode = ( 'slider' === $layout ) ? 'none' : $pagination_type;
    $is_numbers      = ( 'numbers' === $pagination_mode );
    $is_ajax_mode    = in_array( $pagination_mode, array( 'ajax', 'infinite' ), true );
    $paginate        = ( $is_numbers || $is_ajax_mode );

    $args = [
      'post_type'      => 'team_manager',
      'post_status'    => 'publish',
      'posts_per_page' => $posts_per_page,
      'orderby'        => $order_by,
      // numbers: need counts for links; ajax/infinite: save DB overhead on first paint
      'no_found_rows'  => $is_numbers ? false : true,
    ];

    if ( $is_numbers ) {
      $args['paged'] = $paged;
    }

    if ( ! $is_random ) {
      $args['order'] = $asc_desc;
    }

    // Build taxonomy filters combining: selected slugs, include IDs, exclude IDs
    $tax_parts = [];

    if ( ! empty( $all_groups_member ) ) {
      $feature_groups_raw = maybe_unserialize( $all_groups_member );
      $feature_groups = is_array( $feature_groups_raw ) ? array_filter( array_map( 'sanitize_title', $feature_groups_raw ) ) : [];
      if ( $feature_groups ) {
        $tax_parts[] = [
          'taxonomy' => 'team_groups',
          'field'    => 'slug',
          'terms'    => $feature_groups,
          'operator' => 'IN',
        ];
      }
    }

    if ( $tax_include_ids ) {
      $tax_parts[] = [
        'taxonomy' => 'team_groups',
        'field'    => 'term_id',
        'terms'    => $tax_include_ids,
        'operator' => 'IN',
      ];
    }

    if ( $tax_exclude_ids ) {
      $tax_parts[] = [
        'taxonomy' => 'team_groups',
        'field'    => 'term_id',
        'terms'    => $tax_exclude_ids,
        'operator' => 'NOT IN',
      ];
    }

    if ( $tax_parts ) {
      $args['tax_query'] = array_merge( [ 'relation' => $tax_relation ], $tax_parts );
    }

    if ( ! empty( $display_members ) ) {
      $args['post__in'] = $display_members;
    }

    if ( ! empty( $remove_members ) ) {
      $args['post__not_in'] = $remove_members;
    }

    if ( $keyword ) {
      $args['s'] = $keyword;
    }

    if ( $date_from_valid || $date_to_valid ) {
      $date_query = [ 'inclusive' => true ];
      if ( $date_from_valid ) { $date_query['after']  = $date_from_valid; }
      if ( $date_to_valid )   { $date_query['before'] = $date_to_valid; }
      $args['date_query'] = [ $date_query ];
    }

    if ( isset( $args['tax_query'] ) && ! $args['tax_query'] ) {
      unset( $args['tax_query'] );
    }

    $team_data = Helper::get_team_data($args);

    wp_enqueue_style('wp-team-font-awesome');

    if ( 'slider' === $layout ) {
      wp_enqueue_style( 'wp-team-slick' );
      wp_enqueue_style( 'wp-team-slick-theme' );
      wp_enqueue_script( 'wp-team-slick' );
      wp_enqueue_script( 'wp-team-script' );
    }

    $old_team_manager_style = get_option( 'old_team_manager_style' );

    if( $old_team_manager_style ) {
      wp_enqueue_style( 'wp-old-style' );
    }else{
      wp_enqueue_style( 'wp-team-style' );
    }
    
    $side_arrow_class = ( $arrow_position === 'side' ) ? 'team-arrow-postion-side' : '';
    $teamplate_layout = isset($all_settings['dwl_team_layout_option'][0]) ? $all_settings['dwl_team_layout_option'][0] : 'grid';
    $style_option_key = 'dwl_team_' . $teamplate_layout . '_style_option';
    $teamplate_style  = isset($all_settings[$style_option_key][0]) ? $all_settings[$style_option_key][0] : 'style-1';
    $style_type       = isset($all_settings['dwl_team_' . $teamplate_layout . '_style_option'][0]) ? $all_settings['dwl_team_' . $teamplate_layout . '_style_option'][0] : 'style-1';

    $imageStyle = Helper::get_team_setting( $post_id, 'dwl_team_image_style', '', 'string' );

    // --- Styling & Theming (scoped to this wrapper) ---
    $theme_preset       = Helper::get_team_setting( $post_id, 'dwl_team_theme_preset', 'default', 'string' );
    $theme_primary      = Helper::get_team_setting( $post_id, 'dwl_team_theme_primary_color', '', 'string' );
    $theme_background   = Helper::get_team_setting( $post_id, 'dwl_team_team_background_color', '', 'string' );
    $theme_card_bg      = Helper::get_team_setting( $post_id, 'dwl_team_theme_card_bg', '', 'string' );
    $theme_title_color  = Helper::get_team_setting( $post_id, 'dwl_team_theme_title_color', '', 'string' );
    $theme_text_color   = Helper::get_team_setting( $post_id, 'dwl_team_theme_text_color', '', 'string' );
    $social_icon_color  = Helper::get_team_setting( $post_id, 'dwl_team_social_icon_color', '', 'string' );
    $theme_radius       = Helper::get_team_setting( $post_id, 'dwl_team_theme_border_radius', '', 'string' );
    $theme_gap          = Helper::get_team_setting( $post_id, 'dwl_team_theme_gap', '', 'string' );
    $theme_shadow_key   = Helper::get_team_setting( $post_id, 'dwl_team_theme_shadow', 'sm', 'string' );
    $theme_dark_mode    = Helper::get_team_setting( $post_id, 'dwl_team_theme_dark_mode', false, 'bool' );

    // Sanitize color values
    $theme_primary     = function_exists('sanitize_hex_color') ? ( $theme_primary ? sanitize_hex_color( $theme_primary ) : '' ) : $theme_primary;
    $theme_background     = function_exists('sanitize_hex_color') ? ( $theme_background ? sanitize_hex_color( $theme_background ) : '' ) : $theme_background;
    $theme_card_bg     = function_exists('sanitize_hex_color') ? ( $theme_card_bg ? sanitize_hex_color( $theme_card_bg ) : '' ) : $theme_card_bg;
    $theme_title_color = function_exists('sanitize_hex_color') ? ( $theme_title_color ? sanitize_hex_color( $theme_title_color ) : '' ) : $theme_title_color;
    $theme_text_color  = function_exists('sanitize_hex_color') ? ( $theme_text_color ? sanitize_hex_color( $theme_text_color ) : '' ) : $theme_text_color;
    $social_icon_color = function_exists('sanitize_hex_color') ? ( $social_icon_color ? sanitize_hex_color( $social_icon_color ) : '' ) : $social_icon_color;
   
    // Validate radius & gap CSS units (px|rem|em|%)
    $unit_ok = '/^\d+(?:\.\d+)?(?:px|rem|em|%)$/';
    if ( $theme_radius && ! preg_match( $unit_ok, $theme_radius ) ) { $theme_radius = ''; }
    if ( $theme_gap && ! preg_match( $unit_ok, $theme_gap ) ) { $theme_gap = ''; }

    // Map shadow presets
    $shadow_map = [
      'none' => 'none',
      'sm'   => '0 1px 2px rgba(0,0,0,.06), 0 1px 1px rgba(0,0,0,.05)',
      'md'   => '0 6px 12px rgba(0,0,0,.10)',
      'lg'   => '0 12px 24px rgba(0,0,0,.18)'
    ];
    $theme_shadow = isset( $shadow_map[ $theme_shadow_key ] ) ? $shadow_map[ $theme_shadow_key ] : $shadow_map['sm'];

    // Compose scoped CSS for this instance
    $style_handle = $old_team_manager_style ? 'wp-old-style' : 'wp-team-style';
    

    $scoped_css = '';

    $scoped_id    = '#'. esc_attr( $shortcode_id );

    // Preset: minimal / soft-card / glass (optional quick styles)
    if ( 'minimal' === $theme_preset ) {
      $scoped_css .= "$scoped_id .team-member-info-content{border:1px solid rgba(0,0,0,.06);box-shadow:none;}\n";
    } elseif ( 'soft-card' === $theme_preset ) {
      $scoped_css .= "$scoped_id .team-member-info-content{box-shadow:0 10px 30px rgba(0,0,0,.08);}\n";
    } elseif ( 'glass' === $theme_preset ) {
      $scoped_css .= "$scoped_id .team-member-info-content{background:rgba(255,255,255,.08);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.12);}\n";
    }
    if ( $theme_background ) {
      $scoped_css .= "$scoped_id .dwl-team-wrapper--main{background-color:{$theme_background};}\n";
    }
    if ( $theme_card_bg ) {
      $scoped_css .= "$scoped_id .team-member-info-content{background-color:{$theme_card_bg};}\n";
    }
    if ( $theme_radius ) {
      $scoped_css .= "$scoped_id .team-member-info-content, $scoped_id .team-member-info-content header img{border-radius:{$theme_radius};}\n";
    }
    if ( $theme_shadow_key ) {
      $scoped_css .= "$scoped_id .team-member-info-content{box-shadow:{$theme_shadow};}\n";
    }
    if ( $theme_title_color ) {
      $scoped_css .= "$scoped_id .team-member-title{color:{$theme_title_color};}\n";
    }
    if ( $theme_text_color ) {
      $scoped_css .= "$scoped_id .team-short-bio, $scoped_id .team-member-other-info, $scoped_id .team-member-info, $scoped_id .team-member-other-info a{color:{$theme_text_color};}\n";
    }
    if ( $theme_primary ) {
      $scoped_css .= "$scoped_id .team-member-socials a, $scoped_id a.wtm-read-more{background-color:{$theme_primary};}\n";
    }
       if ( $social_icon_color ) {
      $scoped_css .= "$scoped_id .team-member-socials a, $scoped_id a.wtm-read-more{color:{$social_icon_color};}\n";
      
    }
    // if ( $theme_gap ) {
    //   $scoped_css .= "$scoped_id .wtm-row, $scoped_id .dwl-team-cards{gap:{$theme_gap};}\n";
    // }
    if ( $theme_gap ) {
        $scoped_css .= "$scoped_id .team-member-info-wrap{";
        $scoped_css .= "padding-left: calc({$theme_gap} / 2) !important;";
        $scoped_css .= "padding-right: calc({$theme_gap} / 2) !important;";
        $scoped_css .= "}\n";
    }
  
    if ( $theme_dark_mode ) {
      $scoped_css .= "$scoped_id{color-scheme:dark;} $scoped_id .team-member-info-content{background-color:rgba(255,255,255,.06);} $scoped_id .team-member-title{color:#fff;}";
    }
    
    if ( ! empty( $scoped_css ) ) {
      $enqueued = false;
      $handle_or_false = \DWL\Wtm\Classes\Helper::enqueueGeneratedCss( $post_id, $scoped_css );
      $enqueued = (bool) $handle_or_false;

      if ( ! $enqueued ) {
          // Fallback inline to guarantee styles even if filesystem is not writable
          echo '<style type="text/css" id="wtm-inline-' . esc_attr( $shortcode_id ) . '">' . $scoped_css . '</style>';
      }
    }

    // Add a class if dark mode is enabled
    $dark_mode_class = $theme_dark_mode ? 'wtm-dark' : '';

    // Visible focus ring for keyboard users (scoped to this instance)
    if ( $a11y_enable && $a11y_focus_ring ) {
        echo '<style id="wtm-focus-'. esc_attr( $shortcode_id ) .'">'
           . '#'. esc_attr( $shortcode_id ) .' a:focus, '
           . '#'. esc_attr( $shortcode_id ) .' button:focus, '
           . '#'. esc_attr( $shortcode_id ) .' [tabindex]:focus { outline: 2px solid #2271b1; outline-offset: 2px; box-shadow: none; }'
           . '</style>';
    }

    ?>
      <div id="<?php echo esc_attr( $shortcode_id ); ?>" class="dwl-team-wrapper wtm-container-fluid wtm-team-manager-shortcode-generator <?php echo esc_attr( $dark_mode_class ); ?>" <?php echo $a11y_enable ? ' role="region" aria-label="' . esc_attr( $a11y_region_label ) . '"' : ''; ?> data-settings="<?php echo $settings_json; ?>"
        data-pagination="<?php echo esc_attr( $pagination_mode ); ?>"
        data-query-paged="<?php echo esc_attr( $paged ); ?>"
        data-query-ppp="<?php echo esc_attr( $posts_per_page ); ?>"
        data-filter-taxonomy="<?php echo esc_attr( $filter_taxonomy ); ?>"
      >
      <?php if ( $filter_enable == 'on' && in_array( $pagination_mode, [ 'ajax', 'infinite' ], true ) ) : ?>
          <?php 
            $terms = get_terms( [ 'taxonomy' => $filter_taxonomy, 'hide_empty' => true ] );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) :
          ?>
            <div class="dwl-team-filters" role="tablist" aria-label="<?php echo esc_attr( ucfirst( str_replace('_',' ', $filter_taxonomy ) ) ); ?>">
              <button type="button" class="button dwl-team-filter is-active" data-term="" data-taxonomy="<?php echo esc_attr( $filter_taxonomy ); ?>">
                <?php esc_html_e( 'All', 'wp-team-manager' ); ?>
              </button>
              <?php foreach ( $terms as $t ) : ?>
                <button type="button" class="button dwl-team-filter" data-term="<?php echo esc_attr( $t->slug ); ?>" data-taxonomy="<?php echo esc_attr( $filter_taxonomy ); ?>">
                  <?php echo esc_html( $t->name ); ?>
                </button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php
          $gap = isset($gap) ? $gap : '';
          $columns = isset($columns) ? $columns : '';
        ?>

        <div class="dwl-team-wrapper--main dwl-team-layout-<?php echo esc_attr( $layout ) ?> dwl-team-wrapper-layout-<?php echo esc_attr( $layout ) ?> <?php echo esc_attr( $wrapper_calss ); ?> dwl-team-<?php echo esc_attr( $teamplate_layout ); ?>-<?php echo esc_attr( $teamplate_style ); ?> dwl-new-team-layout-<?php echo esc_attr( $layout ) ?> dwl-team-image-style-<?php echo esc_attr( $imageStyle );?> wp-team-arrow-<?php echo esc_attr($arrow_position); ?> <?php echo esc_attr($side_arrow_class);?> dwl-team-cards"
          data-arrows="<?php echo esc_attr($arrows); ?>" 
          data-dots="<?php echo esc_attr($dot_nav); ?>"  
          data-autoplay="<?php echo esc_attr($autoplay); ?>"
          data-desktop="<?php echo esc_attr(  $desktop )?>" 
          data-tablet="<?php echo esc_attr(  $tablet )?>" 
          data-mobile="<?php echo esc_attr(  $mobile )?>"
          style="<?php echo $gap !== '' ? '--wtm-gap:' . esc_attr($gap) . 'px;' : ''; ?>
          <?php echo $columns !== '' ? '--wtm-columns:' . esc_attr($columns) . ';' : ''; ?>"
          data-columns="<?php echo esc_attr($columns); ?>"
        >  

          <?php Helper::renderTeamLayout( $teamplate_layout, $team_data, $style_type, $all_settings ); 
      
          //var_dump($all_settings);
          ?>
          
        </div>
        <?php if ( $paginate ) : ?>
          <?php if ( $is_numbers ) : ?>
            <?php
              $args_for_pagination = $args;
              $args_for_pagination['no_found_rows'] = false; // ensure proper max_num_pages
              $query_for_pagination = new \WP_Query( $args_for_pagination );
              echo wp_kses_post( Helper::get_pagination_markup( $query_for_pagination, $posts_per_page ) );
            ?>
          <?php elseif ( $is_ajax_mode ) : ?>
            <div class="dwl-team-pagination-ajax" data-mode="<?php echo esc_attr( $pagination_mode ); ?>" data-next-page="<?php echo esc_attr( $paged + 1 ); ?>">
              <?php if ( 'ajax' === $pagination_mode ) : ?>
                <button type="button" class="button dwl-team-load-more" aria-live="polite"><?php echo esc_html__( 'Load More', 'wp-team-manager' ); ?></button>
              <?php else : ?>
                <div class="dwl-team-infinite-sentinel" aria-hidden="true"></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php

    // Pro JSON-LD via helper (outputs when Pro & globally enabled)
    if ( ! empty( $team_data['posts'] ) ) {
        if ( class_exists( '\\DWL_Wtm_Pro_Helper' ) ) {
            \DWL_Wtm_Pro_Helper::output_jsonld_for_listing( $team_data['posts'] );
        }
    }
    
    wp_enqueue_script( 'wp-team-search' );

    // Enqueue pagination CSS for both numbered and ajax/infinite modes
    if ( $is_numbers ) {
        wp_enqueue_style( 'wp-team-pagination-style' );
    }
    if ( $is_ajax_mode ) {
        // Shared pagination CSS
        wp_enqueue_style( 'wp-team-pagination-style' );

        // AJAX/Infinite JS
        // Enqueue pagination script and provide AJAX config robustly
        wp_enqueue_script( 'wp-team-ajax-pagination' );


    }
    
    if ( $a11y_enable && $a11y_list_roles ) {
      echo '<script>
      (function(){
        var root = document.getElementById(' . wp_json_encode( $shortcode_id ) . ');
        if (!root) return;

        // try to find a list container inside the wrapper
        var list =
          root.querySelector(".dwl-team-row") ||
          root.querySelector(".dwl-isotope-grid") ||
          root.querySelector(".wtm-row") ||
          root; // fallback to wrapper

        if (list && !list.hasAttribute("role")) {
          list.setAttribute("role","list");
        }

        var items = root.querySelectorAll(".team-member-info-wrap");
        items.forEach(function(el){
          if (!el.hasAttribute("role")) {
            el.setAttribute("role","listitem");
          }
          // Optional: use the member title as aria-label for the card
          var title = el.querySelector(".team-member-title");
          if (title && !el.hasAttribute("aria-label")) {
            var txt = title.textContent || title.innerText || "";
            if (txt.trim()) {
              el.setAttribute("aria-label", txt.trim());
            }
          }
        });

        // Ensure images have alt text: fallback to member name if alt is empty
        var imgs = root.querySelectorAll("img");
        imgs.forEach(function(img){
          if (img && (!img.hasAttribute("alt") || img.getAttribute("alt") === "")) {
            // try to find the nearest title text
            var card = img.closest(".team-member-info-wrap");
            var title = card ? card.querySelector(".team-member-title") : null;
            var txt = title ? (title.textContent || title.innerText || "").trim() : "";
            if (txt) {
              img.setAttribute("alt", txt);
            } else {
              img.setAttribute("alt", "");
            }
          }
        });
      })();
      </script>';
  }


    return ob_get_clean();
  }


}