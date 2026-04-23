<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Search & Filtering System
 * 
 * Provides live search with autocomplete, advanced multi-criteria filtering,
 * search analytics, and saved filter presets.
 */
class EnhancedSearch {
    
    use \DWL\Wtm\Traits\Singleton;

    private const ANALYTICS_OPTION = 'wtm_search_analytics';
    private const PRESETS_OPTION = 'wtm_filter_presets';
    private const CACHE_GROUP = 'wtm_search';

    // Rate limiting constants
    private const RATE_LIMIT_REQUESTS = 15; // Max requests per time window
    private const RATE_LIMIT_WINDOW = 60;   // Time window in seconds
    private const MIN_SEARCH_LENGTH = 2;    // Minimum search term length
    private const MAX_SEARCH_LENGTH = 100;  // Maximum search term length
    private const MAX_RESULTS_PER_PAGE = 100; // Maximum results per page
    private const MAX_ANALYTICS_ENTRIES_PER_DAY = 100; // Limit analytics entries

    public function __construct() {
        $this->init_hooks();
        $this->init_cache_hooks();
    }

    private function init_hooks(): void {
        // Enhanced live search AJAX handlers - Changed action names to avoid conflict with LiveSearch
        add_action('wp_ajax_wtm_enhanced_search', [$this, 'handle_live_search']);
        add_action('wp_ajax_nopriv_wtm_enhanced_search', [$this, 'handle_live_search']);
        add_action('wp_ajax_wtm_get_autocomplete', [$this, 'handle_autocomplete']);
        add_action('wp_ajax_nopriv_wtm_get_autocomplete', [$this, 'handle_autocomplete']);
        add_action('wp_ajax_wtm_save_preset', [$this, 'handle_save_preset']);
        add_action('wp_ajax_wtm_load_preset', [$this, 'handle_load_preset']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Check if user has exceeded rate limit
     *
     * @return bool True if rate limit exceeded
     */
    private function is_rate_limited(): bool {
        $user_ip = $this->get_user_ip();
        $transient_key = 'wtm_enhanced_search_rate_' . md5($user_ip);
        $requests = get_transient($transient_key);

        if ($requests === false) {
            set_transient($transient_key, 1, self::RATE_LIMIT_WINDOW);
            return false;
        }

        if ($requests >= self::RATE_LIMIT_REQUESTS) {
            return true;
        }

        set_transient($transient_key, $requests + 1, self::RATE_LIMIT_WINDOW);
        return false;
    }

    /**
     * Get user IP address
     *
     * @return string
     */
    private function get_user_ip(): string {
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
     * Handle live search with real-time results
     */
    public function handle_live_search(): void {
        check_ajax_referer('wtm_search_nonce', 'nonce');

        // Rate limiting check
        if ($this->is_rate_limited()) {
            wp_send_json_error([
                'message' => __('Too many search requests. Please try again in a minute.', 'wp-team-manager')
            ], 429);
            return;
        }

        if (Helper::freemius_is_free_user()) {
            wp_send_json_error(__('Pro feature only', 'wp-team-manager'));
        }

        $search_term = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $filters = $this->sanitize_filters($_POST['filters'] ?? []);
        $settings = $this->sanitize_settings($_POST['settings'] ?? []);

        // Validate search term length
        $search_length = mb_strlen($search_term);
        if (!empty($search_term) && $search_length < self::MIN_SEARCH_LENGTH) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Search term must be at least %d characters long.', 'wp-team-manager'),
                    self::MIN_SEARCH_LENGTH
                )
            ], 400);
            return;
        }

