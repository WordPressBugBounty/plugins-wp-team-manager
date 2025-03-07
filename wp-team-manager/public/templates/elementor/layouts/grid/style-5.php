<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if(tmwstm_fs()->is_paying_or_trial()){

    if(!empty($data)):
        $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   
        $show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';
        
        foreach ($data as $key => $teamInfo):
            $meta = get_post_meta( $teamInfo->ID );
            $job_title = isset($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
            $short_bio = isset($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
          ?>
            <div <?php post_class("team-member-info-wrap team-grid-style-5-wrap ". esc_attr( $settings['bootstrap_class'] )); ?>>
                <div class="team-member-info-content dwl-team-grid-<?php echo esc_attr( $settings['grid_style_type'] )?>">  
                    <div class="team-member-desc">
                        <?php if("yes" == $settings['show_image']): ?>
                            <header>
                                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                    <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                </a>
                            </header>
                        <?php endif;?>
                        <?php if('yes'== $settings['show_title']  ): ?>
                            <h2 class="team-member-title">
                                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                    <?php echo esc_html( get_the_title($teamInfo->ID) ); ?>
                                </a>
                            </h2>
                        <?php endif;?>
                        <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
                            <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
                        <?php endif;?>
                        <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                            <div class="team-member-social-layout-5">
                                <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
          <?php
        endforeach;
    endif;
}

?>