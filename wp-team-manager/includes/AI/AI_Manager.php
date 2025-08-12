<?php
namespace DWL\Wtm\AI;

use DWL\Wtm\Classes\Log;
use DWL\Wtm\Classes\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Class AI_Manager
 *
 * Responsible for initializing and managing AI agent modules.
 */
class AI_Manager {

    /**
     * Holds registered modules.
     *
     * @var array
     */
    protected $modules = [];

    /**
     * Boot the AI Manager.
     */
    public function __construct() {
        add_action( 'init', [ $this, 'init' ] );
    }

    /**
     * Initialize the AI modules.
     */
    public function init() {
        $enabled_modules = get_option( 'wp_team_ai_enabled_modules', [] );

        if ( ! is_array( $enabled_modules ) ) {
            $enabled_modules = [];
        }

        $allowed = ['telegram', 'sync', 'faq', 'slack', 'onboarding'];
        $enabled_modules = array_intersect($enabled_modules, $allowed);

        $module_path = __DIR__ . '/Modules/';
        $pro_active = Helper::is_pro_active();
        $pro_module_path = ($pro_active && defined('TM_PRO_PATH'))
            ? TM_PRO_PATH . '/includes/AI/Modules/'
            : null;

        // Free: Telegram
        if ( in_array( 'telegram', $enabled_modules, true ) ) {
            $file = $module_path . 'Telegram_Agent.php';
            if ( file_exists( $file ) ) {
                require_once $file;
                $this->modules['telegram'] = new Modules\Telegram_Agent();
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::info('AI_Manager: Telegram module loaded');
                }
            } else {
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::warning('AI_Manager: Telegram module file missing', ['path' => basename($file)]);
                }
            }
        }

        // Free: Profile Sync
        if ( in_array( 'sync', $enabled_modules, true ) ) {
            $file = $module_path . 'Profile_Sync_Agent.php';
            if ( file_exists( $file ) ) {
                require_once $file;
                $this->modules['sync'] = new Modules\Profile_Sync_Agent();
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::info('AI_Manager: Profile Sync module loaded');
                }
            } else {
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::warning('AI_Manager: Profile Sync module file missing', ['path' => basename($file)]);
                }
            }
        }

        // Pro: FAQ (only if Pro path available)
        if ( in_array( 'faq', $enabled_modules, true ) ) {
            if ( $pro_active ) {
                $file = $pro_module_path . 'FAQ_Agent.php';
                if ( file_exists( $file ) ) {
                    require_once $file;
                    // Pro namespace for FAQ agent
                    $this->modules['faq'] = new \WP_Team_Manager_Pro\AI\Modules\FAQ_Agent();
                    if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                        Log::info('AI_Manager: Pro FAQ module loaded');
                    }
                } else {
                    if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                        Log::warning('AI_Manager: Pro FAQ module file missing', ['path' => basename($file)]);
                    }
                }
            } else {
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::warning('AI_Manager: FAQ requested but Pro not active');
                }
            }
        }

        // Pro: Slack (only if Pro path available)
        if ( in_array( 'slack', $enabled_modules, true ) ) {
            if ( $pro_active ) {
                $file = $pro_module_path . 'Slack_Agent.php';
                if ( file_exists( $file ) ) {
                    require_once $file;
                    $this->modules['slack'] = new \WP_Team_Manager_Pro\AI\Modules\Slack_Agent();
                    if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                        Log::info('AI_Manager: Pro Slack module loaded');
                    }
                } else {
                    if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                        Log::warning('AI_Manager: Pro Slack module file missing', ['path' => basename($file)]);
                    }
                }
            } else {
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::warning('AI_Manager: Slack requested but Pro not active');
                }
            }
        }

        // Pro: Onboarding (only if Pro path available)
        if ( in_array( 'onboarding', $enabled_modules, true ) ) {
            if ( $pro_active ) {
                $file = $pro_module_path . 'Onboarding_Agent.php';
                if ( file_exists( $file ) ) {
                    require_once $file;
                    $this->modules['onboarding'] = new \WP_Team_Manager_Pro\AI\Modules\Onboarding_Agent();
                    if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                        Log::info('AI_Manager: Pro Onboarding module loaded');
                    }
                } else {
                    if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                        Log::warning('AI_Manager: Pro Onboarding module file missing', ['path' => basename($file)]);
                    }
                }
            } else {
                if ( class_exists( '\\DWL\\Wtm\\Classes\\Log' ) ) {
                    Log::warning('AI_Manager: Onboarding requested but Pro not active');
                }
            }
        }
    }

    /**
     * Get all loaded modules.
     *
     * @return array<string,object>
     */
    public function get_modules() {
        return $this->modules;
    }
}
