<?php
namespace ElementorPro\Modules\GlobalWidget\Widgets;

use Elementor\Plugin;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Global_Widget extends Widget_Base {
	/**
	 * @var Widget_Base
	 */
	private $_original_element_instance;

	public function __construct( $data = [], $args = null ) {
		if ( $data ) {
			$templates_manager = Plugin::instance()->templates_manager;

			$template_content = $templates_manager->get_template_content( [
				'source' => 'local',
				'template_id' => $data['templateID'],
			] );

			$data['settings'] = $template_content[0]['settings'];
		}

		parent::__construct( $data, $args );
	}

	public function show_in_panel() {
		return false;
	}

	public function get_raw_data( $with_html_content = false ) {
		$raw_data = parent::get_raw_data( $with_html_content );

		unset( $raw_data['settings'] );

		$raw_data['templateID'] = $this->get_data( 'templateID' );

		return $raw_data;
	}

	public function render_content() {
		$this->get_original_element_instance()->render_content();
	}

	public function get_unique_selector() {
		return '.elementor-global-' . $this->get_data( 'templateID' );
	}

	public function get_name() {
		return 'global';
	}

	public function get_controls( $control_id = null ) {
		if ( $this->is_type_instance() ) {
			return [];
		}

		return $this->get_original_element_instance()->get_controls();
	}

	public function get_original_element_instance() {
		if ( ! $this->_original_element_instance ) {
			$this->_init_original_element_instance();
		}

		return $this->_original_element_instance;
	}

	public function on_export() {
		return $this->_get_template_content();
	}

	protected function _add_render_attributes() {
		parent::_add_render_attributes();

		$skin_type = $this->get_settings( '_skin' );

		$original_widget_type = $this->get_original_element_instance()->get_data( 'widgetType' );

		$this->add_render_attribute( '_wrapper', [
			'class' => [
				'elementor-global-' . $this->get_data( 'templateID' ),
				'elementor-widget-' . $original_widget_type,
			],
		] );

		$this->set_render_attribute( '_wrapper', 'data-element_type', $original_widget_type . '.' . ( $skin_type ? $skin_type : 'default' ) );
	}

	private function _get_template_content() {
		$template_content = Plugin::instance()->templates_manager->get_template_content( [
			'source' => 'local',
			'template_id' => $this->get_data( 'templateID' ),
		] );

		return $template_content[0];
	}

	private function _init_original_element_instance() {
		$template_content = $this->_get_template_content();

		$widget_type = Plugin::instance()->widgets_manager->get_widget_types( $template_content['widgetType'] );

		$widget_class = $widget_type->get_class_name();

		$template_content['id'] = $this->get_id();

		$this->_original_element_instance = new $widget_class( $template_content, $widget_type->get_default_args() );
	}
}
