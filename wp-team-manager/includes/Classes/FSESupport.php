<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class FSESupport {

    /**
     * Store temporary template files for cleanup
     */
    private $temp_templates = [];

    /**
     * Cache for template file existence checks
     */
    private $template_cache = [];

    public function __construct() {
        add_action('init', [$this, 'register_block_templates']);
        add_filter('theme_templates', [$this, 'add_block_templates'], 10, 4);
        add_filter('template_include', [$this, 'load_block_template'], 10, 1);
        add_action('shutdown', [$this, 'cleanup_temp_templates']);
    }

    /**
     * Register block templates for FSE themes
     */
    public function register_block_templates() {
        if (!current_theme_supports('block-templates')) {
            return;
        }

        // Register custom templates
        add_theme_support('block-template-parts');
        
        // Add template directory
        add_filter('block_template_hierarchy', [$this, 'add_template_hierarchy']);
    }

    /**
     * Check if template file exists (with caching)
     */
    private function template_exists($template_name) {
        if (!isset($this->template_cache[$template_name])) {
            $template_path = wp_normalize_path(TM_PATH . '/public/templates/block-templates/' . $template_name . '.html');
            $this->template_cache[$template_name] = file_exists($template_path);
        }
        return $this->template_cache[$template_name];
    }

    /**
     * Add block templates to theme templates list
     */
    public function add_block_templates($templates, $theme, $post, $post_type) {
        if ($post_type !== 'team_manager') {
            return $templates;
        }

        if ($this->template_exists('single-team_manager')) {
            $templates['single-team_manager'] = [
                'title' => __('Single Team Member', 'wp-team-manager'),
                'description' => __('Template for displaying individual team members', 'wp-team-manager'),
            ];
        }

        if ($this->template_exists('archive-team_manager')) {
            $templates['archive-team_manager'] = [
                'title' => __('Team Archive', 'wp-team-manager'),
                'description' => __('Template for displaying team member archive', 'wp-team-manager'),
            ];
        }

        return $templates;
    }

    /**
     * Load block template if available
     */
    public function load_block_template($template) {
        if (!current_theme_supports('block-templates')) {
            return $template;
        }

        $post_type = get_post_type();

        if ($post_type !== 'team_manager') {
            return $template;
        }

        if (is_single() && $this->template_exists('single-team_manager')) {
            return $this->render_block_template('single-team_manager');
        }

        if (is_post_type_archive('team_manager') && $this->template_exists('archive-team_manager')) {
            return $this->render_block_template('archive-team_manager');
        }

        return $template;
    }

    /**
     * Render block template
     */
    private function render_block_template($template_name) {
        // Sanitize template name to prevent path traversal
        $template_name = sanitize_file_name($template_name);

        // Validate template name format
        if (!preg_match('/^[a-z0-9_-]+$/i', $template_name)) {
            return false;
        }

        $template_path = wp_normalize_path(TM_PATH . '/public/templates/block-templates/' . $template_name . '.html');

        // Verify the path is within our templates directory (prevent path traversal)
        $base_path = wp_normalize_path(TM_PATH . '/public/templates/block-templates/');
        if (strpos($template_path, $base_path) !== 0) {
            return false;
        }

        if (!file_exists($template_path)) {
            return false;
        }

        $template_content = file_get_contents($template_path);

        if ($template_content === false) {
            return false;
        }

        // Create a temporary template file with unique name
        $temp_template = wp_normalize_path(get_temp_dir() . 'wp-team-manager-' . $template_name . '-' . md5($template_content) . '.php');

        // Only create if doesn't exist
        if (!file_exists($temp_template)) {
            $php_content = '<?php
// Temporary template file for WP Team Manager FSE support
get_header();

// Parse and render blocks
$blocks = parse_blocks(\'' . addslashes($template_content) . '\');
foreach ($blocks as $block) {
    echo render_block($block);
}

get_footer();
?>';

            $result = file_put_contents($temp_template, $php_content);

            if ($result === false) {
                return false;
            }
        }

        // Track temp file for cleanup
        $this->temp_templates[] = $temp_template;

        return $temp_template;
    }

    /**
     * Add template hierarchy for block templates
     */
    public function add_template_hierarchy($hierarchy) {
        $template_path = TM_PATH . '/public/templates/block-templates/';
        
        // Add our template directory to the hierarchy
        array_unshift($hierarchy, $template_path);
        
        return $hierarchy;
    }

    /**
     * Clean up temporary template files
     */
    public function cleanup_temp_templates() {
        foreach ($this->temp_templates as $temp_file) {
            if (file_exists($temp_file)) {
                @unlink($temp_file);
            }
        }
        $this->temp_templates = [];
    }

    /**
     * Register theme.json for block styling
     */
    public static function register_theme_json() {
        $theme_json_path = TM_PATH . '/public/assets/theme.json';

        if (file_exists($theme_json_path)) {
            add_filter('wp_theme_json_data_theme', function($theme_json) use ($theme_json_path) {
                $plugin_theme_json = json_decode(file_get_contents($theme_json_path), true);

                if ($plugin_theme_json) {
                    return $theme_json->update_with($plugin_theme_json);
                }

                return $theme_json;
            });
        }
    }
}

// Initialize FSE support
new FSESupport();

// Register theme.json on init
add_action('init', [FSESupport::class, 'register_theme_json']);