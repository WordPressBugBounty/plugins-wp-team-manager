<?php
namespace DWL\Wtm\Elementor\ThemeBuilder;

if (!defined('ABSPATH')) exit;

use DWL\Wtm\Classes\Helper;

/**
 * Team Archive Widget - Free version with Pro upgrade notice
 */
class TeamArchive {
    
    public static function render_upgrade_notice() {
        if (Helper::freemius_is_free_user()) {
            echo '<div class="wtm-pro-notice">';
            echo '<h3>' . __('Team Archive Widget', 'wp-team-manager') . '</h3>';
            echo '<p>' . __('Advanced theme builder integration available in Pro version.', 'wp-team-manager') . '</p>';
            echo '<a href="' . esc_url(Helper::freemius_upgrade_url()) . '" class="button button-primary">';
            echo __('Upgrade to Pro', 'wp-team-manager');
            echo '</a>';
            echo '</div>';
        }
    }
}