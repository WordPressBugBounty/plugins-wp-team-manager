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
        // Only add ONE submenu for all tools
         $hook_suffix = add_submenu_page(
            'edit.php?post_type=team_manager', // parent slug
            __( 'Tools', 'wp-team-manager' ),
            __( 'Tools', 'wp-team-manager' ),
            'manage_options',
            'team-manager-tools',
            [ $this, 'render_tools_page' ]
        );

        // Enqueue assets only on this settings screen per WP standards
        add_action( 'load-' . $hook_suffix, [ $this, 'add_admin_script' ] );
    }

        
    public function add_admin_script() {
        wp_enqueue_style( 'wp-team-setting-admin' );
        wp_enqueue_style( 'wp-team-get-help-admin' ); 
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
                    <p><?php esc_html_e( 'Migrate your team data from other plugins or formats.', 'wp-team-manager' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=team-manager-migration' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                        <?php esc_html_e( 'Go to Migration Tool', 'wp-team-manager' ); ?>
                    </a>
                </div>

                <!-- AI Agent Modules Section -->
                <div class="wtm-tools-section card">
                    <span class="dashicons dashicons-superhero-alt wtm-tools-card-icon"></span>
                    <h2 class="title"><?php esc_html_e( 'AI Agent Modules', 'wp-team-manager' ); ?></h2>
                    <p><?php esc_html_e( 'Manage and configure AI-powered agents integrated with your team data.', 'wp-team-manager' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=team-ai-agents' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                        <?php esc_html_e( 'Go to AI Agent Modules', 'wp-team-manager' ); ?>
                    </a>
                </div>

                <!-- Shortcode Generator Section -->
                <div class="wtm-tools-section card">
                    <span class="dashicons dashicons-editor-code wtm-tools-card-icon"></span>
                    <h2 class="title"><?php esc_html_e( 'Shortcode Generator', 'wp-team-manager' ); ?></h2>
                    <p><?php esc_html_e( 'Create and customize shortcodes to display your team members easily.', 'wp-team-manager' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=team-manager-shortcode-generator' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                        <?php esc_html_e( 'Go to Shortcode Generator', 'wp-team-manager' ); ?>
                    </a>
                </div>

                <!-- Import/Export Section -->
                <div class="wtm-tools-section card">
                    <span class="dashicons dashicons-database-import wtm-tools-card-icon"></span>
                    <h2 class="title"><?php echo esc_html__( 'Import/Export', 'wp-team-manager' ); ?></h2>
                    <p><?php echo esc_html__( 'Import or export your team data easily.', 'wp-team-manager' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=team_manager&page=wtm-import-export' ) ); ?>" class="button button-secondary wtm-btn-secondary">
                        <?php echo esc_html__( 'Go to Import/Export', 'wp-team-manager' ); ?>
                    </a>
                </div>

                <!-- Future Tools Section -->
                <div class="wtm-tools-section card">
                    <span class="dashicons dashicons-hammer wtm-tools-card-icon"></span>
                    <h2 class="title"><?php esc_html_e( 'More Tools (Coming Soon)', 'wp-team-manager' ); ?></h2>
                    <ul class="coming-soon">
                        <li><?php esc_html_e( 'Bulk Edit Team Members', 'wp-team-manager' ); ?></li>
                        <li><?php esc_html_e( 'Data Cleanup', 'wp-team-manager' ); ?></li>
                        <li><?php esc_html_e( 'Advanced Export Options', 'wp-team-manager' ); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }



}