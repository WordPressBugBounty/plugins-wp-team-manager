<?php
use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Retrieve settings with default values
$image_size = $settings['dwl_team_select_image_size'][0] ?? 'thumbnail';
$show_other_info = !empty($settings['dwl_team_team_show_other_info'][0]);
$show_social = !empty($settings['dwl_team_team_show_social'][0]);
$show_read_more = empty($settings['dwl_team_team_show_read_more'][0]); 
$show_progress_bar = !empty($settings['dwl_team_show_progress_bar'][0]);
$hide_short_bio_control = !empty($settings['dwl_team_hide_short_bio'][0]);

// Determine if the single template should be disabled
$disable_single_template = get_option('single_team_member_view') === 'True';

// Get team member fields with a default value
$tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']);
$tm_single_fields = is_array($tm_single_fields) ? $tm_single_fields : ['tm_jtitle'];

// Ensure data['posts'] exists

    ?>

        <div class="dwl-team-elementor-layout-table">
            <div class="dwl-team-table-responsive team-table-style-1">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( "Image", "wp-team-manager" )?></th>

                            <th scope="col"><?php esc_html_e( "Name", "wp-team-manager" )?></th>

                            <th scope="col"><?php esc_html_e( "Designation", "wp-team-manager" )?></th>

                            <th scope="col"><?php esc_html_e( "Short Bio", "wp-team-manager" )?></th>

                            <th scope="col"><?php esc_html_e( "EMAIL", "wp-team-manager" )?></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        
                            foreach ($data['posts'] as $key => $teamInfo):

                                $meta = get_post_custom($teamInfo->ID);
                                
                                $job_title = !empty($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
                                $short_bio = !empty($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
                                $tm_email = !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '';
                                
                                ?>
                                
                                    <tr class="dwl-table-row" scope="row">
                                        
                                            <td class="dwl-table-data">
                                                <div class="dwl-table-img-icon-wraper">
                                                        <div class="dwl-table-img-wraper">
                                                            <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                                <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                            </a>
                                                        </div>

                                                        <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                                                </div>
                                                
                                            </td>

                                            <td class="dwl-table-data">
                                                <div class="team-member-head">
                                                    <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                                                </div>
                                            </td>

                                            <td class="dwl-table-data">
                                                <div class="team-position-wraper">
                                                    <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
                                                </div>
                                            </td>

                                            <td class="dwl-table-data-short-bio">
                                                <div class="team-short-bio">

                                                        <?php echo esc_html( wp_trim_words( $short_bio, 20, '...' ) ); ?>

                                                        <?php 
                                                        $post_content = !empty($teamInfo->post_excerpt) 
                                                            ? $teamInfo->post_excerpt 
                                                            : wp_trim_words(strip_tags($teamInfo->post_content), 20, '...');

                                                        echo esc_html($post_content);
                                                        ?>

                                                </div>
                                            </td>

                                            <td class="dwl-table-data">
                                                <div class="team-member-info">
                                                    <a href="mailto:<?php echo esc_html($tm_email) ?>" target="_blank">
                                                        <i class="fas fa-envelope"></i>
                                                        <?php echo esc_html($tm_email) ?>
                                                    </a>
                                                </div>
                                            </td>
                                    </tr>
                                <?php
                        ?>
                            <?php endforeach; ?>
                    </tbody>    
                </table>
            </div>
        </div>


    <?php

?>