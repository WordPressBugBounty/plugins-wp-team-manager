<?php
namespace DWL\Wtm\AI\Modules;

use DWL\Wtm\Classes\Log;

defined( 'ABSPATH' ) || exit;

class Telegram_Agent {

    public function __construct() {
        add_action( 'save_post_team_manager', [ $this, 'maybe_notify_telegram' ], 10, 3 );
    }

    /**
     * Notify Telegram when a team member is published or updated (no AI).
     */
    public function maybe_notify_telegram( $post_id, $post, $update ) {
        // 1) Basic sanity checks
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }
        if ( 'team_manager' !== $post->post_type ) {
            return;
        }
        if ( 'publish' !== get_post_status( $post_id ) ) {
            return;
        }
        if ( ! $this->is_enabled() ) {
            return; // module toggle off
        }

        // 2) Debounce duplicate sends for rapid consecutive saves
        $lock_key = $this->get_lock_key( $post_id );
        if ( get_transient( $lock_key ) ) {
            if ( get_option( 'wtm_debug_log' ) ) {
                Log::debug( 'Telegram Agent debounced duplicate send', [ 'post_id' => $post_id ] );
            }
            return;
        }
        set_transient( $lock_key, 1, 15 ); // 15s debounce window

        // 3) Build message (MarkdownV2 safe)
        $event   = $update ? 'updated' : 'created';
        $name    = $this->escape_markdown_v2( get_the_title( $post_id ) );
        $link    = get_permalink( $post_id );
        $link_md = $this->escape_markdown_v2( $link );

        $message = "👤 *Team member {$this->escape_markdown_v2($event)}*\n{$name}\n🔗 {$link_md}";

        if ( get_option( 'wtm_debug_log' ) ) {
            Log::info( 'Telegram Agent triggered', [ 'post_id' => $post_id, 'event' => $event ] );
        }

        // 4) Send to Telegram
        $this->send_to_telegram( $message );
    }

    /**
     * Send a message to Telegram using a Bot Token and Chat ID.
     */
    protected function send_to_telegram( $message ) {
        $bot_token = trim( (string) get_option( 'wtm_telegram_bot_token' ) );
        $chat_id   = trim( (string) get_option( 'wtm_telegram_chat_id' ) );

        if ( empty( $bot_token ) || empty( $chat_id ) ) {
            if ( get_option( 'wtm_debug_log' ) ) {
                Log::error( 'Telegram Agent: missing bot token or chat ID.' );
            }
            return;
        }

        $url  = "https://api.telegram.org/bot{$bot_token}/sendMessage";
        $body = [
            'chat_id'    => $chat_id,
            'text'       => $message,
            'parse_mode' => 'MarkdownV2',
            'disable_web_page_preview' => true,
        ];

        if ( get_option( 'wtm_debug_log' ) ) {
            Log::debug( 'Telegram Agent: sending', [ 'chat_id' => $chat_id ] );
        }

        $response = wp_remote_post( $url, [
            'headers' => [ 'Content-Type' => 'application/x-www-form-urlencoded' ],
            'body'    => $body,
            'timeout' => 12,
            'blocking'=> true,
        ] );

        if ( is_wp_error( $response ) ) {
            if ( get_option( 'wtm_debug_log' ) ) {
                Log::error( 'Telegram Agent: request error', [ 'error' => $response->get_error_message() ] );
            }
            return;
        }

        $code = (int) wp_remote_retrieve_response_code( $response );
        if ( get_option( 'wtm_debug_log' ) ) {
            Log::info( 'Telegram Agent: sent', [ 'http_code' => $code ] );
        }
    }

    /**
     * Check if the telegram module is enabled in settings.
     *
     * @return bool
     */
    protected function is_enabled() {
        $enabled = get_option( 'wp_team_ai_enabled_modules', [] );
        if ( ! is_array( $enabled ) ) {
            return false;
        }
        return in_array( 'telegram', $enabled, true );
    }

    /**
     * Escape text for Telegram MarkdownV2.
     *
     * @param string $text
     * @return string
     */
    protected function escape_markdown_v2( $text ) {
        $text = (string) $text;
        $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
        $repl  = array_map( fn($c) => '\\' . $c, $chars );
        return str_replace( $chars, $repl, $text );
    }

    /**
     * Create a transient key for debouncing.
     *
     * @param int $post_id
     * @return string
     */
    protected function get_lock_key( $post_id ) {
        return 'wtm_tg_lock_' . (int) $post_id;
    }
}