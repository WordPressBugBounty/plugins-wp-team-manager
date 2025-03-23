<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class GutenbergBlock {

	public function __construct() {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('init', [$this, 'register_block']);
    }

    public function enqueue_editor_assets() {
        // Ensure script and style are registered only when needed
        wp_register_script(
            'wtm-team-block-js',
            TM_PUBLIC . '/assets/js/block.js',
            ['wp-blocks', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-element'],
            filemtime(TM_PATH . '/public/assets/js/block.js'),
            true
        );

        wp_register_style(
            'wtm-team-block-css',
            TM_PUBLIC . '/assets/css/block.css',
            [],
            filemtime(TM_PATH . '/public/assets/css/block.css')
        );

        // Enqueue only inside block editor
        wp_enqueue_script('wtm-team-block-js');
        wp_enqueue_style('wtm-team-block-css');
    }

    public function register_block() {
        register_block_type('wp-team-manager/team-block', [
            'editor_script'   => 'wtm-team-block-js',
            'editor_style'    => 'wtm-team-block-css',
            'render_callback' => [$this, 'wtm_team_block_render'],
            'attributes'      => [
                'orderby' => ['type' => 'string', 'default' => 'menu_order'],
                'layout' => ['type' => 'string', 'default' => 'grid'],
                'postsPerPage' => ['type' => 'number', 'default' => -1],
                'category' => ['type' => 'string', 'default' => '0'],
                'showSocial' => ['type' => 'boolean', 'default' => true],
                'showOtherInfo' => ['type' => 'boolean', 'default' => true],
                'showReadMore' => ['type' => 'boolean', 'default' => true],
                'imageSize' => ['type' => 'string', 'default' => 'medium'],
            ],
        ]);
    }

    // Render Callback
    public function wtm_team_block_render($attributes) {
        ob_start();
    
        // Ensure attributes are correctly formatted for the shortcode
        $atts = [
            'orderby'        => $attributes['orderby'] ?? 'menu_order',
            'layout'         => $attributes['layout'] ?? 'grid',
            'posts_per_page' => $attributes['postsPerPage'] ?? -1,
            'category'       => $attributes['category'] ?? '0',
            'show_social'    => isset($attributes['showSocial']) ? ($attributes['showSocial'] ? 'yes' : 'no') : 'yes',
            'show_other_info' => isset($attributes['showOtherInfo']) ? ($attributes['showOtherInfo'] ? 'yes' : 'no') : 'yes',
            'show_read_more' => isset($attributes['showReadMore']) ? ($attributes['showReadMore'] ? 'yes' : 'no') : 'yes',
            'image_size'     => $attributes['imageSize'] ?? 'medium',
        ];
        //var_dump($atts);
        // Manually construct the shortcode
        $shortcode = '[team_manager';
        foreach ($atts as $key => $value) {
            $shortcode .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }
        $shortcode .= ']';
    
        echo do_shortcode($shortcode);
    
        return ob_get_clean();
    }

}

new GutenbergBlock();