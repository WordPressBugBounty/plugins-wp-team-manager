<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$path = TM_PATH . '/public/templates/elementor/grid-style/';

  if(!empty($data)){
    foreach ($data as $key => $teamInfo) {

      ?>
  
        <div <?php post_class("team-member-info-wrap"); ?>>

          <?php include Helper::wtm_locate_template($settings['style_type'].'.php','',$path); ?>

        </div>
  
      <?php

}
}