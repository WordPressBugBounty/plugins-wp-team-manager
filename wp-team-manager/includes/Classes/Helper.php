<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

use DWL\Wtm\Classes\Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Helper {

    /**
     * ===== Pro Features Registry =====
     * Centralized list of all pro features/fields in the plugin
     * Use this list to validate and check pro feature access throughout the codebase
     */

    /**
     * Get the complete list of pro feature keys
     *
     * @return array List of pro feature setting keys
     */
    public static function get_pro_features_list() {
        return [
            // Elementor Widget Pro Features
            'popup_bar_show'        => 'Team member detail popup modal',
            'disable_single_member' => 'Disable single member page links',
            'other_info_link'       => 'Enable clickable links for phone/mobile in other info',
            'progress_bar_show'     => 'Skills progress bars display',
            'enable_ajax_search'    => 'AJAX live search functionality',
            'pagination'            => 'Ajax pagination (when value is "ajax")',

            // Single Team Member Pro Features
            'tm_single_team_lightbox' => 'Lightbox/gallery feature for single team member page',
            'tm_skills'             => 'Skills/progress bars on single page',

            // Global Settings Pro Features
            'single_team_member_view' => 'Disable single team member page view (global setting)',
            'tm_custom_labels'        => 'Custom field labels (global setting)',
            'team_image_size_change'  => 'Custom single page image size (global setting)',
            'tm_schema_markup'        => 'Enable schema markup for SEO (global setting)',
            'tm_meta_description'     => 'Custom meta description template (global setting)',

            // Add future pro features here
            // 'feature_key'         => 'Feature description',
        ];
    }

    /**
     * Check if a given feature key is a pro feature
     *
     * @param string $feature_key The feature key to check
     * @return bool True if it's a pro feature, false otherwise
     */
    public static function is_pro_feature( $feature_key ) {
        $pro_features = self::get_pro_features_list();
        return array_key_exists( $feature_key, $pro_features );
    }

    /**
     * Get the description of a pro feature
     *
     * @param string $feature_key The feature key
     * @return string|null Feature description or null if not found
     */
    public static function get_pro_feature_description( $feature_key ) {
        $pro_features = self::get_pro_features_list();
        return $pro_features[ $feature_key ] ?? null;
    }

    /**
     * ===== Freemius helpers (centralized) =====
     * These wrappers avoid directly calling tmwstm_fs() all over the codebase
     * and give Admin UI a stable API to query Pro status / upgrade URL.
     */

    /** Get the Freemius SDK instance or null if unavailable */
    public static function fs() {
        return function_exists( 'tmwstm_fs' ) ? tmwstm_fs() : null;
    }

    /** Can current site use premium code (Freemius) */
    public static function freemius_can_use_premium() {
        $fs = self::fs();
        return $fs ? (bool) $fs->can_use_premium_code() : false;
    }

    /** Is user paying or on trial */
    public static function freemius_is_paying_or_trial() {
        $fs = self::fs();
        return $fs ? (bool) $fs->is_paying_or_trial() : false;
    }

    /** Is user NOT paying and NOT on trial */
    public static function freemius_is_not_paying() {
        $fs = self::fs();
        return $fs ? (bool) $fs->is_not_paying() && ! $fs->is_trial() : true;
    }

    /** Public upgrade URL for CTA buttons/links */
    public static function freemius_upgrade_url() {
        $fs = self::fs();
        return $fs ? esc_url( $fs->get_upgrade_url() ) : '#';
    }

    /**
     * Checks if the current user is a free user (not paying and not on trial) via Freemius.
     *
     * Returns true if Freemius is available, the user is not paying, and not on a trial.
     * Returns false otherwise (including if Freemius is unavailable).
     *
     * @return bool True if user is free (not paying, not trial), false otherwise.
     */
    public static function freemius_is_free_user() {
       
        return function_exists('tmwstm_fs')
            && tmwstm_fs()->is_not_paying()
            && ! tmwstm_fs()->is_trial();
    }

    /** Convenience: Is Pro effectively active for gating premium modules */
    public static function is_pro_active() {
        // Prefer can_use_premium_code which accounts for dev-mode scenarios too
        return self::freemius_can_use_premium() || self::freemius_is_paying_or_trial();
    }

    /**
     * Check if a pro feature setting is enabled and user has pro access
     *
     * This method provides secure validation for pro features by checking:
     * 1. Feature is registered in the pro features list (optional warning if not)
     * 2. User has an active pro license/trial
     * 3. The feature setting is explicitly enabled
     *
     * @param array $settings The settings array
     * @param string $feature_key The setting key to check (e.g. 'popup_bar_show')
     * @param string $expected_value The expected value (default: 'yes')
     * @param bool $strict If true, logs warning for unregistered features (default: false for backward compatibility)
     * @return bool True if user has pro access AND feature is enabled, false otherwise
     */
    public static function is_pro_feature_enabled( $settings, $feature_key, $expected_value = 'yes', $strict = false ) {
        // Optional: Warn if feature is not in the registry (helps catch mistakes during development)
        if ( $strict && ! self::is_pro_feature( $feature_key ) ) {
            if ( class_exists( __NAMESPACE__ . '\\Log' ) ) {
                Log::warning( 'Unregistered pro feature check', [
                    'feature_key' => $feature_key,
                    'tip' => 'Add this feature to Helper::get_pro_features_list()'
                ] );
            }
        }

        // Default to false if no pro access
        if ( ! self::is_pro_active() ) {
            return false;
        }

        // Default to false if setting is not set or doesn't match expected value
        if ( ! isset( $settings[ $feature_key ] ) || $settings[ $feature_key ] !== $expected_value ) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize settings array by removing/disabling pro features for free users
     *
     * This ensures that even if a free user somehow has pro settings enabled,
     * they will be stripped out before being used.
     *
     * @param array $settings The settings array to sanitize
     * @return array Sanitized settings array
     */
    public static function sanitize_pro_features( $settings ) {
        // If user has pro access, return settings as-is
        if ( self::is_pro_active() ) {
            return $settings;
        }

        // Remove/disable all pro features for free users
        $pro_features = array_keys( self::get_pro_features_list() );
        foreach ( $pro_features as $feature_key ) {
            if ( isset( $settings[ $feature_key ] ) ) {
                // Set to 'no' or empty to disable
                $settings[ $feature_key ] = '';
            }
        }

        return $settings;
    }

    /**
     * Get list of all pro features with their current status
     * Useful for debugging and admin displays
     *
     * @param array $settings Optional settings array to check
     * @return array Array of features with status
     */
    public static function get_pro_features_status( $settings = [] ) {
        $pro_features = self::get_pro_features_list();
        $has_pro = self::is_pro_active();
        $status = [];

        foreach ( $pro_features as $key => $description ) {
            $setting_value = $settings[ $key ] ?? null;
            $is_enabled = self::is_pro_feature_enabled( $settings, $key );

            $status[ $key ] = [
                'description'   => $description,
                'setting_value' => $setting_value,
                'is_enabled'    => $is_enabled,
                'has_pro'       => $has_pro,
                'reason'        => $is_enabled ? 'Active' : ( ! $has_pro ? 'No Pro License' : 'Setting Disabled' ),
            ];
        }

        return $status;
    }

    /**
     * ===== Dashboard Mode Presets =====
     * Central configuration for plugin "modes" (Corporate, Sports, Portfolio, etc.)
     * This is the SINGLE SOURCE OF TRUTH for all mode-related labels and visibility.
     */

    /**
     * Get all available dashboard mode presets.
     *
     * Each preset contains:
     * - 'label': Human-readable name for the dropdown
     * - 'description': Short description for the settings UI
     * - 'field_labels': Array mapping field keys to display labels
     * - 'taxonomy_labels': Array mapping taxonomy slugs to display labels
     * - 'hidden_fields': Array of field keys to hide in this mode
     *
     * @return array
     */
    public static function get_dashboard_presets() {
        return apply_filters( 'wp_team_manager_dashboard_presets', [
            'corporate' => [
                'label'       => __( 'Corporate / Business', 'wp-team-manager' ),
                'description' => __( 'Default mode for companies, agencies, and organizations.', 'wp-team-manager' ),
                'field_labels' => [
                    'tm_jtitle'          => __( 'Position/Job Title', 'wp-team-manager' ),
                    'tm_location'        => __( 'Location', 'wp-team-manager' ),
                    'tm_year_experience' => __( 'Years of Experience', 'wp-team-manager' ),
                    'tm_short_bio'       => __( 'Short Bio', 'wp-team-manager' ),
                    'tm_long_bio'        => __( 'Long Bio', 'wp-team-manager' ),
                    'tm_vcard'           => __( 'Add vCard File', 'wp-team-manager' ),
                    'tm_resume_url'      => __( 'Resume URL', 'wp-team-manager' ),
                    'tm_email'           => __( 'Email Address', 'wp-team-manager' ),
                    'tm_telephone'       => __( 'Telephone (Office)', 'wp-team-manager' ),
                    'tm_mobile'          => __( 'Mobile (Personal)', 'wp-team-manager' ),
                    'tm_web_url'         => __( 'Web URL', 'wp-team-manager' ),
                ],
                'taxonomy_labels' => [
                    'team_department'  => [
                        'name'                       => __( 'Departments', 'wp-team-manager' ),
                        'singular_name'              => __( 'Department', 'wp-team-manager' ),
                        'search_items'               => __( 'Search Department', 'wp-team-manager' ),
                        'popular_items'              => __( 'Popular Department', 'wp-team-manager' ),
                        'all_items'                  => __( 'All Departments', 'wp-team-manager' ),
                        'edit_item'                  => __( 'Edit Department', 'wp-team-manager' ),
                        'update_item'                => __( 'Update Department', 'wp-team-manager' ),
                        'add_new_item'               => __( 'Add New Department', 'wp-team-manager' ),
                        'new_item_name'              => __( 'New Department', 'wp-team-manager' ),
                        'separate_items_with_commas' => __( 'Separate Departments with commas', 'wp-team-manager' ),
                        'add_or_remove_items'        => __( 'Add or remove Department', 'wp-team-manager' ),
                        'choose_from_most_used'      => __( 'Choose from the most used Department', 'wp-team-manager' ),
                        'not_found'                  => __( 'No Department found.', 'wp-team-manager' ),
                        'menu_name'                  => __( 'Departments', 'wp-team-manager' ),
                    ],
                    'team_designation' => [
                        'name'                       => __( 'Designations', 'wp-team-manager' ),
                        'singular_name'              => __( 'Designation', 'wp-team-manager' ),
                        'search_items'               => __( 'Search Designation', 'wp-team-manager' ),
                        'popular_items'              => __( 'Popular Designation', 'wp-team-manager' ),
                        'all_items'                  => __( 'All Designations', 'wp-team-manager' ),
                        'edit_item'                  => __( 'Edit Designation', 'wp-team-manager' ),
                        'update_item'                => __( 'Update Designation', 'wp-team-manager' ),
                        'add_new_item'               => __( 'Add New Designation', 'wp-team-manager' ),
                        'new_item_name'              => __( 'New Designation', 'wp-team-manager' ),
                        'separate_items_with_commas' => __( 'Separate Designations with commas', 'wp-team-manager' ),
                        'add_or_remove_items'        => __( 'Add or remove Designation', 'wp-team-manager' ),
                        'choose_from_most_used'      => __( 'Choose from the most used Designation', 'wp-team-manager' ),
                        'not_found'                  => __( 'No Designation found.', 'wp-team-manager' ),
                        'menu_name'                  => __( 'Designations', 'wp-team-manager' ),
                    ],
                    'team_groups' => [
                        'name'                       => __( 'Groups', 'wp-team-manager' ),
                        'singular_name'              => __( 'Group', 'wp-team-manager' ),
                        'search_items'               => __( 'Search Groups', 'wp-team-manager' ),
                        'popular_items'              => __( 'Popular Groups', 'wp-team-manager' ),
                        'all_items'                  => __( 'All Groups', 'wp-team-manager' ),
                        'edit_item'                  => __( 'Edit Group', 'wp-team-manager' ),
                        'update_item'                => __( 'Update Group', 'wp-team-manager' ),
                        'add_new_item'               => __( 'Add New Group', 'wp-team-manager' ),
                        'new_item_name'              => __( 'New Group Name', 'wp-team-manager' ),
                        'separate_items_with_commas' => __( 'Separate Groups with commas', 'wp-team-manager' ),
                        'add_or_remove_items'        => __( 'Add or remove Groups', 'wp-team-manager' ),
                        'choose_from_most_used'      => __( 'Choose from the most used Groups', 'wp-team-manager' ),
                        'not_found'                  => __( 'No Groups found.', 'wp-team-manager' ),
                        'menu_name'                  => __( 'Groups', 'wp-team-manager' ),
                    ],
                ],
                'hidden_fields' => [],
            ],

            'sports' => [
                'label'       => __( 'Sports League', 'wp-team-manager' ),
                'description' => __( 'Optimized for sports teams, athletes, and leagues.', 'wp-team-manager' ),
                'field_labels' => [
                    'tm_jtitle'          => __( 'Position', 'wp-team-manager' ),
                    'tm_location'        => __( 'Jersey Number', 'wp-team-manager' ),
                    'tm_year_experience' => __( 'Seasons / Games Played', 'wp-team-manager' ),
                    'tm_short_bio'       => __( 'Player Stats', 'wp-team-manager' ),
                    'tm_long_bio'        => __( 'Player Bio', 'wp-team-manager' ),
                    'tm_vcard'           => __( 'vCard', 'wp-team-manager' ),
                    'tm_resume_url'      => __( 'Resume', 'wp-team-manager' ),
                    'tm_email'           => __( 'Email Address', 'wp-team-manager' ),
                    'tm_telephone'       => __( 'Phone', 'wp-team-manager' ),
                    'tm_mobile'          => __( 'Mobile', 'wp-team-manager' ),
                    'tm_web_url'         => __( 'Website', 'wp-team-manager' ),
                ],
                'taxonomy_labels' => [
                    'team_department'  => [
                        'name'                       => __( 'Teams', 'wp-team-manager' ),
                        'singular_name'              => __( 'Team', 'wp-team-manager' ),
                        'search_items'               => __( 'Search Teams', 'wp-team-manager' ),
                        'popular_items'              => __( 'Popular Teams', 'wp-team-manager' ),
                        'all_items'                  => __( 'All Teams', 'wp-team-manager' ),
                        'edit_item'                  => __( 'Edit Team', 'wp-team-manager' ),
                        'update_item'                => __( 'Update Team', 'wp-team-manager' ),
                        'add_new_item'               => __( 'Add New Team', 'wp-team-manager' ),
                        'new_item_name'              => __( 'New Team', 'wp-team-manager' ),
                        'separate_items_with_commas' => __( 'Separate Teams with commas', 'wp-team-manager' ),
                        'add_or_remove_items'        => __( 'Add or remove Team', 'wp-team-manager' ),
                        'choose_from_most_used'      => __( 'Choose from the most used Team', 'wp-team-manager' ),
                        'not_found'                  => __( 'No Team found.', 'wp-team-manager' ),
                        'menu_name'                  => __( 'Teams', 'wp-team-manager' ),
                    ],
                    'team_designation' => [
                        'name'                       => __( 'Positions', 'wp-team-manager' ),
                        'singular_name'              => __( 'Position', 'wp-team-manager' ),
                        'search_items'               => __( 'Search Positions', 'wp-team-manager' ),
                        'popular_items'              => __( 'Popular Positions', 'wp-team-manager' ),
                        'all_items'                  => __( 'All Positions', 'wp-team-manager' ),
                        'edit_item'                  => __( 'Edit Position', 'wp-team-manager' ),
                        'update_item'                => __( 'Update Position', 'wp-team-manager' ),
                        'add_new_item'               => __( 'Add New Position', 'wp-team-manager' ),
                        'new_item_name'              => __( 'New Position', 'wp-team-manager' ),
                        'separate_items_with_commas' => __( 'Separate Positions with commas', 'wp-team-manager' ),
                        'add_or_remove_items'        => __( 'Add or remove Position', 'wp-team-manager' ),
                        'choose_from_most_used'      => __( 'Choose from the most used Position', 'wp-team-manager' ),
                        'not_found'                  => __( 'No Position found.', 'wp-team-manager' ),
                        'menu_name'                  => __( 'Positions', 'wp-team-manager' ),
                    ],
                    'team_groups' => [
                        'name'                       => __( 'Leagues', 'wp-team-manager' ),
                        'singular_name'              => __( 'League', 'wp-team-manager' ),
                        'search_items'               => __( 'Search Leagues', 'wp-team-manager' ),
                        'popular_items'              => __( 'Popular Leagues', 'wp-team-manager' ),
                        'all_items'                  => __( 'All Leagues', 'wp-team-manager' ),
                        'edit_item'                  => __( 'Edit League', 'wp-team-manager' ),
                        'update_item'                => __( 'Update League', 'wp-team-manager' ),
                        'add_new_item'               => __( 'Add New League', 'wp-team-manager' ),
                        'new_item_name'              => __( 'New League Name', 'wp-team-manager' ),
                        'separate_items_with_commas' => __( 'Separate Leagues with commas', 'wp-team-manager' ),
                        'add_or_remove_items'        => __( 'Add or remove Leagues', 'wp-team-manager' ),
                        'choose_from_most_used'      => __( 'Choose from the most used Leagues', 'wp-team-manager' ),
                        'not_found'                  => __( 'No Leagues found.', 'wp-team-manager' ),
                        'menu_name'                  => __( 'Leagues', 'wp-team-manager' ),
                    ],
                ],
                'hidden_fields' => [
                    'tm_vcard',
                    'tm_resume_url',
                ],
            ],
        ]);
    }

    /**
     * Get the currently active dashboard mode.
     *
     * @return string Mode key (e.g., 'corporate', 'sports')
     */
    public static function get_active_mode() {
        $mode = get_option( 'tm_dashboard_mode', 'corporate' );
        $presets = self::get_dashboard_presets();

        // Safety: If stored mode doesn't exist, fall back to corporate
        if ( ! isset( $presets[ $mode ] ) ) {
            return 'corporate';
        }

        return $mode;
    }

    /**
     * Get the display label for a field based on the active mode.
     *
     * @param string $field_key The field meta key (e.g., 'tm_jtitle')
     * @return string The localized display label
     */
    public static function get_field_label( $field_key ) {
        $mode = self::get_active_mode();
        $presets = self::get_dashboard_presets();

        // Check if this mode has a custom label for this field
        if ( isset( $presets[ $mode ]['field_labels'][ $field_key ] ) ) {
            return $presets[ $mode ]['field_labels'][ $field_key ];
        }

        // Fall back to corporate labels
        if ( isset( $presets['corporate']['field_labels'][ $field_key ] ) ) {
            return $presets['corporate']['field_labels'][ $field_key ];
        }

        // Ultimate fallback: return the key itself (shouldn't happen)
        return $field_key;
    }

    /**
     * Get the display labels for a taxonomy based on the active mode.
     *
     * @param string $taxonomy The taxonomy slug (e.g., 'team_department')
     * @param string $label_key Optional. Specific label key to retrieve (e.g., 'name', 'singular_name')
     * @return array|string Array of all labels, or specific label if $label_key provided
     */
    public static function get_taxonomy_labels( $taxonomy, $label_key = null ) {
        $mode = self::get_active_mode();
        $presets = self::get_dashboard_presets();

        // Get labels from current mode, fallback to corporate
        $labels = $presets[ $mode ]['taxonomy_labels'][ $taxonomy ]
            ?? $presets['corporate']['taxonomy_labels'][ $taxonomy ]
            ?? [];

        if ( $label_key !== null ) {
            return $labels[ $label_key ] ?? '';
        }

        return $labels;
    }

    /**
     * Check if a field should be hidden in the current mode.
     *
     * @param string $field_key The field meta key (e.g., 'tm_vcard')
     * @return bool True if field should be hidden
     */
    public static function is_field_hidden( $field_key ) {
        $mode = self::get_active_mode();
        $presets = self::get_dashboard_presets();

        $hidden_fields = $presets[ $mode ]['hidden_fields'] ?? [];

        return in_array( $field_key, $hidden_fields, true );
    }

    /**
     * Get all available mode options for a dropdown.
     *
     * @return array Associative array of mode_key => label
     */
    public static function get_mode_options() {
        $presets = self::get_dashboard_presets();
        $options = [];

        foreach ( $presets as $key => $preset ) {
            $options[ $key ] = $preset['label'];
        }

        return $options;
    }

    /**
     * Check if a pro global option is enabled
     * This is for global WordPress options (not widget settings)
     *
     * @param string $option_name The option name to check
     * @param string $expected_value The expected value for the option (default: 'True')
     * @return bool True if pro is active AND option is set to expected value, false otherwise
     */
    public static function is_pro_option_enabled( $option_name, $expected_value = '1' ) {
        // Always check if it's a registered pro feature
        if ( ! self::is_pro_feature( $option_name ) ) {
            if ( class_exists( __NAMESPACE__ . '\\Log' ) ) {
                Log::warning( 'Checking unregistered pro option', [
                    'option_name' => $option_name,
                    'tip' => 'Add this feature to Helper::get_pro_features_list()'
                ] );
            }
        }

        // Default to false if no pro access
        if ( ! self::is_pro_active() ) {
            return false;
        }

        // Check option value
        $option_value = get_option( $option_name );
        return $option_value === $expected_value;
    }

    /**
     * Safely instantiate a controller class. Uses get_instance() if available,
     * otherwise falls back to `new $class()`.
     *
     * @param string $class
     * @return void
     */
    private static function boot_class( $class ) {
        try {
            if ( ! class_exists( $class ) ) {
                return; // Silently skip missing classes
            }

            if ( method_exists( $class, 'get_instance' ) ) {
                $class::get_instance();
                return;
            }

            // Fallback for non-singleton controllers
            new $class();
        } catch ( \Throwable $e ) {
            // Optional logging if Log class is available
            if ( class_exists( __NAMESPACE__ . '\\Log' ) ) {
                Log::error( 'Helper boot_class exception', [ 'class' => $class, 'error' => $e->getMessage() ] );
            }
        }
    }

    /**
     * Instantiate a list of classes only in wp-admin. Accepts both singleton and non-singleton classes.
     *
     * @param array $classes
     * @return void
     */
    public static function admin_instances( array $classes ) {
        if ( empty( $classes ) || ! is_admin() ) {
            return;
        }
        foreach ( $classes as $class ) {
            self::boot_class( $class );
        }
    }

    /**
     * Classes instatiation.
     *
     * @param array $classes Classes to init.
     *
     * @return void
     */
    public static function instances( array $classes ) {
        if ( empty( $classes ) ) {
            return;
        }
        foreach ( $classes as $class ) {
            // Back-compat: prefer singleton if present; otherwise allow direct instantiation
            if ( method_exists( $class, 'get_instance' ) ) {
                $class::get_instance();
            } else {
                self::boot_class( $class );
            }
        }
    }

    /**
     * Retrieves the team member's picture as an HTML image element.
     *
     * @param int $post_id The ID of the post for which the picture is being retrieved.
     * @param string $thumb_image_size The size of the thumbnail image to retrieve.
     * @param string $class Optional. Additional CSS class(es) to apply to the image. Default is an empty string.
     *
     * @return string|null The HTML image element or null if no thumbnail ID is found.
     */
 public static function get_team_picture($post_id, $thumb_image_size = 'thumbnail', $class = '') {
    // Ensure a valid post ID
    $post_id = intval($post_id);
    
    // Get the thumbnail ID once
    $thumbnail_id = get_post_thumbnail_id($post_id);
    
    // Check accessibility and performance settings
    $alt_text_enabled = get_option('tm_alt_text', 1);
    $lazy_loading = get_option('tm_lazy_loading', 0);
    $loading_attr = ($lazy_loading == 1) ? 'lazy' : 'eager';

    // Return default image if no thumbnail is found
    if (!has_post_thumbnail($post_id)) {
        $placeholder_url = plugin_dir_url(__FILE__) . 'assets/images/placeholder.png';
        $alt_text = ($alt_text_enabled == 1) ? esc_attr__('Team member placeholder image', 'wp-team-manager') : esc_attr__('No Image', 'wp-team-manager');
        return '<img src="' . esc_url($placeholder_url) . '" alt="' . $alt_text . '" class="' . esc_attr($class) . '" loading="' . esc_attr($loading_attr) . '" />';
    }

    // Prepare image attributes
    $image_attrs = [
        "class" => esc_attr($class),
        "loading" => esc_attr($loading_attr)
    ];
    
    // Add descriptive alt text if accessibility is enabled
    if ($alt_text_enabled == 1) {
        $team_name = get_the_title($post_id);
        $job_title = get_post_meta($post_id, 'tm_jtitle', true);
        
        if ($job_title) {
            $alt_text = sprintf(__('%s, %s', 'wp-team-manager'), $team_name, $job_title);
        } else {
            $alt_text = sprintf(__('Photo of %s', 'wp-team-manager'), $team_name);
        }
        $image_attrs['alt'] = esc_attr($alt_text);
    }

    // Return the formatted image with proper escaping for class attributes
    return apply_filters(
        'wp_team_manager_team_picture_html',
        wp_get_attachment_image($thumbnail_id, $thumb_image_size, false, $image_attrs),
        $post_id,
        $thumb_image_size,
        $class
    );
}



    /**
     * @todo Need to remove
     * Retrieves the team member's social media links as an HTML structure.
     *
     * This function retrieves the social media links associated with a team member
     * and generates an HTML structure containing anchor tags for each link. The
     * HTML structure includes a wrapper element with a class attribute specifying
     * the size of the links. Each anchor tag also includes a class attribute specifying
     * the social media network and its size.
     *
     * @param int $post_id The ID of the post for which the social media links are being retrieved.
     *
     * @return string The HTML structure containing the social media links.
     */
    public static function get_team_social_links($post_id) {
        // Retrieve settings once and cache them
        static $social_size = null;
        static $link_window = null;
    
        if (is_null($social_size)) {
            $social_size = intval(get_option('tm_social_size', 16));
        }
    
        if (is_null($link_window)) {
            $link_window = (get_option('tm_link_new_window') === '1') ? 'target="_blank"' : '';
        }
    
        // Fetch all social links in a single call to reduce DB queries
        $meta = get_post_meta($post_id);
    
        // Define supported social networks with their metadata keys and icons
        $social_links = [
            'facebook'    => ['key' => 'tm_flink', 'icon' => 'fab fa-facebook-f', 'title' => __('Facebook', 'wp-team-manager')],
            'twitter'     => ['key' => 'tm_tlink', 'icon' => 'fab fa-twitter', 'title' => __('Twitter', 'wp-team-manager')],
            'linkedin'    => ['key' => 'tm_llink', 'icon' => 'fab fa-linkedin', 'title' => __('LinkedIn', 'wp-team-manager')],
            'googleplus'  => ['key' => 'tm_gplink', 'icon' => 'fab fa-google-plus-g', 'title' => __('Google Plus', 'wp-team-manager')],
            'dribbble'    => ['key' => 'tm_dribbble', 'icon' => 'fab fa-dribbble-square', 'title' => __('Dribbble', 'wp-team-manager')],
            'youtube'     => ['key' => 'tm_ylink', 'icon' => 'fab fa-youtube', 'title' => __('YouTube', 'wp-team-manager')],
            'vimeo'       => ['key' => 'tm_vlink', 'icon' => 'fab fa-vimeo', 'title' => __('Vimeo', 'wp-team-manager')],
            'email'       => ['key' => 'tm_emailid', 'icon' => 'far fa-envelope', 'title' => __('Email', 'wp-team-manager')],
        ];
    
        // Start output buffering
        ob_start();
    
        echo '<div class="team-member-socials size-' . esc_attr($social_size) . '">';
        do_action('wp_team_manager_before_social_links', $post_id);
        $social_links = apply_filters('wp_team_manager_social_links', $social_links, $post_id);
    
        foreach ($social_links as $network => $data) {
            // Get the social URL from metadata
            $value = isset($meta[$data['key']][0]) ? trim($meta[$data['key']][0]) : '';
            if (!empty($value)) {
                $href = ($network === 'email') ? 'mailto:' . sanitize_email($value) : esc_url($value);
                echo '<a class="' . esc_attr($network . '-' . $social_size) . '" href="' . $href . '" ' . esc_attr($link_window) . ' title="' . esc_attr($data['title']) . '">';
                echo '<i class="' . esc_attr($data['icon']) . '"></i></a>';
            }
        }
        echo '</div>';
        do_action('wp_team_manager_after_social_links', $post_id);
    
        return ob_get_clean();
    }

    /**
     * Displays the social media profile output.
     *
     * This function retrieves the social media data associated with a team member
     * and then iterates over the retrieved data to generate a set of HTML
     * elements representing the social media profiles.
     *
     * The generated HTML includes a wrapper, a set of labels, and a set of
     * anchor tags. The labels are the social media types, and the anchor tags
     * are styled to represent the social media icons.
     *
     * @param int $post_id The team member post ID.
     *
     * @return string The social media profile output.
     */
    public static function display_social_profile_output($post_id = 0) {
        // Ensure a valid post ID is retrieved
        $post_id = $post_id ? intval($post_id) : get_the_ID();
    
        // Retrieve and cache social settings
        static $social_size = null;
        static $link_window = null;
    
        if (is_null($social_size)) {
            $social_size = intval(get_option('tm_social_size', 16));
        }
    
        if (is_null($link_window)) {
            $link_window = (get_option('tm_link_new_window') === '1') ? 'target="_blank"' : '';
        }
    
        // Fetch all metadata at once (reducing database queries)
        $post_meta = get_post_custom($post_id);
        $wptm_social_data = isset($post_meta['wptm_social_group'][0]) ? maybe_unserialize($post_meta['wptm_social_group'][0]) : [];
    
        // Return early if no social data exists
        if (empty($wptm_social_data) || !is_array($wptm_social_data)) {
            return '';
        }
    
        // Define social media mappings (Font Awesome classes)
        $social_media_icons = [
            'email'          => ['icon' => 'far fa-envelope', 'title' => __('Email', 'wp-team-manager')],
            'facebook'       => ['icon' => 'fab fa-facebook-f', 'title' => __('Facebook', 'wp-team-manager')],
            'twitter'        => ['icon' => 'fab fa-twitter', 'title' => __('Twitter', 'wp-team-manager')],
            'linkedin'       => ['icon' => 'fab fa-linkedin', 'title' => __('LinkedIn', 'wp-team-manager')],
            'googleplus'     => ['icon' => 'fab fa-google-plus-g', 'title' => __('Google Plus', 'wp-team-manager')],
            'dribbble'       => ['icon' => 'fab fa-dribbble', 'title' => __('Dribbble', 'wp-team-manager')],
            'youtube'        => ['icon' => 'fab fa-youtube', 'title' => __('YouTube', 'wp-team-manager')],
            'vimeo'          => ['icon' => 'fab fa-vimeo', 'title' => __('Vimeo', 'wp-team-manager')],
            'instagram'      => ['icon' => 'fab fa-instagram', 'title' => __('Instagram', 'wp-team-manager')],
            'discord'        => ['icon' => 'fab fa-discord', 'title' => __('Discord', 'wp-team-manager')],
            'tiktok'         => ['icon' => 'fab fa-tiktok', 'title' => __('TikTok', 'wp-team-manager')],
            'github'         => ['icon' => 'fab fa-github', 'title' => __('GitHub', 'wp-team-manager')],
            'stack-overflow' => ['icon' => 'fab fa-stack-overflow', 'title' => __('Stack Overflow', 'wp-team-manager')],
            'medium'         => ['icon' => 'fab fa-medium', 'title' => __('Medium', 'wp-team-manager')],
            'telegram'       => ['icon' => 'fab fa-telegram', 'title' => __('Telegram', 'wp-team-manager')],
            'pinterest'      => ['icon' => 'fab fa-pinterest', 'title' => __('Pinterest', 'wp-team-manager')],
            'square-reddit'  => ['icon' => 'fab fa-reddit-square', 'title' => __('Reddit', 'wp-team-manager')],
            'tumblr'         => ['icon' => 'fab fa-tumblr', 'title' => __('Tumblr', 'wp-team-manager')],
            'quora'          => ['icon' => 'fab fa-quora', 'title' => __('Quora', 'wp-team-manager')],
            'snapchat'       => ['icon' => 'fab fa-snapchat', 'title' => __('Snapchat', 'wp-team-manager')],
            'goodreads'      => ['icon' => 'fab fa-goodreads', 'title' => __('Goodreads', 'wp-team-manager')],
            'twitch'         => ['icon' => 'fab fa-twitch', 'title' => __('Twitch', 'wp-team-manager')],
            'phone'          => ['icon' => 'fas fa-phone-alt', 'title' => __('Phone', 'wp-team-manager')],
            'phone-app'      => ['icon' => 'fas fa-mobile-alt', 'title' => __('Phone App', 'wp-team-manager')],
            'address'        => ['icon' => 'fas fa-address-card', 'title' => __('Business Card', 'wp-team-manager')],
            'xing'         => ['icon' => 'fab fa-xing', 'title' => __('Xing', 'wp-team-manager')],
        ];
    
        // Start output buffering for better performance
        ob_start();
        ?>
        <div class="team-member-socials size-<?php echo esc_attr($social_size); ?>">
            <?php
            foreach ($wptm_social_data as $data) {
                if (!isset($data['type'], $data['url']) || !isset($social_media_icons[$data['type']])) {
                    continue;
                }
    
                $type  = sanitize_key($data['type']);
                $icon  = esc_attr($social_media_icons[$type]['icon']);
                $title = esc_attr($social_media_icons[$type]['title']);
                if ($type === 'email') {
                    $url = 'mailto:' . sanitize_email($data['url']);
                } elseif ($type === 'phone' || $type === 'phone-app') {
                    $url = 'tel:' . preg_replace('/[^0-9+\-\s\(\)]/', '', $data['url']);
                } else {
                    $url = esc_url($data['url']);
                }

                ?>
                    <a class="<?php echo esc_attr($type . '-' . $social_size); ?>" href="<?php echo $url; ?>" <?php echo $link_window; ?> title="<?php echo $title; ?>">
                        <i class="<?php echo $icon; ?>"></i>
                    </a>
                <?php
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }


        /**
         * Retrieves additional information about a team member and formats it into HTML.
         *
         * This function fetches metadata for a specified team member, including fields
         * like mobile, experience, email, and more. It sanitizes the data and generates
         * an HTML structure displaying this information. The output can include icons,
         * text, and links based on the metadata and user settings.
         *
         * @param int $post_id The ID of the team member post.
         *
         * @return string The HTML representation of the team member's additional information.
         */
        
 public static function get_team_other_infos($post_id, $tm_single_fields = [], $enable_links = null) {

    $is_pro = ! Helper::freemius_is_free_user();

    $custom_labels = get_option('tm_custom_labels', []);
    $web_btn_text = $custom_labels['tm_web_url'] ?? self::get_field_label('tm_web_url');
    $vcard_btn_text = $custom_labels['tm_vcard'] ?? __('Download CV', 'wp-team-manager');
    $meta = get_post_meta($post_id);

    $mode = self::get_active_mode();

    $jarseNumber = [];
    $gameplayed = [];

    if ($mode === 'sports') {
        $jarseNumber = ['icon' => 'fas fa-tshirt', 'is_link' => true];
        
    }else{
        $jarseNumber = ['icon' => 'fas fa-map-marker', 'is_link' => false];
    }

    $fields = [
        'tm_mobile'          => !empty($meta['tm_mobile'][0]) ? sanitize_text_field($meta['tm_mobile'][0]) : '',
        'tm_year_experience' => !empty($meta['tm_year_experience'][0]) ? sanitize_text_field($meta['tm_year_experience'][0]) : '',
        'tm_email'           => !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '',
        'tm_telephone'       => !empty($meta['tm_telephone'][0]) ? sanitize_text_field($meta['tm_telephone'][0]) : '',
        'tm_location'        => !empty($meta['tm_location'][0]) ? sanitize_text_field($meta['tm_location'][0]) : '',
        'tm_web_url'         => !empty($meta['tm_web_url'][0]) ? esc_url($meta['tm_web_url'][0]) : '',
        'tm_vcard'           => !empty($meta['tm_vcard'][0]) ? esc_url($meta['tm_vcard'][0]) : '',
        // New custom detail url field (Pro)
        'tm_custom_detail_url' => !empty($meta['tm_custom_detail_url'][0]) ? esc_url($meta['tm_custom_detail_url'][0]) : '',
        // New fields: tm_resume_url and tm_hire_me_url
        'tm_resume_url'      => !empty($meta['tm_resume_url'][0]) ? esc_url($meta['tm_resume_url'][0]) : '',
        'tm_hire_me_url'     => !empty($meta['tm_hire_me_url'][0]) ? esc_url($meta['tm_hire_me_url'][0]) : '',
    ];

    // Remove hidden fields based on dashboard mode (e.g., vCard in Sports Mode)
    foreach ( array_keys( $fields ) as $field_key ) {
        if ( self::is_field_hidden( $field_key ) ) {
            $fields[ $field_key ] = '';
        }
    }

    if (empty(array_filter($fields))) return '';

    $output = '<div class="team-member-other-info">';

    $field_mappings = [
        'tm_mobile'          => ['icon' => 'fas fa-mobile-alt', 'prefix' => 'tel:', 'is_link' => true],
        'tm_telephone'       => ['icon' => 'fas fa-phone-alt', 'prefix' => 'tel:', 'is_link' => true],
        'tm_year_experience' => ['icon' => 'fas fa-history', 'is_link' => false],
        'tm_location'        => $jarseNumber,
        'tm_email'           => ['icon' => 'fas fa-envelope', 'prefix' => 'mailto:', 'is_link' => true],
        'tm_web_url'         => ['icon' => 'fas fa-link', 'prefix' => '', 'is_link' => true, 'link_text' => $web_btn_text],
        'tm_vcard'           => ['icon' => 'fas fa-download', 'prefix' => '', 'is_link' => true, 'link_text' => $vcard_btn_text],
    ];

    if ( $is_pro ) {
        $field_mappings['tm_custom_detail_url'] = ['icon' => 'fas fa-external-link-alt', 'prefix' => '', 'is_link' => true, 'link_text' => __('Details', 'wp-team-manager')];
        $field_mappings['tm_resume_url']        = ['icon' => 'fas fa-file-alt', 'prefix' => '', 'is_link' => true, 'link_text' => __('Resume', 'wp-team-manager')];
        $field_mappings['tm_hire_me_url']       = ['icon' => 'fas fa-user-tie', 'prefix' => '', 'is_link' => true, 'link_text' => __('Hire Me', 'wp-team-manager')];
    }
    if (!empty($tm_single_fields) && is_array($tm_single_fields) && $is_pro) {
        $tm_selected_fields = array_intersect_key($field_mappings, array_flip($tm_single_fields));
    } else {
        $tm_selected_fields = $field_mappings;
    }

    $toggleable_fields = ['tm_mobile', 'tm_telephone'];

    foreach ($tm_selected_fields as $key => $info) {
        if (empty($fields[$key])) continue;

        $output .= '<div class="team-member-info">';
        if (!empty($info['icon'])) $output .= '<i class="' . esc_attr($info['icon']) . '"></i> ';

        $text = esc_html($fields[$key]);
        $url = '';

 
            if (in_array($key, $toggleable_fields, true)) {
                if ($enable_links === 'yes') {
                    $url = 'tel:' . preg_replace('/[^0-9+]/', '', $fields[$key]);
                }
            } else {
                if ($key === 'tm_email') {
                    $url = 'mailto:' . sanitize_email($fields[$key]);
                } elseif (in_array($key, ['tm_web_url','tm_vcard','tm_custom_detail_url', 'tm_resume_url', 'tm_hire_me_url'])) {
                    $url = $info['prefix'] . $fields[$key];
                    $text = $info['link_text'] ?? $text;
                }
            }
   

        if (!empty($url)) {
            $output .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer"><span>' . $text . '</span></a>';
        } else {
            $output .= '<span>' . $text . '</span>';
        }

        $output .= '</div>';
    }

    $output .= '</div>';

    return apply_filters('wp_team_manager_other_info_html', $output, $post_id);
}


        
        

        
    /**
	 * Get Post Pagination, Load more & Scroll markup
	 *
	 * @param $query
	 * @param $data
	 *
	 * @return false|string|void
	 */
	public static function get_pagination_markup( $query, $posts_per_page ) {

        $big = '999999999'; // need an unlikely string

		if ( $query->max_num_pages > 0 ) {
			$html = "<div class='wtm-pagination-wrap dwl-team-number-pagination-container' data-total-pages='{$query->max_num_pages}' data-posts-per-page='{$posts_per_page}' data-type='pagination' >";   
            $html .= paginate_links( array(
                'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format' => '?paged=%#%',
                'current' => max( 1, get_query_var('paged') ),
                'total' => $query->max_num_pages
            ) );
            $html .= "</div>";
			return $html;
		}

		return false;
	}


    /**
     * Get all post status
     *
     * @return boolean
     */
    public static function getPostStatus() {
        return [
            'publish'    => esc_html__( 'Publish', 'wp-team-manager' ),
            'pending'    => esc_html__( 'Pending', 'wp-team-manager' ),
            'draft'      => esc_html__( 'Draft', 'wp-team-manager' ),
            'auto-draft' => esc_html__( 'Auto draft', 'wp-team-manager' ),
            'future'     => esc_html__( 'Future', 'wp-team-manager' ),
            'private'    => esc_html__( 'Private', 'wp-team-manager' ),
            'inherit'    => esc_html__( 'Inherit', 'wp-team-manager' ),
            'trash'      => esc_html__( 'Trash', 'wp-team-manager' ),
        ];
    }

    /**
     * Get all Order By
     *
     * @return boolean
     */
    public static function getOrderBy() {
        return [
            'date'          => esc_html__( 'Date', 'wp-team-manager' ),
            'ID'            => esc_html__( 'Order by post ID', 'wp-team-manager' ),
            'author'        => esc_html__( 'Author', 'wp-team-manager' ),
            'title'         => esc_html__( 'Title', 'wp-team-manager' ),
            'modified'      => esc_html__( 'Last modified date', 'wp-team-manager' ),
            'parent'        => esc_html__( 'Post parent ID', 'wp-team-manager' ),
            'comment_count' => esc_html__( 'Number of comments', 'wp-team-manager' ),
            'menu_order'    => esc_html__( 'Menu order', 'wp-team-manager' ),
        ];
    }

    /**
     * Get bootstrap layout class
     *
     * @return string
     */

    public static function get_grid_layout_bootstrap_class( $desktop = '1' , $tablet = '1', $mobile = '1' ){

        $desktop_class = '';
        $tablet_class = '';
        $mobile_class = '';

        $desktop_layouts = [
            '1' => 'lg-12',
            '2' => 'lg-6',
            '3' => 'lg-4',
            '4' => 'lg-3'
        ];

        $tablet_layouts = [
            '1' => 'md-12',
            '2' => 'md-6',
            '3' => 'md-4',
            '4' => 'md-3'
        ];

        $mobile_layouts = [
            '1' => '12',
            '2' => '6',
            '3' => '4',
            '4' => '3'
        ];

        if( array_key_exists( $desktop, $desktop_layouts ) ){
            $desktop_class = $desktop_layouts[$desktop];
        }

        if( array_key_exists( $tablet, $tablet_layouts ) ){
            $tablet_class = $tablet_layouts[$tablet];
        }

        if( array_key_exists( $mobile, $mobile_layouts ) ){
            $mobile_class = $mobile_layouts[$mobile];
        }

        return "wtm-col-{$desktop_class} wtm-col-{$tablet_class} wtm-col-{$mobile_class}";

    }

    /**
	 * Render.
	 *
	 * @param string  $view_name View name.
	 * @param array   $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function render( $view_name, $args = [], $return = false ) {
		$path = str_replace( '.', '/', $view_name );
        $template_file = TM_PATH . '/public/templates/' . $path.'.php';

        if ( $args ) {
			extract( $args );
		}

		if ( ! file_exists( $template_file ) ) {
			return;
		}

		if ( $return ) {
			ob_start();
			include $template_file;

			return ob_get_clean();
		} else {
			include $template_file;
		}
	}
    
        /**
         * Generate custom css and save in uploads folder
         *
         * @param int $scID Shortcode id
         *
         * @return void
         */
    public static function generatorShortcodeCss($scID, $cssOverride = '')
    {
        global $wp_filesystem;

        // Sanitize & validate `$scID`
        $scID = absint($scID);
        if ( ! $scID ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('WPTM: generatorShortcodeCss invalid $scID');
            }
            return false;
        }

        // In admin we enforce caps; on front-end we skip hard-fail to avoid white screens
        if ( is_admin() && ! current_user_can('manage_options') ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('WPTM: generatorShortcodeCss unauthorized');
            }
            return false;
        }

        // Init Filesystem API
        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }
        if ( empty( $wp_filesystem ) ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('WPTM: generatorShortcodeCss could not initialize WP_Filesystem');
            }
            return false;
        }

        $upload_dir    = wp_upload_dir();
        $upload_basedir = isset($upload_dir['basedir']) ? $upload_dir['basedir'] : '';
        $upload_baseurl = isset($upload_dir['baseurl']) ? $upload_dir['baseurl'] : '';
        $allowedPath    = $upload_basedir ? realpath( $upload_basedir ) : false;

        if ( ! $allowedPath ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('WPTM: generatorShortcodeCss uploads base invalid');
            }
            return false;
        }

        // Ensure plugin subdirectory exists
        $target_dir = trailingslashit( $allowedPath ) . 'wp-team-manager';
        if ( ! file_exists( $target_dir ) ) {
            if ( ! wp_mkdir_p( $target_dir ) ) {
                if ( defined('WP_DEBUG') && WP_DEBUG ) {
                    error_log('WPTM: generatorShortcodeCss failed to create directory');
                }
                return false;
            }
        }

        // Build & validate final file path
        $cssFile = $target_dir . '/team.css';
        $cssReal = realpath( file_exists($cssFile) ? $cssFile : $target_dir );
        if ( ! $cssReal || strpos( $cssReal, $allowedPath ) !== 0 ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('WPTM: generatorShortcodeCss invalid css path');
            }
            return false;
        }

        // Render CSS chunk for this shortcode (prefer override when provided)
        $css = '';
        if ( is_string( $cssOverride ) && '' !== trim( $cssOverride ) ) {
            $css = (string) $cssOverride;
        } else {
            $css = self::render( 'style', compact('scID'), true );
        }

        if ( ! $css || '' === trim( $css ) ) {
            // Nothing to write
            return false;
        }

        $chunk = sprintf('/*wp_team-%2$d-start*/%1$s/*wp_team-%2$d-end*/', $css, (int) $scID);

        // Merge/replace existing chunk if file exists
        $final_css = '';
        if ( file_exists( $cssFile ) ) {
            $oldCss = $wp_filesystem->get_contents( $cssFile );
            if ( $oldCss ) {
                $oldCss = preg_replace( '/\/\*wp_team\-' . $scID . '\-start[\s\S]+?wp_team\-' . $scID . '\-end\*\//', '', $oldCss );
                // Trim extra blank lines
                $oldCss = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $oldCss );
                $final_css = trim( $oldCss ) . "\n" . $chunk;
            } else {
                $final_css = $chunk;
            }
        } else {
            $final_css = $chunk;
        }

        // Write file
        $write_ok = $wp_filesystem->put_contents( $cssFile, $final_css, FS_CHMOD_FILE );
        if ( ! $write_ok ) {
            if ( defined('WP_DEBUG') && WP_DEBUG ) {
                error_log('WPTM: generatorShortcodeCss failed to write file');
            }
            return false;
        }

        // Return public URL so caller can enqueue
        $public_url = trailingslashit( $upload_baseurl ) . 'wp-team-manager/team.css';
        return esc_url( $public_url );
    }

    /**
     * Enqueue the generated CSS file for a given shortcode instance.
     * Returns the handle on success or false on failure.
     */
    public static function enqueueGeneratedCss( $scID, $cssOverride = '' ) {
        $url = self::generatorShortcodeCss( $scID, $cssOverride );
        if ( ! $url ) {
            return false;
        }
        $handle = 'wptm-generated-styles';
        // Use filemtime for cache-busting when possible
        $upload_dir = wp_upload_dir();
        $path = trailingslashit( $upload_dir['basedir'] ) . 'wp-team-manager/team.css';
        $ver  = file_exists( $path ) ? (string) filemtime( $path ) : null;

        // Only enqueue once per request
        static $enqueued = false;
        if ( ! $enqueued ) {
            wp_register_style( $handle, $url, array(), $ver );
            wp_enqueue_style( $handle );
            $enqueued = true;
        }
        return $handle;
    }

    /**
     * Generate Shortcode for remove css
     *
     * @param integer $scID
     *
     * @return void
    */
    public static function removeGeneratorShortcodeCss($scID)
    {
    // Ensure the user has admin privileges
    if (!current_user_can('manage_options')) {
        die('Unauthorized access.');
    }

    // Validate `$scID` to prevent injection
    if (!is_numeric($scID)) {
        die('Invalid shortcode ID.');
    }

    // Load the WordPress filesystem API securely
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!WP_Filesystem()) {
        return; // Failed to initialize, handle error appropriately
    }

    global $wp_filesystem;

    $upload_dir = wp_upload_dir();
    $upload_basedir = realpath($upload_dir['basedir']);

    // Validate the path to prevent path traversal
    if (!$upload_basedir || strpos($upload_basedir, realpath(WP_CONTENT_DIR . '/uploads')) !== 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Team: Invalid file path uploads for team.css');
        }
    }

    $cssFile = realpath($upload_basedir . '/wp-team-manager/team.css');

    // Ensure `$cssFile` is inside the allowed directory
    if (!$cssFile || strpos($cssFile, $upload_basedir) !== 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Team: Invalid file path team.css');
        }
    }

    // Securely read the existing CSS file
    if (file_exists($cssFile)) {
        $oldCss = $wp_filesystem->get_contents($cssFile);

        if ($oldCss !== false && strpos($oldCss, '/*wp_team-' . $scID . '-start') !== false) {
            $css = preg_replace('/\/\*wp_team-' . $scID . '-start[\s\S]+?wp_team-' . $scID . '-end\*\//', '', $oldCss);
            $css = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $css);

            // Securely write the updated CSS file
            if (!$wp_filesystem->put_contents($cssFile, $css, FS_CHMOD_FILE)) {
               
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Team: Error updating CSS file');
                }
            }
        }
    }
}


    /**
     * Retrieve content from meta key and apply wpautop and do_shortcode to it
     *
     * @param string $meta_key
     * @param int $post_id
     * @return string
     */
    public static function get_wysiwyg_output( $meta_key, $post_id = 0 ) {
        $post_id = $post_id ? intval($post_id) : get_the_ID();
    
        // Retrieve post meta safely
        $content = get_post_meta( $post_id, $meta_key, true );
    
        if (!empty($content)) {
            // Process content using WordPress filters (including autoembed, shortcodes, and autop)
            $content = apply_filters( 'the_content', $content );
    
            // Sanitize output to prevent XSS
            $content = wp_kses_post( $content );
        }
    
        return $content;
    }

    /**
     * Outputs the image gallery for a team member.
     *
     * This function retrieves the image gallery meta data for a team member,
     * then iterates over the retrieved data to generate a set of HTML
     * elements representing the image gallery.
     *
     * The generated HTML includes a wrapper, a set of links, and a set of
     * images. Each link points to a full-size image, and the images are
     * displayed in a grid layout.
     *
     * @param int $post_id The team member post ID.
     *
     * @return void
     */
    public static function get_image_gallery_output( $post_id = 0 ) {
        $post_id              = $post_id ? $post_id : get_the_ID();
        $team_gallery_data    = get_post_meta( $post_id, 'wptm_cm2_gallery_image' );
        $light_box_selector   = '';
        $is_lightbox_selected = get_option('tm_single_team_lightbox');
        $team_image_column    = get_option( 'tm_single_gallery_column' );

        if ( 1 === $is_lightbox_selected && tmwstm_fs()->is_paying_or_trial()) {
            $light_box_selector = 'wtm-image-gallery-lightbox';
        }

        if( is_array($team_gallery_data) AND  empty($team_gallery_data) ){
            return false;
        }
        ?>
            <div class="wtm-image-gallery-wrapper <?php echo esc_attr($team_image_column) ?? '' ?> <?php echo esc_attr($light_box_selector) ?? ''; ?>" data="<?php echo esc_attr($is_lightbox_selected); ?>">
                <?php foreach( $team_gallery_data[0] as $attachment_id => $attachment_url ): ?>
                    <div class="wtm-single-image">
                        <a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" title="">
                            <?php echo wp_get_attachment_image( $attachment_id , 'medium'); ?>
                        </a>
                    </div>
                <?php endforeach;?>
            </div>
       <?php
    }

    /**
     * Generates a set of HTML checkbox inputs for single fields.
     *
     * This function retrieves single field options from the WordPress database,
     * then iterates over a predefined list of field keys and labels to generate
     * corresponding checkbox inputs. Each checkbox represents a field option,
     * and it will be checked if its key exists in the retrieved options.
     *
     * The generated HTML includes a wrapper, input checkbox, label, and display
     * name for each field option.
     */
    public static function generate_single_fields( $backend = 'backend') {

    $tm_single_fields = get_option('tm_single_fields') ? get_option('tm_single_fields') : [];

    $fields = array(
        'tm_email'           => 'Email',
        // 'tm_jtitle'       => 'Job Title',
        'tm_telephone'       => 'Telephone (Office)',
        'tm_mobile'          => 'Mobile (Personal)',
        'tm_location'        => 'Location',
        'tm_year_experience' => 'Years of Experience',
        'tm_web_url'         => 'Web URL',
        'tm_vcard'           => 'vCard',
        'tm_custom_detail_url' => 'Custom Detail URL (Pro)',
    );

    // premium check (same as your first function)
    $is_locked = tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial();

    if ( $backend === 'backend' ) {
        foreach ($fields as $key => $value) {
            printf(
                '<div class="tm-nice-checkbox-wrapper">
                    <input type="checkbox" class="tm-checkbox" id="tm_%s" name="tm_single_fields[]" value="%s" %s %s />
                    <label for="tm_%s" class="toggle"><span></span></label>
                    <span>%s</span>  
                </div><!--.tm-nice-checkbox-wrapper-->',
                esc_attr( $key ),
                esc_attr( $key ),
                in_array($key, $tm_single_fields) ? 'checked' : '',
                $is_locked ? 'disabled' : '',
                esc_attr( $key ),
                esc_html( $value )
            );
        }

        if ( $is_locked ) {
            echo '<p style="color: #d63638; margin-top: 5px;">' . 
                __('Field switching is only available in the premium version.', 'wp-team-manager') . 
                '</p>';
        }
    }

    if ( $backend === 'frontend' ) {
        return $tm_single_fields;
    }

}


    /**
     * Outputs a select dropdown list of image sizes to use for the team member pictures.
     *
     * The list of options is generated from the $fields array, which includes the
     * 'medium', 'thumbnail', 'medium_large', 'large', and 'full' image sizes.
     *
     * The selected attribute is set based on the value of the 'team_image_size_change'
     * option in the database. If no value is set, the default value is 'medium'.
     */
    public static function get_image_sizes() {
        $selected = get_option('team_image_size_change', 'medium');
    
        $fields = array(
            'medium'       => __('Medium', 'wp-team-manager'),
            'thumbnail'    => __('Thumbnail', 'wp-team-manager'),
            'medium_large' => __('Medium Large', 'wp-team-manager'),
            'large'        => __('Large', 'wp-team-manager'),
            'full'         => __('Full', 'wp-team-manager'),
        );
    
        $fields = apply_filters('wp_team_manager_image_sizes', $fields);
    
        $is_locked = tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial();
    
        foreach ($fields as $key => $value) {
            printf(
                '<option value="%s" %s %s>%s</option>',
                esc_attr($key),
                selected($selected, $key, false),
                $is_locked ? 'disabled' : '',
                esc_html($value)
            );
        }
    
        if ($is_locked) {
            echo '<p style="color: #d63638; margin-top: 5px;">' . __('Image size change is only available in the premium version.', 'wp-team-manager') . '</p>';
        }
    }
    /**
     * Generates and outputs HTML <option> elements for gallery column selection.
     *
     * This function retrieves the current setting for the number of gallery columns
     * from the WordPress options table and then generates a set of HTML <option>
     * elements. Each <option> represents a possible column configuration (e.g., one column,
     * two columns, etc.), and the current setting is marked as selected.
     *
     * The generated HTML is intended for use in a <select> dropdown, allowing users
     * to select the number of columns to display in an image gallery.
     */

     public static function get_gallery_columns() {
        // Retrieve selected gallery column option
        $selected = get_option('tm_single_gallery_column', 'four_columns');
    
        // Default gallery column options
        $fields = array(
            'one_column'    => __('One', 'wp-team-manager'),
            'two_columns'   => __('Two', 'wp-team-manager'),
            'three_columns' => __('Three', 'wp-team-manager'),
            'four_columns'  => __('Four', 'wp-team-manager'),
        );
    
        // Allow developers to modify the gallery columns list
        $fields = apply_filters('wp_team_manager_gallery_columns', $fields);
    
        // Return early if no valid options are available
        if (empty($fields) || !is_array($fields)) {
            return;
        }
    
        foreach ($fields as $key => $value) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($key),
                selected($selected, $key, false),
                esc_html($value)
            );
        }
    }

    /**
     * Outputs HTML checkboxes for taxonomy settings.
     *
     * This function retrieves taxonomy field options from the WordPress database
     * and iterates over a predefined list of taxonomy keys and labels to generate
     * corresponding checkbox inputs. Each checkbox represents a taxonomy option and
     * will be checked if its key exists in the retrieved options.
     *
     * The generated HTML includes a wrapper, input checkbox, label, and display name
     * for each taxonomy option.
     */

    public static function get_taxonomy_settings(){

        $tm_taxonomy_fields =  get_option('tm_taxonomy_fields')
        ? get_option('tm_taxonomy_fields') : 
        [];
        $fields = array(
            'team_designation' => 'Designations',
            'team_department'  => 'Departments',
            'team_groups'      => 'Groups',
            'team_genders'     => 'Genders',
        );
        
        foreach ($fields as $key => $value) {

            printf(
                '<div class="tm-nice-checkbox-wrapper">
                <input type="checkbox" class="tm-checkbox" id="tm_%s" name="tm_taxonomy_fields[]" value="%s" %s/>
                <label for="tm_%s" class="toggle"><span></span></label>
                <span>%s</span>  
                </div><!--.tm-nice-checkbox-wrapper-->',
                esc_attr( $key ) ,
                esc_attr( $key ),
                in_array($key,$tm_taxonomy_fields) ? 'checked' : '',
                esc_attr( $key ),
                esc_html( $value ) ,
                
            );

        }

    }

