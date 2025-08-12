<?php

use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Ensure $settings is an array and sanitize inputs properly
$settings = is_array($settings) ? $settings : [];

// Retrieve settings with default values
$image_size = sanitize_text_field($settings['dwl_team_select_image_size'][0] ?? 'thumbnail');
$show_other_info = !empty($settings['dwl_team_team_show_other_info']); // Ensure this returns a boolean
$show_social = !empty($settings['dwl_team_team_show_social']); // Ensure this returns a boolean
$show_read_more = empty($settings['dwl_team_team_show_read_more']); // Make sure it's correctly handled as boolean

// Retrieve single fields with default value and ensure it’s an array
$tm_single_fields = get_option('tm_single_fields', ['tm_jtitle']);
$tm_single_fields = is_array($tm_single_fields) ? $tm_single_fields : ['tm_jtitle'];

// Determine if the single template should be disabled (strict comparison)
$disable_single_template = get_option('single_team_member_view') === 'True';

// Retrieve column settings with default values and ensure they are integers
$desktop_column = absint($settings['dwl_team_desktop'][0] ?? 4);
$tablet_column = absint($settings['dwl_team_tablet'][0] ?? 3);
$mobile_column = absint($settings['dwl_team_mobile'][0] ?? 1);

$bootstrap_class = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);

foreach ($data['posts'] as $teamInfo) {
    $job_title = sanitize_text_field(get_post_meta($teamInfo->ID, 'tm_jtitle', true));
    ?>

<div <?php post_class("team-member-info-wrap m-0 p-2 " . esc_attr($bootstrap_class)); ?>>
        <div class="team-member-info-content">
            <div class="team-member-grid-style-two">
                <div class="grid-team-inner">
                    <?php if (!$disable_single_template): ?>
                        <a href="<?php echo esc_url(get_the_permalink($teamInfo->ID)); ?>">
                    <?php endif; ?>
                            <?php echo wp_kses_post(Helper::get_team_picture($teamInfo->ID, $image_size, 'dwl-box-shadow')); ?>
                            <div class="team-member-grid-content-overlay"></div>
                            <div class="team-member-grid-content">
                                <div class="team-member-grid-info">
                                    <h2 class="team-member-title"><?php echo esc_html($teamInfo->post_title); ?></h2>
                                    <?php if (!empty($job_title) && in_array('tm_jtitle', $tm_single_fields)): ?>
                                        <h4 class="team-position"><?php echo esc_html($job_title); ?></h4>
                                    <?php endif; ?>
                                    <div class="team-member-grid-arrow">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                    <?php if (!$disable_single_template): ?>        
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php } ?>