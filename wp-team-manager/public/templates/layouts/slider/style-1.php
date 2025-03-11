<?php
use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Retrieve settings with default values
$image_size = sanitize_text_field($settings['dwl_team_select_image_size'][0] ?? 'thumbnail');
$show_other_info = !empty($settings['dwl_team_team_show_other_info']);
$show_social = !empty($settings['dwl_team_team_show_social']);
$show_read_more = empty($settings['dwl_team_team_show_read_more']);
$show_progress_bar = !empty($settings['dwl_team_show_progress_bar'][0]);

// Determine if the single template should be disabled
$disable_single_template = get_option('single_team_member_view') === 'True';

// Get team member fields with a default value
$tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']);
$tm_single_fields = is_array($tm_single_fields) ? $tm_single_fields : ['tm_jtitle'];

// Ensure data['posts'] exists
if (!empty($data['posts'])) {
    foreach ($data['posts'] as $teamInfo) {
        // Retrieve all post meta at once to reduce database queries
        $post_meta = get_post_meta($teamInfo->ID);

        // Retrieve and sanitize values from the $post_meta array
        $job_title = isset($post_meta['tm_jtitle'][0]) ? sanitize_text_field($post_meta['tm_jtitle'][0]) : '';
        $short_bio = isset($post_meta['tm_short_bio'][0]) ? sanitize_textarea_field($post_meta['tm_short_bio'][0]) : '';

        ?>

        <div <?php post_class('team-member-info-wrap'); ?>>
            <header>
                <?php if (!$disable_single_template): ?>
                    <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                <?php endif; ?>

                <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size)); ?>

                <?php if (!$disable_single_template): ?>
                    </a>
                <?php endif; ?>
            </header>

            <div class="team-member-desc">
                <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>

                <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields)): ?>
                    <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
                <?php endif; ?>

                <div class="team-short-bio">
                    <?php 
                    echo esc_html(!empty($short_bio) ? $short_bio : wp_trim_words(get_the_content(null, false, $teamInfo->ID), 40, '...')); 
                    ?>
                </div>

                <?php if (!$show_other_info): ?>
                    <?php echo wp_kses_post(Helper::get_team_other_infos($teamInfo->ID)); ?>
                <?php endif; ?>

                <?php if (!$show_read_more): ?>
                    <div class="wtm-read-more-wrap">
                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>" class="wtm-read-more">
                            <?php esc_html_e('Read More', 'wp-team-manager'); ?>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (function_exists('tmwstm_fs') && tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() && !$show_progress_bar): ?>
                    <?php echo wp_kses_post(Helper::display_skills_output($teamInfo->ID)); ?>
                <?php endif; ?>

                <?php if (!$show_social): ?>
                    <?php echo wp_kses_post(Helper::display_social_profile_output($teamInfo->ID)); ?>
                <?php endif; ?>
            </div>
        </div>

        <?php
    }
}
?>