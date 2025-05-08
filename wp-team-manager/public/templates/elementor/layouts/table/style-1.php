
<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)):
    

    $style_type_name = isset( $settings['layout_type'] ) ? sanitize_text_field( $settings['layout_type'] ) . '_style_type' : '';  // Sanitize layout type before concatenation
    $style_type = isset( $settings[$style_type_name] ) && !empty( $settings[$style_type_name] ) ? sanitize_text_field( $settings[$style_type_name] ) : '';  // Sanitize style type
    $image_size = isset( $settings['image_size'] ) ? sanitize_text_field( $settings['image_size'] ) : 'thumbnail';  // Sanitize image size
    $show_shortBio = isset( $settings['team_show_short_bio'] ) && !empty( $settings['team_show_short_bio'] ) ? sanitize_textarea_field( $settings['team_show_short_bio'] ) : '';  // Sanitize short bio

    
        ?>
        <div class="dwl-team-table-responsive team-table-<?php echo esc_attr( $style_type )?>">
            <table class="table">
                <thead>
                    <tr>
                        <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_social'] ): ?>
                            <th scope="col"><?php esc_html_e( "Image", "wp-team-manager" )?></th>
                        <?php endif; ?>

                        <?php if('yes'== $settings['show_title']  ): ?>
                            <th scope="col"><?php esc_html_e( "Name", "wp-team-manager" )?></th>
                        <?php endif; ?>

                        <?php if( 'yes'== $settings['show_sub_title'] ): ?>
                            <th scope="col"><?php esc_html_e( "Designation", "wp-team-manager" )?></th>
                        <?php endif; ?>

                        <?php if( 'yes' === $show_shortBio ) : ?>
                            <th scope="col"><?php esc_html_e( "Short Bio", "wp-team-manager" )?></th>
                        <?php endif; ?>

                        <?php if( isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info'] ) : ?>
                            <th scope="col"><?php esc_html_e( "EMAIL", "wp-team-manager" )?></th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    
                        foreach ($data as $key => $teamInfo):

                            $meta = get_post_custom($teamInfo->ID);
                            $job_title = !empty($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
                            $short_bio = !empty($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
                            $tm_email = !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '';
                            $team_read_more = !empty( $settings['read_more_text'] ) ? sanitize_text_field( $settings['read_more_text'] ) : 'Read More';
                                
                            ?>
                            
                            <tr class="dwl-table-row" scope="row">
                                <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_social'] ): ?>
                                    <td class="dwl-table-data">
                                        <div class="dwl-table-img-icon-wraper">
                                            <?php if("yes" == $settings['show_image']): ?>
                                                <div class="dwl-table-img-wraper">
                                                    <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                        <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                    </a>
                                                </div>
                                            <?php endif;?>

                                            <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                                                <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                                            <?php endif; ?>
                                        </div>
                                        
                                    </td>
                                <?php endif;?>
                                
                                <?php if('yes'== $settings['show_title']  ): ?>
                                    <td class="dwl-table-data">
                                        <div class="team-member-head">
                                            <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                                        </div>
                                    </td>
                                <?php endif;?>

                                <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
                                    <td class="dwl-table-data">
                                        <div class="team-position-wraper">
                                            <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
                                        </div>
                                    </td>
                                <?php endif;?>

                                <?php if( 'yes' === $show_shortBio ) : ?>
                                    <td class="dwl-table-data-short-bio">
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
                                    </td>
                                <?php endif; ?>

                                <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info']) : ?>
                                    <td class="dwl-table-data">
                                        <?php if(isset($tm_email) && !empty($tm_email)): ?>
                                        <div class="team-member-info">
                                            <a href="mailto:<?php echo esc_html($tm_email) ?>" target="_blank">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo esc_html($tm_email) ?>
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                
                                    <?php if ( isset( $settings['show_read_more'] ) && 'yes' === $settings['show_read_more'] ) : ?>
                                    <div class="wtm-read-more-wrap">
                                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="wtm-read-more">
                                        <?php echo esc_html( $team_read_more ); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                   
                                    </td>
                             
                                <?php endif; ?>
                  

                            </tr>

                        <?php

                    ?>
                        <?php endforeach; ?>
                    </tbody>    
            </table>
        </div>
            
        <?php
    endif;
?>