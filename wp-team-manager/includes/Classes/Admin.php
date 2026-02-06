<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'Direct access not allowed.' );
}
class Admin {

    use \DWL\Wtm\Traits\Singleton;

    protected function init(){

		\add_filter( 'manage_dwl_team_generator_posts_columns', [ $this, 'shortcode_in_post_column'] );

		\add_filter( 'manage_dwl_team_generator_posts_custom_column', [ $this, 'shortcode_in_post_column_data' ], 10, 2 );

		\add_action( 'save_post_team_manager', [ $this, 'clear_team_cache' ] );

		\add_action( 'admin_head', [ $this, 'add_css' ] );
	}

	/**
	 * Hides specific menu items that are now integrated into unified tools
	 */
	public function add_css() {
		// Use wp_add_inline_style for better security and WordPress standards
		$custom_css = '
			#menu-posts-team_manager ul.wp-submenu li a[href*="team-manager-migration"],
			#menu-posts-team_manager ul.wp-submenu li a[href*="team-ai-agents"],
			#menu-posts-team_manager ul.wp-submenu li a[href*="team-manager-shortcode-generator"],
			#menu-posts-team_manager ul.wp-submenu li a[href*="wtm_dashboard"] {
				display: none !important;
			}
		';
		wp_register_style( 'wtm-admin-inline', false );
		wp_enqueue_style( 'wtm-admin-inline' );
		wp_add_inline_style( 'wtm-admin-inline', $custom_css );
	}

	/**
	 * Add shortcode admin column
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	public function shortcode_in_post_column( $columns ) {

		unset( $columns['date'] );

		$columns['shortcode'] = __( 'Shortcode', 'wp-team-manager' );

		$columns['date']      = __( 'Date', 'wp-team-manager' );

		return $columns;

	}

	/**
	 * Show shortcode admin column
	 *
	 * @param string $column
	 * @param int $post_id
	 */
	public function shortcode_in_post_column_data($column, $post_id) {
		if ($column === 'shortcode') {
			echo '<code>' . sprintf( '[dwl_create_team id="%d"]', esc_attr($post_id) ) . '</code>';
		}
	}

	/**
	 * Clear the team cache on save post
	 *
	 * @param int $post_id The post ID
	 */
	public function clear_team_cache( $post_id ) {
		// Skip for autosaves
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		// Verify user has permission to edit this post
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Only clear cache for published posts (not drafts, pending, trash, etc.)
		$post_status = get_post_status( $post_id );
		if ( $post_status !== 'publish' ) {
			return;
		}

		// Clear team data transients with proper LIKE escaping
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_wtm_team_data_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_wtm_team_data_' ) . '%'
		) );
	}

}