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
            <style>
            /* Scoped styles for Get Help page */
            .wtm-section{margin-bottom:20px}
            .wtm-list{margin-left:20px}
            .wtm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px}
            .wtm-card{border:1px solid #ddd;padding:15px;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,.04)}
            .wtm-plugins{margin-top:15px}
            .wtm-full{width:100%;max-width:100%;display:block}
            /* Ensure the card section doesn't constrain width */
            .wp-team-card-section.wtm-full{display:block}
            .wp-team-card-section.wtm-full .wp-team-document-box{max-width:100%}
            /* Wider, responsive grid for plugin cards */
            .wtm-grid-plugins{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
            @media (min-width: 1200px){
              .wtm-grid-plugins{grid-template-columns:repeat(3,minmax(280px,1fr))}
            }
            </style>
            <section class="wp-team-get-help wp-team-header">

                <div class="wp-team-header-area">

                    <div class="wp-team-container">
                        <div class="wp-team-header-logo">
                            <img src="<?php echo esc_url( 'https://wpteammanager.com/wp-content/uploads/2024/07/Logo.svg' )?>" alt="team logo">
                            <span><?php echo TM_VERSION; ?></span>
                        </div>
                        <div class="wp-team-header-pro">
                       <?php
                        if(tmwstm_fs()-> is_not_paying() && !tmwstm_fs()->is_trial()){
                            echo '<a class="wp-team-upgrade-button button button-primary" href="' . esc_url(tmwstm_fs()->get_upgrade_url()) . '">Upgrade to Pro !</a>';
                        }
                        ?>
                    </div>
                    </div>

                    

                </div>

            </section>

            <section class="wp-team-document-wrap">

                <div class="wp-team-document-box">

                    <div class="wp-team-box-icon">
                        <i class="dashicons dashicons-media-document"></i>
                        <h3 class="wp-team-main-title"><?php esc_html_e( 'Thank you for installing WP Team Manager Plugin', 'wp-team-manager' )?></h3>
                    </div>

                    <div class="wp-team-box-content">

                        <div class="wp-team-video-wrapper">
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

                <div class="wp-team-document-box">

                    <div class="wp-team-box-icon">
                        <i class="dashicons dashicons-megaphone"></i>
                        <h3 class="wp-team-main-title"><?php esc_html_e( 'Pro Features of the WP team manager plugin', 'wp-team-manager' )?></h3>
                    </div>
                    <div class="wp-team-features-box-wrapper">
                        <ul class="wp-team-features-box wtm-list">
                            <li><strong><?php esc_html_e('8+ Additional Layouts:', 'wp-team-manager'); ?></strong></li>
                            <li>• <strong><?php esc_html_e('Grid Layouts (Elementor)', 'wp-team-manager'); ?></strong> – <?php esc_html_e('Multiple grid styles for showcasing team members.', 'wp-team-manager'); ?></li>
                            <li>• <strong><?php esc_html_e('List Layouts (Elementor)', 'wp-team-manager'); ?></strong> – <?php esc_html_e('Stylish list-based layouts for better readability.', 'wp-team-manager'); ?></li>
                            <li>• <strong><?php esc_html_e('Slider Layouts (Elementor)', 'wp-team-manager'); ?></strong> – <?php esc_html_e('Over 4 dynamic slider designs for engaging displays.', 'wp-team-manager'); ?></li>
                            <li>• <strong><?php esc_html_e('Isotope Layouts', 'wp-team-manager'); ?></strong> – <?php esc_html_e('2+ advanced filtering layouts for interactive team showcases.', 'wp-team-manager'); ?></li>
                            <li><strong><?php esc_html_e('Customization & Features:', 'wp-team-manager'); ?></strong></li>
                            <li>• <strong><?php esc_html_e('Customizable Bio Field Labels', 'wp-team-manager'); ?></strong> – <?php esc_html_e('Edit and personalize team member bio labels.', 'wp-team-manager'); ?></li>
                            <li>• <strong><?php esc_html_e('Ajax-Based Navigation', 'wp-team-manager'); ?></strong> – <?php esc_html_e('Includes number pagination, load more button, and smooth transitions.', 'wp-team-manager'); ?></li>
                            <li>• <strong><?php esc_html_e('Progress Bar for Skills', 'wp-team-manager'); ?></strong> – <?php esc_html_e('Showcase expertise with animated skill bars.', 'wp-team-manager'); ?></li>
                            <li>• <strong><?php esc_html_e('Image Gallery Popup', 'wp-team-manager'); ?></strong> – <?php esc_html_e('View member images in a sleek lightbox on the details page.', 'wp-team-manager'); ?></li>
                            <li><?php esc_html_e('…and many more powerful features to enhance your team display!', 'wp-team-manager'); ?></li>
                        </ul>

                    </div>

                    <div class="wp-team-box-content">
                        <?php if ( tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ) : ?>
                            <div class="wtm-upgrade-cta">
                                <a href="<?php echo esc_url( tmwstm_fs()->get_upgrade_url() ); ?>" target="_blank" class="button button-primary wtm-cta-button">
                                    <?php esc_html_e( 'Unlock All Pro Features', 'wp-team-manager' ); ?>
                                </a>
                                <p class="description"><?php esc_html_e( 'Includes 1 year of updates & support.', 'wp-team-manager' ); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="wtm-upgrade-cta">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <strong><?php esc_html_e( 'You’re on Pro — thanks for supporting us!', 'wp-team-manager' ); ?></strong>
                            </div>
                        <?php endif; ?>     
                    </div>

                </div>

            </section>
        
            <section class="wp-team-document-wrap">

               <div class="wp-team-document-box wtm-section">

                    <div class="wp-team-box-icon">
                        <i class="dashicons dashicons-thumbs-up"></i>
                        <h3 class="wp-team-box-title"><?php esc_html_e( 'Happy clients of WP Team Manager plugin', 'wp-team-manager' )?></h3>
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
                                    <img src="<?php echo esc_url( 'https://secure.gravatar.com/avatar/093ca6f23cf63c3679a07a8c2bdd2a62?s=100&d=retro&r=g' )?>">
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
                <div class="wp-team-card-section wtm-section">
                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-media-document"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Documentation', 'wp-team-manager' )?></h3>
                        </div>

                        <div class="wp-team-box-content">
                                <p><?php esc_html_e( 'Get started by spending some time with the documentation we included step by step process with screenshots with video.', 'wp-team-manager' )?></p>
                                <a href="<?php echo esc_url( 'https://wpteammanager.com/docs/team-manager/getting-started/system-requirements/?utm_source=wordpress&utm_medium=settings-card' )?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'Documentation', 'wp-team-manager' )?></a>
                        </div>
                    </div>

                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-sos"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Need Help?', 'wp-team-manager' )?></h3>
                        </div>

                        <div class="wp-team-box-content">
                            <p><?php esc_html_e( 'Stuck with something? Please create a ticket here', 'wp-team-manager' )?></p>
                            <a href="<?php echo esc_url( 'https://dynamicweblab.com/submit-a-request/?utm_source=wordpress&utm_medium=settings-card' )?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'Get Support', 'wp-team-manager' )?></a>
                        </div>
                    </div>

                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-smiley"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'Happy with our work?', 'wp-team-manager' )?></h3>
                        </div>
                        <div class="wp-team-box-content">
                            <p><?php esc_html_e( "If you're happy with the", 'wp-team-manager' ); ?> <strong><?php esc_html_e( 'Team', 'wp-team-manager' ); ?></strong> <?php esc_html_e( 'plugin, please leave a 5-star rating. It helps a lot!', 'wp-team-manager' ); ?></p>
                            <a href="<?php echo esc_url( 'https://wordpress.org/support/plugin/wp-team-manager/reviews/?filter=5#new-post' )?>" class="wp-team-admin-btn" target="_blank"><?php esc_html_e( 'Post Review', 'wp-team-manager' )?></a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="wp-team-document-wrap">
                <div class="wp-team-card-section wtm-section wtm-full">
                    <div class="wp-team-document-box wp-team-document-box-card">
                        <div class="wp-team-box-icon">
                            <i class="dashicons dashicons-admin-plugins"></i>
                            <h3 class="wp-team-box-title"><?php esc_html_e( 'More Plugins from Dynamic Web Lab', 'wp-team-manager' )?></h3>
                        </div>
                        <div class="wp-team-box-content">
                            <div class="wp-team-plugins-grid wtm-grid-plugins">
                                <?php
                                $plugins = apply_filters( 'wtm_gethelp_plugins', [
                                    [
                                        'title' => 'SearchJet Instant Search',
                                        'desc'  => 'Lightning-fast AJAX search for WordPress & WooCommerce.',
                                        'link'  => 'https://wordpress.org/plugins/searchjet-instant-search/',
                                    ],
                                    [
                                        'title' => 'Dynamic Product Categories Design',
                                        'desc'  => 'Beautiful, customizable WooCommerce category layouts.',
                                        'link'  => 'https://wordpress.org/plugins/dynamic-product-categories-design/',
                                    ],
                                    [
                                        'title' => 'Lean GA4 Tracker',
                                        'desc'  => 'Lightweight Google Analytics 4 tracking for WordPress.',
                                        'link'  => 'https://wordpress.org/plugins/lean-ga4-tracker/',
                                    ],
                                ] );
                                foreach ( $plugins as $plugin ) : ?>
                                    <div class="wp-team-plugin-card wtm-card">
                                        <h4><?php echo esc_html( $plugin['title'] ); ?></h4>
                                        <p><?php echo esc_html( $plugin['desc'] ); ?></p>
                                        <a href="<?php echo esc_url( $plugin['link'] ); ?>" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'View Plugin', 'wp-team-manager' ); ?></a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                           <div class="wtm-plugins">
                                <a href="https://profiles.wordpress.org/maidulcu/#content-plugins" target="_blank" class="wp-team-admin-btn"><?php esc_html_e( 'See all our plugins', 'wp-team-manager' ); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        <?php 
    } 

}