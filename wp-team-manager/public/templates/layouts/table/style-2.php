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
$hide_team_show_position = !empty($settings['dwl_team_team_show_position'][0]);


// Determine if the single template should be disabled
// Pro feature: Disable single team member view (global setting)
$disable_single_template = Helper::is_pro_option_enabled( 'single_team_member_view' );

// Get team member fields with a default value
$tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']);
$tm_single_fields = is_array($tm_single_fields) ? $tm_single_fields : ['tm_jtitle'];
$context = isset($settings['context']) ? $settings['context'] : '';

if(!empty($data['posts'])): ?>
    <div class="dwl-team-elementor-layout-table">
        <div class="dwl-team-table-responsive team-table-style-2 team-table-short-code">
            <table class="table">
                <?php if($context != 'ajax'): ?>    
                <thead>
                    <tr>
                        <th><?php esc_html_e("Name", "wp-team-manager")?></th>
                        <th><?php esc_html_e("Department", "wp-team-manager")?></th>
                        <th><?php esc_html_e("Designation", "wp-team-manager")?></th>
                        <th><?php esc_html_e("Profession", "wp-team-manager")?></th>
                        <th><?php esc_html_e("Short Bio", "wp-team-manager")?></th>
                        <th><?php esc_html_e("Number", "wp-team-manager")?></th>
                        <th class="dwl-table-head-cotact"><?php esc_html_e("Contact", "wp-team-manager")?></th>
                    </tr>
                </thead>
                <?php endif;?>
                <tbody>
                    <?php foreach ($data['posts'] as $key => $teamInfo):
                        $team_department = wp_get_post_terms($teamInfo->ID, 'team_department');
                        $team_designation = wp_get_post_terms($teamInfo->ID, 'team_designation');
                        
                        $meta = get_post_custom($teamInfo->ID);
                        $job_title = !empty($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
                        $short_bio = $meta['tm_short_bio'][0] ?? '';
                        $tm_email = !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '';
                        $tm_mobile = !empty($meta['tm_mobile'][0]) ? sanitize_text_field($meta['tm_mobile'][0]) : '';
                    ?>
                        <tr class="dwl-table-row">
                            <td class="dwl-table-data">
                                <div class="dwl-table-img-wraper">
                                    <?php if (!$disable_single_template): ?>
                                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                                    <?php endif; ?>
                                        <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>
                                    <?php if (!$disable_single_template): ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$disable_single_template): ?>
                                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                                    <?php endif; ?>
                                        <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>
                                    <?php if (!$disable_single_template): ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="dwl-table-data">
                                <?php echo wp_kses_post(Helper::render_terms($teamInfo->ID, 1, 'team_department')); ?>
                            </td>
                            
                            <td class="dwl-table-data">
                                <?php echo wp_kses_post(Helper::render_terms($teamInfo->ID, 1, 'team_designation')); ?>
                            </td>
                            
                            <td class="dwl-table-data">
                                <div class="team-member-job-title">
                                    <?php if (!$hide_team_show_position): ?>
                                        <span class="team-position"><?php echo esc_html($job_title); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="dwl-table-data">
                                <div class="team-short-bio">
                                    <?php if (!$hide_short_bio_control): ?>
                                       <?php 
                                                 // Trim to 20 words, default is escaped output
                                                    $trimmed_bio = wp_trim_words( $short_bio, 20, '...' );
                                                 echo apply_filters('wtm_team_short_bio_output', esc_html($trimmed_bio), $short_bio, $teamInfo->ID); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <td class="dwl-table-data dwl-table-data-short-bio">
                                <?php if(isset($tm_mobile) && !empty($tm_mobile)): ?>
                                    <div class="team-member-mobile-info">
                                        <a href="tel://<?php echo esc_html($tm_mobile) ?>" target="_blank">
                                            <?php echo esc_html($tm_mobile) ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            
                            <td class="dwl-table-data">
                                <div class="dwl-table-icon-wraper">
                                    <?php if (!$show_social): ?>
                                        <?php echo wp_kses_post(Helper::display_social_profile_output($teamInfo->ID)); ?>
                                    <?php endif; ?>
                                    <div class="dwl-table-full-biograph">
                                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>" class="dwl-table-button"><?php esc_html_e('Full Biography', 'wp-team-manager'); ?></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>   
            </table>
        </div>
    </div>
<?php endif; ?>