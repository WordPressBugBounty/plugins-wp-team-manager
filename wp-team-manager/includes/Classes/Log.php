<?php
namespace DWL\Wtm\Classes;

defined( 'ABSPATH' ) || exit;

/**
 * Lightweight file logger for WP Team Manager.
 *
 * Usage:
 *   Log::info( 'Profile updated', [ 'post_id' => 123 ] );
 *   Log::error( 'Webhook failed', [ 'error' => $wp_error->get_error_message() ] );
 *
 * Notes:
 * - Logging is conditional on the option `wtm_debug_log` (boolean).
 * - Default log file lives in uploads: /wp-content/uploads/wp-team-manager/logs/wtm.log
 * - File rotates at ~5 MB (wtm.log.1 kept as previous).
 */
class Log {
	/** @var string Option key to toggle logging */
	const OPT_ENABLED = 'wtm_debug_log';
	/** @var string Option key to override log file path */
	const OPT_PATH    = 'wtm_debug_log_path';

	/** @var int Max log size before rotation (bytes) ~5MB */
	const MAX_SIZE = 5242880; // 5 * 1024 * 1024

	/**
	 * Write a DEBUG message
	 */
	public static function debug( $message, array $context = [] ) { self::write( 'DEBUG', $message, $context ); }
	/** Write an INFO message */
	public static function info( $message, array $context = [] )  { self::write( 'INFO',  $message, $context ); }
	/** Write a WARNING message */
	public static function warning( $message, array $context = [] ) { self::write( 'WARNING', $message, $context ); }
	/** Write an ERROR message */
	public static function error( $message, array $context = [] ) { self::write( 'ERROR', $message, $context ); }

	/**
	 * Core writer – respects enable flag, ensures directory, rotates file, and appends line.
	 *
	 * @param string $level
	 * @param string $message
	 * @param array  $context
	 */
	public static function write( $level, $message, array $context = [] ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		$path = self::get_log_path();
		self::ensure_directory( $path );
		self::maybe_rotate( $path );

		$line = self::format_line( $level, $message, $context );

		// Suppress warnings to avoid front-end noise; fail silently if permissions are missing.
		@file_put_contents( $path, $line, FILE_APPEND | LOCK_EX );
	}

	/** Check if logging is enabled via option */
	public static function is_enabled() {
		$enabled = get_option( self::OPT_ENABLED, false );
		return (bool) apply_filters( 'wtm_debug_log_enabled', $enabled );
	}

	/** Build absolute path to log file; defaults to uploads dir if not overridden */
	public static function get_log_path() {
		$override = trim( (string) get_option( self::OPT_PATH, '' ) );
		if ( ! empty( $override ) ) {
			return wp_normalize_path( $override );
		}

		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wp-team-manager/logs/';
		return wp_normalize_path( $dir . 'wtm.log' );
	}

	/** Ensure directory exists and is writable */
	protected static function ensure_directory( $path ) {
		$dir = dirname( $path );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
	}

	/** Rotate log file if it exceeds MAX_SIZE */
	protected static function maybe_rotate( $path ) {
		if ( file_exists( $path ) && filesize( $path ) > self::MAX_SIZE ) {
			$rotated = $path . '.1';
			// Remove previous rotated file if exists
			if ( file_exists( $rotated ) ) {
				@unlink( $rotated );
			}
			@rename( $path, $rotated );
		}
	}

	/** Format a single log line */
	protected static function format_line( $level, $message, array $context ) {
		$ts   = wp_date( 'Y-m-d H:i:s' );
		$host = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : php_uname( 'n' );
		$req  = isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] . ' ' . ( $_SERVER['REQUEST_URI'] ?? '' ) : '';

		$ctx  = '';
		if ( ! empty( $context ) ) {
			// Safely encode context – avoid objects/resources; keep size tame
			$sanitized = [];
			foreach ( $context as $k => $v ) {
				if ( is_scalar( $v ) || is_null( $v ) ) {
					$sanitized[ $k ] = $v;
				} elseif ( is_array( $v ) ) {
					$sanitized[ $k ] = self::limit_json( $v );
				} elseif ( $v instanceof \WP_Error ) {
					$sanitized[ $k ] = $v->get_error_message();
				} else {
					$sanitized[ $k ] = gettype( $v );
				}
			}
			$ctx = ' ' . wp_json_encode( $sanitized );
		}

		return sprintf( "[%s] [%s] %s %s%s\n", $ts, $level, $host, trim( (string) $message ), $ctx );
	}

	/** Reduce depth/size of JSON to avoid megabyte lines */
	protected static function limit_json( array $data, $maxDepth = 2, $maxLen = 5000 ) {
		$json = wp_json_encode( self::truncate_depth( $data, $maxDepth ) );
		if ( strlen( $json ) > $maxLen ) {
			$json = substr( $json, 0, $maxLen ) . '…';
		}
		return $json;
	}

	protected static function truncate_depth( $data, $depth, $seen = 0 ) {
		if ( $depth <= 0 || $seen > 1000 ) {
			return is_array( $data ) ? array_keys( $data ) : (array) $data; // keys only
		}
		$out = [];
		foreach ( (array) $data as $k => $v ) {
			if ( is_scalar( $v ) || is_null( $v ) ) {
				$out[ $k ] = $v;
			} elseif ( is_array( $v ) ) {
				$out[ $k ] = self::truncate_depth( $v, $depth - 1, $seen + 1 );
			} elseif ( $v instanceof \WP_Error ) {
				$out[ $k ] = $v->get_error_message();
			} else {
				$out[ $k ] = gettype( $v );
			}
		}
		return $out;
	}
}