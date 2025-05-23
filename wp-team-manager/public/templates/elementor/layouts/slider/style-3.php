<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if( tmwstm_fs()->is_paying_or_trial() ):
    if(!empty($data)):
        $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   
    
        $show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';
        $team_arrow_positon = isset( $settings['team_arrow_position'] ) && ($settings['team_arrow_position'] === 'side') ? 'team-arrow-postion-side' : '';
        ?>
    
        <div class="team-member-slider-wrap <?php echo esc_attr( $team_arrow_positon ); ?>" data-slider_settings="<?php echo esc_attr( json_encode( $settings['slider_settings'] ) ); ?>">
        <?php
        foreach ($data as $key => $teamInfo):
    
            $meta = get_post_meta( $teamInfo->ID );
            $job_title = isset($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
            $short_bio = isset($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
          ?>
        <div <?php post_class("team-member-info-wrap"); ?>>
            <div class="team-member-info-content"> 
                <?php if("yes" == $settings['show_image']): ?>
                    <div class="team-member-thumbnail">
                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                            <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                        </a>
                    </div>
                <?php endif;?>
                <div class="team-member-head">
                    <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
                        <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
                    <?php endif;?>
    
                    <?php if('yes'== $settings['show_title']  ): ?>
                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                            <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                        </a>
                    <?php endif;?>
                </div>
                <div class="team-member-desc">
                    <?php if( 'yes' === $show_shortBio ) : ?>
                    <div class="team-short-bio">
                        <?php if( !empty( $short_bio ) && 'yes'== $settings['team_show_short_bio'] ): ?>
                            <?php echo esc_html( $short_bio ); ?>
                        <?php else: ?>
                            <?php echo esc_html( wp_trim_words( get_the_content(null, false,$teamInfo->ID), 40, '...' ) ); ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
    
                    <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                        <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                    <?php endif; ?>  
    
                    <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info']) : ?>
                        <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
                    <?php endif; ?>
    
                    <?php if (tmwstm_fs()->is_paying_or_trial()): ?>
                        <?php if (isset($settings['progress_bar_show']) && 'yes' === $settings['progress_bar_show']): ?>
                            <div class="wtm-progress-bar">
                                <?php
                                if (class_exists('DWL_Wtm_Pro')) {

                                    $obj_skill = new \DWL_Wtm_Pro();
                                    echo $obj_skill->display_skills_output($teamInfo->ID);

                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ( isset( $settings['show_read_more'] ) && 'yes' === $settings['show_read_more'] ) : ?>
                        <div class="wtm-read-more-wrap">
                            <a href="<?php echo esc_url( get_the_permalink( $teamInfo->ID ) ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' ); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
        <?php endforeach; ?>
            </div>
         <?php endif; ?>   
<?php endif; ?>
