<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Fetch image size, sanitize it (optional validation)
$image_size = isset( $settings['image_size'] ) ? sanitize_text_field( $settings['image_size'] ) : 'thumbnail';  

// Fetch post meta in bulk to reduce database queries (tm_jtitle, tm_short_bio)
$post_meta = get_post_meta( $teamInfo->ID );
$job_title = isset( $post_meta['tm_jtitle'][0] ) ? sanitize_text_field( $post_meta['tm_jtitle'][0] ) : '';
$short_bio = isset( $post_meta['tm_short_bio'][0] ) ? sanitize_textarea_field( $post_meta['tm_short_bio'][0] ) : '';

// Store option value in a variable for better performance
$single_team_member_view = get_option('single_team_member_view');

// Validate and sanitize the option value for the template disable flag
$disable_single_template = ( false !== $single_team_member_view && filter_var( $single_team_member_view, FILTER_VALIDATE_BOOLEAN ) ) ? true : false;

?>
    <div class="team-member-info-content"> 
        <header>
            <?php if(isset($disable_single_template) AND !$disable_single_template): ?>
                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                <?php endif;?>
                <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                <?php if(isset($disable_single_template) AND !$disable_single_template): ?>
                </a>
            <?php endif;?>
        </header>
        <div class="team-member-desc">

            <h2 class="team-member-title"><?php echo wp_kses_post( get_the_title($teamInfo->ID) ); ?></h2>

            <?php if(isset($job_title) AND  !empty( $job_title ) ): ?>
                <h4 class="team-position"><?php echo wp_kses_post( $job_title ); ?></h4>
            <?php endif;?>

            <div class="team-short-bio">
                <?php if( !empty( $short_bio ) ): ?>
                    <?php echo wp_kses_post( $short_bio ); ?>
                    <?php else: ?>
                        <?php 
                        $post_content = !empty($teamInfo->post_excerpt) 
                            ? $teamInfo->post_excerpt 
                            : wp_trim_words(strip_tags($teamInfo->post_content), 40, '...');

                        echo esc_html($post_content);
                    ?>
                <?php endif; ?>
            </div>

            <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info']) : ?>
                <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
            <?php endif; ?>

            <?php if(isset($settings['show_social']) AND 'yes' == $settings['show_social']) : ?>
                <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
            <?php endif; ?>

            <?php if(isset($settings['show_read_more']) AND 'yes' == $settings['show_read_more']) : ?>
                <div class="wtm-read-more-wrap">
                    <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID) ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' )?></a>
                </div>
            <?php endif; ?>
                
        </div>
    </div>
