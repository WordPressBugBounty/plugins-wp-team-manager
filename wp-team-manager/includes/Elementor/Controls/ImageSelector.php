<?php
/**
 * Elementor Custom Control: Image Selector Class.
 *
 * @package Wp_Team_Manager
 */
namespace DWL\Wtm\Elementor;

if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Base_Data_Control as Control;

/**
 * Image Selector Class.
 */
class ImageSelector extends Control {

	/**
	 * Set control name.
	 *
	 * @var string
	 */
	const ImageSelector = 'wptm_image_selector';

	/**
	 * Set control type.
	 *
	 * @return string
	 */
	public function get_type() {
		return self::ImageSelector;
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * @return void
	 */
	public function enqueue() {
		wp_enqueue_style( 'wptm-image-selector', TM_ADMIN_ASSETS . '/css/image-selector.min.css', [], '1.0.0' );
	}

	/**
	 * Set default settings
	 *
	 * @return array
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'toggle'      => true,
			'options'     => [],
		];
	}

	/**
	 * Control field markup
	 *
	 * @return void
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid( '{{ value }}' );
		$is_admin_pro = 'is-pro';
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<# if ( data.description ) { #>
				<div class="elementor-control-field-description">{{{ data.description }}}</div>
			<# } #>
			
			
			<div class="elementor-control-image-selector-wrapper">
				<# _.each(data.options, function(options, value) { #>
					<# var is_pro_style = (
						(data.name === 'grid_style_type' && [ 'Style 5'].includes(options.title)) ||
						(data.name === 'slider_style_type' && ['Style 3','Style 4', 'Style 5', 'Style 6'].includes(options.title)) ||
						(data.name === 'isotope_style_type' && [ 'Style 1', 'Style 2'].includes(options.title))||
						(data.name === 'list_style_type' && [ 'Style 3'].includes(options.title))
					); #>
					<div class="image-selector-inner<# if ( is_pro_style && <?php echo json_encode(tmwstm_fs()-> is_not_paying()); ?> ) { #> <?php echo esc_attr($is_admin_pro); ?> <# } #>" 
						title="{{ options.title }}" data-tooltip="{{ options.title }}">
						
						<input id="<?php echo esc_attr($control_uid); ?>" type="radio" 
							name="elementor-image-selector-{{ data.name }}-{{ data._cid }}" 
							value="{{ value }}" data-setting="{{ data.name }}">
							
						<label class="elementor-image-selector-label tooltip-target" 
							for="<?php echo esc_attr($control_uid); ?>" 
							data-tooltip="{{ options.title }}" title="{{ options.title }}">
							<img src="{{ options.url }}" alt="{{ options.title }}">
							<span class="elementor-screen-only">{{{ options.title }}}</span>
						</label>
					</div>
				<# }); #>
			</div>
		</div>

		<?php
	}
}