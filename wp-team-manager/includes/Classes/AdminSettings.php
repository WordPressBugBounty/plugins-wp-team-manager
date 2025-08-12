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

        wp_enqueue_style( 'wp-team-get-help-admin' );

        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Team Manager Settings', 'wp-team-manager'); ?></h2>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php 
                    settings_fields( 'tm-settings-group' );
                    do_settings_sections( 'tm-settings-group' );
                    
                    $file_path = realpath(TM_PATH . '/admin/includes/content-settings.php');
                    if ($file_path && strpos($file_path, TM_PATH) === 0) {
                        include_once $file_path;
                    }
                ?>
                <?php submit_button(); ?>
            </form>
        
            <!-- Support -->
            <div id="wptm_support" class="wp-team-box-content">      
                <div class="wp-team-card-section">
                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-media-document"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Documentation', 'wp-team-manager' )?></h3>
                        </div>

                        <div class="wp-team-box-content">
                            <p><?php esc_html_e( 'Get started by spending some time with the documentation we included step by step process with screenshots with video.', 'wp-team-manager' )?></p>
                            <a href="<?php echo esc_url( 'https://wpteammanager.com/docs/team-manager/getting-started/?utm_source=wordrpess&utm_medium=settings-card' )?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'Documentation', 'wp-team-manager' )?></a>
                        </div>
                    </div>
                    
                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-sos"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Need Help?', 'wp-team-manager' )?></h3>
                        </div>

                        <div class="wp-team-box-content wp-team-need-help">
                            <p><?php esc_html_e( 'Stuck with something? Please create a ticket here', 'wp-team-manager' )?></p>
                            <a href="<?php echo esc_url( 'https://dynamicweblab.com/submit-a-request/?utm_source=wordrpess&utm_medium=settings-card' )?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'Get Support', 'wp-team-manager' )?></a>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    <?php 
    } 

    /**
     * Register and add settings
     */
    public function page_init(){    
       register_setting( 
           'tm-settings-group', 
           'tm_social_size', 
           array( $this, 'tm_social_sanitize' ) // Add sanitization
       );
        register_setting( 'tm-settings-group', 'tm_link_new_window' );
        register_setting( 'tm-settings-group', 'single_team_member_view' );
        register_setting( 
            'tm-settings-group', 
            'tm_custom_css', 
            array( $this, 'tm_custom_css_sanitize' ) // Add sanitization
        );
        register_setting( 'tm-settings-group', 'old_team_manager_style' );
        register_setting( 'tm-settings-group', 'tm_single_fields');
        register_setting( 'tm-settings-group', 'tm_taxonomy_fields');
        register_setting( 'tm-settings-group', 'tm_custom_template' );
        register_setting( 'tm-settings-group', 'tm_single_team_lightbox' );
        register_setting( 
            'tm-settings-group', 
            'tm_vcard_btn_text', 
            array( $this, 'tm_vcard_btn_sanitize' ) // Add sanitization
        );
        register_setting( 'tm-settings-group', 'tm_single_gallery_column' );
        register_setting( 
            'tm-settings-group', 
            'tm_slug',
            array( $this, 'tm_slug_sanitize' ) // Sanitize
        );
        
        register_setting( 'tm-settings-group', 'team_image_size_change' );
        register_setting( 
            'tm-settings-group', 
            'tm_custom_labels', 
            array( $this, 'tm_custom_labels_sanitize' ) // Add sanitization
        );
        register_setting(
            'tm-settings-group',
            'tm_log_path',
            array( $this, 'tm_log_path_sanitize' )
        );
    }

    /**
     * Sanitize the custom CSS input for the team manager.
     *
     * This function is hooked to the settings api and will sanitize the input
     * for the custom CSS. It will make sure that the input is sanitized and
     * removes any unwanted tags, HTML tags, and potential script injections.
     *
     * @param string $input The input string for the custom CSS.
     *
     * @return string The sanitized string for the custom CSS.
     */
    public function tm_custom_css_sanitize( $input ) {
        return esc_textarea( $input ); // Removes HTML tags but preserves formatting
    }
    
    /**
     * Sanitize the vCard button text.
     *
     * This function is used to sanitize the input for the vCard button text
     * setting. It removes unwanted characters, HTML tags, and trims spaces
     * to ensure the input is safe and clean.
     *
     * @param string $input The input string for the vCard button text.
     *
     * @return
     */
    public function tm_vcard_btn_sanitize( $input ) {
        return sanitize_text_field( $input ); // Removes unwanted characters, HTML tags, and trims spaces
    }

    /**
     * Sanitize the social links input for the team manager.
     *
     * This function is hooked to the settings api and will sanitize the input
     * for the social links. It will make sure that the key is sanitized and the
     * value is an escaped URL.
     *
     * @param array $input The input array that contains the social links.
     *
     * @return array The sanitized array with the social links.
     */
    public function tm_social_sanitize( $input ) {
        return ( is_numeric( $input ) && $input > 0 ) ? absint( $input ) : get_option( 'tm_social_size', 16 ); // Default to 16px if invalid
    }


    /**
     * Sanitize the slug input for the team manager.
     *
     * This function removes unwanted characters, converts the input to lowercase,
     * and ensures that the resulting slug is not empty or a reserved slug.
     * If the input is invalid, it adds a settings error and returns the existing
     * valid slug option.
     *
     * @param string $input The slug input to sanitize.
     * @return string The sanitized slug or the existing slug if input is invalid.
     */

    public function tm_slug_sanitize( $input ) {
        // Remove any unwanted characters and make it lowercase
        $slug = sanitize_title( $input );
    
        // Ensure it's not empty
        if ( empty( $slug ) ) {
            add_settings_error( 'tm_slug', 'invalid-slug', __( 'The slug cannot be empty. Using default: team-details.', 'wp-team-manager' ) );
            return 'team-details'; // Return default slug if invalid
        }
    
        // List of reserved slugs (prevent conflicts with WP default pages)
        $reserved_slugs = array( 'page', 'post', 'category', 'tag', 'team', 'admin', 'login' );
    
        if ( in_array( $slug, $reserved_slugs ) ) {
            add_settings_error( 'tm_slug', 'reserved-slug', __( 'This slug is reserved. Using default: team-details.', 'wp-team-manager' ) );
            return 'team-details'; // Return default slug if reserved
        }
    
        return $slug;
    }

    public function tm_custom_labels_sanitize( $input ) {
        $sanitized = array();
        if ( is_array( $input ) ) {
            foreach ( $input as $key => $value ) {
                $sanitized[$key] = sanitize_text_field( $value );
            }
        }
        return $sanitized;
    }

    /**
     * Sanitize the log path input for the team manager.
     *
     * This function strips unwanted characters and ensures the path is a safe string.
     *
     * @param string $input The input string for the log path.
     *
     * @return string The sanitized log path.
     */
    public function tm_log_path_sanitize( $input ) {
        $input = trim( $input );
        // Remove any tags or special characters except basic path characters
        $input = wp_strip_all_tags( $input );
        // Allow only safe characters for file paths: alphanumeric, slashes, dots, hyphens, underscores
        $input = preg_replace( '/[^a-zA-Z0-9\/\._\-]/', '', $input );
        return $input;
    }
}