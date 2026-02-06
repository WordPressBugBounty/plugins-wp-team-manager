<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Tools {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_tools_submenu' ] );

    }

    /**
     * Register Tools submenu under Team.
     * This acts as the central hub for all tools-related functionality.
     */
    public function register_tools_submenu() {
        // Disabled - using UnifiedTools instead
        return;
    }

        
    public function add_admin_script() {
        wp_enqueue_style( 'wp-team-setting-admin' );
        wp_enqueue_style( 'wp-team-get-help-admin' );
        wp_enqueue_style( 'wp-team-tools-admin', TM_URL . '/admin/assets/css/tm-tools.css', [], TM_VERSION );
    }

    /**
     * Render the Tools Hub page.
     * Dashboard-like UI with sections for Import/Export, Migration Tool, and placeholders for future tools.
     */
    public function render_tools_page() {
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
        <div class="wrap">
            <h1><?php esc_html_e( 'Team Manager Tools Hub', 'wp-team-manager' ); ?></h1>
            <p class="description"><?php esc_html_e( 'Welcome to the Team Manager Tools Hub. Access import/export, migration, and other tools from this central dashboard.', 'wp-team-manager' ); ?></p>

            <div class="wtm-tools-hub-sections grid">


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

                <!-- Import/Export Section -->
                <div class="wtm-tools-section card">
                    <span class="dashicons dashicons-database-import wtm-tools-card-icon"></span>
                    <h2 class="title"><?php echo esc_html__( 'Import/Export Data', 'wp-team-manager' ); ?></h2>
                    <p><?php echo esc_html__( 'Import team members from CSV files or export your existing team data for backup, sharing, or migration purposes. Supports bulk operations.', 'wp-team-manager' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=wtm-import-export' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                        <span class="dashicons dashicons-database-import"></span>
                        <?php echo esc_html__( 'Manage Data', 'wp-team-manager' ); ?>
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
        </div>

        <div class="wtm-footer">
            <p>
                <?php esc_html_e( 'Made with', 'wp-team-manager' ); ?> ❤️ <a href="https://dynamicweblab.com/"><?php esc_html_e( 'by the Dynamic Web Lab', 'wp-team-manager' ); ?></a>
            </p>
        </div>
        <?php
    }



}