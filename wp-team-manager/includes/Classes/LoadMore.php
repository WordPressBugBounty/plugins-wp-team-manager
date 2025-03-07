<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class LoadMore{
	
	use \DWL\Wtm\Traits\Singleton;

	protected function init() {
		\add_action('wp_ajax_load_more_team', [$this, 'wp_team_manager_load_more_posts']);
		\add_action('wp_ajax_nopriv_load_more_team', [$this, 'wp_team_manager_load_more_posts']);
	}

	public function wp_team_manager_load_more_posts() {
		check_ajax_referer('load_more_nonce', 'nonce');
	
		$paged = isset($_POST['paged']) ? intval($_POST['paged']) + 1 : 2;
		// Limit to 50 posts max, Prevents excessive resource usage.
		$posts_per_page = isset($_POST['posts_per_page']) ? min( intval($_POST['posts_per_page']), 50 ) : 6;
		$settings = isset($_POST['settings']) ? array_map('sanitize_text_field', $_POST['settings']) : [];
		
		$args = array(
			'post_type'      => 'team_manager',
			'posts_per_page' => $posts_per_page, 
			'paged'          => $paged,
		);
	
		$team_data = Helper::get_team_data($args);
	
		if ($team_data) {
			ob_start();

			//print_r($team_data['max_num_pages']);
	
			Helper::renderElementorLayout($settings['layout_type'],$team_data['posts'],$settings);
	
			$data = ob_get_clean();
	
			// Determine if there are more posts
			$has_more = $paged < $team_data['max_num_pages'];

			wp_send_json_success(array(
				'data'     => $data,
				'has_more' => $has_more,
			));

		} else {
			wp_send_json_error('No more posts', 200);
		}
	
		wp_die();
	}
	

	
}