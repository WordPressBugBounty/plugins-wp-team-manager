<?php
declare(strict_types=1);

namespace DWL\Wtm\Classes;

if (!defined('ABSPATH')) exit;

/**
 * Comprehensive Error Handler
 */
class ErrorHandler {
    
    private static ?self $instance = null;
    private array $errors = [];
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_loaded', [$this, 'init']);
    }
    
    public function init(): void {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
        }
    }
    
    public function handleError(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $this->logError([
            'type' => 'error',
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => time()
        ]);
        
        return true;
    }
    
    public function handleException(\Throwable $exception): void {
        $this->logError([
            'type' => 'exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => time()
        ]);
    }
    
    public function logError(array $error): void {
        $this->errors[] = $error;
        
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf(
                'WTM Error: %s in %s:%d',
                $error['message'],
                $error['file'],
                $error['line']
            ));
        }
    }
    
    public function getErrors(): array {
        return $this->errors;
    }
    
    public function clearErrors(): void {
        $this->errors = [];
    }
}