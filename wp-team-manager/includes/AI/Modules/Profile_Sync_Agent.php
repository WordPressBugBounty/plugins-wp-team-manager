<?php
namespace DWL\Wtm\AI\Modules;

use DWL\Wtm\Classes\Log;

defined( 'ABSPATH' ) || exit;

/**
 * Profile_Sync_Agent
 *
 * Sends team profile data to an external webhook (e.g., n8n) whenever
 * a team member is created or updated. Designed for FREE tier (no AI credits).
 */
class Profile_Sync_Agent {

	/**
	 * Option keys
	 */
	const OPT_WEBHOOK_URL    = 'wtm_sync_webhook_url';        // e.g. https://n8n.searchjetengine.com/webhook/team-sync
	const OPT_WEBHOOK_SECRET = 'wtm_sync_webhook_secret';     // optional HMAC secret for signing requests
	const OPT_ENABLE_STATUS  = 'wtm_sync_status_only';        // if set truthy, only sync when status is 'publish'

	public function __construct() {
		// Trigger on save of team_manager post type
		add_action( 'save_post_team_manager', [ $this, 'maybe_sync' ], 10, 3 );
	}

	/**
	 * Determines whether to sync and dispatches the webhook call.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 * @param bool     $update
	 */
	public function maybe_sync( $post_id, $post, $update ) {
		// Bail on autosave/revision
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Ensure correct post type
		if ( 'team_manager' !== $post->post_type ) {
			return;
		}

		$only_publish = (bool) get_option( self::OPT_ENABLE_STATUS, true );
		$status       = get_post_status( $post_id );
		if ( $only_publish && 'publish' !== $status ) {
			return; // only sync published updates
		}

		$webhook = trim( (string) get_option( self::OPT_WEBHOOK_URL, '' ) );
		if ( empty( $webhook ) || ! filter_var( $webhook, FILTER_VALIDATE_URL ) ) {
			return; // no valid endpoint configured
		}

		$payload = $this->build_payload( $post_id, $post, $update );
		$this->send_webhook( $webhook, $payload );
		Log::info('Profile Sync Agent triggered', [
			'post_id' => $post_id,
			'webhook' => $webhook,
			'status'  => get_post_status($post_id),
			'event'   => $update ? 'updated' : 'created'
		]);
	}

	/**
	 * Build the payload for the webhook.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 * @param bool     $update
	 * @return array
	 */
	protected function build_payload( $post_id, $post, $update ) {
		// Basic fields
		$data = [
			'event'        => $update ? 'updated' : 'created',
			'post_id'      => (int) $post_id,
			'post_type'    => $post->post_type,
			'status'       => get_post_status( $post_id ),
			'title'        => get_the_title( $post_id ),
			'permalink'    => get_permalink( $post_id ),
			'excerpt'      => get_the_excerpt( $post_id ),
			'author_id'    => (int) $post->post_author,
			'author_name'  => get_the_author_meta( 'display_name', $post->post_author ),
			'site_url'     => home_url(),
			'site_name'    => get_bloginfo( 'name' ),
			'updated_at'   => current_time( 'mysql' ),
		];

		// Featured image
		$thumb_id = get_post_thumbnail_id( $post_id );
		if ( $thumb_id ) {
			$src = wp_get_attachment_image_src( $thumb_id, 'full' );
			$data['featured_image'] = is_array( $src ) ? $src[0] : '';
		}

		// Meta (limit to safe scalar values to avoid huge payloads)
		$raw_meta = get_post_meta( $post_id );
		$meta     = [];
		foreach ( $raw_meta as $key => $vals ) {
			if ( is_protected_meta( $key, 'post' ) ) {
				continue;
			}
			$value = is_array( $vals ) ? reset( $vals ) : $vals;
			if ( is_scalar( $value ) ) {
				$meta[ $key ] = $value;
			}
		}
		$data['meta'] = $meta;

		/**
		 * Filter the outgoing sync payload.
		 *
		 * @param array   $data    The payload array.
		 * @param int     $post_id The post ID.
		 * @param \WP_Post $post   The post object.
		 * @param bool    $update  Whether this is an update.
		 */
		return apply_filters( 'wtm_ai_sync_payload', $data, $post_id, $post, $update );
	}

	/**
	 * Send the payload to the external webhook as JSON.
	 * Adds optional HMAC signature header if a secret is configured.
	 *
	 * @param string $url
	 * @param array  $payload
	 */
	protected function send_webhook( $url, array $payload ) {
		$body     = wp_json_encode( $payload );
		$headers  = [ 'Content-Type' => 'application/json' ];
		$secret   = (string) get_option( self::OPT_WEBHOOK_SECRET, '' );

		if ( ! empty( $secret ) && is_string( $secret ) ) {
			$headers['X-WTM-Signature'] = hash_hmac( 'sha256', $body, $secret );
		}

		$args = [
			'headers'   => $headers,
			'body'      => $body,
			'timeout'   => 10,
			'blocking'  => false, // fire-and-forget for speed
		];
		Log::debug('Sending webhook', [
			'url' => $url,
			'headers' => $headers,
			'payload' => $payload
		]);

		$response = wp_remote_post( esc_url_raw( $url ), $args );

		if ( is_wp_error( $response ) ) {
			Log::error('Profile Sync Agent webhook error', [
				'error' => $response->get_error_message(),
				'url'   => $url
			]);
		} else {
			Log::info('Profile Sync Agent webhook sent successfully', [
				'url'      => $url,
				'httpCode' => wp_remote_retrieve_response_code($response)
			]);
		}
	}
}