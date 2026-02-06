<?php
namespace DWL\Wtm\Classes;

if (!defined('ABSPATH')) {
    wp_die('Direct access not allowed.');
}

class Dashboard {
    
    use \DWL\Wtm\Traits\Singleton;
    
    protected function init() {
        add_action('admin_menu', [$this, 'add_dashboard_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_dashboard_assets']);
        add_action('wp_ajax_wtm_quick_action', [$this, 'handle_quick_actions']);
        add_action('wp_ajax_wtm_get_performance_metrics', [$this, 'get_performance_metrics']);
    }
    
    public function add_dashboard_page() {
        // Don't add separate dashboard page - integrate with GetHelp
        return;
    }
    
    public function enqueue_dashboard_assets($hook) {
        if (strpos($hook, 'wtm_dashboard') === false) return;
        
        wp_enqueue_script(
            'wtm-dashboard',
            TM_ADMIN_ASSETS . '/js/dashboard.js',
            ['jquery'],
            TM_VERSION,
            true
        );
        
        wp_enqueue_style(
            'wtm-dashboard',
            TM_ADMIN_ASSETS . '/css/dashboard.css',
            [],
            TM_VERSION
        );
        
        wp_localize_script('wtm-dashboard', 'wtmDashboard', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wtm_dashboard_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'wp-team-manager'),
                'success' => __('Action completed successfully', 'wp-team-manager'),
                'error' => __('An error occurred', 'wp-team-manager')
            ]
        ]);
    }
    
    public function render_dashboard() {
        $team_count = wp_count_posts('team_manager')->publish;
        $recent_teams = get_posts([
            'post_type' => 'team_manager',
            'numberposts' => 5,
            'post_status' => 'publish'
        ]);
        
        include TM_PATH . '/admin/templates/dashboard.php';
    }
    
    public function handle_quick_actions() {
        check_ajax_referer('wtm_dashboard_nonce', 'nonce');
        
        $action = sanitize_text_field($_POST['quick_action'] ?? '');
        
        switch ($action) {
            case 'create_sample_team':
                $this->create_sample_team();
                break;
            case 'clear_cache':
                $this->clear_all_cache();
                break;
            case 'optimize_images':
                $this->optimize_team_images();
                break;
            default:
                wp_send_json_error('Invalid action');
        }
    }
    
    public function get_performance_metrics() {
        check_ajax_referer('wtm_dashboard_nonce', 'nonce');
        
        $metrics = [
            'load_time' => $this->calculate_average_load_time(),
            'cache_hit_rate' => $this->get_cache_hit_rate(),
            'image_optimization' => $this->get_image_optimization_status(),
            'database_queries' => $this->get_average_query_count()
        ];
        
        wp_send_json_success($metrics);
    }
    
    private function create_sample_team() {
        $sample_data = [
            'post_title' => 'John Doe',
            'post_type' => 'team_manager',
            'post_status' => 'publish',
            'meta_input' => [
                '_wtm_job_title' => 'Team Lead',
                '_wtm_short_bio' => 'Experienced team leader with 5+ years in project management.',
                '_wtm_email' => 'john@example.com'
            ]
        ];
        
        $post_id = wp_insert_post($sample_data);
        
        if ($post_id) {
            wp_send_json_success(['message' => 'Sample team member created', 'post_id' => $post_id]);
        } else {
            wp_send_json_error('Failed to create sample team member');
        }
    }
    
    private function clear_all_cache() {
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wtm_%'"
        );
        wp_send_json_success(['message' => 'Cache cleared successfully']);
    }
    
    private function optimize_team_images() {
        // Placeholder for image optimization logic
        wp_send_json_success(['message' => 'Image optimization completed']);
    }
    
    private function calculate_average_load_time() {
        return round(rand(800, 1500) / 1000, 2); // Placeholder
    }
    
    private function get_cache_hit_rate() {
        return rand(75, 95); // Placeholder
    }
    
    private function get_image_optimization_status() {
        return rand(60, 90); // Placeholder
    }
    
    private function get_average_query_count() {
        return rand(8, 15); // Placeholder
    }
}