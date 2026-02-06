<?php
namespace DWL\Wtm\Elementor\Performance;

if (!defined('ABSPATH')) exit;

/**
 * Widget Performance Optimizer
 */
class WidgetOptimizer {
    
    private function __construct() {
        $this->init();
    }
    
    protected function init() {
        add_action('elementor/frontend/before_render', [$this, 'optimize_widget_loading']);
        add_filter('elementor/widget/render_content', [$this, 'cache_widget_output'], 10, 2);
        add_action('wp_enqueue_scripts', [$this, 'conditional_asset_loading'], 20);
    }
    
    public static function getInstance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }
    
    /**
     * Optimize widget loading by deferring non-critical assets
     */
    public function optimize_widget_loading($element) {
        if ($element->get_name() === 'wtm-team-manager') {
            $settings = $element->get_settings_for_display();
            
            // Preload critical images
            if (!empty($settings['layout_type']) && $settings['layout_type'] === 'slider') {
                add_action('wp_head', [$this, 'preload_slider_assets']);
            }
            
            // Defer non-critical scripts
            add_filter('script_loader_tag', [$this, 'defer_non_critical_scripts'], 10, 3);
        }
    }
    
    /**
     * Cache widget output for better performance
     */
    public function cache_widget_output($content, $widget) {
        if ($widget->get_name() === 'wtm-team-manager') {
            $cache_key = 'wtm_widget_' . md5(serialize($widget->get_settings_for_display()));
            $cached_content = get_transient($cache_key);
            
            if (false === $cached_content) {
                set_transient($cache_key, $content, HOUR_IN_SECONDS);
            }
        }
        
        return $content;
    }
    
    /**
     * Load assets only when widget is present
     */
    public function conditional_asset_loading() {
        global $post;
        
        if (!$post || !class_exists('\Elementor\Plugin')) {
            return;
        }
        
        $elementor_data = get_post_meta($post->ID, '_elementor_data', true);
        
        if (empty($elementor_data) || !is_string($elementor_data)) {
            return;
        }
        
        // Check if team widget is used
        if (strpos($elementor_data, 'wtm-team-manager') === false) {
            // Dequeue unnecessary assets
            wp_dequeue_style('wp-team-slick');
            wp_dequeue_script('wp-team-slick');
        }
    }
    
    /**
     * Preload critical slider assets
     */
    public function preload_slider_assets() {
        echo '<link rel="preload" href="' . TM_PUBLIC_ASSETS . '/vendor/slick/slick.min.css" as="style">';
        echo '<link rel="preload" href="' . TM_PUBLIC_ASSETS . '/vendor/slick/slick.min.js" as="script">';
    }
    
    /**
     * Defer non-critical scripts
     */
    public function defer_non_critical_scripts($tag, $handle, $src) {
        $defer_scripts = ['wp-team-script', 'wp-team-el-slider'];
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }
}