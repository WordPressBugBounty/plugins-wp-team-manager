<?php
 
    use DWL\Wtm\Classes\Helper;
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
    $image_size = isset( $settings['dwl_team_select_image_size'] [0] ) ? $settings['dwl_team_select_image_size'] [0] : 'thumbnail';   
    $show_other_info         = isset($settings['dwl_team_team_show_other_info']) ? true : false;
    $show_social             = isset($settings['dwl_team_team_show_social']) ? true : false;
    $show_read_more          = isset($settings['dwl_team_team_show_read_more']) ? false : true;

    $tm_single_fields = get_option('tm_single_fields') ? get_option('tm_single_fields') : ['tm_jtitle'];
   
    $disable_single_template = ( false !== get_option('single_team_member_view')  && 'True' == get_option('single_team_member_view') ) ? true : false;

    $desktop_column = isset($settings['dwl_team_desktop'] [0]) ? $settings['dwl_team_desktop'] [0] : (
        isset($settings['dwl_team_desktop'] [0]) ? $settings['dwl_team_desktop'] [0] : '4');

    $tablet_column = isset($settings['dwl_team_tablet'] [0]) ? $settings['dwl_team_tablet'] [0] : (
        isset($settings['dwl_team_tablet'] [0]) ? $settings['dwl_team_tablet'] [0] : '3');
    
    $mobile_column = isset($settings['dwl_team_mobile'] [0]) ? $settings['dwl_team_mobile'] [0] : (
        isset($settings['dwl_team_mobile'] [0]) ? $settings['dwl_team_mobile'] [0] : '12');


    $bootstrap_class = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);

foreach($data as $key => $teamInfo) {  

    $job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
    $short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
    
    ?>
        <div <?php post_class("team-member-info-wrap ". esc_attr( $bootstrap_class )); ?>>
            <div class="team-member-info-content">  
                <div class="team-member-grid-style-two">
                    <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="grid-team-inner">
                        <?php echo wp_kses_post( Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow') ); ?>
                        <div class="team-member-grid-content-overlay"></div>
                        <div class="team-member-grid-content">
                            <div class="team-member-grid-info">
                                <h2 class="team-member-title"><?php echo esc_html($teamInfo->post_title); ?></h2>
                                <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields)): ?>
                                    <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
                                <?php endif; ?>

                                <div class="team-member-grid-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

<?php }