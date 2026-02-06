<?php
namespace DWL\Wtm\Elementor\DynamicTags;

if (!defined('ABSPATH')) exit;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;
use DWL\Wtm\Classes\Helper;

/**
 * Basic Team Member Dynamic Tags - Free Version
 */
class TeamMemberName extends Tag {
    
    public function get_name() {
        return 'team-member-name';
    }
    
    public function get_title() {
        return __('Team Member Name', 'wp-team-manager');
    }
    
    public function get_group() {
        return 'team-manager';
    }
    
    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }
    
    public function render() {
        $post_id = get_the_ID();
        if (get_post_type($post_id) === 'team_manager') {
            echo esc_html(get_the_title($post_id));
        }
    }
}

class TeamMemberPosition extends Tag {
    
    public function get_name() {
        return 'team-member-position';
    }
    
    public function get_title() {
        return __('Team Member Position', 'wp-team-manager');
    }
    
    public function get_group() {
        return 'team-manager';
    }
    
    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }
    
    public function render() {
        $post_id = get_the_ID();
        if (get_post_type($post_id) === 'team_manager') {
            $position = get_post_meta($post_id, 'tm_jtitle', true);
            echo esc_html($position);
        }
    }
}

class TeamMemberImage extends Tag {
    
    public function get_name() {
        return 'team-member-image';
    }
    
    public function get_title() {
        return __('Team Member Image', 'wp-team-manager');
    }
    
    public function get_group() {
        return 'team-manager';
    }
    
    public function get_categories() {
        return [Module::IMAGE_CATEGORY];
    }
    
    public function render() {
        $post_id = get_the_ID();
        if (get_post_type($post_id) === 'team_manager') {
            echo Helper::get_team_picture($post_id, 'medium');
        }
    }
}

class TeamMemberBio extends Tag {
    
    public function get_name() {
        return 'team-member-bio';
    }
    
    public function get_title() {
        return __('Team Member Bio', 'wp-team-manager');
    }
    
    public function get_group() {
        return 'team-manager';
    }
    
    public function get_categories() {
        return [Module::TEXT_CATEGORY];
    }
    
    public function render() {
        $post_id = get_the_ID();
        if (get_post_type($post_id) === 'team_manager') {
            $bio = get_post_meta($post_id, 'tm_short_bio', true);
            echo wp_kses_post($bio);
        }
    }
}

// Pro upgrade notice for advanced features
class TeamMemberProNotice {
    
    public static function show_upgrade_notice() {
        if (Helper::freemius_is_free_user()) {
            echo '<div class="wtm-pro-notice">';
            echo '<p>' . __('Advanced dynamic tags (Image, Bio, Social Links) available in Pro version.', 'wp-team-manager') . '</p>';
            echo '<a href="' . esc_url(Helper::freemius_upgrade_url()) . '" class="button button-primary">';
            echo __('Upgrade to Pro', 'wp-team-manager');
            echo '</a>';
            echo '</div>';
        }
    }
}