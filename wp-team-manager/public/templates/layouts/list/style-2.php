<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

  if(!empty($data)):

    $image_size = isset( $settings['image_size'] ) ? sanitize_text_field( $settings['image_size'] ) : 'thumbnail';
    $team_read_more = !empty( $settings['read_more_text'] ) ? sanitize_text_field( $settings['read_more_text'] ) : 'Read More';
    $disable_single_template = ( false !== get_option('single_team_member_view')  && 'True' == get_option('single_team_member_view') ) ? true : false;
    $selected = Helper::generate_single_fields('frontend');

    foreach ($data as $key => $teamInfo) :
      
      $meta = get_post_meta( $teamInfo->ID );
      $job_title = isset($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
      $short_bio = isset($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
      ?>
  
        <div <?php post_class('team-member-info-wrap wtm-col-12 wtm-col-lg-6 wtm-col-md-6'); ?>>
          <div class="wtm-row g-0 team-member-info-content"> 
            <header class="wtm-col-12 wtm-col-lg-4 wtm-col-md-6">
            <div class="dwl-team-overlay-container">
              <?php if("yes" == $settings['show_image']): ?>
                <a href="#" class="team-popup" data-id="<?php echo esc_attr($teamInfo->ID); ?>">
                <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>
                <div class="list-style-overlay">
                  <div class="list-icon"><i class="fa fa-plus"></i></div>
                </div>
              </a>

              <?php endif;?>
            </div>
            </header>

          <div class="team-member-desc wtm-col-12 wtm-col-lg-8 wtm-col-md-6">
            <?php if('yes'== $settings['show_title']  ): ?>
              <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
              </a>
            <?php endif;?>
            <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
              <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
            <?php endif;?>
            <div class="team-short-bio">
                    <?php if( !empty( $short_bio ) ): ?>
                        <?php echo esc_html( $short_bio ); ?>
                    <?php else: ?>
                        <?php 
                            $post_content = !empty($teamInfo->post_excerpt) 
                                ? $teamInfo->post_excerpt 
                                : wp_trim_words(strip_tags($teamInfo->post_content), 40, '...');

                            echo esc_html($post_content);
                            ?>
                    <?php endif; ?>
                </div>
            <?php if('yes' == $settings['show_other_info']) : ?>
              <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID, $selected )); ?>
            <?php endif; ?>
            <?php if (tmwstm_fs()->is_paying_or_trial()): ?>
                        <?php if (isset($settings['progress_bar_show']) && 'yes' === $settings['progress_bar_show']): ?>
                            <div class="wtm-progress-bar">
                            <?php
                                  if (class_exists('DWL_Wtm_Pro_Helper')) {

                                      echo DWL_Wtm_Pro_Helper::display_skills_output($teamInfo->ID);

                                  } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
            <?php if(isset($settings['show_read_more']) AND 'yes' == $settings['show_read_more']) : ?>
              <div class="wtm-read-more-wrap">
                  <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="wtm-read-more">
                      <?php echo esc_html( $team_read_more ); ?>
                  </a>
              </div>
            <?php endif; ?>
            <?php if('yes' == $settings['show_social']) : ?>
              <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
  
      <?php
  
  endforeach;
endif;