<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Control_Image_Dimensions extends Control_Base_Multiple {

	public function get_type() {
		return 'image_dimensions';
	}

	public function get_default_value() {
		return [
			'width' => '',
			'height' => '',
		];
	}

	protected function get_default_settings() {
		return [
			'label_block' => true,
			'show_label' => false,
		];
	}

	public function content_template() {
		if ( ! $this->_is_image_editor_supports() ) : ?>
		<div class="panel-alert panel-alert-danger">
			<?php _e( 'The server does not have ImageMagick or GD installed and/or enabled! Any of these libraries are required for WordPress to be able to resize images. Please contact your server administrator to enable this before continuing.', 'elementor' ); ?>
		</div>
		<?php
			return;
		endif;
		?>
		<# if ( data.description ) { #>
			<div class="elementor-control-description">{{{ data.description }}}</div>
		<# } #>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<div class="elementor-image-dimensions-field">
					<input type="text" data-setting="width" />
					<div class="elementor-image-dimensions-field-description"><?php _e( 'Width', 'elementor' ); ?></div>
				</div>
				<div class="elementor-image-dimensions-separator">x</div>
				<div class="elementor-image-dimensions-field">
					<input type="text" data-setting="height" />
					<div class="elementor-image-dimensions-field-description"><?php _e( 'Height', 'elementor' ); ?></div>
				</div>
				<button class="elementor-button elementor-button-success elementor-image-dimensions-apply-button"><?php _e( 'Apply', 'elementor' ); ?></button>
			</div>
		</div>
		<?php
	}

	private function _is_image_editor_supports() {
		$arg = [ 'mime_type' => 'image/jpeg' ];
		return ( wp_image_editor_supports( $arg ) );
	}
}
