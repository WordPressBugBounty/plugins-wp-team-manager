<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

if (!defined('ABSPATH')) exit;

/**
 * Typed Helper Class with PHP 8.1+ Features
 */
class TypedHelper {
    
    /**
     * Get team data with strict typing
     */
    public static function getTeamData(array $args): array {
        $defaults = [
            'post_type' => 'team_manager',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'no_found_rows' => false,
        ];
        
        $query_args = array_merge($defaults, $args);
        $query = new \WP_Query($query_args);
        
        return [
            'posts' => $query->posts,
            'max_num_pages' => $query->max_num_pages,
            'found_posts' => $query->found_posts,
        ];
    }
    
    /**
     * Validate team member data with union types
     */
    public static function validateTeamMember(array $data): bool|string {
        $required_fields = ['post_title', 'tm_jtitle'];
        
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return "Missing required field: {$field}";
            }
        }
        
        return true;
    }
    
    /**
     * Get team member meta with nullable return
     */
    public static function getTeamMemberMeta(int $post_id, string $meta_key): ?string {
        $meta_value = get_post_meta($post_id, $meta_key, true);
        return !empty($meta_value) ? (string) $meta_value : null;
    }
    
    /**
     * Process team settings with readonly properties
     */
    public static function processTeamSettings(array $settings): TeamSettings {
        return new TeamSettings(
            layout: $settings['layout_type'] ?? 'grid',
            columns: (int) ($settings['columns'] ?? 3),
            showImage: ($settings['show_image'] ?? 'yes') === 'yes',
            imageSize: $settings['image_size'] ?? 'medium'
        );
    }
}

/**
 * Team Settings Value Object
 */
class TeamSettings {
    public string $layout;
    public int $columns;
    public bool $showImage;
    public string $imageSize;
    
    public function __construct(
        string $layout,
        int $columns,
        bool $showImage,
        string $imageSize
    ) {
        $this->layout = $layout;
        $this->columns = $columns;
        $this->showImage = $showImage;
        $this->imageSize = $imageSize;
    }
}