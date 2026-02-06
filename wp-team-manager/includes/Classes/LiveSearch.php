<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

if (!defined('ABSPATH')) {
    exit;
}

class LiveSearch {

    use \DWL\Wtm\Traits\Singleton;

    /**
     * Rate limiting constants
     */
    const RATE_LIMIT_REQUESTS = 10; // Max requests per time window
    const RATE_LIMIT_WINDOW = 60;   // Time window in seconds
    const MIN_SEARCH_LENGTH = 2;    // Minimum search term length
    const MAX_SEARCH_LENGTH = 100;  // Maximum search term length

    /**
     * Allowed layout types
     */
    private $allowed_layouts = ['grid', 'list', 'table', 'slider', 'carousel'];

    /**
     * Allowed style types
     */
    private $allowed_styles = ['style-1', 'style-2', 'style-3', 'style-4', 'style-5'];

    public function __construct() {
        add_action('wp_ajax_wtm_live_search', [$this, 'handle_search']);
        add_action('wp_ajax_nopriv_wtm_live_search', [$this, 'handle_search']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'wtm-live-search',
            TM_PUBLIC . '/assets/js/live-search.js',
            ['jquery'],
            TM_VERSION,
            true
        );
        
        wp_localize_script('wtm-live-search', 'wtm_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wtm_search_nonce')
        ]);
    }

    /**
     * Check if user has exceeded rate limit
     *
     * @return bool True if rate limit exceeded
     */
    private function is_rate_limited() {
        $user_ip = $this->get_user_ip();
        $transient_key = 'wtm_search_rate_' . md5($user_ip);
        $requests = get_transient($transient_key);

        if ($requests === false) {
            // First request in the time window
            set_transient($transient_key, 1, self::RATE_LIMIT_WINDOW);
            return false;
        }

        if ($requests >= self::RATE_LIMIT_REQUESTS) {
            return true;
        }

        // Increment request count
        set_transient($transient_key, $requests + 1, self::RATE_LIMIT_WINDOW);
        return false;
    }

    /**
     * Get user IP address
     *
     * @return string
     */
    private function get_user_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return sanitize_text_field($ip);
    }

    /**
     * Validate search parameters
     *
     * @param string $search Search term
     * @param string $layout Layout type
     * @param string $style Style type
     * @return array|WP_Error Array with validated params or WP_Error on failure
     */
    private function validate_search_params($search, $layout, $style) {
        // Validate search term length
        $search_length = mb_strlen($search);

        if ($search_length < self::MIN_SEARCH_LENGTH) {
            return new \WP_Error(
                'invalid_search_length',
                sprintf(
                    __('Search term must be at least %d characters long.', 'wp-team-manager'),
                    self::MIN_SEARCH_LENGTH
                )
            );
        }

        if ($search_length > self::MAX_SEARCH_LENGTH) {
            return new \WP_Error(
                'search_too_long',
                sprintf(
                    __('Search term cannot exceed %d characters.', 'wp-team-manager'),
                    self::MAX_SEARCH_LENGTH
                )
            );
        }

        // Validate layout
        if (!in_array($layout, $this->allowed_layouts, true)) {
            $layout = 'grid'; // Fallback to default
        }

        // Validate style
        if (!in_array($style, $this->allowed_styles, true)) {
            $style = 'style-1'; // Fallback to default
        }

        return [
            'search' => $search,
            'layout' => $layout,
            'style' => $style
        ];
    }

    public function handle_search() {
        // Check nonce
        check_ajax_referer('wtm_search_nonce', 'nonce');

        // Rate limiting check
        if ($this->is_rate_limited()) {
            wp_send_json_error([
                'message' => __('Too many search requests. Please try again in a minute.', 'wp-team-manager')
            ], 429);
            return;
        }

        // Sanitize inputs
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $layout = isset($_POST['layout']) ? sanitize_text_field(wp_unslash($_POST['layout'])) : 'grid';
        $style = isset($_POST['style']) ? sanitize_text_field(wp_unslash($_POST['style'])) : 'style-1';

        // Validate parameters
        $validated = $this->validate_search_params($search, $layout, $style);

        if (is_wp_error($validated)) {
            wp_send_json_error([
                'message' => $validated->get_error_message()
            ], 400);
            return;
        }

        $search = $validated['search'];
        $layout = $validated['layout'];
        $style = $validated['style'];

        // Check cache first
        $cache_key = 'wtm_search_' . md5($search . $layout . $style);
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            wp_send_json_success($cached_result);
            return;
        }

        // Perform search
        $query_args = [
            'post_type' => 'team_manager',
            'posts_per_page' => 20,
            's' => $search,
            'post_status' => 'publish'
        ];

        $team_data = Helper::get_team_data($query_args);

        if (empty($team_data['posts'])) {
            $no_results = '<p>' . esc_html__('No team members found.', 'wp-team-manager') . '</p>';
            // Cache empty results for shorter time
            set_transient($cache_key, $no_results, 5 * MINUTE_IN_SECONDS);
            wp_send_json_success($no_results);
            return;
        }

        $settings = [
            'layout_type' => $layout,
            $layout . '_style_type' => $style,
            'show_image' => 'yes',
            'show_title' => 'yes',
            'show_sub_title' => 'yes',
            'team_show_short_bio' => 'yes',
            'show_social' => 'yes',
            'columns' => '4',
            'columns_tablet' => '2',
            'columns_mobile' => '1',
            'image_size' => 'full',
            'popup_bar_show' => 'yes',
            'disable_single_member' => '',
            'bootstrap_class' => Helper::get_grid_layout_bootstrap_class('3', '2', '1')
        ];

        ob_start();
        echo '<div class="wtm-row g-2 g-lg-3">';
        Helper::renderElementorLayout($layout, $team_data['posts'], $settings);
        echo '</div>';
        $html = ob_get_clean();

        // Cache results for 15 minutes
        set_transient($cache_key, $html, 15 * MINUTE_IN_SECONDS);

        wp_send_json_success($html);
    }
}