<?php

use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Retrieve settings with default values
$image_size = $settings['dwl_team_select_image_size'][0] ?? 'thumbnail';
$show_other_info = !empty($settings['dwl_team_team_show_other_info']);
$show_social = !empty($settings['dwl_team_team_show_social']);
$show_read_more = empty($settings['dwl_team_team_show_read_more']); // Flipped logic for clarity
$show_progress_bar = !empty($settings['dwl_team_show_progress_bar'][0]);

$disable_single_template = get_option('single_team_member_view') === 'True';

if (!empty($data['posts'])) {
    $tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']); // Set default value directly

    foreach ($data['posts'] as $teamInfo) {
        $meta = get_post_meta($teamInfo->ID);
        $job_title = sanitize_text_field($meta['tm_jtitle'][0] ?? '');
        $short_bio = sanitize_textarea_field($meta['tm_short_bio'][0] ?? '');
        ?>

        <div <?php post_class('team-member-info-wrap wtm-col-12'); ?>>
            <div class="wtm-row g-0 team-member-info-content"> 
                <header class="wtm-col-12 wtm-col-lg-3 wtm-col-md-6">
                    <?php if (!$disable_single_template): ?>
                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                    <?php endif; ?>
                    
                    <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>

                    <?php if (!$disable_single_template): ?>
                        </a>
                    <?php endif; ?>
                </header>

                <div class="team-member-desc wtm-col-12 wtm-col-lg-8 wtm-col-md-6">
                    <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>

                    <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields)): ?>
                        <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
                    <?php endif; ?>

                    <div class="team-short-bio">
                        <?php echo esc_html(!empty($short_bio) ? $short_bio : wp_trim_words(get_the_content(null, false, $teamInfo->ID), 40, '...')); ?>
                    </div>

                    <?php if (!$show_other_info): ?>
                        <?php echo wp_kses_post(Helper::get_team_other_infos($teamInfo->ID)); ?>
                    <?php endif; ?>

                    <?php if (tmwstm_fs()->is_paying_or_trial()): ?>
                        <?php if (isset($settings['progress_bar_show']) && 'yes' === $settings['progress_bar_show']): ?>
                            <div class="wtm-progress-bar">
                                <?php
                                if (class_exists('DWL_Wtm_Pro')) {

                                    $obj_skill = new \DWL_Wtm_Pro();
                                    echo $obj_skill->display_skills_output($teamInfo->ID);

                                } ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!$show_social): ?>
                        <?php echo wp_kses_post(Helper::display_social_profile_output($teamInfo->ID)); ?>
                    <?php endif; ?>

                    <?php if (!$show_read_more): ?>
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
    }
}
?>