<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MigrationTools {

	/**
	 * MigrationTools handles bulk migration of team member posts
	 * from other team plugins into the wp-team-manager plugin.
	 */
	
	/**
	 * Cached post counts to avoid duplicate queries
	 * @var array
	 */
	private $post_counts = [];
	
	/**
	 * Migration results
	 * @var int
	 */
	private $migrated_count = 0;
	
	/**
	 * Migrated post titles
	 * @var array
	 */
	private $migrated_titles = [];
	
	public function __construct() {
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_menu', [ $this, 'register_hidden_page' ] );
		
		// AJAX endpoints for background migration
		add_action( 'wp_ajax_wtm_start_migration', [ $this, 'ajax_start_migration' ] );
		add_action( 'wp_ajax_wtm_migrate_batch', [ $this, 'ajax_migrate_batch' ] );
		add_action( 'wp_ajax_wtm_get_progress', [ $this, 'ajax_get_progress' ] );
		add_action( 'wp_ajax_wtm_cancel_migration', [ $this, 'ajax_cancel_migration' ] );
		
		// Clear cache when plugins are activated/deactivated
		add_action( 'activated_plugin', [ $this, 'clear_plugin_cache' ] );
		add_action( 'deactivated_plugin', [ $this, 'clear_plugin_cache' ] );
	}


	/**
	 * Register hidden admin submenu page for migration.
	 */
	public function register_hidden_page() {
		$hook_suffix = add_submenu_page(
			'edit.php?post_type=team_manager', // parent slug
			__( 'Migration', 'wp-team-manager' ),
			__( 'Migration', 'wp-team-manager' ),
			'manage_options',
			'team-manager-migration',
			[ $this, 'render_section' ]
		);
		// Enqueue assets only on this settings screen per WP standards
        add_action( 'load-' . $hook_suffix, [ $this, 'add_admin_script' ] );
	}

	public function add_admin_script() {
        wp_enqueue_style( 'wp-team-setting-admin' );
		wp_enqueue_style( 'wp-team-get-help-admin' );
		wp_enqueue_style( 'wtm-migration-styles', TM_URL . '/admin/assets/css/tm-migration.css', [], TM_VERSION );
		wp_enqueue_script( 'wtm-migration-script', TM_URL . '/admin/assets/js/migration.js', ['jquery'], TM_VERSION, true );
		wp_localize_script( 'wtm-migration-script', 'wtmMigration', [
			'nonce' => wp_create_nonce( 'wtm_migration_action' ),
			'strings' => [
				'confirm' => __( 'Are you sure you want to migrate? This will create new team member posts.', 'wp-team-manager' ),
				'migrating' => __( 'Migrating team members...', 'wp-team-manager' ),
				'complete' => __( 'Migration completed successfully!', 'wp-team-manager' ),
				'error' => __( 'Migration failed. Please try again.', 'wp-team-manager' )
			]
		]);
    }

public function render_section() {
    $plugins = MigrationTools::detect_plugins();
    
    // Check for incomplete migrations
    $incomplete_migrations = $this->get_incomplete_migrations();
    ?>
    <?php if ( Helper::freemius_is_free_user() ) : ?>
        <div class="wtm-upgrade-banner">
            <p>
                <?php esc_html_e( 'You\'re using WP Team Manager Free Version. Upgrade to Pro to unlock advanced team layouts, filtering, Elementor widgets, and more.', 'wp-team-manager' ); ?>
                <a href="<?php echo esc_url( tmwstm_fs()->get_upgrade_url() ); ?>" class="wtm-upgrade-link" target="_blank">
                     🚀 <?php esc_html_e( 'Upgrade to Pro!', 'wp-team-manager' ); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if ( ! empty( $incomplete_migrations ) ) : ?>
        <div class="notice notice-warning is-dismissible" style="margin: 20px 0;">
            <p>
                <strong><?php esc_html_e( 'Incomplete Migration Detected', 'wp-team-manager' ); ?></strong><br>
                <?php 
                foreach ( $incomplete_migrations as $plugin => $progress ) {
                    printf(
                        esc_html__( 'Migration for "%s" was interrupted. Progress: %d/%d posts processed.', 'wp-team-manager' ),
                        esc_html( $plugin ),
                        $progress['processed'],
                        $progress['total']
                    );
                }
                ?>
            </p>
            <p>
                <button type="button" class="button button-secondary" onclick="location.reload();">
                    <?php esc_html_e( 'Resume Migration', 'wp-team-manager' ); ?>
                </button>
                <button type="button" class="button" id="wtm-cancel-migration" data-plugin="<?php echo esc_attr( key( $incomplete_migrations ) ); ?>">
                    <?php esc_html_e( 'Cancel Migration', 'wp-team-manager' ); ?>
                </button>
            </p>
        </div>
    <?php endif; ?>

    <div class="wrap wtm-migration-page">

        <!-- Header -->
        <div class="wtm-migration-header">
            <h1><?php esc_html_e( 'Team Migration Center', 'wp-team-manager' ); ?></h1>
            <p class="wtm-migration-subtitle"><?php esc_html_e( 'Seamlessly import your team members from other WordPress plugins', 'wp-team-manager' ); ?></p>
        </div>

        <!-- Stats -->
        <div class="wtm-migration-stats">
            <div class="wtm-stat-card">
                <span class="wtm-stat-number"><?php echo esc_html( count( array_filter( $plugins, function($p) { return $p['present']; } ) ) ); ?></span>
                <span class="wtm-stat-label"><?php esc_html_e( 'Plugins Detected', 'wp-team-manager' ); ?></span>
            </div>
            <div class="wtm-stat-card">
                <span class="wtm-stat-number"><?php echo esc_html( count($plugins) ); ?></span>
                <span class="wtm-stat-label"><?php esc_html_e( 'Supported Plugins', 'wp-team-manager' ); ?></span>
            </div>
            <div class="wtm-stat-card">
                <span class="wtm-stat-number">100%</span>
                <span class="wtm-stat-label"><?php esc_html_e( 'Data Preserved', 'wp-team-manager' ); ?></span>
            </div>
        </div>

        <!-- Notice -->
        <div class="wtm-migration-notice">
            <div class="wtm-notice-icon">
                <span class="dashicons dashicons-info"></span>
            </div>
            <div class="wtm-notice-content">
                <h4><?php esc_html_e( 'Before You Start', 'wp-team-manager' ); ?></h4>
                <p><?php esc_html_e( 'We recommend creating a database backup before migration. While the process is safe, backups are always a good practice.', 'wp-team-manager' ); ?></p>
            </div>
        </div>

        <!-- Migration Table -->
        <div class="wtm-migration-table-container">
            <h2><?php esc_html_e( 'Available Migrations', 'wp-team-manager' ); ?></h2>
            <form method="post" action="" id="wtm-migration-form">
                <?php wp_nonce_field( 'wtm_migration_action', 'wtm_migration_nonce' ); ?>
                <div class="wtm-migration-table">
                    <?php foreach ( $plugins as $key => $info ) : 
                        $post_count = $info['present'] ? $this->get_post_count($info['cpt']) : 0;
                    ?>
                        <div class="wtm-migration-row <?php echo $info['present'] ? 'available' : 'unavailable'; ?>">
                            <div class="wtm-plugin-info">
                                <div class="wtm-plugin-icon">
                                    <span class="dashicons dashicons-admin-plugins"></span>
                                </div>
                                <div class="wtm-plugin-details">
                                    <h3><?php echo esc_html( $info['label'] ); ?></h3>
                                    <p><?php printf( esc_html__( 'Post Type: %s', 'wp-team-manager' ), '<code>' . esc_html( $info['cpt'] ) . '</code>' ); ?></p>
                                    <?php if ( $info['present'] && $post_count > 0 ) : ?>
                                        <p class="wtm-post-count"><?php printf( esc_html__( '%d team members found', 'wp-team-manager' ), $post_count ); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="wtm-plugin-status">
                                <?php if ( $info['present'] ) : ?>
                                    <?php if ( $post_count > 0 ) : ?>
                                        <span class="wtm-status-badge available">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php esc_html_e( 'Ready', 'wp-team-manager' ); ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="wtm-status-badge empty">
                                            <span class="dashicons dashicons-warning"></span>
                                            <?php esc_html_e( 'No Data', 'wp-team-manager' ); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="wtm-status-badge unavailable">
                                        <span class="dashicons dashicons-dismiss"></span>
                                        <?php esc_html_e( 'Not Found', 'wp-team-manager' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="wtm-plugin-action">
                                <?php if ( $info['present'] && $post_count > 0 ) : ?>
                                    <button type="submit" name="wtm_migrate_plugin" value="<?php echo esc_attr( $key ); ?>" class="wtm-migrate-btn" data-plugin="<?php echo esc_attr( $key ); ?>" data-count="<?php echo esc_attr( $post_count ); ?>">
                                        <span class="dashicons dashicons-migrate"></span>
                                        <?php esc_html_e( 'Migrate', 'wp-team-manager' ); ?>
                                    </button>
                                <?php else : ?>
                                    <button type="button" class="wtm-migrate-btn disabled" disabled>
                                        <span class="dashicons dashicons-lock"></span>
                                        <?php esc_html_e( 'Unavailable', 'wp-team-manager' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>

        <!-- Migration Progress -->
        <div class="wtm-migration-progress" id="wtm-migration-progress" style="display: none;">
            <div class="wtm-progress-header">
                <h3><?php esc_html_e( 'Migration in Progress', 'wp-team-manager' ); ?></h3>
                <p><?php esc_html_e( 'Please wait while we migrate your team members...', 'wp-team-manager' ); ?></p>
            </div>
            <div class="wtm-progress-bar">
                <div class="wtm-progress-fill" id="wtm-progress-fill"></div>
            </div>
            <div class="wtm-progress-status" id="wtm-progress-status">
                <?php esc_html_e( 'Initializing migration...', 'wp-team-manager' ); ?>
            </div>
        </div>

        <!-- Footer -->
        <div class="wtm-footer">
            <p>
                <?php esc_html_e( 'Made with', 'wp-team-manager' ); ?> ❤️ 
                <a href="https://dynamicweblab.com/"><?php esc_html_e( 'by the Dynamic Web Lab', 'wp-team-manager' ); ?></a>
            </p>
        </div>

    </div> <!-- .wrap.wtm-migration-page -->
    <?php
}


	/**
	 * AJAX: Start migration process
	 */
	public function ajax_start_migration() {
		check_ajax_referer( 'wtm_migration_action', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied', 'wp-team-manager' ) ] );
		}
		
		$plugin = sanitize_text_field( $_POST['plugin'] ?? '' );
		
		if ( empty( $plugin ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid plugin', 'wp-team-manager' ) ] );
		}
		
		// Get total count
		$plugins = self::detect_plugins();
		if ( empty( $plugins[ $plugin ] ) || ! $plugins[ $plugin ]['present'] ) {
			wp_send_json_error( [ 'message' => __( 'Plugin not found', 'wp-team-manager' ) ] );
		}
		
		$cpt = $plugins[ $plugin ]['cpt'];
		$total = $this->get_post_count( $cpt );
		
		// Initialize progress
		$progress = [
			'plugin' => $plugin,
			'total' => $total,
			'processed' => 0,
			'migrated' => 0,
			'failed' => 0,
			'status' => 'running',
			'started' => time(),
			'last_update' => time(),
			'errors' => [],
			'migrated_titles' => []
		];
		
		set_transient( 'wtm_migration_progress_' . $plugin, $progress, HOUR_IN_SECONDS );
		
		wp_send_json_success( [
			'total' => $total,
			'message' => sprintf( __( 'Starting migration of %d posts...', 'wp-team-manager' ), $total )
		] );
	}
	
	/**
	 * AJAX: Migrate a batch of posts
	 */
	public function ajax_migrate_batch() {
		check_ajax_referer( 'wtm_migration_action', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied', 'wp-team-manager' ) ] );
		}
		
		$plugin = sanitize_text_field( $_POST['plugin'] ?? '' );
		$offset = intval( $_POST['offset'] ?? 0 );
		$batch_size = 20; // Process 20 posts per batch
		
		// Get progress
		$progress = get_transient( 'wtm_migration_progress_' . $plugin );
		if ( ! $progress ) {
			wp_send_json_error( [ 'message' => __( 'Migration session expired', 'wp-team-manager' ) ] );
		}
		
		// Run batch migration
		$result = $this->migrate_batch( $plugin, $offset, $batch_size );
		
		// Update progress
		$progress['processed'] += $result['processed'];
		$progress['migrated'] += $result['migrated'];
		$progress['failed'] += $result['failed'];
		$progress['last_update'] = time();
		$progress['errors'] = array_merge( $progress['errors'], $result['errors'] );
		$progress['migrated_titles'] = array_merge( $progress['migrated_titles'], $result['titles'] );
		
		// Check if complete
		if ( $progress['processed'] >= $progress['total'] ) {
			$progress['status'] = 'complete';
		}
		
		set_transient( 'wtm_migration_progress_' . $plugin, $progress, HOUR_IN_SECONDS );
		
		wp_send_json_success( [
			'processed' => $progress['processed'],
			'migrated' => $progress['migrated'],
			'failed' => $progress['failed'],
			'total' => $progress['total'],
			'complete' => $progress['status'] === 'complete',
			'titles' => $result['titles']
		] );
	}
	
	/**
	 * AJAX: Get current migration progress
	 */
	public function ajax_get_progress() {
		check_ajax_referer( 'wtm_migration_action', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied', 'wp-team-manager' ) ] );
		}
		
		$plugin = sanitize_text_field( $_POST['plugin'] ?? '' );
		$progress = get_transient( 'wtm_migration_progress_' . $plugin );
		
		if ( ! $progress ) {
			wp_send_json_error( [ 'message' => __( 'No migration in progress', 'wp-team-manager' ) ] );
		}
		
		wp_send_json_success( $progress );
	}
	
	/**
	 * Migrate a batch of posts
	 *
	 * @param string $plugin Plugin key
	 * @param int $offset Starting offset
	 * @param int $limit Number of posts to process
	 * @return array Results with processed, migrated, failed counts
	 */
	private function migrate_batch( $plugin, $offset, $limit ) {
		$plugins = self::detect_plugins();
		if ( empty( $plugins[ $plugin ] ) || ! $plugins[ $plugin ]['present'] ) {
			return [
				'processed' => 0,
				'migrated' => 0,
				'failed' => 0,
				'errors' => [],
				'titles' => []
			];
		}
		
		$cpt = $plugins[ $plugin ]['cpt'];
		$mapping = self::get_mappings( $plugin );
		
		$args = [
			'post_type' => $cpt,
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'offset' => $offset,
			'no_found_rows' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];
		
		$query = new \WP_Query( $args );
		$result = [
			'processed' => 0,
			'migrated' => 0,
			'failed' => 0,
			'errors' => [],
			'titles' => []
		];
		
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$src_id = get_the_ID();
				$result['processed']++;
				
				// Check if already migrated
				$existing_migration = get_post_meta( $src_id, '_wtm_migration_id', true );
				if ( $existing_migration && get_post( $existing_migration ) ) {
					// Already migrated, skip
					continue;
				}
				
				// Migrate post
				$new_id = $this->migrate_single_post( $src_id, $mapping );
				
				if ( is_wp_error( $new_id ) ) {
					$result['failed']++;
					$result['errors'][] = $new_id->get_error_message();
				} elseif ( $new_id ) {
					$result['migrated']++;
					$result['titles'][] = get_the_title( $new_id );
					
					// Mark as migrated
					update_post_meta( $src_id, '_wtm_migration_id', $new_id );
					update_post_meta( $src_id, '_wtm_migration_date', current_time( 'mysql' ) );
					update_post_meta( $new_id, '_wtm_migrated_from', $src_id );
					update_post_meta( $new_id, '_wtm_source_plugin', $plugin );
				}
			}
			wp_reset_postdata();
		}
		
		return $result;
	}
	
	/**
	 * Migrate a single post
	 *
	 * @param int $src_id Source post ID
	 * @param array $mapping Field mapping
	 * @return int|WP_Error New post ID or error
	 */
	private function migrate_single_post( $src_id, $mapping ) {
		$post_title_key = isset( $mapping['post_title'] ) ? $mapping['post_title'] : 'post_title';
		$post_content_key = isset( $mapping['tm_long_bio'] ) ? $mapping['tm_long_bio'] : 'post_content';
		
		$postarr = [
			'post_type' => 'team_manager',
			'post_status' => 'publish',
			'post_title' => get_post_field( $post_title_key, $src_id ),
			'post_content' => get_post_field( $post_content_key, $src_id ),
		];
		
		$new_id = wp_insert_post( $postarr, true );
		
		if ( is_wp_error( $new_id ) ) {
			return $new_id;
		}
		
		if ( ! $new_id ) {
			return new \WP_Error( 'migration_failed', __( 'Failed to create post', 'wp-team-manager' ) );
		}
		
		// Handle featured image
		if ( ! empty( $mapping['image'] ) ) {
			$image_id = get_post_meta( $src_id, $mapping['image'], true );
			if ( $image_id ) {
				self::maybe_sideload_image( $image_id, $new_id );
			}
		}
		
		// Copy meta fields
		foreach ( $mapping as $dest_key => $src_key ) {
			if ( in_array( $dest_key, [ 'post_title', 'tm_long_bio', 'image' ], true ) ) {
				continue;
			}
			
			$value = '';
			if ( in_array( $src_key, [ 'post_title', 'post_content' ], true ) ) {
				$value = get_post_field( $src_key, $src_id );
			} else {
				$value = get_post_meta( $src_id, $src_key, true );
			}
			
			if ( $value !== '' && $value !== false && $value !== null ) {
				update_post_meta( $new_id, $dest_key, maybe_unserialize( $value ) );
			}
		}
		
		return $new_id;
	}

	/**
	 * AJAX: Cancel migration
	 */
	public function ajax_cancel_migration() {
		check_ajax_referer( 'wtm_migration_action', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied', 'wp-team-manager' ) ] );
		}
		
		$plugin = sanitize_text_field( $_POST['plugin'] ?? '' );
		
		// Delete progress transient
		delete_transient( 'wtm_migration_progress_' . $plugin );
		
		wp_send_json_success( [ 'message' => __( 'Migration cancelled', 'wp-team-manager' ) ] );
	}
	
	/**
	 * Get incomplete migrations
	 *
	 * @return array Array of incomplete migrations
	 */
	private function get_incomplete_migrations() {
		$plugins = self::detect_plugins();
		$incomplete = [];
		
		foreach ( $plugins as $key => $info ) {
			$progress = get_transient( 'wtm_migration_progress_' . $key );
			
			if ( $progress && $progress['status'] === 'running' ) {
				// Check if migration is stale (older than 1 hour)
				if ( ( time() - $progress['last_update'] ) > HOUR_IN_SECONDS ) {
					// Mark as stale but keep it
					$progress['status'] = 'stale';
				}
				$incomplete[ $key ] = $progress;
			}
		}
		
		return $incomplete;
	}
	
	/**
	 * Clear plugin detection cache
	 */
	public function clear_plugin_cache() {
		delete_transient( 'wtm_detected_plugins' );
	}
	
	/**
	 * Display admin notices for migration results
	 */
	public function admin_notices() {
		// Only show migration notices if we have results
		if ( ! empty( $this->migrated_count ) && $this->migrated_count > 0 ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p><ul>',
				esc_html( sprintf( _n( 'Migration completed. %d post migrated.', 'Migration completed. %d posts migrated.', $this->migrated_count, 'wp-team-manager' ), $this->migrated_count ) )
			);
			$count = 0;
			foreach ( $this->migrated_titles as $title ) {
				if ( $count >= 10 ) {
					echo '<li>…</li>';
					break;
				}
				echo '<li>' . esc_html( $title ) . '</li>';
				$count++;
			}
			echo '</ul></div>';
		} elseif ( isset( $_POST['wtm_migrate_plugin'], $_POST['wtm_migration_nonce'] ) && 
		           wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wtm_migration_nonce'] ) ), 'wtm_migration_action' ) ) {
			// Only show warning if nonce is valid
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'No posts were migrated.', 'wp-team-manager' ) . '</p></div>';
		}
	}
	
	/**
	 * Detect supported plugins and if their CPTs exist.
	 * Results are cached for 1 hour.
	 *
	 * @return array
	 */
	public static function detect_plugins() {
		// Check cache first
		$cached = get_transient( 'wtm_detected_plugins' );
		if ( $cached !== false ) {
			return $cached;
		}
		
		$supported = [
			'tlp-team' => [
				'label' => 'Team – Team Members Showcase Plugin',
				'cpt'   => 'team',
			],
			'team-showcase-post' => [
				'label' => 'Team Grid Showcase',
				'cpt'   => 'team_showcase_post',
			],
			'gs-team' => [
				'label' => 'Team Members',
				'cpt'   => 'gs_team',
			],
		];
		
		$found = [];
		foreach ( $supported as $key => $info ) {
			$found[ $key ] = [
				'label' => $info['label'],
				'cpt'   => $info['cpt'],
				'present' => post_type_exists( $info['cpt'] ),
			];
		}
		
		// Cache for 1 hour
		set_transient( 'wtm_detected_plugins', $found, HOUR_IN_SECONDS );
		
		return $found;
	}

	/**
	 * Get default field mapping for a plugin.
	 *
	 * Mappings define how external plugin meta fields correspond
	 * to wp-team-manager fields, enabling correct data transfer.
	 *
	 * @param string $plugin
	 * @return array
	 */
	public static function get_mappings( $plugin ) {
		$defaults = [
			'tlp-team' => [
				'post_title'        => 'post_title',
				'tm_designation'    => '_tlp_designation',
				'tm_long_bio'       => 'post_content',
				'_thumbnail_id'     => '_thumbnail_id',
				'tm_email'          => 'email',
				'tm_experience_year'=> 'experience_year',
				'tm_fax'            => 'fax',
				'tm_location'       => 'location',
				'tm_mobile'         => 'mobile',
				'tm_telephone'      => 'telephone',
				'tm_short_bio'      => 'short_bio',
				'tm_social'         => 'social',
				'tm_web_url'         => 'web_url',
				'tm_hire_me_url'     => 'ttp_hire_me',
				'tm_custom_detail_url' => 'ttp_custom_detail_url',
				'tm_resume_url'      => 'ttp_my_resume'
			],
		
			'team-showcase-post' => [
				'post_title'        => 'post_title',
				'tm_jtitle'    => '_member_designation',
				'tm_long_bio'       => 'post_content',
				'_thumbnail_id'     => '_thumbnail_id',
				'tm_experience_year'=> '_member_experience',
			],
				'gs-team' => [
				'post_title'        => 'post_title',
				'tm_jtitle'    => '_gs_des',
				'tm_long_bio'       => 'post_content',
				'_thumbnail_id'     => '_thumbnail_id',
				'tm_email'          => '_gs_email',
				'tm_experience_year'=> 'experience_year',
				'tm_location'       => '_gs_address',
				'tm_mobile'         => '_gs_cell',
				'tm_telephone'      => '_gs_land'
			],
		
		];
		return isset( $defaults[ $plugin ] ) ? $defaults[ $plugin ] : [];
	}

	/**
	 * Run migration from source CPT to wp-team-manager CPT.
	 *
	 * Loops through posts of the source custom post type,
	 * copies data and meta fields according to the mapping,
	 * and imports them into the 'team_manager' post type.
	 *
	 * Uses batch processing to prevent memory exhaustion on large datasets.
	 *
	 * @param string $plugin
	 * @param array $map
	 * @return array Migrated post IDs.
	 */
	public static function run_migration( $plugin, $map = [] ) {
		$plugins = self::detect_plugins();
		if ( empty( $plugins[ $plugin ] ) || empty( $plugins[ $plugin ]['present'] ) ) {
			return [];
		}
		$cpt = $plugins[ $plugin ]['cpt'];
		$mapping = $map ? $map : self::get_mappings( $plugin );

		// Use batch processing to prevent memory issues
		$batch_size = 50;
		$paged = 1;
		$migrated = [];
		
		do {
			$args = [
				'post_type'      => $cpt,
				'post_status'    => 'publish',
				'posts_per_page' => $batch_size,
				'paged'          => $paged,
				'no_found_rows'  => true, // Performance optimization
				'update_post_meta_cache' => false, // Performance optimization
				'update_post_term_cache' => false, // Performance optimization
			];
			$query = new \WP_Query( $args );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$src_id = get_the_ID();

					// Create new team_manager post with mapped title and content
					$post_title_key = isset($mapping['post_title']) ? $mapping['post_title'] : 'post_title';
					$post_content_key = isset($mapping['tm_long_bio']) ? $mapping['tm_long_bio'] : 'post_content';

					$postarr = [
						'post_type'   => 'team_manager',
						'post_status' => 'publish',
						'post_title'  => get_post_field( $post_title_key, $src_id ),
						'post_content'=> get_post_field( $post_content_key, $src_id ),
					];
					$new_id = wp_insert_post( $postarr, true ); // Return WP_Error on failure

					if ( is_wp_error( $new_id ) ) {
						// Log error but continue with other posts
						error_log( 'WTM Migration Error: ' . $new_id->get_error_message() );
						continue;
					}
					
					if ( $new_id ) {
					// Handle featured image
					if ( ! empty( $mapping['image'] ) ) {
						$image_id = get_post_meta( $src_id, $mapping['image'], true );
						if ( $image_id ) {
							self::maybe_sideload_image( $image_id, $new_id );
						}
					}

					// Loop through all mappings except post_title, tm_long_bio, and image
					foreach ( $mapping as $dest_key => $src_key ) {
						if ( in_array( $dest_key, ['post_title', 'tm_long_bio', 'image'], true ) ) {
							continue;
						}
						$value = '';
						if ( in_array( $src_key, ['post_title', 'post_content'], true ) ) {
							$value = get_post_field( $src_key, $src_id );
						} else {
							$value = get_post_meta( $src_id, $src_key, true );
						}
						if ( $value !== '' && $value !== false && $value !== null ) {
							update_post_meta( $new_id, $dest_key, maybe_unserialize( $value ) );
						}
					}

						$migrated[] = $new_id;
					}
				}
				wp_reset_postdata();
			}
			
			$paged++;
			
		} while ( $query->have_posts() );
		
		return $migrated;
	}

	/**
	 * Copy or sideload image as featured image.
	 *
	 * Handles both attachment IDs and URLs, ensuring a proper
	 * featured image is set for the migrated post.
	 *
	 * @param int|string $image_id Attachment ID or image URL
	 * @param int $post_id The post to set the featured image for
	 */
	public static function maybe_sideload_image( $image_id, $post_id ) {
		// If image is an attachment, just set as featured
		if ( get_post_type( $image_id ) === 'attachment' ) {
			set_post_thumbnail( $post_id, $image_id );
			return;
		}
		// If image is a URL, sideload
		$image_url = false;
		if ( filter_var( $image_id, FILTER_VALIDATE_URL ) ) {
			$image_url = $image_id;
		} else {
			$url = wp_get_attachment_url( $image_id );
			if ( $url ) {
				$image_url = $url;
			}
		}
		if ( $image_url ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			$att_id = media_sideload_image( $image_url, $post_id, null, 'id' );
			if ( ! is_wp_error( $att_id ) ) {
				set_post_thumbnail( $post_id, $att_id );
			}
		}
	}

	/**
	 * Get post count for a post type with caching
	 *
	 * @param string $post_type
	 * @return int
	 */
	private function get_post_count( $post_type ) {
		// Return cached value if available
		if ( isset( $this->post_counts[ $post_type ] ) ) {
			return $this->post_counts[ $post_type ];
		}
		
		// Get and cache the count
		$count = wp_count_posts( $post_type );
		$this->post_counts[ $post_type ] = isset( $count->publish ) ? $count->publish : 0;
		
		return $this->post_counts[ $post_type ];
	}
}
