<?php
use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$image_size             = $settings['dwl_team_select_image_size'][0] ?? 'thumbnail';
$show_other_info        = !empty($settings['dwl_team_team_show_other_info'][0]);
$show_social            = !empty($settings['dwl_team_team_show_social'][0]);
$show_read_more         = empty($settings['dwl_team_team_show_read_more'][0]); 
$enable_links = (isset($settings['dwl_team_team_link_mobile_phone'][0]) && 'on' == $settings['dwl_team_team_link_mobile_phone'][0]) ? 'yes' : 'no'; 
$show_progress_bar      = !empty($settings['dwl_team_show_progress_bar'][0]);
$hide_short_bio_control = !empty($settings['dwl_team_hide_short_bio'][0]);
$hide_team_show_position = !empty($settings['dwl_team_team_show_position'][0]);


// Pro feature: Disable single team member view (global setting)
$disable_single_template = Helper::is_pro_option_enabled( 'single_team_member_view' );
$selected = Helper::generate_single_fields('frontend');

if (!empty($data['posts'])) {
    $tm_single_fields = (array) get_option('tm_single_fields', ['tm_jtitle']); 

    foreach ($data['posts'] as $teamInfo) :
        $meta      = get_post_meta($teamInfo->ID);
        $job_title = sanitize_text_field($meta['tm_jtitle'][0] ?? '');

        $short_bio = $meta['tm_short_bio'][0] ?? '';

        $allowed_tags = array_merge(
            wp_kses_allowed_html( 'post' ),
            array(
                'progress' => array(
                    'value' => true,
                    'max'   => true,
                    'style' => true,
                ),
            )
        );
        ?>

        <div <?php post_class('team-member-info-wrap wtm-col-12 wtm-col-lg-6 wtm-col-md-6'); ?>>
            <div class="wtm-row g-0 team-member-info-content"> 
                
                <header class="wtm-col-12 wtm-col-lg-4 wtm-col-md-6">
                    <div class="dwl-team-overlay-container">
                        <?php if (!$disable_single_template): ?>
                            <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>" class="team-popup" data-id="<?php echo esc_attr($teamInfo->ID); ?>">
                        <?php endif; ?>

                        <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>

                        <div class="list-style-overlay">
                            <div class="list-icon"><i class="fa fa-plus"></i></div>
                        </div>

                        <?php if (!$disable_single_template): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </header>

                <div class="team-member-desc wtm-col-12 wtm-col-lg-8 wtm-col-md-6">
                    <?php if (!$disable_single_template): ?>
                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                    <?php endif; ?>
                        <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>
                    <?php if (!$disable_single_template): ?>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($job_title) && !$hide_team_show_position): ?>
                        <p class="team-position"><?php echo esc_html($job_title); ?></p>
                    <?php endif; ?>
                      <?php if (!$hide_short_bio_control): ?>
                          <div class="team-short-bio">
                              <?php if (!empty($short_bio)): ?>
                                  <?php echo apply_filters('wtm_team_short_bio_output', wp_strip_all_tags($short_bio), $short_bio, $teamInfo->ID); ?>
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
                                <?php
                                if (class_exists('DWL_Wtm_Pro_Helper')) {
                                    echo wp_kses(DWL_Wtm_Pro_Helper::display_skills_output($teamInfo->ID), $allowed_tags);
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

        <?php
    endforeach;
}
?>
