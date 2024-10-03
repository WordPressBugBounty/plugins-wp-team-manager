<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)){
  foreach ($data as $key => $teamInfo) {

    $desktop_column = isset($settings['dwl_team_desktop'] [0]) ? $settings['dwl_team_desktop'] [0] : (
      isset($settings['dwl_team_desktop'] [0]) ? $settings['dwl_team_desktop'] [0] : '4');

    $tablet_column = isset($settings['dwl_team_tablet'] [0]) ? $settings['dwl_team_tablet'] [0] : (
      isset($settings['dwl_team_tablet'] [0]) ? $settings['dwl_team_tablet'] [0] : '3');
    
    $mobile_column = isset($settings['dwl_team_mobile'] [0]) ? $settings['dwl_team_mobile'] [0] : (
      isset($settings['dwl_team_mobile'] [0]) ? $settings['dwl_team_mobile'] [0] : '12');

    $bootstrap_class = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);

    ?>

      <div <?php post_class("team-member-info-wrap ". esc_attr( $bootstrap_class )); ?>>

        <?php include Helper::wtm_locate_template('team-content-memeber-grid.php'); ?>

      </div>

    <?php

  }
}