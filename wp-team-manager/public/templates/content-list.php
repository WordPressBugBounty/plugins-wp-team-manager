<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Retrieve the value of 'image_size' from settings, default to 'thumbnail' if not set
$image_size = isset( $settings['image_size'] ) ? sanitize_text_field( $settings['image_size'] ) : 'thumbnail';  

// Fetch the option value once and store it in a variable for efficiency
$single_team_member_view = get_option('single_team_member_view');

// Perform a strict comparison to ensure the option is exactly 'True' (case-sensitive)
$disable_single_template = ( 'true' === strtolower( $single_team_member_view ) );

if(!empty($data)){
    foreach ($data['posts'] as $key => $teamInfo) {
    
      // Fetch all post meta data at once to avoid multiple database queries
      $post_meta = get_post_meta( $teamInfo->ID );
      
      // Sanitize the retrieved post meta values
      $job_title = isset( $post_meta['tm_jtitle'][0] ) ? sanitize_text_field( $post_meta['tm_jtitle'][0] ) : '';
      $short_bio = isset( $post_meta['tm_short_bio'][0] ) ? sanitize_textarea_field( $post_meta['tm_short_bio'][0] ) : '';
      
      ?>

          <div <?php post_class('team-member-info-wrap m-0 p-2 wtm-col-12'); ?>>
          <div class="wtm-row g-0 team-member-info-content"> 
           <header class="wtm-col-12 wtm-col-lg-3 wtm-col-md-6">
              <?php if(!$disable_single_template): ?>
              <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
              <?php endif;?>
              <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
              <?php if(!$disable_single_template): ?>
              </a>
              <?php endif;?>
            </header>
          
          <div class="team-member-desc wtm-col-12 wtm-col-lg-8 wtm-col-md-6">
            <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
            <?php if( !empty( $job_title ) ): ?>
              <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
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
            <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
            <?php endif; ?>
            <?php if('yes' == $settings['show_social']) : ?>
              <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
            <?php endif; ?>
            <?php if(isset($settings['show_read_more']) AND 'yes' == $settings['show_read_more']) : ?>
                <div class="wtm-read-more-wrap">
                    <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' )?></a>
                </div>
            <?php endif; ?>
       
          </div>
          </div>
          </div>
  
          <?php
  
	}
  
}