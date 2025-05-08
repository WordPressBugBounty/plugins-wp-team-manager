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


    // Retrieve and sanitize values from the $post_meta array
    $job_title = isset($post_meta['tm_jtitle'][0]) ? sanitize_text_field($post_meta['tm_jtitle'][0]) : '';
    $short_bio = isset($post_meta['tm_short_bio'][0]) ? sanitize_textarea_field($post_meta['tm_short_bio'][0]) : '';

    ?>

        <div class="dwl-team-elementor-layout-table">
            <div class="dwl-team-table-responsive team-table-style-2 team-table-short-code">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( "Picture", " wp-team-manager " )?></th>

                            <th><?php esc_html_e( "Name", " wp-team-manager " )?></th>

                            <th><?php esc_html_e( "Profession", " wp-team-manager " )?></th>


                            <th><?php esc_html_e( "Short bio", " wp-team-manager " )?></th>

                            <th class="dwl-table-head-cotact"><?php esc_html_e( "Social Link", " wp-team-manager " )?></th>

                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        
                            foreach ($data['posts'] as $key => $teamInfo):
                                
                                $team_department = wp_get_post_terms($teamInfo->ID, 'team_department');
                                $team_designation = wp_get_post_terms($teamInfo->ID, 'team_designation');

                                $meta = get_post_custom($teamInfo->ID);
                                $job_title = !empty($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
                                $short_bio = !empty($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
                                $tm_email = !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '';
                                $tm_mobile = !empty($meta['tm_mobile'][0]) ? sanitize_text_field($meta['tm_mobile'][0]) : '';
                                
                                ?>
                                
                                
                                <tr class="dwl-table-row">
                                    
                                        <td class="dwl-table-data">
                                            <div class="dwl-table-img-wraper">

                                                <?php if(true): ?>
                                                    <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                        <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                    </a>
                                                <?php endif;?>
                                                
                                            </div>
                                        </td>
                                  

                                    
                                        <td class="dwl-table-data">
                                            <?php if(true): ?>
                                                <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                                            <?php endif;?>
                                        </td>
                                    

                                    
                                        <td class="dwl-table-data">
                                            <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields)): ?>
                                                <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
                                            <?php endif; ?>
                                        </td>
                                    
                                    
                                    
                                        <td class="dwl-table-data dwl-table-data-short-bio">
                                            <div class="team-short-bio">
                                                <?php if( true ): ?>
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
                                    

                                   
                                        <td class="dwl-table-data">
                                            <div class="dwl-table-icon-wraper">
                                                <?php if(true) : ?>
                                                    <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                                                <?php endif; ?>
                                                <?php if( true) : ?>
                                                    <div class="dwl-table-full-biograph">
                                                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="dwl-table-button"><?php echo esc_attr_e( 'Full Biograph', 'wp-team-manager' )?></a>
                                                    </div>
                                                <?php endif; ?>
                                        
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