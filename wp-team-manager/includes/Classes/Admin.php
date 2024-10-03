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
	public function shortocode_in_post_column_data( $column, $post_id ) {

		switch ( $column ) {

			case 'shortcode' :
				echo '<code>[dwl_create_team id="'.esc_attr($post_id).'"]</code>';
				break;

		}

	}

}