/**
 * Migrates old social icon metadata to a unified social group format.
 *
 * This function retrieves individual social media links (e.g., Facebook, Twitter)
 * from the post meta of a given post ID and consolidates them into a single
 * 'wptm_social_group' meta entry. Each entry in this group contains the social
 * media type and its corresponding URL.
 *
 * @param int $post_id The ID of the post whose social icons are to be migrated.
 */
    public static function team_social_icon_migration( $post_id ) {

        $post_id     = $post_id ? $post_id: get_the_ID();
        $entries     = get_post_meta( $post_id,  'wptm_social_group', false );
        $facebook    = get_post_meta( $post_id,  'tm_flink', true );
        $twitter     = get_post_meta( $post_id,  'tm_tlink', true );
        $link_in     = get_post_meta( $post_id,  'tm_llink', true );
        $google_plus = get_post_meta( $post_id,  'tm_gplink', true );
        $dribble     = get_post_meta( $post_id,  'tm_dribbble', true );
        $youtube     = get_post_meta( $post_id,  'tm_ylink', true );
        $vimeo       = get_post_meta( $post_id,  'tm_vlink', true );
        $email       = get_post_meta( $post_id,  'tm_emailid', true );
    
        if( $facebook ) {
            array_push($entries, [
                'type' => 'facebook',
                'url' => $facebook
            ]);
        }
    
        if( $twitter ) {
            array_push($entries, [
                'type' => 'twitter',
                'url' => $twitter
            ]);
        }
    
        if( $link_in ) {
            array_push($entries, [
                'type' => 'linkedin',
                'url' => $link_in
            ]);
        }
    
        if( $google_plus ) {
            array_push($entries, [
                'type' => 'googleplus',
                'url' => $google_plus
            ]);
        }
    
        if( $dribble ) {
            array_push($entries, [
                'type' => 'dribbble',
                'url' => $dribble
            ]);
        }
    
        if( $youtube ) {
            array_push($entries, [
                'type' => 'youtube',
                'url' => $youtube
            ]);
        }
    
        if( $vimeo ) {
            array_push($entries, [
                'type' => 'vimeo',
                'url' => $vimeo
            ]);
        }
    
        if( $email ) {
            array_push($entries, [
                'type' => 'email',
                'url' => $email
            ]);
        }
    
        update_post_meta( $post_id, 'wptm_social_group', $entries );
        
    }

    /**
	 * Custom Template locator.
	 *
	 * @param  mixed $template_name template name.
	 * @param  mixed $template_path template path.
	 * @param  mixed $default_path default path.
	 * @return string
	 */
    public static function wtm_locate_template( $template_name, $template_path = '', $default_path = '' ) {
        if ( ! $template_path ) {
            $template_path = 'public/templates';
        }
        if ( ! $default_path ) {
            $default_path = TM_PATH . '/public/templates/';
        }
    
        // Sanitize template name to prevent directory traversal
        $template_name = basename($template_name);
    
        // // Allowlist of valid template files
        // $allowed_templates = ['content-memeber.php', 'footer.php', 'team-template.php','content-grid.php'];
    
        // if (!in_array($template_name, $allowed_templates, true)) {
        //     return ''; // Block unauthorized template names
        // }
    
        $template = locate_template( trailingslashit( $template_path ) . $template_name );
    
        // Get default template securely
        if ( ! $template ) {
            $real_path = realpath($default_path . $template_name);
            
            if ($real_path && strpos($real_path, realpath($default_path)) === 0 && file_exists($real_path)) {
                $template = $real_path;
            } else {
                return ''; // Prevent file inclusion attacks
            }
        }
    
        return $template;
    }

    /**
     * Retrieves team data based on the provided query arguments.
     *
     * This function performs a WordPress query using the specified arguments
     * to fetch team-related posts and returns the results along with the maximum
     * number of pages available for pagination.
     *
     * @param array $args Arguments for the WP_Query to retrieve team posts.
     * @return array An associative array containing 'posts' (list of team posts)
     *               and 'max_num_pages' (total number of pagination pages), or an
     *               empty array if no posts are found.
     */

    public static function get_team_data($args){

          $default_args = [
            'post_type'           => 'team_manager',
            'posts_per_page'      => -1,
            'orderby'             => 'date',
            'order'               => 'desc',
            'suppress_filters'    => false,
            'no_found_rows'       => false,
        ];
        
        $args = wp_parse_args( $args, $default_args );

        // if ( ! empty( $args['doing_ajax_call'] ) || ! empty( $args['enable_pagination'] ) ) {
        //     $args['no_found_rows'] = false;
        // }

        if (empty($args['post_type'])) {
            $args['post_type'] = 'team_manager';
        }
        // Ensure WPML compatibility by allowing filters to run on queries
        if ( ! isset( $args['suppress_filters'] ) ) {
            $args['suppress_filters'] = false;
        }

        $args = apply_filters('wp_team_manager_query_args', $args);

        /**
         * Fires before the team query is executed.
         *
         * @since x.x.x
         * @param array $args The query arguments.
         */
        do_action('wp_team_manager_before_team_query', $args);


        ksort($args); // Optional: to ensure consistent ordering

        // Add caching for performance with large datasets
        $cache_hash = md5( maybe_serialize( $args ) );
        $cache_key = 'wtm_team_data_' . substr( $cache_hash, 0, 32 );
        $cached = get_transient( $cache_key );

        if ( false !== $cached ) {
            return $cached;
        }

        $tmQuery = new \WP_Query( $args );

        /**
         * Fires after the team query is executed and before caching.
         *
         * @since x.x.x
         * @param \WP_Query $tmQuery The WP_Query object.
         * @param array $args The query arguments.
         */
        do_action('wp_team_manager_after_team_query', $tmQuery, $args);

        $result = ($tmQuery->posts)
            ? ['posts' => $tmQuery->posts, 'max_num_pages' => $tmQuery->max_num_pages]
            : ['posts' => [], 'max_num_pages' => 0];

        set_transient( $cache_key, $result, HOUR_IN_SECONDS );

        return $result;
    }

    /**
     * Renders the Elementor layout based on the given layout, data, and settings.
     *
     * @param string $layout The name of the layout to render.
     * @param array $data The data to pass to the layout template.
     * @param array $settings The settings for the layout.
     * @throws None
     * @return void
     */
    public static function renderElementorLayout(string $layout, array $data, array $settings, $callBack = 'init'): void{
        $allowedLayouts = ['grid', 'list', 'slider', 'table', 'isotope']; // Allowed layouts

        if (!in_array($layout, $allowedLayouts, true)) {
             return;
           // wp_die(__('Invalid layout.', 'wp-team-manager'));
        }

        $styleTypeKey = "{$layout}_style_type";
        $styleType = $settings[$styleTypeKey] ?? '';

        // Ensure only safe characters (alphanumeric + underscores)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $styleType)) {
             return;
           // wp_die(__('Invalid style type.', 'wp-team-manager'));
        }

        // Ensure constants exist before using them
        if (!defined('TM_PATH')) {
            wp_die(__('TM_PATH is not defined.', 'wp-team-manager'));
        }

        // Define Free path (always available)
        $basePath = realpath(TM_PATH . '/public/templates/elementor/layouts/');

        // Define Pro path if available
        $proPath = defined('TM_PRO_PATH') ? realpath(TM_PRO_PATH . '/public/templates/elementor/layouts/') : null;

        // Ensure the free path is valid
        if (!$basePath) {
             return;
            //wp_die(__('Invalid base template path.', 'wp-team-manager'));
        }

        $templateName = sanitize_file_name($styleType . '.php');

        // Define possible template paths (Pro first, then Free)
        $proFullPath = $proPath ? $proPath . '/' . $layout . '/' . $templateName : null;
        $freeFullPath = $basePath . '/' . $layout . '/' . $templateName;

        // Check if Pro template exists and is readable
        if ($proFullPath && is_readable($proFullPath) && strpos(realpath($proFullPath), $proPath) === 0) {
            include $proFullPath;
            return;
        }

        // Check if Free template exists and is readable
        if (is_readable($freeFullPath) && strpos(realpath($freeFullPath), $basePath) === 0) {
            include $freeFullPath;
            return;
        }

        // If neither file is found, show an error
        wp_die(__('Template not found or invalid file.', 'wp-team-manager'));
    }
    
           /**
         * Renders the Elementor layout based on the given layout, data, and settings.
         *
         * @param string $layout The name of the layout to render.
         * @param array $data The data to pass to the layout template.
         * @param array $settings The settings for the layout.
         * @throws None
         * @return void
         */
        public static function renderTeamLayout(string $layout, array $data, string $styleType, array $settings = []): void
        {
            $allowedLayouts = ['grid', 'list', 'slider', 'table', 'isotope']; // Allowed layouts

            // Validate layout
            if ( ! in_array( $layout, $allowedLayouts, true ) ) {
                return;
            }

            // Validate style type (alphanumeric, dash, underscore only)
            if ( ! preg_match( '/^[a-zA-Z0-9_-]+$/', (string) $styleType ) ) {
                return;
            }

            // Ensure required constants exist
            if ( ! defined( 'TM_PATH' ) ) {
                return;
            }

            // Resolve Free and (optionally) Pro template roots
            $freeBase = realpath( TM_PATH . '/public/templates/layouts/' );
            $proBase  = defined( 'TM_PRO_PATH' ) ? realpath( TM_PRO_PATH . '/public/templates/layouts/' ) : null;

            if ( ! $freeBase ) {
                return;
            }

            // Build safe template name
            $templateName = sanitize_file_name( $styleType . '.php' );

            // Candidate full paths (Pro preferred)
            $proFull  = $proBase ? $proBase . '/' . $layout . '/' . $templateName : null;
            $freeFull = $freeBase . '/' . $layout . '/' . $templateName;

            // Make data/settings available to templates
            // (Most templates expect $data and $settings; keep names as-is.)
            $team_data = $data; // optional alias if some templates expect $team_data

            // Include Pro template if it exists and is inside the Pro base
            if ( $proFull && is_readable( $proFull ) ) {
                $resolvedPro = realpath( $proFull );
                if ( $resolvedPro && strpos( $resolvedPro, $proBase ) === 0 ) {
                    include $resolvedPro;
                    return;
                }
            }

            // Fallback to Free template (ensure path is inside Free base)
            if ( is_readable( $freeFull ) ) {
                $resolvedFree = realpath( $freeFull );
                if ( $resolvedFree && strpos( $resolvedFree, $freeBase ) === 0 ) {
                    include $resolvedFree;
                    return;
                }
            }

            // Nothing to render if neither exists
            return;
        }
        
    
        /**
         * Locates a template file based on the given template name, template path, and default path.
         *
         * @param string $templateName The name of the template file to locate.
         * @param string $templatePath The path to search for the template file. Defaults to 'public/templates'.
         * @param string $defaultPath The default path to use if the template file is not found in the template path. Defaults to TM_PATH . '/public/templates/'.
         * @return string The path to the located template file, or the default path if the template file is not found.
         */
      
        private static function locateTemplate(string $templateName, string $templatePath = '', string $defaultPath = ''): string
        {
            // Ensure template name is safe (allow only alphanumeric, dashes, and underscores)
            if (!preg_match('/^[a-zA-Z0-9_-]+\.php$/', $templateName)) {
                die('Invalid template name.');
            }
        
            $templatePath = $templatePath ?: 'public/templates';
            $defaultPath = $defaultPath ?: TM_PATH . '/public/templates/';
        
            // Ensure paths are properly resolved
            $resolvedDefaultPath = realpath($defaultPath);
            $resolvedTemplatePath = realpath(trailingslashit($templatePath));
        
            // Validate resolved paths
            if (!$resolvedDefaultPath || strpos($resolvedDefaultPath, realpath(TM_PATH . '/public/templates/')) !== 0) {
                die('Invalid default path.');
            }
        
            if ($resolvedTemplatePath && strpos($resolvedTemplatePath, realpath(TM_PATH . '/public/templates/')) === 0) {
                $template = locate_template($resolvedTemplatePath . '/' . $templateName);
                if ($template && file_exists($template)) {
                    return $template;
                }
            }
        
            // Build the final safe path
            $finalPath = $resolvedDefaultPath . '/' . $templateName;
        
            // Ensure the final path is within the allowed directory
            if (file_exists($finalPath) && strpos(realpath($finalPath), $resolvedDefaultPath) === 0) {
                return $finalPath;
            }
        
            die('Template not found or invalid.');
        }
        
        /**
         * Displays the HTML output of a given layout, with the given data and settings.
         * 
         * @param string $layout The name of the layout to display. Defaults to 'grid'. Valid values are 'grid', 'list', and 'slider'.
         * @param array $data The data to pass to the layout template.
         * @param array $settings The settings for the layout.
         * 
         * @return void
         */
        public static function show_html_output($layout = 'grid', $data = [], $settings = [])
        {
            // Define allowed layouts to prevent arbitrary input
            $allowedLayouts = [
                'grid'   => 'content-grid.php',
                'list'   => 'content-list.php',
                'slider' => 'content-slider.php'
            ];
        
            // Ensure the layout is valid
            if (!array_key_exists($layout, $allowedLayouts)) {
                $layout = 'grid'; // Default fallback
            }
        
            $templateFile = $allowedLayouts[$layout];
            $templateFile = apply_filters('wp_team_manager_template_file', $templateFile, $layout, $settings);
        
            // Locate and validate template path
            $templatePath = self::wtm_locate_template($templateFile);
        
            // Ensure the template file exists and is inside the intended directory
            if (file_exists($templatePath) && strpos(realpath($templatePath), realpath(TM_PATH . '/public/templates/')) === 0) {
                include $templatePath;
            } else {
                die('Invalid template file.');
            }
        }
        
    

    /**
     * Renders a specified number of terms for a given post and taxonomy.
     *
     * @param int $post_id The ID of the post from which to retrieve terms.
     * @param int $term_to_show The number of terms to display. Defaults to 1.
     * @param string $term The taxonomy from which to retrieve terms. Defaults to 'team_designation'.
     * @return bool False if the post ID is empty or no terms are found; otherwise, outputs the terms HTML.
     */
    public static function render_terms( $post_id, $term_to_show = 1, $term = 'team_designation' ){
		if( empty( $post_id ) ){
			return false;
		}

		$get_the_terms = get_the_terms( $post_id, $term);

		if( ! is_array( $get_the_terms ) ){
			return false;
		}

		$terms = array_slice($get_the_terms, 0, $term_to_show);

		$terms_html = '<div class="team-'.$term.'">';
		foreach( $terms as $term ){
			$terms_html .= '<span class="team-position">'. esc_html( $term->name ) .'</span>';
		}
		$terms_html .= '</div>';

		$terms_html = apply_filters('wp_team_manager_terms_output', $terms_html, $term, $post_id);
		return $terms_html;
	}

    /**
     * Shows a label indicating that a feature is only available in the Pro version.
     *
     * @return string The label HTML, or an empty string if the current user has a paid license.
     */
    public static function showProFeatureLabel(){

        if(tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()){
            return esc_html__(' (Pro Feature)', 'wp-team-manager');
        }

        return '';
    }

    /**
     * Returns a link to upgrade to the Pro version if the current user does not have a paid license.
     *
     * @return string The link HTML, or an empty string if the current user has a paid license.
     */
    public static function showProFeatureLink( $link_text = 'Upgrade Now!') {
        // Validate the link text to ensure it contains safe characters
        $link_text = sanitize_text_field($link_text);
    
        // Ensure the upgrade URL is retrieved once
        $upgrade_url = esc_url(tmwstm_fs()->get_upgrade_url());
    
        // Check if the user is not paying and is not on a trial
        if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) {
            return '<a href="' . $upgrade_url . '" target="_blank" rel="noopener noreferrer" class="pro-feature-link">' . esc_html($link_text) . '</a>';
        }
    
        return '';
    }
    
    /**
     * Get a sanitized team setting from post meta.
     *
     * @param int    $post_id   Team Builder post ID.
     * @param string $key       Meta key.
     * @param mixed  $default   Default if not found.
     * @param string $type      Type: string|int|bool|array|csv_ints.
     * @return mixed
     */
    public static function get_team_setting( $post_id, $key, $default = '', $type = 'string' ) {
        $raw = get_post_meta( $post_id, $key, true );

        if ( $raw === '' || $raw === null ) {
            return $default;
        }

        switch ( $type ) {
            case 'bool':
                return ( $raw === 'yes' || $raw === '1' || $raw === 1 || $raw === true );

            case 'int':
                return absint( $raw );

            case 'array':
                return is_array( $raw ) ? $raw : maybe_unserialize( $raw );

            case 'csv_ints':
                $parts = array_map( 'trim', explode( ',', (string) $raw ) );
                return array_filter( array_map( 'absint', $parts ) );

            case 'string':
            default:
                return sanitize_text_field( $raw );
        }
    }


