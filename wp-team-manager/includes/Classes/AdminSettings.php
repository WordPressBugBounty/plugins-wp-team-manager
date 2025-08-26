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

        $hook_suffix = \add_submenu_page(
            'edit.php?post_type=team_manager',
            esc_html__( 'Team Manager Settings', 'wp-team-manager' ),
            esc_html__( 'Settings', 'wp-team-manager' ),
            'manage_options',
            'tm-settings', // unique, avoids clashing with CPT slug
            [ $this, 'team_manager_setting_function' ]
        );

        // Enqueue assets only on this settings screen per WP standards
        \add_action( 'load-' . $hook_suffix, [ $this, 'add_admin_script' ] );

    }

    public function add_admin_script() {
        \wp_enqueue_style( 'wp-team-setting-admin' );
        \wp_enqueue_script( 'wp-team-settings-admin' );
    }

    /**
     * Register settings function
     *
     * @since 1.0
     */
    public function team_manager_setting_function() {

        if ( ! \current_user_can( 'manage_options' ) ) {
            \wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wp-team-manager' ) );
        }

        \wp_enqueue_style( 'wp-team-get-help-admin' );

        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Team Manager Settings', 'wp-team-manager'); ?></h2>
            
            <?php \settings_errors(); ?>
            <div class="wptm-settings-wrap">
                <div class="wptm-settings-form">
                    <form method="post" action="options.php">
                        <?php 
                            \settings_fields( 'tm-settings-group' );
                            \do_settings_sections( 'tm-settings-group' );
                            
                            $file_path = realpath(TM_PATH . '/admin/includes/content-settings.php');
                            if ($file_path && strpos($file_path, TM_PATH) === 0) {
                                include_once $file_path;
                            }
                        ?>
                        <?php \submit_button(); ?>
                    </form>
                
                   
                </div>            
                <!-- Support / Documentation Hub -->
                <div id="wptm_support" class="wp-team-box-content">
                    <div class="wp-team-card-section wp-team-card-grid">

                        <div class="wp-team-document-box wp-team-document-box-card">
                            <div class="wp-team-box-icon">
                                <i class="dashicons dashicons-media-document"></i>
                                <h3 class="wp-team-box-title"><?php esc_html_e( 'Documentation', 'wp-team-manager' ); ?></h3>
                            </div>
                            <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'Step‑by‑step guides to install, configure, and customize WP Team Manager.', 'wp-team-manager' ); ?></p>
                                <a href="<?php echo esc_url( 'https://wpteammanager.com/docs/team-manager/getting-started/?utm_source=wordpress&utm_medium=settings-card' ); ?>" target="_blank" rel="noopener" class="wp-team-admin-btn">
                                    <?php esc_html_e( 'Open Docs', 'wp-team-manager' ); ?>
                                </a>
                            </div>
                        </div>

                        <div class="wp-team-document-box wp-team-document-box-card">
                            <div class="wp-team-box-icon">
                                <i class="dashicons dashicons-video-alt3"></i>
                                <h3 class="wp-team-box-title"><?php esc_html_e( 'Video Tutorials', 'wp-team-manager' ); ?></h3>
                            </div>
                            <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'Quick videos covering layouts, Elementor widgets, and shortcode builder.', 'wp-team-manager' ); ?></p>
                                <a href="<?php echo esc_url( 'https://wpteammanager.com/videos/?utm_source=wordpress&utm_medium=settings-card' ); ?>" target="_blank" rel="noopener" class="wp-team-admin-btn">
                                    <?php esc_html_e( 'Watch Videos', 'wp-team-manager' ); ?>
                                </a>
                            </div>
                        </div>

                        <div class="wp-team-document-box wp-team-document-box-card">
                            <div class="wp-team-box-icon">
                                <i class="dashicons dashicons-list-view"></i>
                                <h3 class="wp-team-box-title"><?php esc_html_e( 'Changelog', 'wp-team-manager' ); ?></h3>
                            </div>
                            <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'See what’s new, improved, and fixed in each release.', 'wp-team-manager' ); ?></p>
                                <a href="<?php echo esc_url( 'https://wordpress.org/plugins/wp-team-manager/#developers' ); ?>" target="_blank" rel="noopener" class="wp-team-admin-btn">
                                    <?php esc_html_e( 'View Changelog', 'wp-team-manager' ); ?>
                                </a>
                            </div>
                        </div>

                        <div class="wp-team-document-box wp-team-document-box-card">
                            <div class="wp-team-box-icon">
                                <i class="dashicons dashicons-editor-help"></i>
                                <h3 class="wp-team-box-title"><?php esc_html_e( 'FAQ', 'wp-team-manager' ); ?></h3>
                            </div>
                            <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'Answers for common questions about setup, templates, and performance.', 'wp-team-manager' ); ?></p>
                                <a href="<?php echo esc_url( 'https://wpteammanager.com/faq/?utm_source=wordpress&utm_medium=settings-card' ); ?>" target="_blank" rel="noopener" class="wp-team-admin-btn">
                                    <?php esc_html_e( 'Read FAQ', 'wp-team-manager' ); ?>
                                </a>
                            </div>
                        </div>

                        <div class="wp-team-document-box wp-team-document-box-card">
                            <div class="wp-team-box-icon">
                                <i class="dashicons dashicons-editor-code"></i>
                                <h3 class="wp-team-box-title"><?php esc_html_e( 'Developer Hooks', 'wp-team-manager' ); ?></h3>
                            </div>
                            <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'Actions & filters for extending layouts, queries, and templates.', 'wp-team-manager' ); ?></p>
                                <a href="<?php echo esc_url( 'https://wpteammanager.com/docs/developers/hooks/?utm_source=wordpress&utm_medium=settings-card' ); ?>" target="_blank" rel="noopener" class="wp-team-admin-btn">
                                    <?php esc_html_e( 'Browse Hooks', 'wp-team-manager' ); ?>
                                </a>
                            </div>
                        </div>

                        <div class="wp-team-document-box wp-team-document-box-card">
                            <div class="wp-team-box-icon">
                                <i class="dashicons dashicons-sos"></i>
                                <h3 class="wp-team-box-title"><?php esc_html_e( 'Need Help?', 'wp-team-manager' ); ?></h3>
                            </div>
                            <div class="wp-team-box-content wp-team-need-help">
                                <p><?php esc_html_e( 'Stuck with something? Create a support ticket and we’ll help you out.', 'wp-team-manager' ); ?></p>
                                <a href="<?php echo esc_url( 'https://dynamicweblab.com/submit-a-request/?utm_source=wordpress&utm_medium=settings-card' ); ?>" target="_blank" rel="noopener" class="wp-team-admin-btn">
                                    <?php esc_html_e( 'Get Support', 'wp-team-manager' ); ?>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <style>
                .wp-team-card-grid{
                    display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;margin-top:8px
                }
                .wp-team-document-box-card .wp-team-box-icon{display:flex;align-items:center;gap:8px}
                .wp-team-document-box-card .wp-team-box-icon .dashicons{font-size:20px;width:20px;height:20px;line-height:20px}
                .wp-team-admin-btn{margin-top:8px;display:inline-block}
            </style>
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

        register_setting( 'tm-settings-group', 'tm_a11y_enable', array( $this, 'sanitize_checkbox' ) );
        register_setting( 'tm-settings-group', 'tm_a11y_region_label', array( $this, 'sanitize_text' ) );
        register_setting( 'tm-settings-group', 'tm_a11y_focus_ring', array( $this, 'sanitize_checkbox' ) );
        register_setting( 'tm-settings-group', 'tm_a11y_list_roles', array( $this, 'sanitize_checkbox' ) );
        register_setting( 'tm-settings-group', 'tm_seo_jsonld_enable', array( $this, 'sanitize_checkbox' ) );

        // Accessibility & SEO section and fields
        add_settings_section(
            'tm_a11y_seo_section',
            __( 'Accessibility & SEO', 'wp-team-manager' ),
            null,
            'tm-advanced-settings'
        );
        add_settings_field(
            'tm_a11y_enable',
            __( 'Enable Accessibility Features', 'wp-team-manager' ),
            array( $this, 'field_tm_a11y_enable' ),
            'tm-advanced-settings',
            'tm_a11y_seo_section'
        );
        add_settings_field(
            'tm_a11y_region_label',
            __( 'Region Label', 'wp-team-manager' ),
            array( $this, 'field_tm_a11y_region_label' ),
            'tm-advanced-settings',
            'tm_a11y_seo_section'
        );
        add_settings_field(
            'tm_a11y_focus_ring',
            __( 'Focus Ring', 'wp-team-manager' ),
            array( $this, 'field_tm_a11y_focus_ring' ),
            'tm-advanced-settings',
            'tm_a11y_seo_section'
        );
        add_settings_field(
            'tm_a11y_list_roles',
            __( 'List Semantics (role=list/listitem)', 'wp-team-manager' ),
            array( $this, 'field_tm_a11y_list_roles' ),
            'tm-advanced-settings',
            'tm_a11y_seo_section'
        );
        add_settings_field(
            'tm_seo_jsonld_enable',
            __( 'Enable SEO JSON-LD', 'wp-team-manager' ),
            array( $this, 'field_tm_seo_jsonld_enable' ),
            'tm-advanced-settings',
            'tm_a11y_seo_section'
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

    /** Generic checkbox sanitizer (0/1) */
    public function sanitize_checkbox( $val ) {
        return $val ? 1 : 0;
    }

    /** Generic text sanitizer */
    public function sanitize_text( $val ) {
        return sanitize_text_field( $val );
    }

    /** Field: Enable Accessibility Features */
    public function field_tm_a11y_enable() {
        $val = (int) get_option( 'tm_a11y_enable', 1 );
        echo '<label><input type="checkbox" name="tm_a11y_enable" value="1" ' . checked( 1, $val, false ) . '> ' . esc_html__( 'Add ARIA roles/labels and sensible fallbacks for images.', 'wp-team-manager' ) . '</label>';
    }

    /** Field: Region Label */
    public function field_tm_a11y_region_label() {
        $val = (string) get_option( 'tm_a11y_region_label', __( 'Team members', 'wp-team-manager' ) );
        echo '<input type="text" class="regular-text" name="tm_a11y_region_label" value="' . esc_attr( $val ) . '" placeholder="' . esc_attr__( 'Team members', 'wp-team-manager' ) . '">';
        echo '<p class="description">' . esc_html__( 'Used for aria-label on the team block landmark region.', 'wp-team-manager' ) . '</p>';
    }

    /** Field: Focus Ring */
    public function field_tm_a11y_focus_ring() {
        $val = (int) get_option( 'tm_a11y_focus_ring', 1 );
        echo '<label><input type="checkbox" name="tm_a11y_focus_ring" value="1" ' . checked( 1, $val, false ) . '> ' . esc_html__( 'Add a visible focus outline for keyboard users.', 'wp-team-manager' ) . '</label>';
    }

    /** Field: List Semantics (role=list/listitem) */
    public function field_tm_a11y_list_roles() {
        $val = (int) get_option( 'tm_a11y_list_roles', 1 );
        echo '<label><input type="checkbox" name="tm_a11y_list_roles" value="1" ' . checked( 1, $val, false ) . '> ' . esc_html__( 'Add role="list" on the grid and role="listitem" on each card for better screen reader navigation.', 'wp-team-manager' ) . '</label>';
    }

    /** Field: Enable SEO JSON-LD (Pro) */
    public function field_tm_seo_jsonld_enable() {
        $val = (int) get_option( 'tm_seo_jsonld_enable', 0 );
        $disabled = '';
        $note = '';
        if ( function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ) {
            $disabled = ' disabled';
            $note = ' <a href="' . esc_url( tmwstm_fs()->get_upgrade_url() ) . '" target="_blank" rel="noopener">' . esc_html__( 'Unlock in Pro', 'wp-team-manager' ) . '</a>';
        }
        echo '<label><input type="checkbox" name="tm_seo_jsonld_enable" value="1" ' . checked( 1, $val, false ) . $disabled . '> ' . esc_html__( 'Output Schema.org Person JSON-LD for each visible team member.', 'wp-team-manager' ) . $note . '</label>';
    }
}