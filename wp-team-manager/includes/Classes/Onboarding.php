<?php
namespace DWL\Wtm\Classes;

if (!defined('ABSPATH')) {
    wp_die('Direct access not allowed.');
}

class Onboarding {
    
    use \DWL\Wtm\Traits\Singleton;
    
    protected function init() {
        add_action('admin_init', [$this, 'check_onboarding_status']);
        add_action('wp_ajax_wtm_complete_onboarding_step', [$this, 'complete_step']);
        add_action('wp_ajax_wtm_skip_onboarding', [$this, 'skip_onboarding']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_onboarding_assets']);
        add_action('save_post', [$this, 'check_team_member_creation'], 10, 3);
    }
    
    public function check_onboarding_status() {
        // Check for step advancement notification
        if (get_transient('wtm_step_advanced_' . get_current_user_id())) {
            add_action('admin_notices', [$this, 'show_step_advanced_notice']);
            delete_transient('wtm_step_advanced_' . get_current_user_id());
        }
        
        if (!$this->should_show_onboarding()) return;
        
        add_action('admin_notices', [$this, 'show_onboarding_notice']);
    }
    
    public function should_show_onboarding(): bool {
        // Don't show if already completed
        if (get_option('wtm_onboarding_completed')) {
            return false;
        }
        
        // Only show on team manager related pages
        $screen = get_current_screen();
        if (!$screen) {
            return false;
        }
        
        $team_pages = [
            'team_manager',
            'edit-team_manager', 
            'team_manager_page_team-manager-tools',
            'team_manager_page_team-manager-shortcode-generator',
            'team_manager_page_wtm-import-export',
            'team_manager_page_wtm-enhanced-search'
        ];
        
        $is_team_page = false;
        foreach ($team_pages as $page) {
            if (strpos($screen->id, $page) !== false) {
                $is_team_page = true;
                break;
            }
        }
        
        if (!$is_team_page) {
            return false;
        }
        
        // Show if plugin was recently activated (within 7 days) or if no team members exist
        $activation_time = get_option('wp_team_manager_activation_time');
        
        // Optimize: Cache team count for 5 minutes to avoid repeated queries
        $team_count_cache = get_transient('wtm_team_count_cache');
        if ($team_count_cache === false) {
            $team_count = wp_count_posts('team_manager');
            $published_count = $team_count && isset($team_count->publish) ? $team_count->publish : 0;
            set_transient('wtm_team_count_cache', $published_count, 5 * MINUTE_IN_SECONDS);
        } else {
            $published_count = $team_count_cache;
        }
        
        return ($activation_time && (time() - $activation_time) < (7 * DAY_IN_SECONDS)) || 
               ($published_count == 0);
    }
    
    public function show_onboarding_notice() {
        $current_step = get_option('wtm_onboarding_step', 1);
        $steps = $this->get_onboarding_steps();
        
        if (!isset($steps[$current_step])) return;
        
        $step = $steps[$current_step];
        
        echo '<div class="notice notice-info wtm-onboarding-notice" data-step="' . esc_attr($current_step) . '">';
        echo '<div class="wtm-onboarding-content">';
        echo '<h3>' . esc_html($step['title']) . '</h3>';
        echo '<p>' . esc_html($step['description']) . '</p>';
        
        if (!empty($step['action_url'])) {
            echo '<p><a href="' . esc_url($step['action_url']) . '" class="button button-primary">' . esc_html($step['action_text']) . '</a></p>';
        }
        
        echo '<div class="wtm-onboarding-progress">';
        echo '<span>' . sprintf(__('Step %d of %d', 'wp-team-manager'), $current_step, count($steps)) . '</span>';
        echo '<div class="wtm-progress-bar">';
        echo '<div class="wtm-progress-fill" style="width: ' . (($current_step / count($steps)) * 100) . '%"></div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="wtm-onboarding-actions">';
        echo '<button class="button wtm-complete-step" data-step="' . esc_attr($current_step) . '">' . __('Mark as Complete', 'wp-team-manager') . '</button>';
        echo '<button class="button-link wtm-skip-onboarding">' . __('Skip Setup', 'wp-team-manager') . '</button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    public function get_onboarding_steps(): array {
        return [
            1 => [
                'title' => __('Welcome to Team Manager!', 'wp-team-manager'),
                'description' => __('Let\'s get you started by creating your first team member.', 'wp-team-manager'),
                'action_text' => __('Add First Member', 'wp-team-manager'),
                'action_url' => admin_url('post-new.php?post_type=team_manager')
            ],
            2 => [
                'title' => __('Explore Tools & Features', 'wp-team-manager'),
                'description' => __('Discover powerful tools for managing your team data and customizing displays.', 'wp-team-manager'),
                'action_text' => __('View Tools', 'wp-team-manager'),
                'action_url' => admin_url('edit.php?post_type=team_manager&page=team-manager-tools')
            ],
            3 => [
                'title' => __('Add Team to Your Site', 'wp-team-manager'),
                'description' => __('Use shortcodes, Gutenberg blocks, or Elementor widgets to display your team.', 'wp-team-manager'),
                'action_text' => __('Getting Started Guide', 'wp-team-manager'),
                'action_url' => admin_url('edit.php?post_type=team_manager&page=team-manager-tools')
            ]
        ];
    }
    
    public function complete_step() {
        check_ajax_referer('wtm_onboarding_nonce', 'nonce');
        
        // Security: Verify user can edit posts (any user who can manage team members)
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to perform this action.', 'wp-team-manager' )
            ], 403 );
        }
        
        $step = intval($_POST['step'] ?? 0);
        $current_step = get_option('wtm_onboarding_step', 1);
        
        if ($step !== $current_step) {
            wp_send_json_error( [
                'message' => __( 'Invalid step.', 'wp-team-manager' )
            ], 400 );
        }
        
        $steps = $this->get_onboarding_steps();
        $next_step = $step + 1;
        
        if ($next_step > count($steps)) {
            // Onboarding complete
            update_option('wtm_onboarding_completed', true);
            delete_option('wtm_onboarding_step');
            wp_send_json_success(['completed' => true]);
        } else {
            update_option('wtm_onboarding_step', $next_step);
            wp_send_json_success(['next_step' => $next_step]);
        }
    }
    
    public function skip_onboarding() {
        check_ajax_referer('wtm_onboarding_nonce', 'nonce');
        
        // Security: Verify user can edit posts (any user who can manage team members)
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( [
                'message' => __( 'You do not have permission to perform this action.', 'wp-team-manager' )
            ], 403 );
        }
        
        update_option('wtm_onboarding_completed', true);
        delete_option('wtm_onboarding_step');
        
        wp_send_json_success( [
            'message' => __( 'Onboarding skipped successfully.', 'wp-team-manager' )
        ] );
    }
    
    public function check_team_member_creation($post_id, $post, $update) {
        // Skip if this is an autosave or revision
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (wp_is_post_revision($post_id)) {
            return;
        }
        
        // Only proceed for team_manager posts
        if ($post->post_type !== 'team_manager') {
            return;
        }
        
        // Only proceed if onboarding is active and on step 1
        $current_step = get_option('wtm_onboarding_step', 1);
        if ($current_step !== 1 || get_option('wtm_onboarding_completed')) {
            return;
        }
        
        // Check if this is a published team member
        if ($post->post_status === 'publish') {
            // If this is a new publish (not an update to already published post)
            if (!$update || get_post_status($post_id) !== 'publish') {
                // Auto-advance to step 2 (we know at least 1 post is now published)
                update_option('wtm_onboarding_step', 2);
                
                // Clear the team count cache
                delete_transient('wtm_team_count_cache');
                
                // Set a transient to show success message
                set_transient('wtm_step_advanced_' . get_current_user_id(), true, 300);
            }
        }
    }
    
    public function enqueue_onboarding_assets($hook) {
        if (!$this->should_show_onboarding()) return;
        
        wp_enqueue_script(
            'wtm-onboarding',
            TM_ADMIN_ASSETS . '/js/onboarding.js',
            ['jquery'],
            TM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wtm-onboarding',
            TM_ADMIN_ASSETS . '/css/onboarding.css',
            [],
            TM_VERSION
        );
        
        wp_localize_script('wtm-onboarding', 'wtmOnboarding', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wtm_onboarding_nonce')
        ]);
    }
    
    public function show_step_advanced_notice() {
        echo '<div class="notice notice-success is-dismissible wtm-step-advanced-notice">';
        echo '<p><strong>🎉 Great job!</strong> You\'ve created your first team member. The setup guide has automatically advanced to the next step.</p>';
        echo '</div>';
    }
}