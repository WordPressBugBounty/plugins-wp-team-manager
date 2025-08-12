<?php
namespace DWL\Wtm\AI\Admin;

use DWL\Wtm\Classes\Helper;

defined( 'ABSPATH' ) || exit;

class AdminUI {

    protected $option_key = 'wp_team_ai_enabled_modules';


    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_settings_tab' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_post_wtm_tg_test', [ $this, 'handle_telegram_test' ] );
    }

    public function register_settings_tab() {
        add_submenu_page(
            'edit.php?post_type=team_manager',
            __( 'AI Agents', 'wp-team-manager' ),
            __( 'AI Agents', 'wp-team-manager' ),
            'manage_options',
            'team-ai-agents',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'wp_team_ai_agent_settings', $this->option_key, [
            'type' => 'array',
            'sanitize_callback' => [ $this, 'sanitize_enabled_modules' ],
            'default' => [],
        ] );

        register_setting( 'wp_team_ai_agent_settings', 'wtm_telegram_bot_token', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ] );

        register_setting( 'wp_team_ai_agent_settings', 'wtm_telegram_chat_id', [
            'type' => 'string',
            'sanitize_callback' => [ $this, 'sanitize_chat_id' ],
            'default' => '',
        ] );

        register_setting( 'wp_team_ai_agent_settings', 'wtm_sync_webhook_url', [
            'type' => 'string',
            'sanitize_callback' => [ $this, 'sanitize_url' ],
            'default' => '',
        ] );

        register_setting( 'wp_team_ai_agent_settings', 'wtm_sync_webhook_secret', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ] );

        register_setting( 'wp_team_ai_agent_settings', 'wtm_sync_status_only', [
            'type' => 'boolean',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ] );

        // Pro FAQ settings (OpenAI)
        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_openai_api_key', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ] );

        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_openai_model', [
            'type' => 'string',
            'sanitize_callback' => [ $this, 'sanitize_model' ],
            'default' => 'gpt-4o-mini',
        ] );

        // Pro Slack settings
        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_slack_webhook_url', [
            'type' => 'string',
            'sanitize_callback' => [ $this, 'sanitize_url' ],
            'default' => '',
        ] );
        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_slack_channel', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ] );

        // Pro Onboarding Guide: simple message/template
        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_onboarding_message', [
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => '',
        ] );

        // Pro Conditional Notifier: rules JSON (future use)
        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_conditional_rules', [
            'type' => 'string',
            'sanitize_callback' => 'wp_kses_post',
            'default' => '',
        ] );

        // Pro Activity Tracker: target endpoint (future use)
        register_setting( 'wp_team_ai_agent_settings', 'wtm_pro_activity_target', [
            'type' => 'string',
            'sanitize_callback' => [ $this, 'sanitize_url' ],
            'default' => '',
        ] );
    }

    public function sanitize_enabled_modules( $input ) {
        $valid = [ 'slack', 'telegram', 'sync', 'faq', 'onboarding', 'conditional_notifier', 'activity_tracker' ];
        return array_values( array_intersect( (array) $input, $valid ) );
    }

    public function sanitize_chat_id( $value ) {
        $value = trim( (string) $value );
        // Allow numbers and leading '-' for channels/groups
        if ( $value === '' ) { return ''; }
        if ( preg_match( '/^-?[0-9]+$/', $value ) ) { return $value; }
        // Fallback: strip non-digits (keeps '-')
        return ltrim( preg_replace( '/[^0-9\-]/', '', $value ) );
    }

    public function sanitize_url( $value ) {
        $value = trim( (string) $value );
        if ( $value === '' ) { return ''; }
        return esc_url_raw( $value );
    }

    // Pro: Sanitize OpenAI model for FAQ Bot
    public function sanitize_model( $value ) {
        $value = trim( (string) $value );
        $allowed = [ 'gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini', 'gpt-4.1' ];
        if ( in_array( $value, $allowed, true ) ) { return $value; }
        return 'gpt-4o-mini';
    }

     public function handle_telegram_test() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized', 'wp-team-manager' ) );
        }
        check_admin_referer( 'wtm_tg_test_action', 'wtm_tg_test_nonce' );

        $token  = get_option( 'wtm_telegram_bot_token', '' );
        $chat_id = get_option( 'wtm_telegram_chat_id', '' );

        if ( empty( $token ) || empty( $chat_id ) ) {
            wp_redirect( add_query_arg( 'wtm_tg_test', rawurlencode( 'Missing token or chat ID' ), wp_get_referer() ) );
            exit;
        }

        $api_url = "https://api.telegram.org/bot{$token}/sendMessage";
        $body    = [
            'chat_id' => $chat_id,
            'text'    => '✅ Telegram test message from WP Team Manager',
            'parse_mode' => 'MarkdownV2'
        ];

        $response = wp_remote_post( $api_url, [
            'body'      => $body,
            'timeout'   => 15,
            'blocking'  => true,
        ] );

        if ( is_wp_error( $response ) ) {
            $msg = $response->get_error_message();
            wp_redirect( add_query_arg( 'wtm_tg_test', rawurlencode( "Error: {$msg}" ), wp_get_referer() ) );
            exit;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code === 200 ) {
            wp_redirect( add_query_arg( 'wtm_tg_test', 'success', wp_get_referer() ) );
        } else {
            $body_str = wp_remote_retrieve_body( $response );
            wp_redirect( add_query_arg( 'wtm_tg_test', rawurlencode( "HTTP {$code}: {$body_str}" ), wp_get_referer() ) );
        }
        exit;
    }
    
    public function render_settings_page() {
        $enabled = get_option( $this->option_key, [] );

        // Pro detection and missing-key notice for FAQ
        $pro_active = Helper::is_pro_active();
        if ( in_array( 'faq', $enabled, true ) && $pro_active ) {
            if ( empty( get_option( 'wtm_pro_openai_api_key', '' ) ) ) {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__( 'FAQ Bot is enabled but OpenAI API Key is missing.', 'wp-team-manager' )
                );
            }
        }

        // Telegram test notice
        if ( isset( $_GET['wtm_tg_test'] ) ) {
            $notice = sanitize_text_field( wp_unslash( $_GET['wtm_tg_test'] ) );
            if ( $notice === 'success' ) {
                echo '<div class="notice notice-success"><p>' . esc_html__( 'Telegram test message sent successfully!', 'wp-team-manager' ) . '</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>' . sprintf( esc_html__( 'Telegram test failed: %s', 'wp-team-manager' ), esc_html( $notice ) ) . '</p></div>';
            }
        }

        // Check for required fields when modules are enabled and output admin notices
        if ( in_array( 'telegram', $enabled, true ) ) {
            $tg_bot_token = get_option( 'wtm_telegram_bot_token', '' );
            $tg_chat_id = get_option( 'wtm_telegram_chat_id', '' );
            if ( empty( $tg_bot_token ) || empty( $tg_chat_id ) ) {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__( 'Telegram Notifier is enabled but Bot Token or Chat ID is missing.', 'wp-team-manager' )
                );
            }
        }
        if ( in_array( 'sync', $enabled, true ) ) {
            $sync_webhook_url = get_option( 'wtm_sync_webhook_url', '' );
            if ( empty( $sync_webhook_url ) ) {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__( 'Profile Sync Agent is enabled but Webhook URL is missing.', 'wp-team-manager' )
                );
            }
        }
        if ( in_array( 'slack', $enabled, true ) && $pro_active ) {
            $slack_webhook = get_option( 'wtm_pro_slack_webhook_url', '' );
            if ( empty( $slack_webhook ) ) {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__( 'Slack Notifier is enabled but Webhook URL is missing.', 'wp-team-manager' )
                );
            }
        }
        $upgrade_url = Helper::freemius_upgrade_url();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'AI Agent Modules', 'wp-team-manager' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'wp_team_ai_agent_settings' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e( 'FAQ Bot (Pro)', 'wp-team-manager' ); ?></th>
                        <td>
                            <?php $faq_enabled = in_array( 'faq', $enabled, true ); ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="faq" <?php checked( $faq_enabled ); ?> <?php disabled( ! $pro_active ); ?> />
                                <?php echo $pro_active
                                    ? esc_html__( 'Enable FAQ chatbot for team profiles.', 'wp-team-manager' )
                                    : esc_html__( 'Unlock FAQ chatbot in Pro.', 'wp-team-manager' ); ?>
                            </label>
                        </td>
                    </tr>
                    <?php $faq_enabled = in_array( 'faq', $enabled, true ); $disabled = ( ! $pro_active || ! $faq_enabled ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('FAQ Bot Settings', 'wp-team-manager'); ?></legend>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('OpenAI API Key', 'wp-team-manager'); ?></span>
                                    <input type="text" name="wtm_pro_openai_api_key" value="<?php echo esc_attr( get_option('wtm_pro_openai_api_key','') ); ?>" class="regular-text" <?php disabled( $disabled ); ?> />
                                </label>
                                <br/>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Model', 'wp-team-manager'); ?></span>
                                    <select name="wtm_pro_openai_model" <?php disabled( $disabled ); ?>>
                                        <?php $model = get_option('wtm_pro_openai_model', 'gpt-4o-mini'); ?>
                                        <option value="gpt-4o-mini" <?php selected( $model, 'gpt-4o-mini' ); ?>>gpt-4o-mini</option>
                                        <option value="gpt-4o" <?php selected( $model, 'gpt-4o' ); ?>>gpt-4o</option>
                                        <option value="gpt-4.1-mini" <?php selected( $model, 'gpt-4.1-mini' ); ?>>gpt-4.1-mini</option>
                                        <option value="gpt-4.1" <?php selected( $model, 'gpt-4.1' ); ?>>gpt-4.1</option>
                                    </select>
                                </label>
                                <p class="description">
                                    <?php echo wp_kses(
                                        __('You need an OpenAI API key. <a href="https://platform.openai.com/api-keys" target="_blank">Create/manage keys</a>. Learn about <a href="https://platform.openai.com/docs/models" target="_blank">available models</a>.', 'wp-team-manager'),
                                        [ 'a' => [ 'href' => [], 'target' => [] ] ]
                                    ); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Telegram Notifier', 'wp-team-manager' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="telegram" <?php checked( in_array( 'telegram', $enabled, true ) ); ?> />
                                <?php esc_html_e( 'Enable Telegram notifications for team updates.', 'wp-team-manager' ); ?>
                            </label>
                        </td>
                    </tr>
                    <?php $tg_enabled = in_array( 'telegram', $enabled, true ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('Telegram Settings', 'wp-team-manager'); ?></legend>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Bot Token', 'wp-team-manager'); ?></span>
                                    <input type="text" name="wtm_telegram_bot_token" value="<?php echo esc_attr( get_option('wtm_telegram_bot_token','') ); ?>" class="regular-text" <?php disabled( ! $tg_enabled ); ?> />
                                </label>
                                <br/>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Chat ID', 'wp-team-manager'); ?></span>
                                    <input type="text" name="wtm_telegram_chat_id" value="<?php echo esc_attr( get_option('wtm_telegram_chat_id','') ); ?>" class="regular-text" <?php disabled( ! $tg_enabled ); ?> />
                                </label>
                                <p class="description"><?php esc_html_e('Enter your Telegram Bot token and the target chat ID (can be negative for channels).', 'wp-team-manager'); ?></p>
                                <p class="description">
                                    <?php echo wp_kses(
                                        __('Learn how to <a href="https://core.telegram.org/bots#6-botfather" target="_blank">create a Telegram Bot</a> and <a href="https://stackoverflow.com/a/37396871" target="_blank">find your Chat ID</a>.', 'wp-team-manager'),
                                        array(
                                            'a' => array(
                                                'href' => array(),
                                                'target' => array(),
                                            ),
                                        )
                                    ); ?>
                                </p>
                                <?php
                                    $token   = get_option( 'wtm_telegram_bot_token', '' );
                                    $chat_id = get_option( 'wtm_telegram_chat_id', '' );
                                    $test_url = wp_nonce_url( admin_url( 'admin-post.php?action=wtm_tg_test' ), 'wtm_tg_test_action', 'wtm_tg_test_nonce' );
                                    $btn_attrs = ( empty( $token ) || empty( $chat_id ) || ! $tg_enabled ) ? 'class="button disabled" aria-disabled="true" onclick="return false;"' : 'class="button"';
                                ?>
                                <p>
                                    <a href="<?php echo esc_url( $test_url ); ?>" <?php echo $btn_attrs; ?>><?php esc_html_e( 'Send Test Message', 'wp-team-manager' ); ?></a>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Profile Sync Agent', 'wp-team-manager' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="sync" <?php checked( in_array( 'sync', $enabled, true ) ); ?> />
                                <?php esc_html_e( 'Sync team profile data to a third-party service via webhook.', 'wp-team-manager' ); ?>
                            </label>
                        </td>
                    </tr>
                    <?php $sync_enabled = in_array( 'sync', $enabled, true ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><?php esc_html_e('Profile Sync Settings', 'wp-team-manager'); ?></legend>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Webhook URL', 'wp-team-manager'); ?></span>
                                    <input type="url" name="wtm_sync_webhook_url" value="<?php echo esc_attr( get_option('wtm_sync_webhook_url','') ); ?>" class="regular-text code" <?php disabled( ! $sync_enabled ); ?> />
                                </label>
                                <br/>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('HMAC Secret (optional)', 'wp-team-manager'); ?></span>
                                    <input type="text" name="wtm_sync_webhook_secret" value="<?php echo esc_attr( get_option('wtm_sync_webhook_secret','') ); ?>" class="regular-text" <?php disabled( ! $sync_enabled ); ?> />
                                </label>
                                <br/>
                                <label>
                                    <input type="checkbox" name="wtm_sync_status_only" value="1" <?php checked( (bool) get_option('wtm_sync_status_only', true ) ); ?> <?php disabled( ! $sync_enabled ); ?> />
                                    <?php esc_html_e('Only sync when status is Published', 'wp-team-manager'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('Send profile payloads to your n8n/endpoint. If secret is set, requests include X-WTM-Signature (HMAC-SHA256).', 'wp-team-manager'); ?></p>
                                <p class="description">
                                    <?php echo wp_kses(
                                        __('See <a href="https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.webhook/" target="_blank">n8n Webhook docs</a> for setup instructions.', 'wp-team-manager'),
                                        array(
                                            'a' => array(
                                                'href' => array(),
                                                'target' => array(),
                                            ),
                                        )
                                    ); ?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Onboarding Guide (Pro)', 'wp-team-manager' ); ?></th>
                        <td>
                            <?php $onb_enabled = in_array( 'onboarding', $enabled, true ); ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="onboarding" <?php checked( $onb_enabled ); ?> <?php disabled( ! $pro_active ); ?> />
                                <?php echo $pro_active ? esc_html__( 'Enable Onboarding Guide agent.', 'wp-team-manager' ) : esc_html__( 'Unlock Onboarding Guide in Pro.', 'wp-team-manager' ); ?>
                                <?php if ( ! $pro_active ) : ?>
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-small" target="_blank"><?php esc_html_e( 'Upgrade', 'wp-team-manager' ); ?></a>
                                <?php endif; ?>
                            </label>
                        </td>
                    </tr>
                    <?php $disabled = ( ! $pro_active || ! $onb_enabled ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Welcome Message / Template', 'wp-team-manager'); ?></span>
                                    <textarea name="wtm_pro_onboarding_message" class="large-text" rows="3" <?php disabled( $disabled ); ?>><?php echo esc_textarea( get_option( 'wtm_pro_onboarding_message', '' ) ); ?></textarea>
                                </label>
                                <p class="description"><?php esc_html_e('Basic template used by the Onboarding Guide agent.', 'wp-team-manager'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( 'Conditional Notifier (Pro)', 'wp-team-manager' ); ?></th>
                        <td>
                            <?php $cond_enabled = in_array( 'conditional_notifier', $enabled, true ); ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="conditional_notifier" <?php checked( $cond_enabled ); ?> <?php disabled( ! $pro_active ); ?> />
                                <?php echo $pro_active ? esc_html__( 'Enable Conditional Notifier agent.', 'wp-team-manager' ) : esc_html__( 'Unlock Conditional Notifier in Pro.', 'wp-team-manager' ); ?>
                                <?php if ( ! $pro_active ) : ?>
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-small" target="_blank"><?php esc_html_e( 'Upgrade', 'wp-team-manager' ); ?></a>
                                <?php endif; ?>
                            </label>
                        </td>
                    </tr>
                    <?php $disabled = ( ! $pro_active || ! $cond_enabled ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Rules (JSON)', 'wp-team-manager'); ?></span>
                                    <textarea name="wtm_pro_conditional_rules" class="large-text code" rows="3" <?php disabled( $disabled ); ?>><?php echo esc_textarea( get_option( 'wtm_pro_conditional_rules', '' ) ); ?></textarea>
                                </label>
                                <p class="description"><?php esc_html_e('Future: Define simple rules for triggering notifications based on member fields.', 'wp-team-manager'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( 'Activity Tracker (Pro)', 'wp-team-manager' ); ?></th>
                        <td>
                            <?php $act_enabled = in_array( 'activity_tracker', $enabled, true ); ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="activity_tracker" <?php checked( $act_enabled ); ?> <?php disabled( ! $pro_active ); ?> />
                                <?php echo $pro_active ? esc_html__( 'Enable Activity Tracker agent.', 'wp-team-manager' ) : esc_html__( 'Unlock Activity Tracker in Pro.', 'wp-team-manager' ); ?>
                                <?php if ( ! $pro_active ) : ?>
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-small" target="_blank"><?php esc_html_e( 'Upgrade', 'wp-team-manager' ); ?></a>
                                <?php endif; ?>
                            </label>
                        </td>
                    </tr>
                    <?php $disabled = ( ! $pro_active || ! $act_enabled ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Target Endpoint (URL)', 'wp-team-manager'); ?></span>
                                    <input type="url" name="wtm_pro_activity_target" value="<?php echo esc_attr( get_option('wtm_pro_activity_target','') ); ?>" class="regular-text code" <?php disabled( $disabled ); ?> />
                                </label>
                                <p class="description"><?php esc_html_e('Future: Where to send activity events (views, clicks).', 'wp-team-manager'); ?></p>
                            </fieldset>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e( 'Slack Notifier (Pro)', 'wp-team-manager' ); ?></th>
                        <td>
                            <?php $slack_enabled = in_array( 'slack', $enabled, true ); ?>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="slack" <?php checked( $slack_enabled ); ?> <?php disabled( ! $pro_active ); ?> />
                                <?php echo $pro_active ? esc_html__( 'Send team profile updates to Slack.', 'wp-team-manager' ) : esc_html__( 'Unlock Slack notifications in Pro.', 'wp-team-manager' ); ?>
                                <?php if ( ! $pro_active ) : ?>
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-small" target="_blank"><?php esc_html_e( 'Upgrade', 'wp-team-manager' ); ?></a>
                                <?php endif; ?>
                            </label>
                        </td>
                    </tr>
                    <?php $disabled = ( ! $pro_active || ! $slack_enabled ); ?>
                    <tr>
                        <th scope="row">&nbsp;</th>
                        <td>
                            <fieldset>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Webhook URL', 'wp-team-manager'); ?></span>
                                    <input type="url" name="wtm_pro_slack_webhook_url" value="<?php echo esc_attr( get_option('wtm_pro_slack_webhook_url','') ); ?>" class="regular-text code" <?php disabled( $disabled ); ?> />
                                </label>
                                <br/>
                                <label>
                                    <span style="display:inline-block;min-width:160px;"><?php esc_html_e('Channel (optional)', 'wp-team-manager'); ?></span>
                                    <input type="text" name="wtm_pro_slack_channel" value="<?php echo esc_attr( get_option('wtm_pro_slack_channel','') ); ?>" class="regular-text" <?php disabled( $disabled ); ?> />
                                </label>
                                <p class="description"><?php esc_html_e('Incoming Webhooks must be enabled in your Slack workspace.', 'wp-team-manager'); ?></p>
                            </fieldset>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

}
