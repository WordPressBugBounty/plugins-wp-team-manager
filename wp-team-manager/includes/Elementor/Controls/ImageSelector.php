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
class ImageSelectorControl extends Control {

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
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<# if ( data.description ) { #>
			<div class="elementor-control-field-description">{{{ data.description }}}</div>
			<# } #>
			<div class="elementor-control-image-selector-wrapper">
				<# _.each( data.options, function( options, value ) { #>
				<div class="image-selector-inner" title="{{ options.title }}" data-tooltip="{{ options.title }}">
					<input id="<?php echo esc_attr( $control_uid ); ?>" type="radio" name="elementor-image-selector-{{ data.name }}-{{ data._cid }}" value="{{ value }}" data-setting="{{ data.name }}">
					<label class="elementor-image-selector-label tooltip-target" for="<?php echo esc_attr( $control_uid ); ?>" data-tooltip="{{ options.title }}" title="{{ options.title }}">
						<img src="{{ options.url }}" alt="{{ options.title }}">
						<span class="elementor-screen-only">{{{ options.title }}}</span>
					</label>
				</div>
				<# } ); #>
			</div>
		</div>
		<?php
	}
}
