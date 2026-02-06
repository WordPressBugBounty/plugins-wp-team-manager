<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Free Search Settings - Shows upgrade notice
 */
class SearchSettings {

    use \DWL\Wtm\Traits\Singleton;

    protected function init() {
        add_action('admin_menu', [$this, 'add_settings_page'], 20);
    }

    public function add_settings_page(): void {
        // Menu registration is commented out - upgrade notice feature is currently disabled
        // Uncomment below to enable the Enhanced Search upgrade page

        // add_submenu_page(
        //     'edit.php?post_type=team_manager',
        //     __('Enhanced Search', 'wp-team-manager'),
        //     __('Enhanced Search', 'wp-team-manager'),
        //     'manage_options',
        //     'wtm-enhanced-search',
        //     [$this, 'render_settings_page']
        // );
    }

    public function render_settings_page(): void {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Enhanced Search Settings', 'wp-team-manager'); ?></h1>

            <div class="wtm-upgrade-notice">
                <div class="wtm-upgrade-content">
                    <h2><?php esc_html_e('🔍 Enhanced Search Features', 'wp-team-manager'); ?></h2>
                    <p><?php esc_html_e('Unlock powerful search capabilities for your team displays:', 'wp-team-manager'); ?></p>

                    <ul class="wtm-feature-list">
                        <li>✨ Live search with autocomplete</li>
                        <li>📊 Search analytics and insights</li>
                        <li>🎯 Advanced filtering options</li>
                        <li>💾 Saved filter presets</li>
                        <li>⚡ Performance optimization</li>
                        <li>🎨 Customizable search interface</li>
                    </ul>

                    <div class="wtm-upgrade-actions">
                        <?php if (function_exists('tmwstm_fs')): ?>
                            <a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()); ?>" class="button button-primary button-large">
                                <?php esc_html_e('Upgrade to Pro', 'wp-team-manager'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php

        // Add inline styles using wp_add_inline_style for better security and WordPress standards
        $custom_css = '
        .wtm-upgrade-notice {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-top: 20px;
            text-align: center;
        }
        .wtm-upgrade-content h2 {
            color: white;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .wtm-feature-list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            text-align: left;
        }
        .wtm-feature-list li {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
        }
        .wtm-upgrade-actions {
            margin-top: 30px;
        }
        .wtm-upgrade-actions .button-primary {
            background: #ff6b6b;
            border-color: #ff6b6b;
            font-size: 18px;
            padding: 12px 30px;
            height: auto;
        }
        .wtm-upgrade-actions .button-primary:hover {
            background: #ff5252;
            border-color: #ff5252;
        }
        ';

        wp_register_style( 'wtm-search-settings-upgrade', false );
        wp_enqueue_style( 'wtm-search-settings-upgrade' );
        wp_add_inline_style( 'wtm-search-settings-upgrade', $custom_css );
    }
}