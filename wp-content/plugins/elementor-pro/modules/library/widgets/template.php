<?php
namespace ElementorPro\Modules\Library\Widgets;

use Elementor\Controls_Manager;
use Elementor\Plugin;
use Elementor\Widget_Base;
use ElementorPro\Modules\Library\Module;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Template extends Widget_Base {

	public function get_name() {
		return 'template';
	}

	public function get_title() {
		return __( 'Template', 'elementor-pro' );
	}

	public function get_icon() {
		return 'eicon-document-file';
	}

	public function get_categories() {
		return [ 'pro-elements' ];
	}

	public function is_reload_preview_required() {
		return false;
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_template',
			[
				'label' => __( 'Template', 'elementor-pro' ),
			]
		);

		$templates = Module::get_templates();

		if ( empty( $templates ) ) {

			$this->add_control(
				'no_templates',
				[
					'label' => false,
					'type' => Controls_Manager::RAW_HTML,
					'raw' => Module::empty_templates_message(),
				]
			);

			return;
		}

		$options = [
			'0' => '— ' . __( 'Select', 'elementor-pro' ) . ' —',
		];

		$types = [];

		foreach ( $templates as $template ) {
			$options[ $template['template_id'] ] = $template['title'] . '(' . $template['type'] . ')';
			$types[ $template['template_id'] ] = $template['type'];
		}

		$this->add_control(
			'template_id',
			[
				'label' => __( 'Choose Template', 'elementor-pro' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => $options,
				'types' => $types,
				'label_block'  => 'true',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$template_id = $this->get_settings( 'template_id' );
		?>
		<div class="elementor-template">
			<?php
			echo Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
			?>
		</div>
		<?php
	}

	public function render_plain_content() {}
}
