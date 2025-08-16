<?php
use DWL\Wtm\Classes;

/**
 * Generate custom CSS
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('This script cannot be accessed directly.');
}

// Retrieve all settings safely
$all_settings = get_post_meta($scID);

// Define selector
$selector = "#dwl-team-wrapper-" . esc_attr($scID);

// Get color settings with default values
$card_background_color = sanitize_hex_color($all_settings['dwl_team_team_background_color'][0] ?? 'none');
$icon_background_color = sanitize_hex_color($all_settings['dwl_team_social_icon_color'][0] ?? '');

// Initialize CSS variable
$css = <<<CSS
$selector .team-member-info-content {
    background-color: {$card_background_color};
}

$selector .team-member-socials a {
    background-color: {$icon_background_color};
}

$selector .team-member-other-info .fas {
    color: {$icon_background_color};
}
CSS;

// Output CSS safely if not empty
if (!empty($css)) {
    echo wp_kses_post($css);
}