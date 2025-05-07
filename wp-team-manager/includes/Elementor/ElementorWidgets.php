<?php
namespace DWL\Wtm\Elementor;

if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

use Elementor\Plugin;
/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.2.0
 */
class ElementorWidgets {

	use \DWL\Wtm\Traits\Singleton;


/**
 * Register a new widget category.
 *
 * This function adds a custom category 'dwl-items' to the Elementor elements
 * manager, allowing widgets to be grouped under 'DWL Elements' with a specified
 * icon.
 *
 * @param object $elements_manager Elementor Elements Manager.
 * @since 1.2.0
 */

	public function register_widget_category( $elements_manager ) {

		$elements_manager->add_category(
			'dwl-items',
			[
				'title' => __( 'Team Manager', 'wp-team-manager' ),
				'icon' => 'fa fa-plug',
			]
		);
	}

	/**
	 * Registers Custom controls.
	 *
	 * @param object $controls_manager Controls Manager.
	 * @return void
	 */
	public function registerControls( $controls_manager ) {
		
		require_once( __DIR__ . '/Controls/ImageSelector.php' );
		
		$controls_manager->register( new ImageSelector() );

	}
	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 1.2.0
	 * @access private
	 */
	private function include_widgets_files() {
		$files = [
			__DIR__ . '/widgets/team.php',
			__DIR__ . '/widgets/isotope.php',
		];
	
		foreach ($files as $file) {
			if ( file_exists( $file ) ) {
				require_once $file;
			} else {
				if (defined('WP_DEBUG') && WP_DEBUG) {
					error_log( "Missing file: " . $file ); // Log error instead of breaking the site
				}
				
			}
		}
	}
	
	/**
	 * Enqueue Editor Scripts
	 *
	 * This function enqueues the necessary JavaScript files for the Elementor editor.
	 * Specifically, it enqueues the 'wp-team-el-admin' script to ensure the editor
	 * has access to the required functionalities and features.
	 *
	 * @since 1.2.0
	 */

	public function editor_scripts(){
		wp_enqueue_script( 'wp-team-el-admin' );
	}

	/**
	 * Register Widgets
	 *
	 * Register new Elementor widgets.
	 *
	 * @since 1.2.0
	 * @access public
	 */
	public function register_widgets() {
		// Its is now safe to include Widgets files
		$this->include_widgets_files();

		// Register Widgets
        Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Team() );
		Plugin::instance()->widgets_manager->register_widget_type( new Widgets\Isotope() );
	}
	

	/**
	 *  Plugin class constructor
	 *
	 * Register plugin action hooks and filters
	 *
	 * @since 1.2.0
	 * @access public
	 */
	protected function init(){

		\add_action( 'elementor/controls/register', [ $this, 'registerControls' ] );

		// Register widgets
		\add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

		\add_action( 'elementor/elements/categories_registered', [ $this, 'register_widget_category' ] );

		// Register editor scripts
		\add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'editor_scripts' ] );
	
	}
	
	
}

