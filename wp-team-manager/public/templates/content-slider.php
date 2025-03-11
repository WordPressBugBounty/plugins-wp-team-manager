<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Default image size
$image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';  

// Disable single template view check
$disable_single_template = ( get_option('single_team_member_view') === 'True' ) ? true : false;


// Validate the $settings array and check if keys exist and their values are valid.
$show_other_info = isset($settings['show_other_info']) && 'yes' === $settings['show_other_info'];
$show_read_more = isset($settings['show_read_more']) && 'yes' === $settings['show_read_more'];
$show_social = isset($settings['show_social']) && 'yes' === $settings['show_social'];


if ( ! empty( $data ) ) {
    foreach ( $data['posts'] as $key => $teamInfo ) {
      
        // Retrieve post meta in bulk to minimize database queries
        $post_meta = get_post_meta( $teamInfo->ID );
        $job_title = ! empty( $post_meta['tm_jtitle'][0] ) ? sanitize_text_field( $post_meta['tm_jtitle'][0] ) : '';
        $short_bio = ! empty( $post_meta['tm_short_bio'][0] ) ? sanitize_textarea_field( $post_meta['tm_short_bio'][0] ) : '';
      
        // Get permalink and image
        $team_permalink = get_the_permalink( $teamInfo->ID );
        $team_picture = Helper::get_team_picture( $teamInfo->ID, $image_size );

        ?>
        <div <?php post_class( 'team-member-info-wrap' ); ?>>
          <header>
            <?php if ( ! $disable_single_template ) : ?>
                <a href="<?php echo esc_url( $team_permalink ); ?>">
            <?php endif; ?>
            
            <?php echo wp_kses_post( $team_picture ); ?>

            <?php if ( ! $disable_single_template ) : ?>
                </a>
            <?php endif; ?>
          </header>
          
          <div class="team-member-desc">
            <h2 class="team-member-title"><?php echo esc_html( get_the_title( $teamInfo->ID ) ); ?></h2>
            
            <?php if ( ! empty( $job_title ) ) : ?>
                <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
            <?php endif; ?>

            <div class="team-short-bio">
              <?php if ( ! empty( $short_bio ) ) : ?>
                  <?php echo esc_html( $short_bio ); ?>
              <?php else : ?>
                  <?php echo esc_html( wp_trim_words( get_the_content( null, false, $teamInfo->ID ), 40, '...' ) ); ?>
              <?php endif; ?>
            </div>

            <?php if ( $show_other_info ) : ?>
                <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
            <?php endif; ?>

            <?php if ( $show_read_more ) : ?>
              <div class="wtm-read-more-wrap">
                <a href="<?php echo esc_url( $team_permalink ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' ); ?></a>
              </div>
            <?php endif; ?>

            <?php if ( $show_social ) : ?>
                <?php echo wp_kses_post( Helper::get_team_social_links( $teamInfo->ID ) ); ?>
            <?php endif; ?>
          </div>
        </div>

        <?php
    }
}