public static function getTaxonomies()
{
    $mode = self::get_active_mode();
    
    if ($mode === 'sports') {
        $options = [
            'team_groups'     => __('Leagues', 'wp-team-manager'),
            'team_department' => __('Teams', 'wp-team-manager'),
            'team_genders'    => __('Gender', 'wp-team-manager'),
            'team_designation'=> __('Positions', 'wp-team-manager'),
        ];
    } else {
        $options = [
            'team_groups'     => __('Group', 'wp-team-manager'),
            'team_department' => __('Department', 'wp-team-manager'),
            'team_genders'    => __('Gender', 'wp-team-manager'),
            'team_designation'=> __('Designation', 'wp-team-manager'),
        ];
    }
    

    // Load hidden taxonomies from option (expects array of slugs)
    $hidden_slugs = get_option('tm_taxonomy_fields') ?: [];

    if (!empty($hidden_slugs) && is_array($hidden_slugs)) {
        foreach ($hidden_slugs as $slug) {
            unset($options[$slug]); // remove matching entries
        }
    }

    return $options;
}

/**
 * Generate taxonomy filters for frontend display (Pro Feature)
 *
 * @param array $allowed_taxonomies Optional. Array of taxonomy slugs to show. Defaults to ['team_groups'].
 * @param array $allowed_terms Optional. Array of term slugs to filter by. If empty, shows all terms.
 */