        if ($search_length > self::MAX_SEARCH_LENGTH) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Search term cannot exceed %d characters.', 'wp-team-manager'),
                    self::MAX_SEARCH_LENGTH
                )
            ], 400);
            return;
        }

        // Track search analytics
        $this->track_search($search_term, $filters);

        $results = $this->perform_search($search_term, $filters, $settings);

        wp_send_json_success([
            'html' => $results['html'],
            'count' => $results['count'],
            'suggestions' => $this->get_search_suggestions($search_term)
        ]);
    }

    /**
     * Handle autocomplete suggestions
     */
    public function handle_autocomplete(): void {
        check_ajax_referer('wtm_search_nonce', 'nonce');

        // Rate limiting check
        if ($this->is_rate_limited()) {
            wp_send_json_error([
                'message' => __('Too many requests. Please try again in a minute.', 'wp-team-manager')
            ], 429);
            return;
        }

        if (Helper::freemius_is_free_user()) {
            wp_send_json_error(__('Pro feature only', 'wp-team-manager'));
        }

        $term = isset($_POST['term']) ? sanitize_text_field(wp_unslash($_POST['term'])) : '';

        // Validate term length
        $term_length = mb_strlen($term);
        if ($term_length > self::MAX_SEARCH_LENGTH) {
            wp_send_json_error([
                'message' => __('Search term too long.', 'wp-team-manager')
            ], 400);
            return;
        }

        $suggestions = $this->get_autocomplete_suggestions($term);

        wp_send_json_success($suggestions);
    }

    /**
     * Save filter preset (per-user)
     */
    public function handle_save_preset(): void {
        check_ajax_referer('wtm_search_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in to save presets', 'wp-team-manager'));
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-team-manager'));
            return;
        }

        $user_id = get_current_user_id();
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
        $filters = $this->sanitize_filters($_POST['filters'] ?? []);

        if (empty($name)) {
            wp_send_json_error(__('Preset name is required', 'wp-team-manager'));
            return;
        }

        // Limit preset name length
        if (mb_strlen($name) > 50) {
            wp_send_json_error(__('Preset name too long (max 50 characters)', 'wp-team-manager'));
            return;
        }

        // Get user's presets (stored per-user)
        $presets = get_user_meta($user_id, 'wtm_filter_presets', true);
        if (!is_array($presets)) {
            $presets = [];
        }



        $presets[$name] = $filters;
        update_user_meta($user_id, 'wtm_filter_presets', $presets);

        wp_send_json_success(__('Preset saved successfully', 'wp-team-manager'));
    }

    /**
     * Load filter preset (per-user)
     */
    public function handle_load_preset(): void {
        check_ajax_referer('wtm_search_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in to load presets', 'wp-team-manager'));
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'wp-team-manager'));
            return;
        }

        $user_id = get_current_user_id();
        $name = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';

        $presets = get_user_meta($user_id, 'wtm_filter_presets', true);
        if (!is_array($presets)) {
            $presets = [];
        }

        if (!isset($presets[$name])) {
            wp_send_json_error(__('Preset not found', 'wp-team-manager'));
            return;
        }

        wp_send_json_success($presets[$name]);
    }



    /**
     * Perform enhanced search with multiple criteria
     */
    private function perform_search(string $search_term, array $filters, array $settings): array {
        $cache_key = md5(serialize(compact('search_term', 'filters', 'settings')));
        $cached = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached !== false) {
            return $cached;
        }

        $query_args = [
            'post_type' => 'team_manager',
            'posts_per_page' => $settings['per_page'] ?? 12,
            'paged' => $settings['page'] ?? 1,
            'post_status' => 'publish',
            'suppress_filters' => false,
            'no_found_rows' => false
        ];

        // Add search term
        if (!empty($search_term)) {
            $query_args['s'] = $search_term;
            
            // Enhanced search in meta fields
            add_filter('posts_search', [$this, 'extend_search_to_meta'], 10, 2);
        }

        // Add taxonomy filters
        if (!empty($filters['taxonomies'])) {
            $query_args['tax_query'] = $this->build_tax_query($filters['taxonomies']);
        }

        // Add meta filters
        if (!empty($filters['meta'])) {
            $query_args['meta_query'] = $this->build_meta_query($filters['meta']);
        }

        // Add date filters
        if (!empty($filters['date_range'])) {
            $query_args['date_query'] = $this->build_date_query($filters['date_range']);
        }

        $query = new \WP_Query($query_args);
        
        // Simple results for standalone search widget
        $results_html = [];
        if ($query->have_posts()) {
            foreach ($query->posts as $post) {
                $job_title = get_post_meta($post->ID, 'tm_jtitle', true);
                $results_html[] = sprintf(
                    '<div class="wtm-search-item" data-id="%d"><h4>%s</h4><p>%s</p></div>',
                    $post->ID,
                    esc_html($post->post_title),
                    esc_html($job_title)
                );
            }
            $html = implode('', $results_html);
        } else {
            $html = '<div class="wtm-no-results">' . __('No team members found', 'wp-team-manager') . '</div>';
        }

        wp_reset_postdata();
        
        // Remove filter after use
        if (!empty($search_term)) {
            remove_filter('posts_search', [$this, 'extend_search_to_meta'], 10);
        }

        $result = [
            'html' => $html,
            'count' => $query->found_posts,
            'pages' => $query->max_num_pages
        ];

        wp_cache_set($cache_key, $result, self::CACHE_GROUP, HOUR_IN_SECONDS);
        return $result;
    }

    /**
     * Extend search to include meta fields
     */
    public function extend_search_to_meta(string $search, \WP_Query $query): string {
        if (!$query->is_search() || $query->get('post_type') !== 'team_manager') {
            return $search;
        }

        global $wpdb;
        $search_term = $query->get('s');

        if (empty($search_term)) {
            return $search;
        }

        $meta_fields = ['tm_jtitle', 'tm_email', 'tm_location', 'tm_mobile'];
        $like_pattern = '%' . $wpdb->esc_like($search_term) . '%';

        // Build safe meta query using array_map with wpdb->prepare
        $meta_conditions = array_map(function($field) use ($wpdb, $like_pattern) {
            return $wpdb->prepare(
                "(pm.meta_key = %s AND pm.meta_value LIKE %s)",
                $field,
                $like_pattern
            );
        }, $meta_fields);

        if (!empty($meta_conditions)) {
            // Use prepared statement for the entire subquery
            $meta_query = implode(' OR ', $meta_conditions);
            $search .= $wpdb->prepare(
                " OR %s IN (
                    SELECT DISTINCT post_id FROM {$wpdb->postmeta} pm
                    WHERE " . $meta_query . "
                )",
                $wpdb->posts . '.ID'
            );
        }

        return $search;
    }

    /**
     * Get autocomplete suggestions
     */
    private function get_autocomplete_suggestions(string $term): array {
        if (strlen($term) < 2) {
            return [];
        }

        $suggestions = [];
        
        // Get from post titles
        $posts = get_posts([
            'post_type' => 'team_manager',
            's' => $term,
            'posts_per_page' => 5,
            'fields' => 'ids'
        ]);

        foreach ($posts as $post_id) {
            $suggestions[] = [
                'label' => get_the_title($post_id),
                'value' => get_the_title($post_id),
                'type' => 'name'
            ];
        }

        // Get from job titles
        global $wpdb;
        $job_titles = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} 
             WHERE meta_key = 'tm_jtitle' 
             AND meta_value LIKE %s 
             LIMIT 5",
            '%' . $wpdb->esc_like($term) . '%'
        ));

        foreach ($job_titles as $job) {
            $suggestions[] = [
                'label' => $job->meta_value,
                'value' => $job->meta_value,
                'type' => 'job_title'
            ];
        }

        return array_slice($suggestions, 0, 10);
    }

    /**
     * Track search analytics
     */
    private function track_search(string $search_term, array $filters): void {
        if (empty($search_term) && empty($filters)) {
            return;
        }

        // Use transient to batch analytics updates (update every 5 minutes)
        $transient_key = 'wtm_analytics_batch_' . current_time('YmdHi');
        $batch = get_transient($transient_key);

        if ($batch === false) {
            $batch = ['searches' => [], 'filters' => []];
        }

        $today = current_time('Y-m-d');

        if (!empty($search_term)) {
            // Limit number of unique search terms tracked per day
            if (count($batch['searches']) < self::MAX_ANALYTICS_ENTRIES_PER_DAY) {
                $batch['searches'][$search_term] = ($batch['searches'][$search_term] ?? 0) + 1;
            }
        }

        if (!empty($filters)) {
            $filter_key = md5(wp_json_encode($filters));
            if (count($batch['filters']) < self::MAX_ANALYTICS_ENTRIES_PER_DAY) {
                $batch['filters'][$filter_key] = ($batch['filters'][$filter_key] ?? 0) + 1;
            }
        }

        // Store batch in transient for 5 minutes
        set_transient($transient_key, $batch, 5 * MINUTE_IN_SECONDS);

        // Periodically flush to database (10% chance per request)
        if (wp_rand(1, 10) === 1) {
            $this->flush_analytics_batch();
        }
    }

    /**
     * Flush analytics batch to database
     */
    private function flush_analytics_batch(): void {
        $analytics = get_option(self::ANALYTICS_OPTION, []);
        $today = current_time('Y-m-d');

        // Get all pending batches
        global $wpdb;
        $transients = $wpdb->get_col(
            "SELECT option_name FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_wtm_analytics_batch_%'"
        );

        foreach ($transients as $option_name) {
            $transient_key = str_replace('_transient_', '', $option_name);
            $batch = get_transient($transient_key);

            if ($batch !== false) {
                if (!isset($analytics[$today])) {
                    $analytics[$today] = ['searches' => [], 'filters' => []];
                }

                // Merge batch data
                foreach ($batch['searches'] as $term => $count) {
                    $analytics[$today]['searches'][$term] =
                        ($analytics[$today]['searches'][$term] ?? 0) + $count;
                }

                foreach ($batch['filters'] as $key => $count) {
                    $analytics[$today]['filters'][$key] =
                        ($analytics[$today]['filters'][$key] ?? 0) + $count;
                }

                delete_transient($transient_key);
            }
        }

        // Keep only last 30 days and limit entries per day
        $cutoff = current_time('Y-m-d', strtotime('-30 days'));
        $analytics = array_filter($analytics, function($key) use ($cutoff) {
            return $key >= $cutoff;
        }, ARRAY_FILTER_USE_KEY);

        // Limit entries per day to prevent unbounded growth
        foreach ($analytics as $date => &$data) {
            if (count($data['searches']) > self::MAX_ANALYTICS_ENTRIES_PER_DAY) {
                arsort($data['searches']);
                $data['searches'] = array_slice($data['searches'], 0, self::MAX_ANALYTICS_ENTRIES_PER_DAY, true);
            }
            if (count($data['filters']) > self::MAX_ANALYTICS_ENTRIES_PER_DAY) {
                arsort($data['filters']);
                $data['filters'] = array_slice($data['filters'], 0, self::MAX_ANALYTICS_ENTRIES_PER_DAY, true);
            }
        }

        update_option(self::ANALYTICS_OPTION, $analytics, false);
    }

    /**
     * Get search suggestions based on popular searches
     */
    private function get_search_suggestions(string $current_term): array {
        $analytics = get_option(self::ANALYTICS_OPTION, []);
        $suggestions = [];

        foreach ($analytics as $day_data) {
            foreach ($day_data['searches'] as $term => $count) {
                if (stripos($term, $current_term) !== false && $term !== $current_term) {
                    $suggestions[$term] = ($suggestions[$term] ?? 0) + $count;
                }
            }
        }

        arsort($suggestions);
        return array_slice(array_keys($suggestions), 0, 5);
    }

    /**
     * Build taxonomy query from filters
     */
    private function build_tax_query(array $taxonomies): array {
        $tax_query = ['relation' => 'AND'];

        foreach ($taxonomies as $taxonomy => $terms) {
            if (!empty($terms) && taxonomy_exists($taxonomy)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field' => 'term_id',
                    'terms' => array_map('absint', (array)$terms),
                    'operator' => 'IN'
                ];
            }
        }

        return $tax_query;
    }

    /**
     * Build meta query from filters
     */
    private function build_meta_query(array $meta_filters): array {
        $meta_query = ['relation' => 'AND'];

        foreach ($meta_filters as $key => $value) {
            if (!empty($value)) {
                $meta_query[] = [
                    'key' => sanitize_key($key),
                    'value' => sanitize_text_field($value),
                    'compare' => 'LIKE'
                ];
            }
        }

        return $meta_query;
    }

    /**
     * Build date query from filters
     */
    private function build_date_query(array $date_range): array {
        $date_query = [];

        if (!empty($date_range['from'])) {
            $date_query['after'] = sanitize_text_field($date_range['from']);
        }

        if (!empty($date_range['to'])) {
            $date_query['before'] = sanitize_text_field($date_range['to']);
        }

        $date_query['inclusive'] = true;
        return [$date_query];
    }



    /**
     * Render standalone search widget
     */
    public function render_search_widget($atts): string {
        if (Helper::freemius_is_free_user()) {
            return '<p>' . __('Pro feature only', 'wp-team-manager') . '</p>';
        }

        $atts = shortcode_atts([
            'placeholder' => __('Search team members...', 'wp-team-manager'),
            'show_filters' => 'true',
            'show_presets' => 'true'
        ], $atts);

        ob_start();
        ?>
        <div class="wtm-live-search-widget">
            <div class="wtm-search-form">
                <input type="text" class="wtm-search-input" placeholder="<?php echo esc_attr($atts['placeholder']); ?>">
                <div class="wtm-search-suggestions"></div>
            </div>
            
            <?php if ($atts['show_filters'] === 'true'): ?>
            <div class="wtm-search-filters">
                <!-- Taxonomy filters will be populated by JS -->
            </div>
            <?php endif; ?>
            
            <?php if ($atts['show_presets'] === 'true'): ?>
            <div class="wtm-search-presets">
                <select class="wtm-preset-select">
                    <option value=""><?php _e('Load Preset', 'wp-team-manager'); ?></option>
                </select>
                <button class="wtm-save-preset"><?php _e('Save Preset', 'wp-team-manager'); ?></button>
            </div>
            <?php endif; ?>
            
            <div class="wtm-search-results"></div>
            <div class="wtm-results-count"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Sanitize filter inputs
     */
    private function sanitize_filters(array $filters): array {
        $sanitized = [];

        if (isset($filters['taxonomies'])) {
            foreach ($filters['taxonomies'] as $tax => $terms) {
                $sanitized['taxonomies'][sanitize_key($tax)] = array_map('absint', (array)$terms);
            }
        }

        if (isset($filters['meta'])) {
            foreach ($filters['meta'] as $key => $value) {
                $sanitized['meta'][sanitize_key($key)] = sanitize_text_field($value);
            }
        }

        if (isset($filters['date_range'])) {
            $sanitized['date_range'] = [
                'from' => sanitize_text_field($filters['date_range']['from'] ?? ''),
                'to' => sanitize_text_field($filters['date_range']['to'] ?? '')
            ];
        }

        return $sanitized;
    }

    /**
     * Sanitize settings inputs
     */
    private function sanitize_settings(array $settings): array {
        $per_page = absint($settings['per_page'] ?? 12);
        $page = absint($settings['page'] ?? 1);

        // Enforce maximum limits to prevent resource exhaustion
        if ($per_page > self::MAX_RESULTS_PER_PAGE) {
            $per_page = self::MAX_RESULTS_PER_PAGE;
        }

        if ($per_page < 1) {
            $per_page = 12;
        }

        if ($page < 1) {
            $page = 1;
        }

        // Prevent excessive pagination (limit to 100 pages)
        if ($page > 100) {
            $page = 100;
        }

        return [
            'layout' => sanitize_key($settings['layout'] ?? 'grid'),
            'style' => sanitize_key($settings['style'] ?? 'style-1'),
            'per_page' => $per_page,
            'page' => $page
        ];
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets(): void {
        if (!$this->should_load_assets() || Helper::freemius_is_free_user()) {
            return;
        }

        wp_enqueue_script(
            'wtm-enhanced-search',
            TM_URL . 'public/assets/js/enhanced-search.js',
            ['jquery', 'jquery-ui-autocomplete'],
            TM_VERSION,
            true
        );

        wp_enqueue_style(
            'wtm-enhanced-search',
            TM_URL . 'public/assets/css/enhanced-search.css',
            [],
            TM_VERSION
        );

        wp_localize_script('wtm-enhanced-search', 'wtmSearch', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wtm_search_nonce'),
            'strings' => [
                'searching' => __('Searching...', 'wp-team-manager'),
                'noResults' => __('No results found', 'wp-team-manager'),
                'savePreset' => __('Save Preset', 'wp-team-manager'),
                'loadPreset' => __('Load Preset', 'wp-team-manager')
            ]
        ]);
    }

    /**
     * Check if assets should be loaded
     */
    private function should_load_assets(): bool {
        return is_singular('team_manager') || 
               has_shortcode(get_post()->post_content ?? '', 'team') ||
               is_post_type_archive('team_manager');
    }

    /**
     * Get search analytics data
     */
    public function get_analytics(): array {
        return get_option(self::ANALYTICS_OPTION, []);
    }

    /**
     * Get saved presets
     */
    public function get_presets(): array {
        return get_option(self::PRESETS_OPTION, []);
    }

    /**
     * Clear search cache when team data changes
     */
    public static function clear_search_cache(): void {
        wp_cache_flush_group(self::CACHE_GROUP);
    }

    /**
     * Hook to clear cache on team member updates
     */
    public function init_cache_hooks(): void {
        add_action('save_post', function($post_id) {
            if (get_post_type($post_id) === 'team_manager') {
                self::clear_search_cache();
            }
        });
        
        add_action('delete_post', function($post_id) {
            if (get_post_type($post_id) === 'team_manager') {
                self::clear_search_cache();
            }
        });
    }
}