<?php 
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Retrieve settings with default values (same as layout-1)
$image_size             = $settings['dwl_team_select_image_size'][0] ?? 'thumbnail';
$show_other_info        = !empty($settings['dwl_team_team_show_other_info'][0]);
$show_social            = !empty($settings['dwl_team_team_show_social'][0]);
$show_read_more         = empty($settings['dwl_team_team_show_read_more'][0]); 
$show_progress_bar      = !empty($settings['dwl_team_show_progress_bar'][0]);
$enable_links = (isset($settings['dwl_team_team_link_mobile_phone'][0]) && 'on' == $settings['dwl_team_team_link_mobile_phone'][0]) ? 'yes' : 'no'; 
$hide_team_show_position = !empty($settings['dwl_team_team_show_position'][0]);
$hide_short_bio_control = !empty($settings['dwl_team_hide_short_bio'][0]);

// Determine if the single template should be disabled
$disable_single_template = get_option('single_team_member_view') === 'True';

// Get team member fields with default
$tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']);
$tm_single_fields = is_array($tm_single_fields) ? $tm_single_fields : ['tm_jtitle'];
$selected = Helper::generate_single_fields('frontend');

// Ensure we have posts
if(!empty($data['posts'])): ?>
        <?php foreach ($data['posts'] as $teamInfo): 
            $post_meta  = get_post_meta($teamInfo->ID);
            $job_title  = isset($post_meta['tm_jtitle'][0]) ? sanitize_text_field($post_meta['tm_jtitle'][0]) : '';
            $short_bio  = isset($post_meta['tm_short_bio'][0]) ? sanitize_textarea_field($post_meta['tm_short_bio'][0]) : '';
        ?>
      
        <div <?php post_class("team-member-info-wrap"); ?>>
            <div class="team-member-info-content"> 

                <!-- Thumbnail -->
                <?php if (!$disable_single_template): ?>
                    <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                <?php endif; ?>
                <div class="team-member-thumbnail">
                    <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>
                </div>
                <?php if (!$disable_single_template): ?>
                    </a>
                <?php endif; ?>

                <!-- Head -->
                <div class="team-member-head">
                    <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>
                    <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields) && !$hide_team_show_position): ?>
                        <p class="team-position"><?php echo esc_html($job_title); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="team-member-desc">
                    <?php if (!$hide_short_bio_control): ?>
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
                    <?php endif; ?>

                    <?php  if (!$show_other_info): 
                  echo wp_kses_post(Helper::get_team_other_infos($teamInfo->ID, $selected, $enable_links));
                 endif;  ?>

                    <?php if (tmwstm_fs()->is_paying_or_trial()): ?>
                        <?php if (!$show_progress_bar): ?>
                            <div class="wtm-progress-bar">
                                <?php if (class_exists('DWL_Wtm_Pro_Helper')) {
                                    echo DWL_Wtm_Pro_Helper::display_skills_output($teamInfo->ID);
                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!$show_social): ?>
                        <?php echo wp_kses_post(Helper::display_social_profile_output($teamInfo->ID)); ?>
                    <?php endif; ?>

                    <?php if ($show_read_more && !$disable_single_template): ?>
                        <div class="wtm-read-more-wrap">
                            <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>" class="wtm-read-more">
                                <?php esc_html_e('Read More', 'wp-team-manager'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
<?php endif; ?>
