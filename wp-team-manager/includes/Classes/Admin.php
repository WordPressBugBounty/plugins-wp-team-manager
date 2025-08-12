<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Admin {

    use \DWL\Wtm\Traits\Singleton;

    protected function init(){

		\add_filter( 'manage_dwl_team_generator_posts_columns', [ $this, 'shortocode_in_post_column'] );

		\add_filter( 'manage_dwl_team_generator_posts_custom_column', [ $this, 'shortocode_in_post_column_data' ], 10, 2 );

		\add_action( 'save_post_team_manager', [ $this, 'clear_team_cache' ] );
	}

	/**
	 * Add shortcode admin column
	 *
	 * @param $columns
	 *
	 * @return mixed
	 */
	public function shortocode_in_post_column( $columns ) {

		unset( $columns['date'] );

		$columns['shortcode'] = __( 'Shortcode', 'wp-team-manager' );

		$columns['date']      = __( 'Date', 'wp-team-manager' );

		return $columns;

	}

	/**
	 * Show shortcode admin column
	 *
	 * @param $column
	 * @param $post_id
	 */
	public function shortocode_in_post_column_data($column, $post_id) {
		if ($column === 'shortcode') {
			printf('<code>[dwl_create_team id="%d"]</code>', esc_attr($post_id));
		}
	}

	/**
	 * Clear the team cache on save post
	 *
	 * @param int $post_id The post ID
	 */
	public function clear_team_cache( $post_id ) {
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wtm_team_data_%' OR option_name LIKE '_transient_timeout_wtm_team_data_%'" );
	}

}