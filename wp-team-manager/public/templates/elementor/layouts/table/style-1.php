
<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)):
    

    $style_type_name = isset( $settings['layout_type'] ) ? sanitize_text_field( $settings['layout_type'] ) . '_style_type' : '';  // Sanitize layout type before concatenation
    $style_type = isset( $settings[$style_type_name] ) && !empty( $settings[$style_type_name] ) ? sanitize_text_field( $settings[$style_type_name] ) : '';  // Sanitize style type
    $image_size = isset( $settings['image_size'] ) ? sanitize_text_field( $settings['image_size'] ) : 'thumbnail';  // Sanitize image size
    $show_shortBio = isset( $settings['team_show_short_bio'] ) && !empty( $settings['team_show_short_bio'] ) ? sanitize_textarea_field( $settings['team_show_short_bio'] ) : '';  // Sanitize short bio

    
    $popup_settings = !empty( $settings['popup_bar_show'] ) && $settings['popup_bar_show'] === 'yes' ? "true" : 'false';
    $show_popup = isset($settings['popup_bar_show']) && $settings['popup_bar_show'] === 'yes';
    $disable_single_member = isset($settings['disable_single_member']) && $settings['disable_single_member'] === 'yes';
    $callType = $callBack ?? '';

        if( 'ajax' !== $callType ):
        ?>
        <div class="dwl-team-table-responsive team-table-<?php echo esc_attr( $style_type )?>">
            <div class="table">
                <!-- Table Header -->
                <div class="team-table-header-wrapper">
                    <div class="team-table-header">
                        <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_social'] ): ?>
                            <div class="team-col image"><?php esc_html_e( "Image", "wp-team-manager" )?></div>
                        <?php endif; ?>

                        <?php if('yes'== $settings['show_title']  ): ?>
                            <div class="team-col name"><?php esc_html_e( "Name", "wp-team-manager" )?></div>
                        <?php endif; ?>

                        <?php if( 'yes'== $settings['show_sub_title'] ): ?>
                            <div class="team-col designation"><?php esc_html_e( "Designation", "wp-team-manager" )?></div>
                        <?php endif; ?>

                        <?php if( 'yes' === $show_shortBio ) : ?>
                            <div class="team-col bio"><?php esc_html_e( "Short Bio", "wp-team-manager" )?></div>
                        <?php endif; ?>

                        <?php if( isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info'] ) : ?>
                            <div class="team-col email"><?php esc_html_e( "EMAIL", "wp-team-manager" )?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="team-table-wrapper">
                    <?php endif; ?>
                    <?php
                        foreach ($data as $key => $teamInfo):

                            $meta = get_post_custom($teamInfo->ID);
                            $job_title = !empty($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
                            $short_bio = !empty($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
                            $tm_email = !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '';
                            $team_read_more = !empty( $settings['read_more_text'] ) ? sanitize_text_field( $settings['read_more_text'] ) : 'Read More';
                                
                                ?>
                                    
                                    <div class="team-table-row">
                                    <!-- Image -->
                                        <div class="team-col image">
                                            <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_social'] ): ?>
                                                <div class="dwl-table-img-icon-wraper">
                                                    <div class="team-image-wrapper" style="display: flex; flex-direction: column; align-items: center;">
                                                        <?php if("yes" == $settings['show_image']): ?>
                                                            <?php if($disable_single_member) : ?>
                                                                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                            <?php endif; ?>
                                                            <?php if($show_popup): ?>
                                                                <div class="team-popup" data-popup="<?php echo esc_attr($popup_settings); ?>" data-id="<?php echo esc_attr($teamInfo->ID); ?>">
                                                            <?php endif; ?>
                                                                    <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                            <?php if($show_popup): ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if($disable_single_member) : ?>
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif;?>
                                                    </div>
                                                    <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                                                        <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif;?>
                                        </div>
                                        
                                        <!-- Name -->
                                        <?php if('yes'== $settings['show_title']  ):
                                            $title = get_the_title( $teamInfo->ID );
                                            ?>
                                            <div class="team-col name">
                                                <?php if($disable_single_member) : ?>
                                                    <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                <?php endif; ?>
                                                <?php if($show_popup): ?>
                                                    <div class="team-popup" data-popup="<?php echo esc_attr($popup_settings); ?>" data-id="<?php echo esc_attr($teamInfo->ID); ?>">
                                                <?php endif; ?>
                                                    <h2 class="team-member-title"><?php echo esc_html( $title ); ?></h2>
                                                <?php if($show_popup): ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if($disable_single_member) : ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        // Sub Title (Designation)
                                        if ( ! empty( $settings['show_sub_title'] ) ) :
                                            ?>
                                            <div class="team-col designation">
                                                <?php if ( ! empty( $job_title ) ) : ?>
                                                    <div class="team-position"><?php echo wp_kses_post( $job_title ); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if( 'yes' === $show_shortBio ) :
                                            // Bio (Short Bio)
                                            ?>
                                            <div class="team-col bio dwl-table-data-short-bio">
                                                <div class="team-short-bio">
                                                    <?php if( !empty( $short_bio ) && 'yes'== $settings['team_show_short_bio'] ): ?>
                                                        <?php echo esc_html( wp_trim_words( $short_bio, 20, '...' ) ); ?>
                                                    <?php else: ?>
                                                        <?php 
                                                        $post_content = !empty($teamInfo->post_excerpt) 
                                                            ? $teamInfo->post_excerpt 
                                                            : wp_trim_words(strip_tags($teamInfo->post_content), 20, '...');

                                                        echo esc_html($post_content);
                                                        ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Email + Read More -->
                                        <div class="team-col email">
                                            <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info']) : ?>                                        
                                                <?php if(isset($tm_email) && !empty($tm_email)): ?>
                                                    <div class="team-member-info">
                                                        <a href="mailto:<?php echo esc_html($tm_email) ?>" target="_blank">
                                                            <i class="fas fa-envelope"></i>
                                                            <?php echo esc_html($tm_email) ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                        
                                                <?php if (isset($settings['show_read_more']) && 'yes' === $settings['show_read_more'] && 'yes' === $settings['disable_single_member']) : ?>
                                                    <div class="wtm-read-more-wrap">
                                                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="wtm-read-more">
                                                        <?php echo esc_html( $team_read_more ); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                <?php
                            ?>
                    <?php endforeach; ?>
                    <?php if( 'ajax' !== $callType ): ?>
                </div>    
            </div>
        </div>
        <?php endif; ?> 
        <?php
    endif;
?>