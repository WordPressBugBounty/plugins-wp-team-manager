<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class UnifiedTools {

    use \DWL\Wtm\Traits\Singleton;

    protected function init() {
        add_action( 'admin_menu', [ $this, 'register_unified_menu' ] );
    }

    public function register_unified_menu() {
        $hook_suffix = add_submenu_page(
            'edit.php?post_type=team_manager',
            __( 'Tools', 'wp-team-manager' ),
            __( 'Tools', 'wp-team-manager' ),
            'manage_options',
            'team-manager-tools',
            [ $this, 'render_unified_page' ]
        );

        add_action( 'load-' . $hook_suffix, [ $this, 'add_admin_script' ] );
    }

    public function add_admin_script() {
        wp_enqueue_style( 'wp-team-setting-admin' );
        wp_enqueue_style( 'wp-team-get-help-admin' );
        wp_enqueue_style( 'wp-team-tools-admin', TM_URL . '/admin/assets/css/tm-tools.css', [], TM_VERSION );
        wp_enqueue_style( 'wtm-dashboard', TM_URL . '/admin/assets/css/dashboard.css', [], TM_VERSION );
        wp_enqueue_style( 'wtm-unified-tools', TM_URL . '/admin/assets/css/unified-tools.css', [], TM_VERSION );
        
        // Enqueue onboarding assets if needed
        if (class_exists('\DWL\Wtm\Classes\Onboarding')) {
            $onboarding = \DWL\Wtm\Classes\Onboarding::get_instance();
            if (method_exists($onboarding, 'should_show_onboarding') && $onboarding->should_show_onboarding()) {
                wp_enqueue_style( 'wtm-onboarding', TM_URL . '/admin/assets/css/onboarding.css', [], TM_VERSION );
                wp_enqueue_script( 'wtm-onboarding', TM_URL . '/admin/assets/js/onboarding.js', ['jquery'], TM_VERSION, true );
                wp_localize_script('wtm-onboarding', 'wtmOnboarding', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('wtm_onboarding_nonce')
                ]);
            }
        }
        
        // Enqueue tab switching JavaScript
        wp_enqueue_script(
            'wtm-unified-tools',
            TM_ADMIN_ASSETS . '/js/unified-tools.js',
            ['jquery'],
            TM_VERSION,
            true
        );
        
        wp_enqueue_script(
            'wtm-dashboard',
            TM_ADMIN_ASSETS . '/js/dashboard.js',
            ['jquery'],
            TM_VERSION,
            true
        );
        
        wp_localize_script('wtm-dashboard', 'wtmDashboard', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wtm_dashboard_nonce'),
            'strings' => [
                'loading' => __('Loading...', 'wp-team-manager'),
                'success' => __('Action completed successfully', 'wp-team-manager'),
                'error' => __('An error occurred', 'wp-team-manager')
            ]
        ]);
    }

    public function render_unified_page() {
        ?>
        <?php if ( Helper::freemius_is_free_user() ) : ?>
            <div class="wtm-upgrade-banner">
                <div class="wtm-upgrade-content">
                    <h3><?php esc_html_e( 'Unlock the Full Potential', 'wp-team-manager' ); ?></h3>
                    <p><?php esc_html_e( 'You are using WP Team Manager Free Version. Upgrade to Pro to unlock advanced team layouts, filtering, Elementor widgets, and more.', 'wp-team-manager' ); ?></p>
                </div>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=team-manager-pricing' ) ); ?>" class="wtm-upgrade-link">
                     🚀 <?php esc_html_e( 'Upgrade to Pro', 'wp-team-manager' ); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <div class="wrap">
            <h1><?php esc_html_e( 'Team Manager Dashboard', 'wp-team-manager' ); ?></h1>
            
            <div class="wtm-tabs-wrapper">
                <ul class="wtm-tab-nav">
                    <li class="active" data-tab="dashboard">📊 <?php esc_html_e( 'Dashboard', 'wp-team-manager' ); ?></li>
                    <li data-tab="tools">🔧 <?php esc_html_e( 'Tools', 'wp-team-manager' ); ?></li>
                    <li data-tab="getting-started">🚀 <?php esc_html_e( 'Getting Started', 'wp-team-manager' ); ?></li>
                    <li data-tab="pro-features">⭐ <?php esc_html_e( 'Pro Features', 'wp-team-manager' ); ?></li>
                    <li data-tab="recommended">🌟 <?php esc_html_e( 'Recommended', 'wp-team-manager' ); ?></li>
                </ul>

                <div class="wtm-tab-content active" id="dashboard">
                    <?php $this->render_dashboard_content(); ?>
                </div>

                <div class="wtm-tab-content" id="tools">
                    <?php $this->render_tools_content(); ?>
                </div>

                <div class="wtm-tab-content" id="getting-started">
                    <?php $this->render_getting_started_content(); ?>
                </div>

                <div class="wtm-tab-content" id="pro-features">
                    <?php $this->render_pro_features_content(); ?>
                </div>

                <div class="wtm-tab-content" id="recommended">
                    <?php $this->render_recommended_content(); ?>
                </div>
            </div>
        </div>

        <div class="wtm-footer">
            <p>
                <?php esc_html_e( 'Made with', 'wp-team-manager' ); ?> ❤️ <a href="https://dynamicweblab.com/"><?php esc_html_e( 'by the Dynamic Web Lab', 'wp-team-manager' ); ?></a>
            </p>
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

    private function render_dashboard_content() {
        if (class_exists('\DWL\Wtm\Classes\Dashboard')) {
            $dashboard = \DWL\Wtm\Classes\Dashboard::get_instance();
            if (method_exists($dashboard, 'render_dashboard')) {
                $dashboard->render_dashboard();
            }
        } else {
            echo '<h2>' . esc_html__('Dashboard Overview', 'wp-team-manager') . '</h2>';
            echo '<p>' . esc_html__('Dashboard content will be loaded here.', 'wp-team-manager') . '</p>';
        }
    }

    private function render_tools_content() {
        ?>
        <h2><?php esc_html_e( 'Team Manager Tools Hub', 'wp-team-manager' ); ?></h2>
        <p class="description"><?php esc_html_e( 'Welcome to the Team Manager Tools Hub. Access import/export, migration, and other tools from this central dashboard.', 'wp-team-manager' ); ?></p>
        
        <div class="wtm-tools-hub-sections grid">
            <!-- Import/Export Section -->
            <div class="wtm-tools-section card">
                <span class="dashicons dashicons-database-import wtm-tools-card-icon"></span>
                <h2 class="title"><?php esc_html_e( 'Import/Export Data', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Import team members from CSV files or export your existing team data for backup, sharing, or migration purposes. Supports bulk operations.', 'wp-team-manager' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=wtm-import-export' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                    <span class="dashicons dashicons-database-import"></span>
                    <?php esc_html_e( 'Manage Data', 'wp-team-manager' ); ?>
                </a>
            </div>
            
            <!-- Enhanced Search Section -->
            <div class="wtm-tools-section card">
                <span class="dashicons dashicons-search wtm-tools-card-icon"></span>
                <h2 class="title"><?php esc_html_e( 'Enhanced Search', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Configure advanced search and filtering options for your team displays. Live search with autocomplete and analytics.', 'wp-team-manager' ); ?></p>
                <a href="#" class="button button-secondary wtm-btn-secondary">
                    <span class="dashicons dashicons-search"></span>
                    <?php esc_html_e( 'Configure Search', 'wp-team-manager' ); ?>
                </a>
            </div>
            
            <!-- Migration Tool Section -->
            <div class="wtm-tools-section card">
                <span class="dashicons dashicons-migrate wtm-tools-card-icon"></span>
                <h2 class="title"><?php esc_html_e( 'Migration Tool', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Seamlessly migrate your team data from other plugins like Team Members, Our Team Showcase, and more. Preserve all your existing data while upgrading to WP Team Manager.', 'wp-team-manager' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=team-manager-migration' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                    <span class="dashicons dashicons-migrate"></span>
                    <?php esc_html_e( 'Start Migration', 'wp-team-manager' ); ?>
                </a>
            </div>

            <!-- AI Agent Modules Section -->
            <div class="wtm-tools-section card">
                <span class="dashicons dashicons-superhero-alt wtm-tools-card-icon"></span>
                <h2 class="title"><?php esc_html_e( 'AI Agent Modules', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Configure AI-powered automation including Telegram notifications, Slack integration, profile sync agents, and FAQ bots to streamline your team management workflow.', 'wp-team-manager' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=team-ai-agents' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                    <span class="dashicons dashicons-superhero-alt"></span>
                    <?php esc_html_e( 'Configure AI Agents', 'wp-team-manager' ); ?>
                </a>
            </div>

            <!-- Shortcode Generator Section -->
            <div class="wtm-tools-section card">
                <span class="dashicons dashicons-editor-code wtm-tools-card-icon"></span>
                <h2 class="title"><?php esc_html_e( 'Shortcode Generator', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Create customized shortcodes with live preview. Configure layouts, filters, styling options, and generate ready-to-use shortcodes for your pages and posts.', 'wp-team-manager' ); ?></p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=team-manager-shortcode-generator' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                    <span class="dashicons dashicons-editor-code"></span>
                    <?php esc_html_e( 'Generate Shortcode', 'wp-team-manager' ); ?>
                </a>
            </div>

            <!-- Future Tools Section -->
            <div class="wtm-tools-section card">
                <span class="dashicons dashicons-hammer wtm-tools-card-icon"></span>
                <h2 class="title"><?php esc_html_e( 'Advanced Tools', 'wp-team-manager' ); ?></h2>
                <p><?php esc_html_e( 'Additional powerful tools are in development to enhance your team management experience:', 'wp-team-manager' ); ?></p>
                <ul class="coming-soon">
                    <li><?php esc_html_e( 'Bulk Edit Team Members', 'wp-team-manager' ); ?></li>
                    <li><?php esc_html_e( 'Data Cleanup & Optimization', 'wp-team-manager' ); ?></li>
                    <li><?php esc_html_e( 'Advanced Export Formats', 'wp-team-manager' ); ?></li>
                    <li><?php esc_html_e( 'Team Analytics Dashboard', 'wp-team-manager' ); ?></li>
                </ul>
            </div>
        </div>
        

        <?php
    }


    
    private function render_getting_started_content() {
        ?>
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
                    <p><?php esc_html_e( 'See what\'s new in each version and track our plugin\'s development progress.', 'wp-team-manager' ); ?></p>
                    <a href="https://wordpress.org/plugins/wp-team-manager/#developers" target="_blank" class="button button-secondary"><?php esc_html_e( 'View Log', 'wp-team-manager' ); ?></a>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_pro_features_content() {
        ?>
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
              <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=team-manager-pricing' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'wp-team-manager' ); ?></a>
          <?php else : ?>
              <span class="dashicons dashicons-yes-alt"></span>
              <strong><?php esc_html_e( 'You\'re on Pro — thanks for supporting us!', 'wp-team-manager' ); ?></strong>
          <?php endif; ?>
          <p style="margin-top:10px;"><a href="https://wpteammanager.com/all-features/" target="_blank"><?php esc_html_e( 'See full list of features', 'wp-team-manager' ); ?> &rarr;</a></p>
        </div>
        <?php
    }
    
    private function render_recommended_content() {
        ?>
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
        <?php
    }

}