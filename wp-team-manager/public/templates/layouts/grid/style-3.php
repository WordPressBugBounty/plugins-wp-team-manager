<?php

use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$image_size = isset( $settings['dwl_team_select_image_size'][0] ) ? $settings['dwl_team_select_image_size'][0] : 'thumbnail';   
$show_other_info = isset($settings['dwl_team_team_show_other_info']) ? true : false;
$show_social = isset($settings['dwl_team_team_show_social']) ? true : false;
$show_read_more = isset($settings['dwl_team_team_show_read_more']) ? true : false;

$tm_single_fields = get_option('tm_single_fields') ? get_option('tm_single_fields') : ['tm_jtitle'];


foreach($data as $key => $teamInfo) { 
    $job_title = get_post_meta($teamInfo->ID, 'tm_jtitle', true);
    ?>
        <div class="team-member-info-content">  
            <div class="team-member-grid-style-two">
                <a class="grid-team-inner">
                    <?php echo wp_kses_post( Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow') ); ?>
                    <div class="team-member-grid-content-overlay">
                        <div class="team-member-grid-content">
                            <div class="team-member-grid-info">
                                <h2 class="team-member-title"><?php echo esc_html($teamInfo->post_title); ?></h2>
                                <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields)): ?>
                                    <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
                                <?php endif; ?>

                                <div class="team-member-grid-arrow">
                                    <img decoding="async" src="https://dev.uniteklearning.com/wp-content/plugins/unitek-team-manager/public/assets/img/temearrow.png" alt="teamarrow">
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

    <?php 
} 
?>

