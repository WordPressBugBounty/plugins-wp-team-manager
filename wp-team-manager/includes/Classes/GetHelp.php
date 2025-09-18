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
        <?php if ( Helper::freemius_is_free_user() ) : ?>
            <div class="wtm-upgrade-banner">
                <p>
                    <?php esc_html_e( 'You’re using WP Team Manager Free Version. Upgrade to Pro to unlock advanced team layouts, filtering, Elementor widgets, and more.', 'wp-team-manager' ); ?>
                    <a href="<?php echo esc_url( tmwstm_fs()->get_upgrade_url() ); ?>" class="wtm-upgrade-link" target="_blank">
                        🚀 <?php esc_html_e( 'Upgrade to Pro!', 'wp-team-manager' ); ?>
                    </a>
                </p>
            </div>
        <?php endif; ?>
        <div class="wtm-tabs-wrapper">
            <ul class="wtm-tab-nav">
                <li class="active" data-tab="getting-started">🎥 <?php esc_html_e( 'Getting Started', 'wp-team-manager' ); ?></li>
                <li data-tab="pro-features">🚀 <?php esc_html_e( 'Lite vs Pro', 'wp-team-manager' ); ?></li>
                <li data-tab="recommended">🌟 <?php esc_html_e( 'Recommended', 'wp-team-manager' ); ?></li>
            </ul>

            <div class="wtm-tab-content active" id="getting-started">
                <h2><?php esc_html_e( 'Welcome to WP Team Manager', 'wp-team-manager' ); ?> <?php echo esc_html( TM_VERSION ); ?></h2>
                <p><?php esc_html_e( 'Thank you for installing. Watch the video below to get started.', 'wp-team-manager' ); ?></p>
                <div class="wp-team-getting-started-wrapper">
                    <div class="wp-team-responsive-iframe wp-team-video-section">
                        <iframe width="800" height="450" src="https://www.youtube.com/embed/T-cF14_TxXE?feature=oembed" title="Intro" frameborder="0" allowfullscreen></iframe>
                    </div>
                    <div class="wp-team-doc-card-grid">
                        <div class="wtm-feature-box">
                            <h4>📘 <?php esc_html_e( 'Getting Started', 'wp-team-manager' ); ?></h4>
                            <p><?php esc_html_e( 'Step-by-step guide to help you quickly launch your first team section.', 'wp-team-manager' ); ?></p>
                            <a href="https://wpteammanager.com/docs/team-manager/getting-started/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Open Guide', 'wp-team-manager' ); ?></a>
                        </div>
                        <div class="wtm-feature-box">
                            <h4>👥 <?php esc_html_e( 'Join the Community', 'wp-team-manager' ); ?></h4>
                            <p><?php esc_html_e( 'Connect with other users, ask questions, share feedback, and get help directly from our team.', 'wp-team-manager' ); ?></p>
                            <a href="https://www.facebook.com/groups/1652621408980514" target="_blank" class="button button-secondary"><?php esc_html_e( 'Join on Facebook', 'wp-team-manager' ); ?></a>
                        </div>
                        <div class="wtm-feature-box">
                            <h4>💡 <?php esc_html_e( 'FAQ', 'wp-team-manager' ); ?></h4>
                            <p><?php esc_html_e( 'Get answers to common questions regarding features, compatibility, and usage.', 'wp-team-manager' ); ?></p>
                            <a href="https://wpteammanager.com/faq/" target="_blank" class="button button-secondary"><?php esc_html_e( 'View FAQs', 'wp-team-manager' ); ?></a>
                        </div>
                        <div class="wtm-feature-box">
                            <h4>📝 <?php esc_html_e( 'Changelog', 'wp-team-manager' ); ?></h4>
                            <p><?php esc_html_e( 'See what’s new in each version and track our plugin\'s development progress.', 'wp-team-manager' ); ?></p>
                            <a href="https://wordpress.org/plugins/wp-team-manager/#developers" target="_blank" class="button button-secondary"><?php esc_html_e( 'View Log', 'wp-team-manager' ); ?></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wtm-tab-content" id="pro-features">
                <h2><?php esc_html_e( 'Lite vs Pro Comparison', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Explore the full list of features available in the free and Pro version of WP Team Manager.', 'wp-team-manager' ); ?></p>
                <div class="wp-team-comparison-wrapper">
                  <table class="wp-team-comparison-table">
                    <thead>
                      <tr>
                        <th><?php esc_html_e( 'FEATURES', 'wp-team-manager' ); ?></th>
                        <th><?php esc_html_e( 'Lite', 'wp-team-manager' ); ?></th>
                        <th><?php esc_html_e( 'Pro', 'wp-team-manager' ); ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td><?php esc_html_e( 'Team Layouts (Grid, List, Slider, Isotope, etc.)', 'wp-team-manager' ); ?></td>
                        <td>✅</td>
                        <td><?php esc_html_e( '✅ + 8+', 'wp-team-manager' ); ?></td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Unlimited Team Member Groups', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Custom Bio Field Labels', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Skills Progress Bar', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Gallery Lightbox Popup', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'vCard Download Support', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Ajax-Based Filtering & Pagination', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Elementor Widgets', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( 'Priority Support', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                      <tr>
                        <td><?php esc_html_e( '...and more', 'wp-team-manager' ); ?></td>
                        <td>❌</td>
                        <td>✅</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div style="margin-top: 20px;">
                  <?php if ( Helper::freemius_is_free_user() ) : ?>
                      <a href="<?php echo esc_url( tmwstm_fs()->get_upgrade_url() ); ?>" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'wp-team-manager' ); ?></a>
                  <?php else : ?>
                      <span class="dashicons dashicons-yes-alt"></span>
                      <strong><?php esc_html_e( 'You’re on Pro — thanks for supporting us!', 'wp-team-manager' ); ?></strong>
                  <?php endif; ?>
                  <p style="margin-top:10px;"><a href="https://wpteammanager.com/all-features/" target="_blank"><?php esc_html_e( 'See full list of features', 'wp-team-manager' ); ?> &rarr;</a></p>
                </div>
            </div>

            <div class="wtm-tab-content" id="recommended">
                <h2><?php esc_html_e( 'Recommended Plugins', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Here are some plugins and tools we recommend to enhance your site:', 'wp-team-manager' ); ?></p>
                <div class="wtm-feature-grid">
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">💬</div>
                        <h4><?php esc_html_e( 'Chat Notifications for Telegram with CF7', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Send Telegram messages when users submit Contact Form 7 forms.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/chat-notifications-for-telegram-cf7/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">📊</div>
                        <h4><?php esc_html_e( 'Dynamic Product Category Grid, Slider for WooCommerce', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Show WooCommerce categories in stylish grids and sliders.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/product-category-grid-slider-for-woocommerce/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">📈</div>
                        <h4><?php esc_html_e( 'Lean GA4 Tracker', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Lightweight Google Analytics 4 tracking plugin.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/lean-ga4-tracker/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">🧱</div>
                        <h4><?php esc_html_e( 'Post and Product Grid for Elementor', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Create blog and product layouts with Elementor.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/post-product-grid-elementor/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">📖</div>
                        <h4><?php esc_html_e( 'Quran in Text and Audio', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Read and listen to the Quran with ease.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/quran-in-text-and-audio/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">🎥</div>
                        <h4><?php esc_html_e( 'Review Showcase for TikTok', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Embed and display TikTok video reviews.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/review-showcase-for-tiktok/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">💾</div>
                        <h4><?php esc_html_e( 'Save & Continue for Contact Form 7', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Enable draft saving for Contact Form 7 submissions.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/save-continue-cf7/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">🔎</div>
                        <h4><?php esc_html_e( 'SearchJet – AI-Powered Instant Search', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Deliver fast, AI-enhanced search for WooCommerce and WordPress.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/searchjet/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">📌</div>
                        <h4><?php esc_html_e( 'Social Pin & Media Showcase', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Showcase Pinterest pins and media in a beautiful layout.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/social-pin-media-showcase/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                    <div class="wtm-feature-box">
                        <div class="wtm-feature-icon">👥</div>
                        <h4><?php esc_html_e( 'Team Manager – Team Showcase with Elementor', 'wp-team-manager' ); ?></h4>
                        <p><?php esc_html_e( 'Showcase your team with grids, sliders, tables, and Elementor.', 'wp-team-manager' ); ?></p>
                        <a href="https://wordpress.org/plugins/team-manager/" target="_blank" class="button button-secondary"><?php esc_html_e( 'Read more', 'wp-team-manager' ); ?> &rarr;</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="wtm-footer">
            <p>
                <?php esc_html_e( 'Made with', 'wp-team-manager' ); ?> ❤️ <a href="https://dynamicweblab.com/"><?php esc_html_e( 'by the Dynamic Web Lab', 'wp-team-manager' ); ?></a>
            </p>
            <p>
                <?php esc_html_e( 'Connect with us on', 'wp-team-manager' ); ?>
                <a href="https://www.facebook.com/dynamicweblab" target="_blank" class="wtm-footer-icon dashicons dashicons-facebook"></a>
                <a href="https://profiles.wordpress.org/maidulcu/#content-plugins" target="_blank" class="wtm-footer-icon dashicons dashicons-wordpress"></a>
                <a href="https://www.youtube.com/@DynamicWebLab" target="_blank" class="wtm-footer-icon dashicons dashicons-video-alt3"></a>
            </p>
            <p class="wtm-footer-rating">
                <?php esc_html_e( 'Enjoying WP Team Manager?', 'wp-team-manager' ); ?>
                <?php esc_html_e( 'Please rate us 5⭐ on', 'wp-team-manager' ); ?>
                <a href="https://wordpress.org/support/plugin/wp-team-manager/reviews/#new-post" target="_blank"><?php esc_html_e( 'WordPress.org', 'wp-team-manager' ); ?></a> 🙏
            </p>
            <p class="wtm-footer-version">WP Team Manager <?php echo esc_html( TM_VERSION ); ?></p>
        </div>

        <script>
        document.querySelectorAll('.wtm-tab-nav li').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.wtm-tab-nav li').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.wtm-tab-content').forEach(c => c.classList.remove('active'));
                tab.classList.add('active');
                document.getElementById(tab.dataset.tab).classList.add('active');
            });
        });
        </script>
        <?php
    }

}