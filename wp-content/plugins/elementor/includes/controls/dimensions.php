<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A Group of Dimensions settings (Top, Right, Bottom, Left) With the option to link them together
 *
 * @property array $default {
 * 		@type integer $top      Default 0
 * 		@type integer $right    Default 0
 * 		@type integer $bottom   Default 0
 * 		@type integer $left     Default 0
 * 		@type string  $unit     The selected CSS Unit. 'px', '%', 'em'
 *                         		Default 'px'
 * 		@type bool    $isLinked Whether to link them together ( prevent set different values )
 *                         		Default true
 * }
 *
 * @property array        $size_units            Array of available CSS Units like 'px', '%', 'em'
 * @property array|string $allowed_dimensions    Which fields to show, 'all' | 'horizontal' | 'vertical' | [ 'top', 'left' ... ]
 *                                              Default 'all'
 *
 * @since 1.0.0
 */
class Control_Dimensions extends Control_Base_Units {

	public function get_type() {
		return 'dimensions';
	}

	public function get_default_value() {
		return array_merge( parent::get_default_value(), [
			'top' => '',
			'right' => '',
			'bottom' => '',
			'left' => '',
			'isLinked' => true,
		] );
	}

	protected function get_default_settings() {
		return array_merge( parent::get_default_settings(), [
			'label_block' => true,
			'allowed_dimensions' => 'all',
			'placeholder' => '',
		] );
	}

	public function content_template() {
		$dimensions = [
			'top' => __( 'Top', 'elementor' ),
			'right' => __( 'Right', 'elementor' ),
			'bottom' => __( 'Bottom', 'elementor' ),
			'left' => __( 'Left', 'elementor' ),
		];
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<?php $this->print_units_template(); ?>
			<div class="elementor-control-input-wrapper">
				<ul class="elementor-control-dimensions">
					<?php foreach ( $dimensions as $dimension_key => $dimension_title ) : ?>
						<li class="elementor-control-dimension">
							<input type="number" data-setting="<?php echo esc_attr( $dimension_key ); ?>"
							       placeholder="<#
						       if ( _.isObject( data.placeholder ) ) {
						        if ( ! _.isUndefined( data.placeholder.<?php echo $dimension_key; ?> ) ) {
						            print( data.placeholder.<?php echo $dimension_key; ?> );
						        }
						       } else {
						        print( data.placeholder );
						       } #>"
							<# if ( -1 === _.indexOf( allowed_dimensions, '<?php echo $dimension_key; ?>' ) ) { #>
								disabled
								<# } #>
									/>
									<span><?php echo $dimension_title; ?></span>
						</li>
					<?php endforeach; ?>
					<li>
						<button class="elementor-link-dimensions tooltip-target" data-tooltip="<?php _e( 'Link values together', 'elementor' ); ?>">
							<span class="elementor-linked"><i class="fa fa-link"></i></span>
							<span class="elementor-unlinked"><i class="fa fa-chain-broken"></i></span>
						</button>
					</li>
				</ul>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}
