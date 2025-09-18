<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class ImportExportTools {

    private $notices = [];

    public function __construct() {
        add_action('admin_menu', [$this, 'register_submenu']);
        add_action( 'admin_init', [ $this, 'handle_export_import' ] );
        add_action( 'admin_notices', [ $this, 'admin_notices' ] );
    }

    /**
     * Register a hidden submenu page for import/export (could be linked from elsewhere)
     */
    public function register_submenu() {
       $hook_suffix = add_submenu_page(
           'edit.php?post_type=team_manager', // parent slug
            __('Team Manager Import/Export', 'wp-team-manager'),
            __('Import/Export', 'wp-team-manager'),
            'manage_options',
            'wtm-import-export',
            [$this, 'render_page']
        );
        // Enqueue assets only on this settings screen per WP standards
        add_action( 'load-' . $hook_suffix, [ $this, 'add_admin_script' ] );
    }

    public function add_admin_script() {
        wp_enqueue_style( 'wp-team-get-help-admin' ); 
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
        <div class="wrap">
            <h1><?php _e('Team Manager Import/Export', 'wp-team-manager'); ?></h1>
            <h2><?php _e('Export Team Managers', 'wp-team-manager'); ?></h2>
                            <!-- Import/Export Section -->
                <div class="tools-section card">
                    <h2 class="title"><?php esc_html_e( 'Import/Export', 'wp-team-manager' ); ?></h2>
                    <p><?php esc_html_e( 'Export or import your team members for backup or migration purposes.', 'wp-team-manager' ); ?></p>
                    <h3><?php esc_html_e( 'Export Team Members', 'wp-team-manager' ); ?></h3>
                    <form method="post">
                        <?php wp_nonce_field( 'wtm_export_csv' ); ?>
                        <input type="submit" name="wtm_export" class="button button-primary wtm-btn-primary" value="<?php esc_attr_e( 'Download CSV', 'wp-team-manager' ); ?>">
                    </form>
                    <hr>
                    <h3><?php esc_html_e( 'Import Team Members', 'wp-team-manager' ); ?></h3>
                    <form method="post" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'wtm_import_csv' ); ?>
                        <input type="file" name="wtm_csv_file" accept=".csv">
                        <input type="submit" name="wtm_import" class="button button-primary wtm-btn-primary" value="<?php esc_attr_e( 'Upload CSV', 'wp-team-manager' ); ?>">
                    </form>
                </div>
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
        if ( $results && ( $results['inserted'] > 0 || $results['updated'] > 0 || $results['skipped'] > 0 || !empty( $results['errors'] ) ) ) {
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

        if ( isset( $_POST['wtm_import'] ) && check_admin_referer( 'wtm_import_csv' ) && ! empty( $_FILES['wtm_csv_file']['tmp_name'] ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                $this->import_csv( $_FILES['wtm_csv_file']['tmp_name'] );
            }
        }
    }

        /**
     * Export Team Members to CSV.
     */
    private function export_csv() {
        $filename = 'team-members-' . date( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv' );
        header( 'Content-Disposition: attachment;filename=' . esc_attr( $filename ) );

        $output = fopen( 'php://output', 'w' );

        // Header
        fputcsv( $output, [ 'ID', 'Name', 'Position', 'Email', 'Location', 'Mobile', 'Telephone', 'Years of Experience' ] );

        $args = [
            'post_type'      => 'team_manager',
            'posts_per_page' => -1,
        ];
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
        fclose( $output );
        exit;
    }

    /**
     * Import Team Members from CSV.
     * Validates header, logs inserted/updated/skipped, and saves results in an option.
     */
    private function import_csv( $file ) {
        $results = [
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors'  => [],
        ];

        // Validate file type
       $original_filename = $_FILES['wtm_csv_file']['name'] ?? '';
       $check_file = wp_check_filetype_and_ext( $file, $original_filename );
        if ( ! $check_file['ext'] || $check_file['ext'] !== 'csv' ) {
            $results['errors'][] = __( 'Invalid file type. Please upload a CSV file.', 'wp-team-manager' );
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
                $id         = intval( wp_unslash( $data[0] ) );
                $name       = sanitize_text_field( wp_unslash( $data[1] ) );
                $position   = sanitize_text_field( wp_unslash( $data[2] ) );
                $email      = sanitize_email( wp_unslash( $data[3] ) );
                $location   = sanitize_text_field( wp_unslash( $data[4] ) );
                $mobile     = sanitize_text_field( wp_unslash( $data[5] ) );
                $telephone  = sanitize_text_field( wp_unslash( $data[6] ) );
                $years      = sanitize_text_field( wp_unslash( $data[7] ) );

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
