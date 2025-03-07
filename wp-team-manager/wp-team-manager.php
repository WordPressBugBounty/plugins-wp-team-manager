<?php

/**
 * @link              https://www.dynamicweblab.com/
 * @since             2.0.2
 * @package           Wp_Team_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Team Manager
 * Plugin URI:        https://wpteammanager.com/
 * Description:       Showcase your team members with grid, list and Carousel layout. Fully customizable with Elementor and shortcode builder.
 * Version:           2.2.0
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
define( 'TM_VERSION', '2.2.0' );
define( 'TM_FILE', __FILE__ );
define( 'TM_PATH', __DIR__ );
define( 'TM_PRO_PATH', __DIR__ . '/pro' );
define( 'TM_URL', plugins_url( '', TM_FILE ) );
define( 'TM_ADMIN_ASSETS', TM_URL . '/admin/assets' );
define( 'TM_PUBLIC', TM_URL . '/public' );
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
                    'id'             => '14958',
                    'slug'           => 'wp-team-manager',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_51d4e036fdcfa3c1272210e3a0733',
                    'is_premium'     => false,
                    'premium_suffix' => 'Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'is_live'        => true,
                    'trial'          => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'           => array(
                        'slug'       => 'edit.php?post_type=team_manager',
                        'first-path' => 'edit.php?post_type=team_manager&page=wtm_get_help',
                        'contact'    => false,
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
    // ... Your plugin's main file logic ...
}
require_once dirname( __FILE__ ) . '/vendor/autoload.php';
require_once dirname( __FILE__ ) . '/includes/Classes/Core.php';