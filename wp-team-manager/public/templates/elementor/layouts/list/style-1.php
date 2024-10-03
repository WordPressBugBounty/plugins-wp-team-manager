<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

  if(!empty($data)):

    $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail'; 

    $disable_single_template = ( false !== get_option('single_team_member_view')  && 'True' == get_option('single_team_member_view') ) ? true : false;


    foreach ($data as $key => $teamInfo) :
      
      $job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
      $short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
      ?>
  
        <div <?php post_class('team-member-info-wrap wtm-col-12'); ?>>
          <div class="wtm-row g-0 team-member-info-content"> 
            <header class="wtm-col-12 wtm-col-lg-3 wtm-col-md-6">
              <?php if("yes" == $settings['show_image']): ?>
                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                  <?php echo wp_kses_post(Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' )); ?>
                </a>
              <?php endif;?>
            </header>
          
          <div class="team-member-desc wtm-col-12 wtm-col-lg-8 wtm-col-md-6">
            <?php if('yes'== $settings['show_title']  ): ?>
              <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
            <?php endif;?>
            <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
              <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
            <?php endif;?>
            <div class="team-short-bio">
              <?php if( !empty( $short_bio ) && 'yes'== $settings['team_show_short_bio'] ): ?>
                  <?php echo esc_html( $short_bio ); ?>
              <?php else: ?>
                  <?php echo esc_html( wp_trim_words( get_the_content(null, false,$teamInfo->ID), 40, '...' ) ); ?>
              <?php endif; ?>
            </div>
            <?php if('yes' == $settings['show_other_info']) : ?>
              <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID )); ?>
            <?php endif; ?>

            <?php if(isset($settings['show_read_more']) AND 'yes' == $settings['show_read_more']) : ?>
              <div class="wtm-read-more-wrap">
                  <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' )?></a>
              </div>
            <?php endif; ?>
            
            <?php if('yes' == $settings['show_social']) : ?>
              <?php echo wp_kses_post( Helper::get_team_social_links($teamInfo->ID) ); ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
  
      <?php
  
  endforeach;
endif;