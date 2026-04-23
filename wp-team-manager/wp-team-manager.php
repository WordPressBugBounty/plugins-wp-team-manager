<?php

/**
 * @link              https://www.dynamicweblab.com/
 * @since             2.0.2
 * @package           Wp_Team_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       Dynamic Team Manager
 * Plugin URI:        https://wpteammanager.com/
 * Description:       Showcase your team members, sports rosters, or creative portfolios with grid, list and Carousel layout. Fully customizable with Elementor and shortcode builder. Supports Corporate, Sports League, and more modes.
 * Version:           2.5.2
 * Author:            DynamicWebLab
 * Author URI:        https://dynamicweblab.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-team-manager
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( function_exists( 'tmwstm_fs' ) ) {
    tmwstm_fs()->set_basename( false, __FILE__ );
} else {
    /**
     * DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE
     * `function_exists` CALL ABOVE TO PROPERLY WORK.
     */
    if ( !function_exists( 'tmwstm_fs' ) ) {
        // Create a helper function for easy SDK access.
        function tmwstm_fs() {
            global $tmwstm_fs;
            if ( !isset( $tmwstm_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_14958_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_14958_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/includes/freemius/start.php';
                $tmwstm_fs = fs_dynamic_init( array(
                    'id'              => '14958',
                    'slug'            => 'wp-team-manager',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_51d4e036fdcfa3c1272210e3a0733',
                    'is_premium'      => false,
                    'premium_suffix'  => 'Pro',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'is_live'         => true,
                    'trial'           => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'has_affiliation' => 'customers',
                    'menu'            => array(
                        'slug'       => 'edit.php?post_type=team_manager',
                        'first-path' => 'edit.php?post_type=team_manager&page=team-manager-tools',
                        'contact'    => true,
                        'support'    => false,
                    ),
                ) );
            }
            return $tmwstm_fs;
        }

        // Init Freemius.
        tmwstm_fs();
        // Signal that SDK was initiated.
        do_action( 'tmwstm_fs_loaded' );
    }
    // Check PHP version compatibility
    if ( version_compare( PHP_VERSION, '8.0.0', '<' ) ) {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo sprintf( __( 'WP Team Manager requires PHP 8.0 or higher. Current version: %s. Please upgrade PHP.', 'wp-team-manager' ), PHP_VERSION );
            echo '</p></div>';
        } );
        return;
    }
    define( 'TM_VERSION', '2.5.2' );
    define( 'TM_PHP_MIN_VERSION', '8.0.0' );
    define( 'TM_FILE', __FILE__ );
    define( 'TM_PATH', __DIR__ );
    define( 'TM_URL', plugins_url( '', TM_FILE ) );
    define( 'TM_ADMIN_ASSETS', TM_URL . '/admin/assets' );
    define( 'TM_PUBLIC', TM_URL . '/public' );
    define( 'TM_PRO_PATH', __DIR__ . '/pro' );
    define( 'TM_PRO_URL', plugins_url( '/pro', TM_FILE ) );
    define( 'TM_PRO_PUBLIC', TM_PRO_URL . '/public' );
    // Load Composer autoloader before Core.php
    $wptm_autoload_path = dirname( __FILE__ ) . '/vendor/autoload.php';
    if ( file_exists( $wptm_autoload_path ) ) {
        require_once $wptm_autoload_path;
    } else {
        add_action( 'admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__( 'The Dynamic Team Manager plugin is missing its Composer dependencies. Please run composer install.', 'wp-team-manager' );
            echo '</p></div>';
        } );
        return;
    }
    $core_path = __DIR__ . '/Core.php';
    if ( file_exists( $core_path ) ) {
        require_once $core_path;
    }
    register_activation_hook( __FILE__, 'wptm_activate_wp_team' );
    /**
     * Plugin activation action.
     *
     * Plugin activation will not work after "plugins_loaded" hook
     * that's why activation hooks run here.
     */
    function wptm_activate_wp_team() {
        $activation = strtotime( 'now' );
        add_option( 'wp_team_manager_activation_time', $activation );
        update_option( 'wp_team_manager_version', TM_VERSION );
        flush_rewrite_rules();
    }

    add_action( 'admin_init', function () {
        $current_version = get_option( 'wp_team_manager_version' );
        if ( version_compare( $current_version, TM_VERSION, '<' ) ) {
            flush_rewrite_rules();
            update_option( 'wp_team_manager_version', TM_VERSION );
        }
    } );
    register_deactivation_hook( __FILE__, 'wptm_deactivate_wtp_team' );
    /**
     * Plugin deactivation action.
     *
     * Plugin deactivation will not work after "plugins_loaded" hook
     * that's why deactivation hooks run here.
     */
    function wptm_deactivate_wtp_team() {
        flush_rewrite_rules();
    }

}