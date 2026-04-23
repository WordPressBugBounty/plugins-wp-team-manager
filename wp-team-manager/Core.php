<?php
use DWL\Wtm\Classes as ControllerClass;
use DWL\Wtm\Elementor as ElementorClass;

/**
 * This is main class for the plugin
 */
final class Wp_Team_Manager {

	use DWL\Wtm\Traits\Singleton;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const version = '2.5.2';

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
		
		// Migration hook - only check on admin pages
		if ( is_admin() ) {
			\add_action( 'admin_init', [ $this, 'check_and_schedule_migration' ] );
		}
		
		// Background migration hook
		\add_action( 'wtm_run_social_fields_migration', [ $this, 'run_social_fields_migration' ] );

		
	}


	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * @access public
	 */
	public function load_plugin_text_domain() {
		load_plugin_textdomain('wp-team-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
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
			ControllerClass\LiveSearch::class,
			ControllerClass\SearchWidget::class,
			DWL\Wtm\AI\Admin\AdminUI::class,
		];

		if (tmwstm_fs()->is_paying_or_trial() && class_exists(DWL\Wtm\Classes\LoadMore::class)) {
			$controllers[] = ControllerClass\LoadMore::class;
		}

		if ( is_admin() ) {
			$controllers[] = ControllerClass\Admin::class;
			$controllers[] = ControllerClass\AdminAssets::class;
			$controllers[] = ControllerClass\AdminSettings::class;
			$controllers[] = ControllerClass\UnifiedTools::class;
			$controllers[] = ControllerClass\ImportExportTools::class;
			$controllers[] = ControllerClass\MigrationTools::class;
			$controllers[] = ControllerClass\Dashboard::class;
			$controllers[] = ControllerClass\Onboarding::class;
			$controllers[] = ControllerClass\SearchSettings::class;
            $controllers[] = ControllerClass\FreemiusConfig::class;
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
		// Initialize AI Manager (stored to prevent memory leaks)
		$ai_manager = new \DWL\Wtm\AI\AI_Manager();
		require_once TM_PATH . '/lib/cmb2/init.php';
		require_once TM_PATH . '/lib/cmb2-radio-image/cmb2-radio-image.php';
		require_once TM_PATH . '/lib/cmb2-tabs/cmb2-tabs.php';
		require_once TM_PATH . '/includes/functions.php';
		require_once TM_PATH . '/includes/Classes/GutenbergBlock.php';
		require_once TM_PATH . '/includes/Classes/BlockPatterns.php';
		require_once TM_PATH . '/includes/Classes/FSESupport.php';
		

	}

	/**
	 * Check if migration is needed and schedule it
	 * Runs only once per version update
	 *
	 * @since 2.4.4
	 */
	public function check_and_schedule_migration() {
		$current_version = get_option( 'wp_team_manager_version' );
		$migration_completed = get_option( 'team_migration_completed' );
		
		// Only run migration if:
		// 1. User is upgrading from version < 2.1.14
		// 2. Migration hasn't been completed yet
		if ( ! $migration_completed && version_compare( $current_version, '2.1.14', '<' ) ) {
			// Check if migration is already scheduled
			if ( ! wp_next_scheduled( 'wtm_run_social_fields_migration' ) ) {
				// Schedule migration to run in 30 seconds (gives time for plugin to fully load)
				wp_schedule_single_event( time() + 30, 'wtm_run_social_fields_migration' );
			}
		}
	}

	/**
	 * Run social fields migration in background
	 * Processes team members in batches to avoid timeouts
	 *
	 * @since 2.4.4
	 */
	public function run_social_fields_migration() {
		// Double-check migration hasn't already completed
		if ( get_option( 'team_migration_completed' ) ) {
			return;
		}
		
		// Get batch offset
		$batch_offset = get_option( 'team_migration_batch_offset', 0 );
		$batch_size = 50; // Process 50 members at a time
		
		$args = array(
			'post_type'      => 'team_manager',
			'posts_per_page' => $batch_size,
			'offset'         => $batch_offset,
			'fields'         => 'ids',
			'post_status'    => 'any', // Include all statuses
		);

		$team_member_ids = get_posts( $args );

		if ( ! empty( $team_member_ids ) ) {
			// Process this batch
			foreach ( $team_member_ids as $team_member_id ) {
				ControllerClass\Helper::team_social_icon_migration( $team_member_id );
			}
			
			// Update offset for next batch
			update_option( 'team_migration_batch_offset', $batch_offset + $batch_size );
			
			// Schedule next batch
			wp_schedule_single_event( time() + 10, 'wtm_run_social_fields_migration' );
		} else {
			// Migration complete
			update_option( 'team_migration_completed', true );
			delete_option( 'team_migration_batch_offset' );
		}
	}

	/**
	 * Load public assets
	*/

	public function wp_team_assets(){
		$upload_dir = wp_upload_dir();
		$css_file   = $upload_dir['basedir'] . '/wp-team-manager/team.css';
		if ( file_exists( $css_file ) ) {
			$version = filemtime( $css_file );
			wp_enqueue_style( 'team-generated', set_url_scheme( $upload_dir['baseurl'] ) . '/wp-team-manager/team.css', null, $version );
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
		add_action( 'before_delete_post', [ $this, 'remove_generated_css_after_delete_post' ], 10, 1 );
	}

	/**
	 * Save generated CSS after saving a post of type `dwl_team_generator`
	 * 
	 * @param int    $post_id The ID of the post being saved.
	 * @param object $post    The post object being saved.
	 * @param bool   $update  Whether the post is being updated or not.
	 *
	 * @return void
	 * 
	 * @since 1.0.0
	 */
	public function add_generated_css_after_save_post( $post_id, $post, $update ) {
		// Bail if autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		// Bail if revision
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}
		
		// Bail if not the correct post type
		if ( 'dwl_team_generator' !== $post->post_type ) {
			return;
		}
		
		// Bail if user doesn't have permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Generate CSS
		ControllerClass\Helper::generatorShortcodeCss( $post_id );
	}

	/**
	 * Removes generated CSS after a post of type `dwl_team_generator` is deleted.
	 *
	 * This method is hooked into the `before_delete_post` action. It checks the post type,
	 * and if it matches `dwl_team_generator`, it calls the helper function to remove the associated
	 * custom CSS.
	 *
	 * @param int $post_id The ID of the post being deleted.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function remove_generated_css_after_delete_post( $post_id ) {
		// Get the post object (before_delete_post only passes post_id)
		$post = get_post( $post_id );
		
		// Check if post exists and is the correct type
		if ( ! $post || 'dwl_team_generator' !== $post->post_type ) {
			return;
		}
		
		ControllerClass\Helper::removeGeneratorShortcodeCss( $post_id );
	}

}


Wp_Team_Manager::get_instance();