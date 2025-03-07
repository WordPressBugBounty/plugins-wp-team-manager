<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if ( ! empty( $data ) ) :
    if( tmwstm_fs()->is_paying_or_trial() ){
    $image_size      = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';
    $show_short_bio   = isset( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';
    $team_arrow_positon = isset( $settings['team_arrow_position'] ) && ($settings['team_arrow_position'] === 'side') ? 'team-arrow-postion-side' : '';
    ?>
    <div class="team-member-slider-wrap <?php echo esc_attr( $team_arrow_positon ); ?>" data-slider_settings="<?php echo esc_attr( json_encode( $settings['slider_settings'] ) ); ?>">
    <?php
    foreach ( $data as $team ) :
        $meta = get_post_meta( $team->ID );
        $job_title = isset($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
        $short_bio = isset($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
        ?>
        <div <?php post_class( 'team-member-info-wrap' ); ?>>
            <div class="team-member-info-content dwl-team-slider-<?php echo esc_attr( $settings['slider_style_type'] )?>">
                <header>
                    <?php if ( 'yes' === $settings['show_image'] ) : ?>
                        <a href="<?php echo esc_url( get_the_permalink( $team->ID ) ); ?>">
                            <?php echo wp_kses_post( Helper::get_team_picture( $team->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                        </a>
                    <?php endif; ?>
                    <div class="team-member-info-inner">
                        <div>
                            <?php if ( 'yes' === $show_short_bio ) : ?>
                                <div class="team-short-bio">
                                    <?php if ( ! empty( $short_bio ) && 'yes' === $settings['team_show_short_bio'] ) : ?>
                                        <?php echo esc_html( $short_bio ); ?>
                                    <?php else : ?>
                                        <?php echo esc_html( wp_trim_words( get_the_content( null, false, $team->ID ), 40, '...' ) ); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="team-member-other-in-slider-6">
                            <?php if ( isset( $settings['show_social'] ) && 'yes' === $settings['show_social'] ) : ?>
                                <?php echo wp_kses_post( Helper::display_social_profile_output( $team->ID ) ); ?>
                            <?php endif; ?>

                            <?php if ( isset( $settings['show_read_more'] ) && 'yes' === $settings['show_read_more'] ) : ?>
                                <div class="wtm-read-more-wrap">
                                    <a href="<?php echo esc_url( get_the_permalink( $team->ID ) ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                </header>
                <div class="team-member-desc">
                    <?php if ( 'yes' === $settings['show_title'] ) : ?>
                        <a href="<?php echo esc_url( get_the_permalink( $team->ID ) ); ?>">
                            <h2 class="team-member-title"><?php echo esc_html( get_the_title( $team->ID ) ); ?></h2>
                        </a>
                    <?php endif; ?>

                    <?php if ( ! empty( $job_title ) && 'yes' === $settings['show_sub_title'] ) : ?>
                        <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php endforeach; ?>
        </div>
        <?php } endif;  ?>
