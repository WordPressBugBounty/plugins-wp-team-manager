<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';  
$disable_single_template = ( false !== get_option('single_team_member_view')  && 'True' == get_option('single_team_member_view') ) ? true : false;

  if(!empty($data)){
    foreach ($data['posts'] as $key => $teamInfo) {
      
      $job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
      $short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
      
      ?>
  
        <div <?php post_class('team-member-info-wrap'); ?>>
          <header>
          <?php if(isset($disable_single_template) AND !$disable_single_template): ?>
            <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
            <?php endif;?>
            <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size ) ); ?>
            <?php if(isset($disable_single_template) AND !$disable_single_template): ?>
            </a>
            <?php endif;?>
          </header>
          
          <div class="team-member-desc">
            <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
            <?php if( !empty( $job_title ) ): ?>
              <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
            <?php endif;?>
            <div class="team-short-bio">
              <?php if( !empty( $short_bio ) ): ?>
                <?php echo esc_html( $short_bio ); ?>
                <?php else: ?>
                <?php echo esc_html( wp_trim_words( get_the_content(null, false,$teamInfo->ID), 40, '...' ) ); ?>
              <?php endif; ?>
            </div>
            <?php if('yes' == $settings['show_other_info']) : ?>
            <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
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
  
      <?php
}
}
