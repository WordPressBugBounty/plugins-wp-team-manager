<?php
namespace DWL\Wtm\Classes;
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the Public-specific stylesheet and JavaScript.
 *
 * @package    Wp_Team_Manager
 * @subpackage Wp_Team_Manager/Public
 * @author     Maidul <dynamicweblab@gmail.com>
 */
class PublicAssets {

	use \DWL\Wtm\Traits\Singleton;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	
	protected function init(){
		\add_action( 'wp_enqueue_scripts', [ $this, 'wp_team_manager_public_assets' ] );
		\add_action( 'wp_head', [ $this, 'team_manager_add_custom_css'] );
		\add_action( 'wp_head', [ $this, 'general_settings'] );
		\add_action('wp_ajax_dwl_team_member_search', '\dwl_team_member_search');
		\add_action('wp_ajax_nopriv_dwl_team_member_search', '\dwl_team_member_search');
	}

	/**
	 * Register the stylesheets and script for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wp_team_manager_public_assets() {

		wp_register_style( 'wp-team-font-awesome', TM_PUBLIC . '/assets/vendor/font-awesome/css/all.min.css', [], '5.9.0');
		wp_register_style( 'wp-team-slick', TM_PUBLIC . '/assets/vendor/slick/slick.css', [], '5.9.0');
		wp_register_style( 'wp-team-slick-theme', TM_PUBLIC . '/assets/vendor/slick/slick-theme.css', [], '5.9.0');
		wp_register_style( 'wp-team-single', TM_PUBLIC . '/assets/css/tm-single.css', [], TM_VERSION );
		wp_register_style( 'wp-team-style', TM_PUBLIC . '/assets/css/tm-style.css', [], TM_VERSION );
		wp_register_style( 'wp-team-isotope', TM_PUBLIC . '/assets/css/tm-isotope.css', [], TM_VERSION );
		wp_register_style( 'wp-old-style', TM_PUBLIC . '/assets/css/tm-old-style.css', [], TM_VERSION );
		
		// Register pagination assets
		if ( defined('TM_PRO_URL') ) {
			wp_register_style( 'wp-team-pagination-style', TM_PRO_URL . '/public/assets/css/pagination.css', [], TM_VERSION );
			wp_register_script( 'wp-team-ajax-pagination', TM_PRO_URL . '/public/assets/js/pagination.js', ['jquery'], TM_VERSION, true );
		}

		wp_register_script( 
			'wp-team-slick', 
			TM_PUBLIC . '/assets/vendor/slick/slick.min.js', 
			array('jquery'), 
			'5.9.0', 
			true 
		);
	

		wp_register_script( 
			'wtm-isotope-js', 
			TM_PUBLIC . '/assets/vendor/isotope/isotope.pkgd.min.js', 
			array('jquery'), 
			'3.0.6', 
			true 
		);

		wp_register_script( 'wp-team-script', TM_PUBLIC . '/assets/js/team.js', array('jquery'), TM_VERSION, true );
		wp_register_script( 'wpteam-admin-js', TM_ADMIN_ASSETS.'/js/admin.js', array('jquery'), TM_VERSION, true );
		wp_register_script( 'wp-team-el-slider', TM_PUBLIC . '/assets/js/team-el-slider.js', array('jquery'), TM_VERSION, true );
		wp_register_script( 'wp-team-search', TM_PUBLIC . '/assets/js/search.js', array('jquery'), TM_VERSION, true );
		wp_register_script( 'wtm-live-search', TM_PUBLIC . '/assets/js/live-search.js', array('jquery'), TM_VERSION, true );
		wp_register_style( 'wtm-live-search-css', TM_PUBLIC . '/assets/css/live-search.css', array(), TM_VERSION );
	
	
		$ajaxurl = '';

		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ), true ) ) {
			$ajaxurl .= admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
		} else {
			$ajaxurl .= admin_url( 'admin-ajax.php' );
		}

		wp_localize_script( 'wp-team-script', 'wptObj', array(
			'ajaxurl' => $ajaxurl,
			'nonce' => wp_create_nonce('wtm_nonce')
		) );
		
		// Localize pagination script
		if ( defined('TM_PRO_URL') ) {
			wp_localize_script( 'wp-team-ajax-pagination', 'wtm_ajax_params', array(
				'ajax_url' => $ajaxurl,
				'nonce' => wp_create_nonce('load_more_nonce')
			) );
		}

		if(is_singular( 'team_manager' )){
			wp_enqueue_script( 'wp-team-script' );
		}
	}
	
    /**
     * Add custom css on theme header
     *
     * @since 1.0
     */
    public function team_manager_add_custom_css(){
		if(is_singular( 'team_manager' )){
			wp_enqueue_style( 'wp-team-font-awesome' );
			wp_enqueue_style( 'wp-team-single' );
			wp_enqueue_style( 'wp-team-style' );
		}

		// Add custom CSS using wp_add_inline_style for better security
		$custom_css = get_option('tm_custom_css');
		if ( ! empty( $custom_css ) ) {
			wp_register_style( 'wtm-custom-css', false );
			wp_enqueue_style( 'wtm-custom-css' );
			wp_add_inline_style( 'wtm-custom-css', wp_strip_all_tags( $custom_css ) );
		}
    } 

