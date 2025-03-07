<?php
use DWL\Wtm\Classes as ControllerClass;
use DWL\Wtm\Elementor as ElementorClass;

/**
 * This is mane class for the plugin
 */
final class Wp_Team_Manager {

	use DWL\Wtm\Traits\Singleton;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '2.2.0';

	/**
	 * Class init.
	 *
	 * @return void
	 */
	protected function init() {

		// Hooks.
		\add_action( 'init', [ $this, 'initial' ] );
		\add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );
		\add_action( 'admin_init', [ $this, 'handle_css_generator_and_remove' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'wp_team_assets' ] );
		\add_action( 'admin_enqueue_scripts', [ $this, 'wp_team_admin_assets' ] );
		\add_action( 'init', [ $this, 'migration_old_cmb_social_fields' ] );

		
	}


	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * @access public
	 */
	public function load_plugin_text_domain() {
		load_plugin_textdomain( 'wp-team-manager', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Init Hooks.
	 *
	 * @return void
	 */
	public function initial() {
		
		ControllerClass\Helper::instances( $this->controllers() );
		$this->load_plugin_text_domain();

		\do_action( 'wtm_loaded' );
	}

	/**
	 * Controllers.
	 *
	 * @return array
	 */
	public function controllers() {

		$controllers = [
			ControllerClass\PostType::class,
			ControllerClass\TeamMetabox::class,
			ControllerClass\ShortcodeGenerator::class,
			ControllerClass\Shortcodes::class,
			ControllerClass\PublicAssets::class,
			ControllerClass\LoadMore::class,
		];

		if ( is_admin() ) {
			$controllers[] = ControllerClass\Admin::class;
			$controllers[] = ControllerClass\AdminAssets::class;
			$controllers[] = ControllerClass\AdminSettings::class;
			$controllers[] = ControllerClass\GetHelp::class;
		}

		if ( did_action( 'elementor/loaded' ) ) {
			$controllers[] = ElementorClass\ElementorWidgets::class;
		}

		return $controllers;
	}

    /**
	 * Initialize the plugin
	 *
	 * Validates that Elementor is already loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed include the plugin class.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function plugins_loaded() {
		require_once TM_PATH . '/lib/cmb2/init.php';
		require_once TM_PATH . '/lib/cmb2-radio-image/cmb2-radio-image.php';
		require_once TM_PATH . '/lib/cmb2-tabs/cmb2-tabs.php';
		require_once TM_PATH . '/includes/functions.php';
	}

	/**
	* Migration old social meta
	*/

	public function migration_old_cmb_social_fields(){

		$current_version = get_option( 'wp_team_manager_version' );

		if ( version_compare( $current_version, '2.1.14', '==' ) ) {

			$migration_completed = get_option( 'team_migration_completed' );

			if ( !$migration_completed ){
				$args = array(
					'post_type'      => 'team_manager',
					'posts_per_page' => -1,
					'fields'         => 'ids'
				);

				$team_member_ids = get_posts( $args );

				if (!empty( $team_member_ids )) {
					foreach ( $team_member_ids as $team_member_id ) {
						ControllerClass\Helper::team_social_icon_migration( $team_member_id );
					}
					update_option('team_migration_completed', true);
				}
			}
		}

	}

	/**
	 * Load public assets
	*/

	public function wp_team_assets(){
		$upload_dir = wp_upload_dir();
		$css_file   = $upload_dir['basedir'] . '/wp-team-manager/team.css';
		if ( file_exists( $css_file ) ) {
			wp_enqueue_style( 'team-generated', set_url_scheme( $upload_dir['baseurl'] ) . '/wp-team-manager/team.css', null, time() );
		}
	}
	

	/**
	 * Load admin assets
	 */

	public function wp_team_admin_assets(){
		wp_enqueue_script(
			'cmb2-conditional-logic', 
			TM_ADMIN_ASSETS.'/js/cmb2-conditional-logic.js',
			array('jquery'), 
			'1.1.1',
			true
		);
	}

	/**
	 * Hooks into save_post and before_delete_post to manage the generation of custom CSS.
	 *
	 * The `add_generated_css_after_save_post` method is hooked into the `save_post` action to generate custom CSS after a post of type `dwl_team_generator` is saved.
	 *
	 * The `remove_generated_css_after_delete_post` method is hooked into the `before_delete_post` action to remove the custom CSS after a post of type `dwl_team_generator` is deleted.
	 */
	public function handle_css_generator_and_remove(){
		add_action( 'save_post', [ $this, 'add_generated_css_after_save_post' ], 10, 3 );
		add_action( 'before_delete_post', [ $this, 'remove_generated_css_after_delete_post' ], 10, 2 );
	}

	/**
	 * Save generated CSS after saving a post of type `dwl_team_generator`
	 * 
	 * @param int    $post_id   The ID of the post being saved.
	 * @param object $post      The post object being saved.
	 * @param bool   $update    Whether the post is being updated or not.
	 *
	 * @return void
	 * 
	 * @since 1.0.0
	 */
	public function add_generated_css_after_save_post( $post_id, $post, $update ) {
		
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return false;
        }

		if( 'dwl_team_generator' !== $post->post_type ) {
			return false;
		}

		ControllerClass\Helper::generatorShortcodeCss( $post_id );
	}

	/**
	 * Removes generated CSS after a post of type `dwl_team_generator` is deleted.
	 *
	 * This method is hooked into the `before_delete_post` action. It checks the post type,
	 * and if it matches `dwl_team_generator`, it calls the helper function to remove the associated
	 * custom CSS.
	 *
	 * @param int    $post_id The ID of the post being deleted.
	 * @param object $post    The post object being deleted.
	 *
	 * @return int The post ID if the post type does not match.
	 *
	 * @since 1.0.0
	 */

	public function remove_generated_css_after_delete_post( $post_id, $post ) {
		if( 'dwl_team_generator' !== $post->post_type ){
			return $post_id;
		}
		ControllerClass\Helper::removeGeneratorShortcodeCss( $post_id );
	}

}

Wp_Team_Manager::get_instance();

// register_activation_hook( __FILE__, 'wptm_activate_wp_team' );
// /**
//  * Plugin activation action.
//  *
//  * Plugin activation will not work after "plugins_loaded" hook
//  * that's why activation hooks run here.
//  */
// function wptm_activate_wp_team() {
// 	$activation = strtotime( 'now' );

// 	add_option( 'wp_team_manager_activation_time', strtotime( 'now' ) );

// 	update_option( 'wp_team_manager_version', TM_VERSION );

// 	flush_rewrite_rules();
// }

// register_deactivation_hook( __FILE__, 'wptm_deactivate_wtp_team' );

// /**
//  * Plugin deactivation action.
//  *
//  * Plugin deactivation will not work after "plugins_loaded" hook
//  * that's why deactivation hooks run here.
//  */
// function wptm_deactivate_wtp_team() {
// 	flush_rewrite_rules();
// }