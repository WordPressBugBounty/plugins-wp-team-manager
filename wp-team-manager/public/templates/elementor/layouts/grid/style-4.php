<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)):

    $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   

    $show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';

    $desktop_column = isset($settings['columns_desktop']) ? $settings['columns_desktop'] : (
        isset($settings['columns']) ? $settings['columns'] : '4');

    $tablet_column = isset($settings['columns_tablet']) ? $settings['columns_tablet'] : 3;
    
    $mobile_column = isset($settings['columns_mobile']) ? $settings['columns_mobile'] : 1;

    $bootstrap_class = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);
    
    foreach ($data as $key => $teamInfo):

        $job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
        $short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );

      ?>
        <div <?php post_class("team-member-info-wrap ". esc_attr( $bootstrap_class )); ?>>

            <div class="team-member-info-content"> 
                <header>
                    <?php if("yes" == $settings['show_image']): ?>
                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                            <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                        </a>
                    <?php endif;?>
                </header>
                <div class="team-member-title-info">
                    <?php if('yes'== $settings['show_title']  ): ?>
                        <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                    <?php endif;?>
                    <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
                        <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
                    <?php endif;?>
                </div>
                <div class="team-member-desc">
                    <?php if('yes'== $settings['show_title']  ): ?>
                        <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                    <?php endif;?>
                    <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
                        <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
                    <?php endif;?>

                    <?php if( 'yes' === $show_shortBio ) : ?>
                    <div class="team-short-bio">
                        <?php if( !empty( $short_bio ) && 'yes'== $settings['team_show_short_bio'] ): ?>
                            <?php echo esc_html( $short_bio ); ?>
                        <?php else: ?>
                            <?php echo esc_html( wp_trim_words( get_the_content(null, false, $teamInfo->ID), 40, '...' ) ); ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info']) : ?>
                        <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
                    <?php endif; ?>

                    <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                        <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                    <?php endif; ?>
                    
                </div>
            </div>
            
        </div>
  
      <?php
        endforeach;
    endif;
?>