    /**
     * Add custom css on theme header
     *
     * @since 2.0
     */
	public function general_settings() {
		$social_size = get_option('tm_social_size', 16);
		$lazy_loading = get_option('tm_lazy_loading', 'True');
		$preload_images = get_option('tm_preload_images', 3);
		$high_contrast = get_option('tm_high_contrast', 'False');
		$focus_style = get_option('tm_focus_style', 'default');
		$keyboard_nav = get_option('tm_keyboard_nav', 'True');
		$alt_text = get_option('tm_alt_text', 'True');
		$screen_reader = get_option('tm_screen_reader', 'True');

		// Build CSS dynamically
		$inline_css = '';

		// Social icon size
		if (!empty($social_size)) {
			$inline_css .= sprintf(
				'.team-member-socials a, .team-member-other-info .fas { font-size: %dpx !important; }',
				absint($social_size)
			);
		}

		// High contrast mode
		if ($high_contrast === 'True') {
			$inline_css .= '.wtm-team-member, .team-member { filter: contrast(150%) brightness(110%); }';
		}

		// Focus indicators
		if ($focus_style === 'enhanced') {
			$inline_css .= '.wtm-team-member a:focus, .team-member a:focus { outline: 3px solid #0073aa; outline-offset: 2px; }';
		} elseif ($focus_style === 'custom') {
			$inline_css .= '.wtm-team-member a:focus, .team-member a:focus { outline: 3px solid var(--wtm-primary, #2D5016); outline-offset: 2px; box-shadow: 0 0 8px rgba(45, 80, 22, 0.3); }';
		}

		// Screen reader optimization
		if ($screen_reader === 'True') {
			$inline_css .= '.wtm-sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }';
		}

		// Add inline CSS using wp_add_inline_style for better security
		if ( ! empty( $inline_css ) ) {
			wp_register_style( 'wtm-general-settings', false );
			wp_enqueue_style( 'wtm-general-settings' );
			wp_add_inline_style( 'wtm-general-settings', $inline_css );
		}

		// Build JavaScript dynamically
		$inline_js = 'document.addEventListener("DOMContentLoaded", function() {';

		// Lazy loading
		if ($lazy_loading === 'True') {
			$inline_js .= 'if ("loading" in HTMLImageElement.prototype) {
				const images = document.querySelectorAll(".team-member img, .wtm-team-member img");
				images.forEach(img => { if (!img.hasAttribute("loading")) img.loading = "lazy"; });
			}';
		}

		// Preload critical images
		if ($preload_images > 0) {
			$inline_js .= sprintf('const criticalImages = document.querySelectorAll(".team-member img, .wtm-team-member img");
			for (let i = 0; i < Math.min(%d, criticalImages.length); i++) {
				criticalImages[i].loading = "eager";
			}', intval($preload_images));
		}

		// Keyboard navigation
		if ($keyboard_nav === 'True') {
			$inline_js .= 'document.querySelectorAll(".team-member, .wtm-team-member").forEach(member => {
				const links = member.querySelectorAll("a");
				links.forEach((link, index) => {
					link.setAttribute("tabindex", "0");
					if (index === 0) link.setAttribute("aria-label", "Team member profile");
				});
			});';
		}

		// Alt text enhancement
		if ($alt_text === 'True') {
			$inline_js .= 'document.querySelectorAll(".team-member img, .wtm-team-member img").forEach(img => {
				if (!img.alt) {
					const memberName = img.closest(".team-member, .wtm-team-member")?.querySelector(".team-name, h3, h4")?.textContent;
					if (memberName) img.alt = "Photo of " + memberName.trim();
				}
			});';
		}

		$inline_js .= '});';

		// Add inline JavaScript using wp_add_inline_script for better security
		wp_register_script( 'wtm-general-settings', false );
		wp_enqueue_script( 'wtm-general-settings' );
		wp_add_inline_script( 'wtm-general-settings', $inline_js );
	}

	/**
	 * Handle team member search AJAX request
	 *
	 * @since 1.0
	 */
	public function dwl_team_member_search() {
		check_ajax_referer('dwl_team_search_nonce', 'nonce');

		$search_term = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
		$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

		$args = array(
			'post_type' => 'team_manager',
			'post_status' => 'publish',
			's' => $search_term,
			'posts_per_page' => 50, // Limit results to prevent performance issues
		);

		$query = new \WP_Query($args);
		$team_data = [];

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				$team_data[] = [
					'ID' => get_the_ID(),
					'title' => get_the_title(),
					'content' => get_the_excerpt(),
					'link' => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		ob_start();
		foreach ($team_data as $member) {
			echo '<div class="team-member">';
			echo '<h4><a href="' . esc_url($member['link']) . '">' . esc_html($member['title']) . '</a></h4>';
			echo '<p>' . esc_html($member['content']) . '</p>';
			echo '</div>';
		}

		wp_send_json_success(ob_get_clean());
	}
}


