<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ImportExportTools {

    use \DWL\Wtm\Traits\Singleton;

    protected function init() {
        add_action('admin_menu', [$this, 'register_submenu']);
        add_action( 'admin_init', [ $this, 'handle_export_import' ] );
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );
    }

    /**
     * Register a hidden submenu page for import/export (could be linked from elsewhere)
     */
    public function register_submenu() {
       $hook_suffix = add_submenu_page(
           'edit.php?post_type=team_manager',
            __('Team Manager Import/Export', 'wp-team-manager'),
            __('Import/Export', 'wp-team-manager'),
            'manage_options',
            'wtm-import-export',
            [$this, 'render_page']
        );
        add_action( 'load-' . $hook_suffix, [ $this, 'add_admin_script' ] );
    }

    public function add_admin_script() {
        wp_enqueue_style( 'wp-team-get-help-admin' );
        wp_enqueue_style( 'wtm-import-export', TM_URL . '/admin/assets/css/tm-import-export.css', [], TM_VERSION );
    }

    /**
     * Render the Import/Export UI
     */
    public function render_page() {
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
        <div class="wrap wtm-import-export-wrap">
            <div class="wtm-page-header">
                <h1 class="wtm-page-title">
                    <span class="dashicons dashicons-database-import"></span>
                    <?php esc_html_e('Import/Export Team Data', 'wp-team-manager'); ?>
                </h1>
                <p class="wtm-page-description">
                    <?php esc_html_e('Manage your team data with powerful import and export tools. Backup, migrate, or bulk update your team members with ease.', 'wp-team-manager'); ?>
                </p>
            </div>

            <?php 
            $team_count = wp_count_posts('team_manager');
            $total_members = $team_count->publish ?? 0;
            ?>
            <div class="wtm-stats-bar">
                <div class="wtm-stat-item">
                    <span class="wtm-stat-number"><?php echo esc_html($total_members); ?></span>
                    <span class="wtm-stat-label"><?php esc_html_e('Team Members', 'wp-team-manager'); ?></span>
                </div>
            </div>

            <div class="wtm-import-export-grid">
                <!-- Export Section -->
                <div class="wtm-section-card wtm-export-card">
                    <div class="wtm-card-header">
                        <div class="wtm-card-icon wtm-export-icon">
                            <span class="dashicons dashicons-download"></span>
                        </div>
                        <div class="wtm-card-title-group">
                            <h2 class="wtm-card-title"><?php esc_html_e('Export Team Data', 'wp-team-manager'); ?></h2>
                            <p class="wtm-card-subtitle"><?php esc_html_e('Download your team data as CSV', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                    
                    <div class="wtm-card-content">
                        <div class="wtm-feature-list">
                            <div class="wtm-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('All team member information', 'wp-team-manager'); ?>
                            </div>
                            <div class="wtm-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Contact details and positions', 'wp-team-manager'); ?>
                            </div>
                            <div class="wtm-feature-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Ready for backup or migration', 'wp-team-manager'); ?>
                            </div>
                        </div>
                        
                        <form method="post" class="wtm-export-form">
                            <?php wp_nonce_field( 'wtm_export_csv' ); ?>
                            <button type="submit" name="wtm_export" class="wtm-btn wtm-btn-primary wtm-btn-export">
                                <span class="dashicons dashicons-download"></span>
                                <?php esc_html_e('Download CSV File', 'wp-team-manager'); ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Import Section -->
                <div class="wtm-section-card wtm-import-card">
                    <div class="wtm-card-header">
                        <div class="wtm-card-icon wtm-import-icon">
                            <span class="dashicons dashicons-upload"></span>
                        </div>
                        <div class="wtm-card-title-group">
                            <h2 class="wtm-card-title"><?php esc_html_e('Import Team Data', 'wp-team-manager'); ?></h2>
                            <p class="wtm-card-subtitle"><?php esc_html_e('Upload CSV file to add team members', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                    
                    <div class="wtm-card-content">
                        <div class="wtm-import-instructions">
                            <h4><?php esc_html_e('CSV Format Requirements:', 'wp-team-manager'); ?></h4>
                            <div class="wtm-csv-format">
                                <code>ID, Name, Position, Email, Location, Mobile, Telephone, Years of Experience</code>
                            </div>
                            <p class="wtm-format-note">
                                <?php esc_html_e('Use the exported CSV as a template. Leave ID empty for new members.', 'wp-team-manager'); ?>
                            </p>
                        </div>
                        
                        <form method="post" enctype="multipart/form-data" class="wtm-import-form">
                            <?php wp_nonce_field( 'wtm_import_csv' ); ?>
                            <div class="wtm-file-upload-area">
                                <div class="wtm-file-upload-content">
                                    <span class="dashicons dashicons-media-spreadsheet"></span>
                                    <p><?php esc_html_e('Choose CSV file or drag and drop', 'wp-team-manager'); ?></p>
                                    <input type="file" name="wtm_csv_file" accept=".csv" class="wtm-file-input" id="wtm-csv-file">
                                    <label for="wtm-csv-file" class="wtm-file-label">
                                        <?php esc_html_e('Select File', 'wp-team-manager'); ?>
                                    </label>
                                </div>
                            </div>
                            <button type="submit" name="wtm_import" class="wtm-btn wtm-btn-secondary wtm-btn-import">
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e('Import CSV File', 'wp-team-manager'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="wtm-help-section">
                <div class="wtm-help-card">
                    <h3><?php esc_html_e('Need Help?', 'wp-team-manager'); ?></h3>
                    <div class="wtm-help-grid">
                        <div class="wtm-help-item">
                            <span class="dashicons dashicons-book-alt"></span>
                            <div>
                                <h4><?php esc_html_e('Documentation', 'wp-team-manager'); ?></h4>
                                <p><?php esc_html_e('Learn how to use import/export features', 'wp-team-manager'); ?></p>
                            </div>
                        </div>
                        <div class="wtm-help-item">
                            <span class="dashicons dashicons-sos"></span>
                            <div>
                                <h4><?php esc_html_e('Support', 'wp-team-manager'); ?></h4>
                                <p><?php esc_html_e('Get help from our support team', 'wp-team-manager'); ?></p>
                            </div>
                        </div>
                    </div>
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





    /**
     * Display admin notices for import results.
     */
    public function admin_notices() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        $results = get_option( 'wtm_import_results', false );
        if ( $results && ( $results['inserted'] > 0 || $results['updated'] > 0 || $results['skipped'] > 0 || ! empty( $results['errors'] ) ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php esc_html_e( 'Team Import Results:', 'wp-team-manager' ); ?></strong></p>
                <ul>
                    <li><?php printf( esc_html__( 'Inserted: %d', 'wp-team-manager' ), intval( $results['inserted'] ) ); ?></li>
                    <li><?php printf( esc_html__( 'Updated: %d', 'wp-team-manager' ), intval( $results['updated'] ) ); ?></li>
                    <li><?php printf( esc_html__( 'Skipped: %d', 'wp-team-manager' ), intval( $results['skipped'] ) ); ?></li>
                </ul>
                <?php if ( ! empty( $results['errors'] ) ) : ?>
                    <!-- wtm-import-errors CSS should be styled in admin CSS instead of inline -->
                    <ul class="wtm-import-errors">
                        <?php foreach ( $results['errors'] as $error ) : ?>
                            <li><?php echo esc_html( $error ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php
            delete_option( 'wtm_import_results' );
        }
    }


    /**
     * Handle export and import requests.
     */
    public function handle_export_import() {
        if ( isset( $_POST['wtm_export'] ) && check_admin_referer( 'wtm_export_csv' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                $this->export_csv();
            }
        }

        if ( isset( $_POST['wtm_import'] ) && check_admin_referer( 'wtm_import_csv' ) ) {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            // Validate file upload
            if ( empty( $_FILES['wtm_csv_file']['tmp_name'] ) || ! isset( $_FILES['wtm_csv_file']['error'] ) ) {
                return;
            }

            // Check for upload errors
            if ( $_FILES['wtm_csv_file']['error'] !== UPLOAD_ERR_OK ) {
                $results = [
                    'inserted' => 0,
                    'updated'  => 0,
                    'skipped'  => 0,
                    'errors'   => [ __( 'File upload failed. Please try again.', 'wp-team-manager' ) ],
                ];
                update_option( 'wtm_import_results', $results );
                return;
            }

            $this->import_csv( $_FILES['wtm_csv_file']['tmp_name'], $_FILES['wtm_csv_file']['name'] );
        }
    }

        /**
     * Export Team Members to CSV.
     */
    private function export_csv() {
        $filename = 'team-members-' . current_time( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $filename ) . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // Add BOM for Excel UTF-8 compatibility
        fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );

        // Header
        fputcsv( $output, [ 'ID', 'Name', 'Position', 'Email', 'Location', 'Mobile', 'Telephone', 'Years of Experience' ] );

        $args = [
            'post_type'      => 'team_manager',
            'posts_per_page' => 100,
            'post_status'    => 'publish',
            'paged'          => 1,
            'no_found_rows'  => false,
        ];
        $query = new \WP_Query( $args );

        $total_pages = $query->max_num_pages;

        // Process first page
        while ( $query->have_posts() ) {
            $query->the_post();
            $id         = get_the_ID();
            $name       = get_the_title();
            $position   = get_post_meta( $id, 'tm_jtitle', true );
            $email      = get_post_meta( $id, 'tm_email', true );
            $location   = get_post_meta( $id, 'tm_location', true );
            $mobile     = get_post_meta( $id, 'tm_mobile', true );
            $telephone  = get_post_meta( $id, 'tm_telephone', true );
            $years      = get_post_meta( $id, 'tm_year_experience', true );

            fputcsv( $output, [ $id, $name, $position, $email, $location, $mobile, $telephone, $years ] );
        }

        wp_reset_postdata();

        // Process remaining pages if there are more than 100 posts
        for ( $page = 2; $page <= $total_pages; $page++ ) {
            $args['paged'] = $page;
            $query = new \WP_Query( $args );

            while ( $query->have_posts() ) {
                $query->the_post();
                $id         = get_the_ID();
                $name       = get_the_title();
                $position   = get_post_meta( $id, 'tm_jtitle', true );
                $email      = get_post_meta( $id, 'tm_email', true );
                $location   = get_post_meta( $id, 'tm_location', true );
                $mobile     = get_post_meta( $id, 'tm_mobile', true );
                $telephone  = get_post_meta( $id, 'tm_telephone', true );
                $years      = get_post_meta( $id, 'tm_year_experience', true );

                fputcsv( $output, [ $id, $name, $position, $email, $location, $mobile, $telephone, $years ] );
            }

            wp_reset_postdata();
        }

        fclose( $output );
        exit;
    }

    /**
     * Import Team Members from CSV.
     * Validates header, logs inserted/updated/skipped, and saves results in an option.
     *
     * @param string $file Temporary file path
     * @param string $original_filename Original filename from upload
     */
    private function import_csv( $file, $original_filename ) {
        $results = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors'  => [],
        ];

        // Validate file type with MIME type check
        $check_file = wp_check_filetype_and_ext( $file, $original_filename, [ 'csv' => 'text/csv' ] );

        // Additional MIME type validation
        $allowed_mimes = [ 'text/csv', 'text/plain', 'application/csv', 'text/comma-separated-values', 'application/excel', 'application/vnd.ms-excel', 'application/vnd.msexcel' ];
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $file );
        finfo_close( $finfo );

        if ( ! $check_file['ext'] || $check_file['ext'] !== 'csv' || ! in_array( $mime_type, $allowed_mimes, true ) ) {
            $results['errors'][] = __( 'Invalid file type. Please upload a valid CSV file.', 'wp-team-manager' );
            update_option( 'wtm_import_results', $results );
            return;
        }

        $expected_header = [ 'ID', 'Name', 'Position', 'Email', 'Location', 'Mobile', 'Telephone', 'Years of Experience' ];
        if ( ( $handle = fopen( $file, 'r' ) ) !== false ) {
            $header = fgetcsv( $handle );
            if ( $header === false || array_map( 'trim', $header ) !== $expected_header ) {
                $results['errors'][] = __( 'Invalid CSV header. Please use the exported CSV format.', 'wp-team-manager' );
                update_option( 'wtm_import_results', $results );
                fclose( $handle );
                return;
            }
            while ( ( $data = fgetcsv( $handle ) ) !== false ) {
                if ( count( $data ) < 8 ) {
                    $results['skipped']++;
                    continue;
                }
                // CSV data doesn't need wp_unslash() - it's read directly from file
                $id         = absint( $data[0] );
                $name       = sanitize_text_field( $data[1] );
                $position   = sanitize_text_field( $data[2] );
                $email      = sanitize_email( $data[3] );
                $location   = sanitize_text_field( $data[4] );
                $mobile     = sanitize_text_field( $data[5] );
                $telephone  = sanitize_text_field( $data[6] );
                $years      = sanitize_text_field( $data[7] );

                if ( empty( $name ) ) {
                    $results['skipped']++;
                    continue;
                }

                if ( $id && get_post( $id ) && get_post_type( $id ) === 'team_manager' ) {
                    // Update existing
                    $post_update = wp_update_post( [
                        'ID'         => $id,
                        'post_title' => $name,
                    ], true );
                    if ( is_wp_error( $post_update ) ) {
                        $results['errors'][] = sprintf( __( 'Failed to update member ID %d: %s', 'wp-team-manager' ), $id, $post_update->get_error_message() );
                        $results['skipped']++;
                        continue;
                    }
                    $results['updated']++;
                } else {
                    // Insert new
                    $new_id = wp_insert_post( [
                        'post_type'   => 'team_manager',
                        'post_status' => 'publish',
                        'post_title'  => $name,
                    ], true );
                    if ( is_wp_error( $new_id ) ) {
                        $results['errors'][] = sprintf( __( 'Failed to insert member "%s": %s', 'wp-team-manager' ), $name, $new_id->get_error_message() );
                        $results['skipped']++;
                        continue;
                    }
                    $id = $new_id;
                    $results['inserted']++;
                }

                if ( $id ) {
                    update_post_meta( $id, 'tm_jtitle', $position );
                    update_post_meta( $id, 'tm_email', $email );
                    update_post_meta( $id, 'tm_location', $location );
                    update_post_meta( $id, 'tm_mobile', $mobile );
                    update_post_meta( $id, 'tm_telephone', $telephone );
                    update_post_meta( $id, 'tm_year_experience', $years );
                }
            }
            fclose( $handle );
        } else {
            $results['errors'][] = __( 'Failed to open uploaded CSV file.', 'wp-team-manager' );
        }
        update_option( 'wtm_import_results', $results );
    }

}
