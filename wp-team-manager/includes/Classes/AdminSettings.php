<?php
namespace DWL\Wtm\Classes;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dynamicweblab.com/
 * @since      1.0.0
 *
 * @package    Wp_Team_Manager
 * @subpackage Wp_Team_Manager/admin
 */

/**
 * Team manager settings class
 */
 class AdminSettings{

    use \DWL\Wtm\Traits\Singleton;

    protected function init(){
        \add_action('admin_menu', array( $this, 'tm_create_menu' ) );
        \add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * create plugin settings menu
     *
     * @since 1.0
     */
    public function tm_create_menu() {

        $tm_settings_menu = add_submenu_page( 
            'edit.php?post_type=team_manager', 
            'Team Manager Settings', 
            'Settings', 
            'manage_options', 
            'team_manager', 
            [ $this, 'team_manager_setting_function'] 
        );

        add_action( $tm_settings_menu, array($this, 'add_admin_script' ) );

    }

    public function add_admin_script() {
        
        wp_enqueue_style( 'wp-team-setting-admin' ); 
        wp_enqueue_script( 'wp-team-settings-admin' ); 

    }

    /**
     * Register settings function
     *
     * @since 1.0
     */
    public function team_manager_setting_function() {

        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Team Manager Settings', 'wp-team-manager'); ?></h2>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php 
                    settings_fields( 'tm-settings-group' );
                    do_settings_sections( 'tm-settings-group' );
                    $tm_social_size = get_option('tm_social_size');
                    $tm_custom_css = get_option('tm_custom_css');
                    $tm_link_new_window = get_option('tm_link_new_window');
                    $single_team_member_view = get_option('single_team_member_view');
                    $old_team_manager_style = get_option( 'old_team_manager_style' );
                    $tm_slug = get_option('tm_slug');
                    $tm_single_fields = get_option('tm_single_fields');
                    $tm_image_size_fields = get_option('image_size_fields');
                    $team_image_size_change = get_option('team_image_size_change');
                    
                    include_once TM_PATH . '/admin/includes/content-settings.php';

                 submit_button(); ?>
            </form>
        
            <!-- Support -->
            <div id="wptm_support">
                <h3><?php esc_html_e('Support & bug report', 'wp-team-manager'); ?></h3>
                <p><?php echo wp_kses_post( sprintf(__('If you have some idea to improve this plugin or any bug to report, please reach us at ', 'wp-team-manager').' <a href="%1$s">%2$s</a>', 'https://dynamicweblab.com/submit-a-request/', 'support') ); ?></p>
                <p><?php echo wp_kses_post( sprintf(__( 'You like this plugin ? Then please provide some support by' , 'wp-team-manager' ).' <a href="%1$s" target="_blank">'.__( 'voting for it' , 'wp-team-manager' ).'</a> '. __( 'and/or says that', 'wp-team-manager' ) .'  <a href="%2$s" target="_blank">'.__( 'it works', 'wp-team-manager').'</a> '.__( 'for your WordPress installation on the official WordPress plugins repository.', 'wp-team-manager' ), 'http://wordpress.org/plugins/wp-team-manager/reviews/?filter=5#new-post', 'http://wordpress.org/plugins/wp-team-manager/reviews/?filter=5#new-post') ); ?></p>
            </div>
        </div>

    <?php 
    } 

    /**
     * Register and add settings
     */
    public function page_init(){    
        register_setting( 'tm-settings-group', 'tm_social_size');
        register_setting( 'tm-settings-group', 'tm_link_new_window' );
        register_setting( 'tm-settings-group', 'single_team_member_view' );
        register_setting( 'tm-settings-group', 'tm_custom_css' );
        register_setting( 'tm-settings-group', 'old_team_manager_style' );
        register_setting( 'tm-settings-group', 'tm_single_fields');
        register_setting( 'tm-settings-group', 'tm_custom_template' );
        register_setting( 
            'tm-settings-group', 
            'tm_slug',
            array( $this, 'tm_slug_sanitize' ) // Sanitize
        );
        
        register_setting( 'tm-settings-group', 'team_image_size_change' );
    }

    /**
     * Sanitize Social
     *
     * @param array $input Contains current data
     */
    public function tm_social_sanitize( $input ){

        $new_input = array();

        if( isset( $input ) ){
            $new_input = absint( $input );
        }
            
        return $new_input;
    }

    /**
     * Sanitize Slug
     *
     * @param array $input Contains current data
     */
    public function tm_slug_sanitize( $input ){

        $new_input = array();

        if( isset( $input ) ){
            $new_input = sanitize_text_field( $input );
        }
            
        return $new_input;
    }
}