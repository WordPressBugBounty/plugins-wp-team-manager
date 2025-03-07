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
 class GetHelp{

    use \DWL\Wtm\Traits\Singleton;

    protected function init(){
        \add_action('admin_menu', array( $this, 'create_menu' ) );
    }

    /**
     * create plugin settings menu
     *
     * @since 1.0
     */
    public function create_menu() {

        $tm_settings_menu = add_submenu_page( 
            'edit.php?post_type=team_manager', 
            __('Get Help', 'wp-team-manager'), 
            __('Get Help', 'wp-team-manager'), 
            'manage_options', 
            'wtm_get_help', 
            [ $this, 'team_manager_setting_function'] 
        );

        add_action( $tm_settings_menu, array($this, 'add_admin_script' ) );

    }

    public function add_admin_script() {

        wp_enqueue_style( 'wp-team-get-help-admin' ); 
    }

    /**
     * Register settings function
     *
     * @since 1.0
     */
    public function team_manager_setting_function() {

        ?>

            <section class="wp-team-get-help wp-team-header">

                <div class="wp-team-header-area">

                    <div class="wp-team-container">
                        <div class="wp-team-header-logo">
                            <img src="<?php echo esc_url( 'https://wpteammanager.com/wp-content/uploads/2024/07/Logo.svg' )?>" alt="">
                            <span><?php esc_html_e( '2.1.17', 'wp-team-manager' )?></span>
                        </div>
                    </div>

                    <div class="wp-team-header-logo-shape">
                        <img src="" alt="">
                    </div>

                </div>

            </section>

            <section class="wp-team-document-wrap">

                <div class="wp-team-document-box">

                    <div class="wp-team-box-icon">
                        <i class="dashicons dashicons-media-document"></i>
                        <h3 class="wp-team-main-title"><?php esc_html_e( 'Thank you for installing Team Plugin', 'wp-team-manager' )?></h3>
                    </div>

                    <div class="wp-team-box-content">

                        <div class="wp-team-video-wrapper">
                            <div class="wp-team-video-col">
                                <div class="wp-team-responsive-iframe">
                                    <iframe width="800" height="450" src="<?php echo esc_url( 'https://www.youtube.com/embed/AkqR6SGxcWo?feature=oembed' )?>" title="How To Create Team Page Using ShortCode With WordPress Team Members Showcase Plugin" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
                                </div>
                            </div>
                            <div class="wp-team-video-col">
                                <div class="wp-team-responsive-iframe">
                                    <iframe width="800" height="450" src="<?php echo esc_url( 'https://www.youtube.com/embed/T-cF14_TxXE?feature=oembed' )?>" title="How To Create Team Page Using Elementor Addon With WordPress Team Members Showcase Plugin" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </section>
        
            <section class="wp-team-document-wrap">

                <div class="wp-team-document-box" style="margin-bottom: 20px;">

                    <div class="wp-team-box-icon">
                        <i class="dashicons dashicons-thumbs-up"></i>
                        <h3 class="wp-team-box-title"><?php esc_html_e( 'Happy clients of Team plugin', 'wp-team-manager' )?></h3>
                    </div>

                    <div class="wp-team-box-content">
                        
                        <div class="wp-team-testimonials">
                            <div class="wp-team-testimonial">
                                <p><?php esc_html_e( 'This plug in has helped many people through our website to get jobs and find what they need for their productions. 100% recommendable!!!', 'wp-team-manager' )?></p>
                                <div class="wp-team-client-info">
                                    <img src="<?php echo esc_url( 'https://secure.gravatar.com/avatar/093ca6f23cf63c3679a07a8c2bdd2a62?s=100&d=retro&r=g' )?>">
                                    <div>
                                        <div class="wp-team-star">
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                        </div>
                                        <span class="wp-team-client-name"><?php esc_html_e( 'laescaleta', 'wp-team-manager' )?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="wp-team-testimonial">
                                <p><?php esc_html_e( 'Great plugin, and so easy to use. I have bought two but this one is much better. Buddy', 'wp-team-manager' )?></p>
                                <div class="wp-team-client-info">
                                    <img src="<?php echo esc_url( 'https://secure.gravatar.com/avatar/431d6e86e03b7de6ce39e9f6e5d053d1?s=100&d=retro&r=g' )?>">
                                    <div>
                                        <div class="wp-team-star">
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                            <i class="dashicons dashicons-star-filled"></i>
                                        </div>
                                        <span class="wp-team-client-name"><?php esc_html_e( 'Buddyr', 'wp-team-manager' )?></span>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </section>

            <section class="wp-team-document-wrap">
                <div class="wp-team-card-section" style="margin-bottom: 20px;">
                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-media-document"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Documentation', 'wp-team-manager' )?></h3>
                        </div>

                        <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'Get started by spending some time with the documentation we included step by step process with screenshots with video.', 'wp-team-manager' )?></p>
                                <a href="<?php echo esc_url( 'https://wpteammanager.com/docs/team-manager/getting-started/system-requirements/?utm_source=wordrpess&utm_medium=settings-card' )?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'Documentation', 'wp-team-manager' )?></a>
                        </div>
                    </div>

                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-sos"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Need Help?', 'wp-team-manager' )?></h3>
                        </div>

                        <div class="wp-team-box-content">
                            <p><?php esc_html_e( 'Stuck with something? Please create aticket here', 'wp-team-manager' )?></p>
                            <a href="<?php echo esc_url( 'https://dynamicweblab.com/submit-a-request/?utm_source=wordrpess&utm_medium=settings-card' )?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'Get Support', 'wp-team-manager' )?></a>
                        </div>
                    </div>

                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-smiley"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Happy Our Work?', 'wp-team-manager' )?></h3>
                        </div>
                        <div class="wp-team-box-content">
                            <p><?php esc_html_e( 'If you happy with', 'wp-team-manager' )?> <strong><?php esc_html_e( 'Team', 'wp-team-manager' )?></strong> <?php esc_html_e( 'plugin, please add a rating. It would be glad to us.', 'wp-team-manager' )?></p>
                            <a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/wp-team-manager/reviews/?filter=5#new-post' )?>" class="wp-team-admin-btn" target="_blank"><?php esc_html_e( 'Post Review', 'wp-team-manager' )?></a>
                        </div>
                    </div>
                </div>
            </section>

        <?php 
    } 

}