public static function render_taxonomy_filters($allowed_taxonomies = ['team_groups'], $allowed_terms = []) {
    // Pro feature check
    if (!self::is_pro_active()) {
        return '';
    }

    // Get option with default of 1 (enabled by default for pro users)
    $show_filters = get_option('tm_show_taxonomy_filter', 1);

    // Return empty if filters are disabled
    if (!$show_filters || $show_filters == '0' || $show_filters === 0) {
        return '';
    }

    $show_count = get_option('tm_show_taxonomy_count', 0);
    $hierarchical = get_option('tm_hierarchical_taxonomy', 0);

    // Filter taxonomies to only allowed ones
    $all_taxonomies = self::getTaxonomies();
    $taxonomies = array_intersect_key($all_taxonomies, array_flip($allowed_taxonomies));

    if (empty($taxonomies)) {
        return '';
    }

    ob_start();
    ?>
    <div class="wtm-taxonomy-filters">
        <div class="wtm-filter-all">
            <button class="wtm-filter-btn active" data-filter="*"><?php esc_html_e('All', 'wp-team-manager'); ?></button>
        </div>
        <?php foreach ($taxonomies as $taxonomy => $label) :
            $term_args = array(
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
                'hierarchical' => $hierarchical ? true : false
            );

            // If specific terms are allowed, filter by slug
            if (!empty($allowed_terms)) {
                $term_args['slug'] = $allowed_terms;
            }

            $terms = get_terms($term_args);

            if (!empty($terms) && !is_wp_error($terms)) : ?>
                <div class="wtm-filter-group" data-taxonomy="<?php echo esc_attr($taxonomy); ?>">
                    <?php foreach ($terms as $term) :
                        $count_text = $show_count ? ' (' . $term->count . ')' : '';
                    ?>
                        <button class="wtm-filter-btn" data-filter=".<?php echo esc_attr($taxonomy . '-' . $term->slug); ?>">
                            <?php echo esc_html($term->name . $count_text); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif;
        endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Get taxonomy classes for team member
 */
public static function get_team_taxonomy_classes($post_id) {
    $show_filters = get_option('tm_show_taxonomy_filter', 1);
    
    // Return empty if filters are disabled
    if (!$show_filters) {
        return '';
    }
    
    $classes = array();
    $taxonomies = self::getTaxonomies();
    
    foreach ($taxonomies as $taxonomy => $label) {
        $terms = get_the_terms($post_id, $taxonomy);
        if ($terms && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $classes[] = $taxonomy . '-' . $term->slug;
            }
        }
    }
    
    return implode(' ', $classes);
}

}