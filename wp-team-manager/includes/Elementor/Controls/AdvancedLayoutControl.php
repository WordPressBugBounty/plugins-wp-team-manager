<?php
namespace DWL\Wtm\Elementor\Controls;

if (!defined('ABSPATH')) exit;

use Elementor\Base_Data_Control;

/**
 * Advanced Layout Control for enhanced styling options
 */
class AdvancedLayoutControl extends Base_Data_Control {
    
    const CONTROL_TYPE = 'wtm_advanced_layout';
    
    public function get_type() {
        return self::CONTROL_TYPE;
    }
    
    public function enqueue() {
        wp_enqueue_style('wtm-advanced-layout-control', TM_ADMIN_ASSETS . '/css/advanced-layout-control.css', [], TM_VERSION);
        wp_enqueue_script('wtm-advanced-layout-control', TM_ADMIN_ASSETS . '/js/advanced-layout-control.js', ['jquery'], TM_VERSION, true);
    }
    
    protected function get_default_settings() {
        return [
            'label_block' => true,
            'options' => [],
            'multiple' => false,
        ];
    }
    
    public function content_template() {
        ?>
        <div class="elementor-control-field">
            <label class="elementor-control-title">{{{ data.label }}}</label>
            <# if (data.description) { #>
                <div class="elementor-control-field-description">{{{ data.description }}}</div>
            <# } #>
            
            <div class="wtm-advanced-layout-wrapper">
                <# _.each(data.options, function(option, key) { #>
                    <div class="wtm-layout-option" data-value="{{ key }}">
                        <div class="wtm-layout-preview">
                            <# if (option.preview) { #>
                                <img src="{{ option.preview }}" alt="{{ option.title }}">
                            <# } #>
                        </div>
                        <div class="wtm-layout-info">
                            <h4>{{ option.title }}</h4>
                            <# if (option.description) { #>
                                <p>{{ option.description }}</p>
                            <# } #>
                        </div>
                        <# if (option.pro && data.is_free) { #>
                            <span class="wtm-pro-badge">PRO</span>
                        <# } #>
                    </div>
                <# }); #>
            </div>
        </div>
        <?php
    }
}