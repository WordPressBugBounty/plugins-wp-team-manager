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
	}

	/**
	 * Register the stylesheets and script for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function wp_team_manager_public_assets() {

		wp_register_style( 'wp-team-font-awesome', TM_PUBLIC . '/assets/vendor/font-awesome/css/all.min.css', null, '5.9.0');
		wp_register_style( 'wp-team-slick', TM_PUBLIC . '/assets/vendor/slick/slick.css', null, '5.9.0');
		wp_register_style( 'wp-team-slick-theme', TM_PUBLIC . '/assets/vendor/slick/slick-theme.css', null, '5.9.0');
		wp_register_style( 'wp-team-single', TM_PUBLIC . '/assets/css/tm-single.css', [], TM_VERSION );
		wp_register_style( 'wp-team-style', TM_PUBLIC . '/assets/css/tm-style.css', [], TM_VERSION );
		wp_register_style( 'wp-old-style', TM_PUBLIC . '/assets/css/tm-old-style.css', [], TM_VERSION );
		
		// register scritps
		wp_register_script( 'wp-team-slick', TM_PUBLIC . '/assets/vendor/slick/slick.min.js', array('jquery'), '5.9.0', true );

		wp_register_script( 
			'wp-team-slick', 
			TM_PUBLIC . '/assets/vendor/slick/slick.min.js', 
			array('jquery'), 
			'5.9.0', 
			true 
		);

		wp_register_script( 
			'wtm-image-load-js', 
			TM_PUBLIC . '/assets/vendor/isotope/imagesloaded.pkgd.min.js', 
			array('jquery'), 
			'5.0.0', 
			true 
		);		

		wp_register_script( 
			'wtm-isotope-js', 
			TM_PUBLIC . '/assets/vendor/isotope/isotope.pkgd.min.js', 
			array('jquery','wtm-image-load-js'), 
			'3.0.6', 
			true 
		);

		wp_register_script( 'wp-team-script', TM_PUBLIC . '/assets/js/team.js', array('jquery'), TM_VERSION, true );

		wp_register_script( 'wp-team-el-slider', TM_PUBLIC . '/assets/js/team-el-slider.js', array(), TM_VERSION, true );
	
		$ajaxurl = '';

		if ( in_array( 'sitepress-multilingual-cms/sitepress.php', get_option( 'active_plugins' ) ) ) {
			$ajaxurl .= admin_url( 'admin-ajax.php?lang=' . ICL_LANGUAGE_CODE );
		} else {
			$ajaxurl .= admin_url( 'admin-ajax.php' );
		}

		wp_localize_script( 'wp-team-script', 'wptObj', array(
			'ajaxurl' => $ajaxurl,
			'nonce' => wp_create_nonce('wtm_nonce')
		) );

		if(is_singular( 'team_manager' )){
			wp_enqueue_script( ['wp-team-script'] );
		}
	}
	
    /**
     * Add custom css on theme header
     *
     * @since 1.0
     */
    public function team_manager_add_custom_css(){
		if(is_singular( 'team_manager' )){
			wp_enqueue_style(['wp-team-font-awesome','wp-team-single','wp-team-style']);
		}
		
        printf( "<style type='text/css' media='screen'>%s</style>", esc_attr(get_option('tm_custom_css')) );
    } 

    /**
     * Add custom css on theme header
     *
     * @since 2.0
     */
	public function general_settings() {

		$style = '';

		$social_size = ( false !== get_option('tm_social_size') ) ? get_option('tm_social_size') : 16;

		if($social_size){
			$style .= '.team-member-socials a,
       		          .team-member-other-info .fas{
                    		font-size: '.esc_attr($social_size).'px !important;
                    	}';
		
		printf( "<style type='text/css' media='screen'>%s</style>", esc_attr($style) );

		}

	}
}