<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Add validation for $data array before using it
  if (!is_array($data) || empty($data['posts'])) {
      return;
  }

  if(!empty($data)){
    foreach ($data['posts'] as $key => $teamInfo) {

      $desktop_column = isset($settings['large_column']) ? absint($settings['large_column']) : 4;

      $tablet_column = isset($settings['tablet_column']) ? absint($settings['tablet_column']) : 3;
      
      $mobile_column = isset($settings['mobile_column']) ? absint($settings['mobile_column']) : 1;
      ?>
        <div <?php post_class("team-member-info-wrap ". " wtm-col-lg-" . esc_attr( $desktop_column ) . " wtm-col-md-" . esc_attr( $tablet_column ) . " wtm-col-" . esc_attr( $mobile_column )); ?>>
  
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