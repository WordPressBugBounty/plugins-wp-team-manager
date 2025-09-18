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
	
	public function __construct() {
		add_action( 'admin_init', [ $this, 'handle_migration_action' ] );
		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
		add_action( 'admin_menu', [ $this, 'register_hidden_page' ] );
		
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
    }

	public function render_section() {
		$plugins = MigrationTools::detect_plugins();
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
			<div class="wtm-migration-wrapper">
				<h1><?php esc_html_e( 'Migrate from Other Team Plugins', 'wp-team-manager' ); ?></h1>
				<div class="wtm-migration-grid">
					<div class="wtm-card">
						<div class="dashicons dashicons-admin-users"></div>
						<p><?php esc_html_e( 'Easily migrate your team members from supported plugins into WP Team Manager.', 'wp-team-manager' ); ?></p>
					</div>
					<div class="wtm-card">
						<div class="dashicons dashicons-admin-plugins"></div>
						<p><?php esc_html_e( 'Detected plugins with compatible team member data will be listed below.', 'wp-team-manager' ); ?></p>
					</div>
					<div class="wtm-card">
						<div class="dashicons dashicons-migrate"></div>
						<p><?php esc_html_e( 'Migrations include team info, meta data, and profile photos where available.', 'wp-team-manager' ); ?></p>
					</div>
				</div>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'Please ensure you have backed up your database before running any migration.', 'wp-team-manager' ); ?></p>
				</div>
				<form method="post" action="">
					<?php wp_nonce_field( 'wtm_migration_action', 'wtm_migration_nonce' ); ?>
					<table class="widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Plugin', 'wp-team-manager' ); ?></th>
								<th><?php esc_html_e( 'Custom Post Type', 'wp-team-manager' ); ?></th>
								<th><?php esc_html_e( 'Status', 'wp-team-manager' ); ?></th>
								<th><?php esc_html_e( 'Action', 'wp-team-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $plugins as $key => $info ) : ?>
								<tr>
									<td><?php echo esc_html( $info['label'] ); ?></td>
									<td><?php echo esc_html( $info['cpt'] ); ?></td>
									<td>
										<?php
										if ( $info['present'] ) {
											echo '<span style="color:green;">' . esc_html__( 'Detected', 'wp-team-manager' ) . '</span>';
										} else {
											echo '<span style="color:red;">' . esc_html__( 'Not Detected', 'wp-team-manager' ) . '</span>';
										}
										?>
									</td>
									<td>
										<?php if ( $info['present'] ) : ?>
											<button type="submit" name="wtm_migrate_plugin" value="<?php echo esc_attr( $key ); ?>" class="button button-primary">
												<?php esc_html_e( 'Migrate', 'wp-team-manager' ); ?>
											</button>
										<?php else : ?>
											<span>–</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</form>
			</div>
		</div>
		<?php
	}

	public function handle_migration_action() {
		if ( ! isset( $_POST['wtm_migrate_plugin'], $_POST['wtm_migration_nonce'] ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wtm_migration_nonce'] ) ), 'wtm_migration_action' ) ) {
			return;
		}

		$plugin = sanitize_text_field( wp_unslash( $_POST['wtm_migrate_plugin'] ) );
		$migrated = MigrationTools::run_migration( $plugin );
		$this->migrated_count = count( $migrated );
		$this->migrated_titles = [];
		if ( $this->migrated_count > 0 ) {
			foreach ( $migrated as $post_id ) {
				$title = get_the_title( $post_id );
				if ( $title ) {
					$this->migrated_titles[] = $title;
				}
			}
		}

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );
	}

	public function admin_notices() {
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
		} elseif ( isset( $_POST['wtm_migrate_plugin'] ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'No posts were migrated.', 'wp-team-manager' ) . '</p></div>';
		}
	}
	
	/**
	 * Detect supported plugins and if their CPTs exist.
	 *
	 * @return array
	 */
	public static function detect_plugins() {
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

		$args = [
			'post_type'      => $cpt,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		];
		$query = new \WP_Query( $args );
		$migrated = [];
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
				$new_id = wp_insert_post( $postarr );

				if ( $new_id && ! is_wp_error( $new_id ) ) {
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
}
