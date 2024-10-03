<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

  if(!empty($data)){
    foreach ($data as $key => $teamInfo) {

      $desktop_column = isset($settings['large_column']) ? $settings['large_column'] : (
        isset($settings['large_column']) ? $settings['large_column'] : '4');

      $tablet_column = isset($settings['tablet_column']) ? $settings['tablet_column'] : (
        isset($settings['tablet_column']) ? $settings['tablet_column'] : '3');
      
      $mobile_column = isset($settings['mobile_column']) ? $settings['mobile_column'] : (
        isset($settings['tablet_column']) ? $settings['tablet_column'] : '1');

      ?>
  

        <div <?php post_class("team-member-info-wrap ". " wtm-col-lg-" . esc_attr( $desktop_column ) . " wtm-col-md-" . esc_attr( $tablet_column ) . " wtm-col-" . esc_attr( $mobile_column )); ?>>
  
          <?php include Helper::wtm_locate_template('content-memeber.php'); ?>

        </div>
  
      <?php

}
}