<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

use DWL\Wtm\Classes\Helper;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Search Widget - Free version with Pro upgrade notice
 */
class SearchWidget {
    
    use \DWL\Wtm\Traits\Singleton;

    protected function init() {
        add_shortcode('wtm_enhanced_search', [$this, 'render_search_shortcode']);
    }

    /**
     * Shortcode handler - shows upgrade notice for free users
     */
    public function render_search_shortcode(array $atts): string {
        if (Helper::freemius_is_free_user()) {
            return $this->render_pro_upgrade_notice();
        }

        // Load Pro version if available
        if (class_exists('DWL\Wtm\Pro\Classes\SearchWidget')) {
            $pro_widget = \DWL\Wtm\Pro\Classes\SearchWidget::get_instance();
            return $pro_widget->render_search_shortcode($atts);
        }

        return $this->render_pro_upgrade_notice();
    }

    /**
     * Render Pro upgrade notice
     */
    private function render_pro_upgrade_notice(): string {
        // Add inline styles using wp_add_inline_style for better security and WordPress standards
        $custom_css = '
        .wtm-pro-notice {
            background: #f8f9fa;
            border: 2px solid #007cba;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .wtm-pro-notice h3 {
            color: #007cba;
            margin-top: 0;
        }
        ';

        wp_register_style( 'wtm-search-widget-upgrade', false );
        wp_enqueue_style( 'wtm-search-widget-upgrade' );
        wp_add_inline_style( 'wtm-search-widget-upgrade', $custom_css );

        ob_start();
        ?>
        <div class="wtm-pro-notice">
            <h3><?php esc_html_e('Enhanced Search & Filtering', 'wp-team-manager'); ?></h3>
            <p><?php esc_html_e('Unlock powerful search capabilities with live search, advanced filters, analytics, and saved presets.', 'wp-team-manager'); ?></p>
            <a href="<?php echo esc_url(Helper::freemius_upgrade_url()); ?>" class="button button-primary">
                <?php esc_html_e('Upgrade to Pro', 'wp-team-manager'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
}