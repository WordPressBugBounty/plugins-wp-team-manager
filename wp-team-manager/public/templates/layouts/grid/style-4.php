<?php
use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$settings = is_array($settings) ? $settings : [];
$data = (is_array($data) && isset($data['posts']) && is_array($data['posts'])) ? $data : null;

if (!$data) {
    return; // Stop execution if no posts exist
}

    // Sanitize and prepare settings
    $image_size = isset($settings['image_size']) ? sanitize_text_field($settings['image_size']) : 'thumbnail';
    $show_shortBio = !empty($settings['team_show_short_bio']) ? sanitize_text_field($settings['team_show_short_bio']) : '';
    $team_read_more = !empty($settings['read_more_text']) ? sanitize_text_field($settings['read_more_text']) : 'Read More';
    $popup_settings = (!empty($settings['popup_bar_show']) && $settings['popup_bar_show'] === 'yes') ? "true" : 'false';
    $show_popup = isset($settings['popup_bar_show']) && $settings['popup_bar_show'] === 'yes';
    $disable_single_member = isset($settings['disable_single_member']) && $settings['disable_single_member'] === 'yes';
    $show_other_info = !empty($settings['dwl_team_team_show_other_info'][0]);
    $hide_short_bio_control = !empty($settings['dwl_team_hide_short_bio'][0]);
    $selected = Helper::generate_single_fields('frontend');
    $show_social = !empty($settings['dwl_team_team_show_social'][0]);
    $show_progress_bar = !empty($settings['dwl_team_show_progress_bar'][0]);
    $show_read_more = empty($settings['dwl_team_team_show_read_more'][0]); 
    $hide_team_show_position = !empty($settings['dwl_team_team_show_position'][0]);


    $disable_single_template = get_option('single_team_member_view') === 'True';

    $allowed_tags = array_merge(
        wp_kses_allowed_html('post'),
        ['progress' => ['value' => true, 'max' => true, 'style' => true]]
    );

    $desktop_column = isset($settings['dwl_team_desktop'][0]) ? absint($settings['dwl_team_desktop'][0]) : 4;
    $tablet_column = isset($settings['dwl_team_tablet'][0]) ? absint($settings['dwl_team_tablet'][0]) : 3;
    $mobile_column = isset($settings['dwl_team_mobile'][0]) ? absint($settings['dwl_team_mobile'][0]) : 1;
    $bootstrap_class = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);

    $selected_fields = Helper::generate_single_fields('frontend');

    $tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']);
    $tm_single_fields = is_array($tm_single_fields) ? $tm_single_fields : ['tm_jtitle'];

    foreach ($data['posts'] as $teamInfo):
        $meta = get_post_meta($teamInfo->ID);
        $job_title = isset($meta['tm_jtitle'][0]) ? sanitize_text_field($meta['tm_jtitle'][0]) : '';
        $short_bio = isset($meta['tm_short_bio'][0]) ? sanitize_textarea_field($meta['tm_short_bio'][0]) : '';
?>
    <div <?php post_class("team-member-info-wrap m-0 p-2 " . esc_attr($bootstrap_class)); ?>>
    <div class="team-member-info-content">
        <header>
                <?php if (!$disable_single_member): ?>
                    <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                <?php endif; ?>

                <?php if ($show_popup): ?>
                    <div class="team-popup" data-popup="<?php echo esc_attr($popup_settings); ?>" data-id="<?php echo esc_attr($teamInfo->ID); ?>">
                <?php endif; ?>

                <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>

                <?php if ($show_popup): ?>
                    </div>
                <?php endif; ?>

                <?php if (!$disable_single_member): ?>
                    </a>
                <?php endif; ?>
        </header>

        
        <div class="team-member-title-info">
            <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>

            <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields) && !$hide_team_show_position): ?>
                <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
            <?php endif; ?>
        </div>

        <div class="team-member-desc">
        <h2 class="team-member-title"><?php echo esc_html(get_the_title($teamInfo->ID)); ?></h2>

        <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields) && !$hide_team_show_position): ?>
            <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
        <?php endif; ?>
            
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

            <?php if (!$show_other_info): ?>
                <?php echo wp_kses_post(Helper::get_team_other_infos($teamInfo->ID, $selected)); ?>
            <?php endif; ?>
            <?php if (!empty($settings['show_other_info'])): ?>
                <div class="team-other-info-icons">
                    <?php 
                        $other_infos = Helper::get_team_other_infos($teamInfo->ID, $selected_fields, true);
                        echo wp_kses_post($other_infos);
                    ?>
                </div>
            <?php endif; ?>


            <?php if (tmwstm_fs()->is_paying_or_trial()): ?>
                <?php if (!$show_progress_bar): ?>
                    <div class="wtm-progress-bar">
                    <?php
                        if (class_exists('DWL_Wtm_Pro_Helper')) {

                            echo DWL_Wtm_Pro_Helper::display_skills_output($teamInfo->ID);

                        } ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$show_social): ?>
                    <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
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
?>
