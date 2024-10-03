<?php
namespace DWL\Wtm\Classes;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Team_Manager
 * @subpackage Wp_Team_Manager/admin
 * @author     Maidul <dynamicweblab@gmail.com>
 */
class AdminAssets {

	use \DWL\Wtm\Traits\Singleton;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	protected function init(){
		\add_action( 'admin_enqueue_scripts', [ $this, 'wp_team_manager_admin_assets' ] );
		\add_action("wp_ajax_wtm_admin_preview" , [ $this, 'wtm_admin_preview' ]);
	}

	/**
	 * Register the stylesheets and script for the admin area.
	 *
	 * @since    1.0.0
	 */

	public function wp_team_manager_admin_assets( $hook ) {

		$screen = get_current_screen(); 

		wp_register_script( 'team-manager-admin', TM_ADMIN_ASSETS . '/js/short-code-builder.js', ['jquery','wp-color-picker'], TM_VERSION );
		wp_localize_script('team-manager-admin', 'wtm_ajax', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('wtm-setting-page'),
		));
		
		wp_register_style( 'team-manager-admin', TM_ADMIN_ASSETS .'/css/tm-admin.css', [], TM_VERSION);
		wp_register_style( 'wp-team-font-awesome-admin', TM_PUBLIC . '/assets/vendor/font-awesome/css/all.min.css', [], '5.9.0');
		wp_register_style( 'wp-team-slick-admin', TM_PUBLIC . '/assets/vendor/slick/slick.css', [], '5.9.0');
		wp_register_style( 'wp-team-slick-theme-admin', TM_PUBLIC . '/assets/vendor/slick/slick-theme.css', [], '5.9.0');
		wp_register_style( 'wp-team-single-admin', TM_PUBLIC . '/assets/css/tm-single.css', [], TM_VERSION );
		wp_register_style( 'wp-team-style-admin', TM_PUBLIC . '/assets/css/tm-style.css', [], TM_VERSION );
		wp_register_style( 'wp-team-setting-admin', TM_ADMIN_ASSETS . '/css/tm-settings.css', [], TM_VERSION );

		// register scritps
		wp_register_script( 'wp-team-slick-admin', TM_PUBLIC . '/assets/vendor/slick/slick.min.js', array(), '5.9.0', true );
		wp_register_script( 'wp-team-script-admin', TM_PUBLIC . '/assets/js/team.js', array(), TM_VERSION, true );
		wp_register_script( 'wp-team-settings-admin', TM_ADMIN_ASSETS . '/js/settings.js', array(), TM_VERSION, true );

		if ( ('post-new.php' == $hook OR 'post.php' == $hook ) AND ( $screen->post_type == 'team_manager' || $screen->post_type == 'dwl_team_generator' ) ) {
			wp_enqueue_style( 'wp-team-post-admin', TM_ADMIN_ASSETS . '/css/tm-post.css', [], TM_VERSION );
		}
	}
	
	public function wtm_admin_preview(){

		if ( !wp_verify_nonce($_POST['nonce'], 'wtm-setting-page') ){ 
			die('Permission Denied.'); 
		}

		if(isset($_POST['shortcode'])){
			echo do_shortcode(stripslashes($_POST['shortcode']));
		}
		
		wp_die();
	}

}