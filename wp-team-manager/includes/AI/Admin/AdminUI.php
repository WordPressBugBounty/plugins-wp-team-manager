<?php
declare(strict_types=1);

namespace DWL\Wtm\AI\Admin;

use DWL\Wtm\Classes\Helper;

defined( 'ABSPATH' ) || exit;

class AdminUI {

    use \DWL\Wtm\Traits\Singleton;

    protected $option_key = 'wp_team_ai_enabled_modules';

    protected function init(): void {
        add_action( 'admin_menu', [ $this, 'register_settings_tab' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_post_wtm_tg_test', [ $this, 'handle_telegram_test' ] );
    }

    public function register_settings_tab(): void {
        $hook_suffix = add_submenu_page(
            'edit.php?post_type=team_manager',
            __( 'AI Agents', 'wp-team-manager' ),
            __( 'AI Agents', 'wp-team-manager' ),
            'manage_options',
            'team-ai-agents',
            [ $this, 'render_settings_page' ]
        );

        add_action( 'load-' . $hook_suffix, [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets(): void {
        wp_enqueue_style( 'wtm-ai-agents', TM_URL . '/admin/assets/css/tm-ai-agents.css', [], TM_VERSION );
        wp_enqueue_script( 'wtm-ai-agents', TM_URL . '/admin/assets/js/ai-agents.js', ['jquery'], TM_VERSION, true );
    }

    public function register_settings(): void {
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

    public function sanitize_enabled_modules( $input ): array {
        $valid = [ 'slack', 'telegram', 'sync', 'faq', 'onboarding', 'conditional_notifier', 'activity_tracker' ];
        return array_values( array_intersect( (array) $input, $valid ) );
    }

    public function sanitize_chat_id( $value ): string {
        $value = trim( (string) $value );
        // Allow numbers and leading '-' for channels/groups
        if ( $value === '' ) {
            return '';
        }
        if ( preg_match( '/^-?[0-9]+$/', $value ) ) {
            return $value;
        }
        // Fallback: strip non-digits (keeps '-')
        return ltrim( preg_replace( '/[^0-9\-]/', '', $value ), '-' );
    }

    public function sanitize_url( $value ): string {
        $value = trim( (string) $value );
        if ( $value === '' ) {
            return '';
        }
        return esc_url_raw( $value );
    }

    // Pro: Sanitize OpenAI model for FAQ Bot
    public function sanitize_model( $value ): string {
        $value = trim( (string) $value );
        $allowed = [ 'gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini', 'gpt-4.1' ];
        if ( in_array( $value, $allowed, true ) ) {
            return $value;
        }
        return 'gpt-4o-mini';
    }

    public function handle_telegram_test(): void {
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
    
    public function render_settings_page(): void {
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
        <div class="wrap wtm-ai-agents-wrap">
            <h1><?php esc_html_e( 'AI Agent Modules', 'wp-team-manager' ); ?></h1>
            <p><?php esc_html_e( 'Configure AI-powered automation to streamline your team management workflow. Enable agents to handle notifications, data sync, and user interactions.', 'wp-team-manager' ); ?></p>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'wp_team_ai_agent_settings' ); ?>
                <div class="wtm-ai-agents-grid">
                    <!-- FAQ Bot Agent -->
                    <div class="wtm-agent-card">
                        <div class="wtm-agent-header">
                            <div class="wtm-agent-title">
                                <span class="dashicons dashicons-superhero-alt wtm-agent-icon"></span>
                                <h3><?php esc_html_e( 'FAQ Bot', 'wp-team-manager' ); ?></h3>
                                <?php if ( ! $pro_active ) : ?>
                                    <span class="wtm-pro-badge"><?php esc_html_e( 'Pro', 'wp-team-manager' ); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="wtm-agent-description"><?php esc_html_e( 'AI-powered chatbot that answers questions about team members using OpenAI GPT models.', 'wp-team-manager' ); ?></p>
                            <?php $faq_enabled = in_array( 'faq', $enabled, true ); ?>
                            <div class="wtm-agent-status">
                                <span class="wtm-status-indicator <?php echo $faq_enabled && $pro_active ? 'active' : ''; ?>"></span>
                                <span class="wtm-status-text"><?php echo $faq_enabled && $pro_active ? esc_html__( 'Active', 'wp-team-manager' ) : esc_html__( 'Inactive', 'wp-team-manager' ); ?></span>
                            </div>
                        </div>
                        <div class="wtm-agent-body">
                            <div class="wtm-agent-toggle">
                                <label class="wtm-toggle-label"><?php esc_html_e( 'Enable FAQ Bot', 'wp-team-manager' ); ?></label>
                                <div class="wtm-toggle-switch">
                                    <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="faq" <?php checked( $faq_enabled ); ?> <?php disabled( ! $pro_active ); ?> id="faq-toggle" data-pro-active="<?php echo $pro_active ? '1' : '0'; ?>" />
                                    <span class="wtm-toggle-slider"></span>
                                </div>
                            </div>
                            <?php $disabled = ( ! $pro_active || ! $faq_enabled ); ?>
                            <div class="wtm-agent-settings <?php echo $faq_enabled ? 'active' : ''; ?>">
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'OpenAI API Key', 'wp-team-manager' ); ?></label>
                                    <input type="text" name="wtm_pro_openai_api_key" value="<?php echo esc_attr( get_option('wtm_pro_openai_api_key','') ); ?>" class="wtm-setting-input" <?php disabled( $disabled ); ?> placeholder="sk-..." />
                                </div>
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'AI Model', 'wp-team-manager' ); ?></label>
                                    <select name="wtm_pro_openai_model" class="wtm-setting-input" <?php disabled( $disabled ); ?>>
                                        <?php $model = get_option('wtm_pro_openai_model', 'gpt-4o-mini'); ?>
                                        <option value="gpt-4o-mini" <?php selected( $model, 'gpt-4o-mini' ); ?>>GPT-4o Mini (Recommended)</option>
                                        <option value="gpt-4o" <?php selected( $model, 'gpt-4o' ); ?>>GPT-4o</option>
                                        <option value="gpt-4.1-mini" <?php selected( $model, 'gpt-4.1-mini' ); ?>>GPT-4.1 Mini</option>
                                        <option value="gpt-4.1" <?php selected( $model, 'gpt-4.1' ); ?>>GPT-4.1</option>
                                    </select>
                                    <p class="wtm-setting-description">
                                        <?php echo wp_kses(
                                            __('Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>. Learn about <a href="https://platform.openai.com/docs/models" target="_blank">available models</a>.', 'wp-team-manager'),
                                            [ 'a' => [ 'href' => [], 'target' => [] ] ]
                                        ); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ( ! $pro_active ) : ?>
                                <div class="wtm-agent-actions">
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="wtm-upgrade-btn" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'wp-team-manager' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Telegram Notifier Agent -->
                    <div class="wtm-agent-card">
                        <div class="wtm-agent-header">
                            <div class="wtm-agent-title">
                                <span class="dashicons dashicons-email-alt wtm-agent-icon"></span>
                                <h3><?php esc_html_e( 'Telegram Notifier', 'wp-team-manager' ); ?></h3>
                            </div>
                            <p class="wtm-agent-description"><?php esc_html_e( 'Send real-time notifications to Telegram when team members are added, updated, or published.', 'wp-team-manager' ); ?></p>
                            <?php $tg_enabled = in_array( 'telegram', $enabled, true ); ?>
                            <div class="wtm-agent-status">
                                <span class="wtm-status-indicator <?php echo $tg_enabled ? 'active' : ''; ?>"></span>
                                <span class="wtm-status-text"><?php echo $tg_enabled ? esc_html__( 'Active', 'wp-team-manager' ) : esc_html__( 'Inactive', 'wp-team-manager' ); ?></span>
                            </div>
                        </div>
                        <div class="wtm-agent-body">
                            <div class="wtm-agent-toggle">
                                <label class="wtm-toggle-label"><?php esc_html_e( 'Enable Telegram Notifications', 'wp-team-manager' ); ?></label>
                                <div class="wtm-toggle-switch">
                                    <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="telegram" <?php checked( $tg_enabled ); ?> id="telegram-toggle" />
                                    <span class="wtm-toggle-slider"></span>
                                </div>
                            </div>
                            <div class="wtm-agent-settings <?php echo $tg_enabled ? 'active' : ''; ?>">
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'Bot Token', 'wp-team-manager' ); ?></label>
                                    <input type="text" name="wtm_telegram_bot_token" value="<?php echo esc_attr( get_option('wtm_telegram_bot_token','') ); ?>" class="wtm-setting-input" <?php disabled( ! $tg_enabled ); ?> placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz" />
                                </div>
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'Chat ID', 'wp-team-manager' ); ?></label>
                                    <input type="text" name="wtm_telegram_chat_id" value="<?php echo esc_attr( get_option('wtm_telegram_chat_id','') ); ?>" class="wtm-setting-input" <?php disabled( ! $tg_enabled ); ?> placeholder="-1001234567890" />
                                    <p class="wtm-setting-description">
                                        <?php echo wp_kses(
                                            __('Learn how to <a href="https://core.telegram.org/bots#6-botfather" target="_blank">create a Telegram Bot</a> and <a href="https://stackoverflow.com/a/37396871" target="_blank">find your Chat ID</a>.', 'wp-team-manager'),
                                            [ 'a' => [ 'href' => [], 'target' => [] ] ]
                                        ); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ( $tg_enabled ) : ?>
                                <div class="wtm-agent-actions">
                                    <?php
                                        $token   = get_option( 'wtm_telegram_bot_token', '' );
                                        $chat_id = get_option( 'wtm_telegram_chat_id', '' );
                                        $test_url = wp_nonce_url( admin_url( 'admin-post.php?action=wtm_tg_test' ), 'wtm_tg_test_action', 'wtm_tg_test_nonce' );
                                        $btn_disabled = ( empty( $token ) || empty( $chat_id ) ) ? 'disabled' : '';
                                    ?>
                                    <a href="<?php echo esc_url( $test_url ); ?>" class="wtm-test-btn <?php echo $btn_disabled; ?>"><?php esc_html_e( 'Send Test Message', 'wp-team-manager' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Profile Sync Agent -->
                    <div class="wtm-agent-card">
                        <div class="wtm-agent-header">
                            <div class="wtm-agent-title">
                                <span class="dashicons dashicons-update wtm-agent-icon"></span>
                                <h3><?php esc_html_e( 'Profile Sync Agent', 'wp-team-manager' ); ?></h3>
                            </div>
                            <p class="wtm-agent-description"><?php esc_html_e( 'Automatically sync team profile data to external services like Google Sheets, Airtable, or n8n workflows via webhooks.', 'wp-team-manager' ); ?></p>
                            <?php $sync_enabled = in_array( 'sync', $enabled, true ); ?>
                            <div class="wtm-agent-status">
                                <span class="wtm-status-indicator <?php echo $sync_enabled ? 'active' : ''; ?>"></span>
                                <span class="wtm-status-text"><?php echo $sync_enabled ? esc_html__( 'Active', 'wp-team-manager' ) : esc_html__( 'Inactive', 'wp-team-manager' ); ?></span>
                            </div>
                        </div>
                        <div class="wtm-agent-body">
                            <div class="wtm-agent-toggle">
                                <label class="wtm-toggle-label"><?php esc_html_e( 'Enable Profile Sync', 'wp-team-manager' ); ?></label>
                                <div class="wtm-toggle-switch">
                                    <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="sync" <?php checked( $sync_enabled ); ?> id="sync-toggle" />
                                    <span class="wtm-toggle-slider"></span>
                                </div>
                            </div>
                            <div class="wtm-agent-settings <?php echo $sync_enabled ? 'active' : ''; ?>">
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'Webhook URL', 'wp-team-manager' ); ?></label>
                                    <input type="url" name="wtm_sync_webhook_url" value="<?php echo esc_attr( get_option('wtm_sync_webhook_url','') ); ?>" class="wtm-setting-input" <?php disabled( ! $sync_enabled ); ?> placeholder="https://hooks.n8n.cloud/webhook/..." />
                                </div>
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'HMAC Secret (Optional)', 'wp-team-manager' ); ?></label>
                                    <input type="text" name="wtm_sync_webhook_secret" value="<?php echo esc_attr( get_option('wtm_sync_webhook_secret','') ); ?>" class="wtm-setting-input" <?php disabled( ! $sync_enabled ); ?> placeholder="your-secret-key" />
                                </div>
                                <div class="wtm-setting-group">
                                    <label class="wtm-toggle-label">
                                        <input type="checkbox" name="wtm_sync_status_only" value="1" <?php checked( (bool) get_option('wtm_sync_status_only', true ) ); ?> <?php disabled( ! $sync_enabled ); ?> />
                                        <?php esc_html_e('Only sync published team members', 'wp-team-manager'); ?>
                                    </label>
                                    <p class="wtm-setting-description">
                                        <?php echo wp_kses(
                                            __('See <a href="https://docs.n8n.io/integrations/builtin/core-nodes/n8n-nodes-base.webhook/" target="_blank">n8n Webhook docs</a> for setup instructions.', 'wp-team-manager'),
                                            [ 'a' => [ 'href' => [], 'target' => [] ] ]
                                        ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Slack Notifier Agent -->
                    <div class="wtm-agent-card">
                        <div class="wtm-agent-header">
                            <div class="wtm-agent-title">
                                <span class="dashicons dashicons-format-chat wtm-agent-icon"></span>
                                <h3><?php esc_html_e( 'Slack Notifier', 'wp-team-manager' ); ?></h3>
                                <?php if ( ! $pro_active ) : ?>
                                    <span class="wtm-pro-badge"><?php esc_html_e( 'Pro', 'wp-team-manager' ); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="wtm-agent-description"><?php esc_html_e( 'Send team profile updates directly to your Slack workspace channels for instant team notifications.', 'wp-team-manager' ); ?></p>
                            <?php $slack_enabled = in_array( 'slack', $enabled, true ); ?>
                            <div class="wtm-agent-status">
                                <span class="wtm-status-indicator <?php echo $slack_enabled && $pro_active ? 'active' : ''; ?>"></span>
                                <span class="wtm-status-text"><?php echo $slack_enabled && $pro_active ? esc_html__( 'Active', 'wp-team-manager' ) : esc_html__( 'Inactive', 'wp-team-manager' ); ?></span>
                            </div>
                        </div>
                        <div class="wtm-agent-body">
                            <div class="wtm-agent-toggle">
                                <label class="wtm-toggle-label"><?php esc_html_e( 'Enable Slack Notifications', 'wp-team-manager' ); ?></label>
                                <div class="wtm-toggle-switch">
                                    <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="slack" <?php checked( $slack_enabled ); ?> <?php disabled( ! $pro_active ); ?> id="slack-toggle" />
                                    <span class="wtm-toggle-slider"></span>
                                </div>
                            </div>
                            <?php $disabled = ( ! $pro_active || ! $slack_enabled ); ?>
                            <div class="wtm-agent-settings <?php echo $slack_enabled ? 'active' : ''; ?>">
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'Webhook URL', 'wp-team-manager' ); ?></label>
                                    <input type="url" name="wtm_pro_slack_webhook_url" value="<?php echo esc_attr( get_option('wtm_pro_slack_webhook_url','') ); ?>" class="wtm-setting-input" <?php disabled( $disabled ); ?> placeholder="https://hooks.slack.com/services/..." />
                                </div>
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'Channel (Optional)', 'wp-team-manager' ); ?></label>
                                    <input type="text" name="wtm_pro_slack_channel" value="<?php echo esc_attr( get_option('wtm_pro_slack_channel','') ); ?>" class="wtm-setting-input" <?php disabled( $disabled ); ?> placeholder="#team-updates" />
                                    <p class="wtm-setting-description"><?php esc_html_e('Incoming Webhooks must be enabled in your Slack workspace.', 'wp-team-manager'); ?></p>
                                </div>
                            </div>
                            <?php if ( ! $pro_active ) : ?>
                                <div class="wtm-agent-actions">
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="wtm-upgrade-btn" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'wp-team-manager' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Onboarding Guide Agent -->
                    <div class="wtm-agent-card">
                        <div class="wtm-agent-header">
                            <div class="wtm-agent-title">
                                <span class="dashicons dashicons-welcome-learn-more wtm-agent-icon"></span>
                                <h3><?php esc_html_e( 'Onboarding Guide', 'wp-team-manager' ); ?></h3>
                                <?php if ( ! $pro_active ) : ?>
                                    <span class="wtm-pro-badge"><?php esc_html_e( 'Pro', 'wp-team-manager' ); ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="wtm-agent-description"><?php esc_html_e( 'Automatically generate personalized onboarding pages and welcome messages for new team members.', 'wp-team-manager' ); ?></p>
                            <?php $onb_enabled = in_array( 'onboarding', $enabled, true ); ?>
                            <div class="wtm-agent-status">
                                <span class="wtm-status-indicator <?php echo $onb_enabled && $pro_active ? 'active' : ''; ?>"></span>
                                <span class="wtm-status-text"><?php echo $onb_enabled && $pro_active ? esc_html__( 'Active', 'wp-team-manager' ) : esc_html__( 'Inactive', 'wp-team-manager' ); ?></span>
                            </div>
                        </div>
                        <div class="wtm-agent-body">
                            <div class="wtm-agent-toggle">
                                <label class="wtm-toggle-label"><?php esc_html_e( 'Enable Onboarding Guide', 'wp-team-manager' ); ?></label>
                                <div class="wtm-toggle-switch">
                                    <input type="checkbox" name="<?php echo esc_attr( $this->option_key ); ?>[]" value="onboarding" <?php checked( $onb_enabled ); ?> <?php disabled( ! $pro_active ); ?> id="onboarding-toggle" />
                                    <span class="wtm-toggle-slider"></span>
                                </div>
                            </div>
                            <?php $disabled = ( ! $pro_active || ! $onb_enabled ); ?>
                            <div class="wtm-agent-settings <?php echo $onb_enabled ? 'active' : ''; ?>">
                                <div class="wtm-setting-group">
                                    <label class="wtm-setting-label"><?php esc_html_e( 'Welcome Message Template', 'wp-team-manager' ); ?></label>
                                    <textarea name="wtm_pro_onboarding_message" class="wtm-setting-input wtm-setting-textarea" rows="3" <?php disabled( $disabled ); ?> placeholder="Welcome to the team, {name}! We're excited to have you join us as our new {job_title}."><?php echo esc_textarea( get_option( 'wtm_pro_onboarding_message', '' ) ); ?></textarea>
                                    <p class="wtm-setting-description"><?php esc_html_e('Use {name}, {job_title}, and other team member fields as placeholders.', 'wp-team-manager'); ?></p>
                                </div>
                            </div>
                            <?php if ( ! $pro_active ) : ?>
                                <div class="wtm-agent-actions">
                                    <a href="<?php echo esc_url( $upgrade_url ); ?>" class="wtm-upgrade-btn" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'wp-team-manager' ); ?></a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="wtm-ai-submit">
                    <?php submit_button( __( 'Save AI Agent Settings', 'wp-team-manager' ), 'primary', 'submit', false ); ?>
                </div>
            </form>
        </div>

        <div class="wtm-footer">
            <p>
                <?php esc_html_e( 'Made with', 'wp-team-manager' ); ?> ❤️ <a href="https://dynamicweblab.com/"><?php esc_html_e( 'by the Dynamic Web Lab', 'wp-team-manager' ); ?></a>
            </p>
        </div>
        <?php
    }

}
