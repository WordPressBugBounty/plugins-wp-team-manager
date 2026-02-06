<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Add validation for $data array before using it
  if (!is_array($data) || empty($data['posts'])) {
      return;
  }

  // Get accessibility settings
  $a11y_enabled = get_option('tm_a11y_enable', 1);
  $list_roles = get_option('tm_a11y_list_roles', 1);
  $region_label = get_option('tm_a11y_region_label', __('Team members', 'wp-team-manager'));

  // Render taxonomy filters if enabled
  echo Helper::render_taxonomy_filters();
  
  if(!empty($data)){
    foreach ($data['posts'] as $key => $teamInfo) {

      $desktop_column = isset($settings['large_column']) ? absint($settings['large_column']) : 4;

      $tablet_column = isset($settings['tablet_column']) ? absint($settings['tablet_column']) : 3;
      
      $mobile_column = isset($settings['mobile_column']) ? absint($settings['mobile_column']) : 1;
      
      // Prepare accessibility attributes
      $aria_attrs = '';
      $role_attr = '';
      
      if ($a11y_enabled == 1 && $list_roles == 1) {
          $role_attr = ' role="listitem"';
          $aria_attrs = ' aria-label="' . esc_attr(sprintf(__('Team member: %s', 'wp-team-manager'), get_the_title($teamInfo->ID))) . '"';
      }
      $taxonomy_classes = Helper::get_team_taxonomy_classes($teamInfo->ID);
      ?>
        <div <?php post_class("team-member-info-wrap ". "m-0 p-2 wtm-col-lg-" . esc_attr( $desktop_column ) . " wtm-col-md-" . esc_attr( $tablet_column ) . " wtm-col-" . esc_attr( $mobile_column ) . ' ' . esc_attr($taxonomy_classes)); ?><?php echo $role_attr . $aria_attrs; ?>>
  
          <?php  
            $template_file = Helper::wtm_locate_template('content-memeber.php');
            if(file_exists($template_file) && validate_file($template_file) === 0) {
              include $template_file;
            }
          ?>
        </div>
	    <?php
    }
  }