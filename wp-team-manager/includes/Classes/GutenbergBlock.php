<?php

namespace DWL\Wtm\Classes;

require_once __DIR__ . '/BlockPatterns.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class GutenbergBlock {

	public function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('init', [$this, 'register_block']);
        add_action('init', [$this, 'register_block_variations']);
        
        // Initialize block patterns
        new \DWL\Wtm\Classes\BlockPatterns();
    }

    public function enqueue_editor_assets() {
        wp_enqueue_script(
            'wtm-team-block-js',
            TM_PUBLIC . '/assets/js/block.js',
            ['wp-blocks', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-element'],
            TM_VERSION,
            true
        );

        wp_enqueue_style(
            'wtm-team-block-css',
            TM_PUBLIC . '/assets/css/block.css',
            [],
            TM_VERSION
        );
    }

    public function register_block() {
        register_block_type('wp-team-manager/team-block', [
            'render_callback' => [$this, 'wtm_team_block_render'],
            'attributes'      => [
                'orderby' => ['type' => 'string', 'default' => 'menu_order'],
                'layout' => ['type' => 'string', 'default' => 'grid'],
                'style' => ['type' => 'string', 'default' => 'style-1'],
                'postsPerPage' => ['type' => 'number', 'default' => -1],
                'category' => ['type' => 'string', 'default' => '0'],
                'showSocial' => ['type' => 'boolean', 'default' => true],
                'showOtherInfo' => ['type' => 'boolean', 'default' => true],
                'showReadMore' => ['type' => 'boolean', 'default' => true],
                'imageSize' => ['type' => 'string', 'default' => 'medium'],
                'columns' => ['type' => 'number', 'default' => 3],
                'gap' => ['type' => 'string', 'default' => 'medium'],
            ],
        ]);
    }

    // Render Callback
    public function wtm_team_block_render($attributes, $content, $block) {
        ob_start();
    
        // Ensure attributes are correctly formatted for the shortcode
        $atts = [
            'orderby'        => $attributes['orderby'] ?? 'menu_order',
            'layout'         => $attributes['layout'] ?? 'grid',
            'style'          => $attributes['style'] ?? 'style-1',
            'posts_per_page' => $attributes['postsPerPage'] ?? -1,
            'category'       => $attributes['category'] ?? '0',
            'show_social'    => isset($attributes['showSocial']) ? ($attributes['showSocial'] ? 'yes' : 'no') : 'yes',
            'show_other_info' => isset($attributes['showOtherInfo']) ? ($attributes['showOtherInfo'] ? 'yes' : 'no') : 'yes',
            'show_read_more' => isset($attributes['showReadMore']) ? ($attributes['showReadMore'] ? 'yes' : 'no') : 'yes',
            'image_size'     => $attributes['imageSize'] ?? 'medium',
            'columns'        => $attributes['columns'] ?? 3,
            'gap'            => $attributes['gap'] ?? 'medium',
        ];

        // Manually construct the shortcode
        $shortcode = '[team_manager';
        foreach ($atts as $key => $value) {
            $shortcode .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
        $shortcode .= ']';
    
        echo do_shortcode($shortcode);
    
        return ob_get_clean();
    }



    /**
     * Register block variations
     */
    public function register_block_variations() {
        wp_enqueue_script('wtm-block-variations', TM_PUBLIC . '/assets/js/block-variations.js', ['wp-blocks', 'wtm-team-block-js'], TM_VERSION, true);
    }

}

// Register block pattern category
add_action('init', function() {
    register_block_pattern_category('team-manager', [
        'label' => __('Team Manager', 'wp-team-manager'),
    ]);
});

new GutenbergBlock();