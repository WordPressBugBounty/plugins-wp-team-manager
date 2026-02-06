<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

if (!defined('ABSPATH')) exit;

/**
 * PHP Version Compatibility Checker
 */
class CompatibilityChecker {
    
    public static function checkPhpVersion(): bool {
        return version_compare(PHP_VERSION, '8.0.0', '>=');
    }
    
    public static function hasReadonlySupport(): bool {
        return version_compare(PHP_VERSION, '8.1.0', '>=');
    }
    
    public static function hasUnionTypes(): bool {
        return version_compare(PHP_VERSION, '8.0.0', '>=');
    }
    
    public static function getRecommendedVersion(): string {
        return '8.1.0';
    }
    
    public static function showCompatibilityNotice(): void {
        if (!self::checkPhpVersion()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning"><p>';
                echo sprintf(
                    __('WP Team Manager: PHP %s or higher is recommended for optimal performance. Current version: %s', 'wp-team-manager'),
                    self::getRecommendedVersion(),
                    PHP_VERSION
                );
                echo '</p></div>';
            });
        }
    }
}