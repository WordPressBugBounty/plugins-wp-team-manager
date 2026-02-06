<?php
declare(strict_types=1);

// Define WordPress constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__) . '/');
}

if (!defined('WP_DEBUG')) {
    define('WP_DEBUG', true);
}

// Load Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Load plugin constants
require_once dirname(__DIR__) . '/wp-team-manager.php';