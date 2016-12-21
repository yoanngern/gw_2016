<?php
namespace ElementorPro\Modules\GlobalWidget;

use Elementor\Element_Base;
use Elementor\Post_CSS_File;
use Elementor\TemplateLibrary\Source_Local;
use Elementor\Widget_Base;
use Elementor\Plugin as ElementorPlugin;
use ElementorPro\Base\Module_Base;
use ElementorPro\Modules\GlobalWidget\Widgets\Global_Widget;
use ElementorPro\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	const TEMPLATE_TYPE = 'widget';

	const WIDGET_TYPE_META_KEY = '_elementor_template_widget_type';

	const SAVE_HASH_META_KEY = '_elementor_template_save_hash';

	const POST_GLOBAL_WIDGETS_META_KEY = '_elementor_global_widgets';

	const INCLUDED_POSTS_LIST_META_KEY = '_elementor_global_widget_included_posts';

	public function __construct() {
		parent::__construct();

		Source_Local::add_template_type( self::TEMPLATE_TYPE );

		Widget_Base::add_edit_tool( 'save', [
			'title' => __( 'Save', 'elementor-pro' ),
			'icon' => 'floppy-o',
		], 'duplicate' );

		ElementorPlugin::instance()->editor->add_editor_template( __DIR__ . '/views/panel-template.php' );

		$this->_add_hooks();
	}

	public function get_widgets() {
		return [
			'Global_Widget',
		];
	}

	public function get_name() {
		return 'global-widget';
	}

	public function add_templates_localize_data() {
		$templates_manager = ElementorPlugin::instance()->templates_manager;

		$widget_templates = array_filter( $templates_manager->get_source( 'local' )->get_items(), function( $template ) {
			return 'local' === $template['source'] && self::TEMPLATE_TYPE === $template['type'];
		} );

		$widget_templates_content = [];

		foreach ( $widget_templates as $widget_template ) {
			$widget_templates_content[ $widget_template['template_id'] ] = [
				'elType' => 'widget',
				'title' => $widget_template['title'],
				'widgetType' => $widget_template['widgetType'],
				'keywords' => $widget_template['keywords'],
			];
		}

		Plugin::instance()->add_localize_settings(
			[
				'widget_templates' => $widget_templates_content,
				'i18n' => [
					'unlink' => __( 'Unlink', 'elementor-pro' ),
					'cancel' => __( 'Cancel', 'elementor-pro' ),
					'unlink_widget' => __( 'Unlink Widget', 'elementor-pro' ),
					'dialog_confirm_unlink' => __( 'This will make the widget stop being global. It\'ll be reverted into being just a regular widget.', 'elementor-pro' ),
					'global_widget_save_title' => __( 'Save your widget as a global widget', 'elementor-pro' ),
					'global_widget_save_description' => __( 'You\'ll be able to add this global widget to multiple areas on your site, and edit it from one single place.', 'elementor-pro' ),
				],
			]
		);
	}

	public function set_template_widget_type_meta( $post_id, $template_data ) {
		if ( self::TEMPLATE_TYPE === $template_data['type'] ) {
			update_post_meta( $post_id, self::WIDGET_TYPE_META_KEY, $template_data['data'][0]['widgetType'] );
		}
	}

	public function update_template_hash( $post_id, $template_data ) {
		if ( self::TEMPLATE_TYPE === $template_data['type'] ) {
			update_post_meta( $post_id, self::SAVE_HASH_META_KEY, time() );
		}
	}

	public function update_post_global_widgets_hash( $post_id, $editor_data ) {
		$global_widget_ids = [];

		ElementorPlugin::instance()->db->iterate_data( $editor_data, function ( $element_data ) use ( & $global_widget_ids ) {
			if ( isset( $element_data['templateID'] ) ) {
				$global_widget_ids[ $element_data['templateID'] ] = true;
			}
		} );

		if ( ! $global_widget_ids ) {
			return;
		}

		foreach ( $global_widget_ids as $widget_id => & $meta_placeholder ) {
			$meta_placeholder = get_post_meta( $widget_id, self::SAVE_HASH_META_KEY, true );
		}

		update_post_meta( $post_id, self::POST_GLOBAL_WIDGETS_META_KEY, $global_widget_ids );

		$this->_set_global_widget_included_posts_list( $post_id, $global_widget_ids );
	}

	public function filter_template_data( $data ) {
		if ( self::TEMPLATE_TYPE === $data['type'] ) {
			$data['widgetType'] = get_post_meta( $data['template_id'], self::WIDGET_TYPE_META_KEY, true );
		}

		return $data;
	}

	public function is_post_css_require_update( $default_value, Post_CSS_File $post_css_file ) {
		if ( $default_value ) {
			return $default_value;
		}

		$global_widget_ids = get_post_meta( $post_css_file->get_post_id(), self::POST_GLOBAL_WIDGETS_META_KEY, true );

		if ( ! $global_widget_ids ) {
			return false;
		}

		foreach ( $global_widget_ids as $widget_id => $hash ) {
			$template_hash = get_post_meta( $widget_id, self::SAVE_HASH_META_KEY, true );

			if ( $hash !== $template_hash ) {
				return true;
			}
		}

		return false;
	}

	public function get_element_child_type( Element_Base $default_child_type, array $element_data ) {
		if ( isset( $element_data['templateID'] ) ) {
			$template_post = get_post( $element_data['templateID'] );

			if ( ! $template_post || 'trash' === $template_post->post_status ) {
				return false;
			}
		}

		return $default_child_type;
	}

	public function is_post_type_support_elementor( $is_supported, $post_id, $post_type ) {
		if ( ! $is_supported || Source_Local::CPT !== $post_type ) {
			return $is_supported;
		}

		return ! $this->is_widget_template( $post_id );
	}

	public function is_template_supports_export( $default_value, $template_id ) {
		return $default_value && ! $this->is_widget_template( $template_id );
	}

	public function remove_user_edit_cap( $all_caps, $caps, $args ) {
		$capability = $args[0];

		if ( empty( $args[2] ) ) {
			return $all_caps;
		}

		$post_id = $args[2];

		if ( 'edit_post' !== $capability ) {
			return $all_caps;
		}

		$post = get_post( $post_id );

		if ( Source_Local::CPT !== $post->post_type ) {
			return $all_caps;
		}

		if ( ! $this->is_widget_template( $post_id ) ) {
			return $all_caps;
		}

		$all_caps[ $caps[0] ] = false;

		return $all_caps;
	}

	public function is_widget_template( $template_id ) {
		$template_type = Source_Local::get_template_type( $template_id );

		return self::TEMPLATE_TYPE === $template_type;
	}

	private function _set_global_widget_included_posts_list( $post_id, $global_widget_ids ) {
		foreach ( $global_widget_ids as $widget_id => $hash ) {
			$included_posts = get_post_meta( $widget_id, self::INCLUDED_POSTS_LIST_META_KEY, true );

			$included_posts[ $post_id ] = true;

			update_post_meta( $widget_id, self::INCLUDED_POSTS_LIST_META_KEY, $included_posts );
		}
	}

	private function _add_hooks() {
		add_action( 'elementor/template-library/after_save_template', [ $this, 'set_template_widget_type_meta' ], 10, 2 );

		add_action( 'elementor/template-library/after_update_template', [ $this, 'update_template_hash' ] , 10, 2 );

		add_action( 'elementor/editor/after_save', [ $this, 'update_post_global_widgets_hash' ], 10, 2 );

		add_action( 'elementor_pro/enqueue_panel_scripts/before_localize', [ $this, 'add_templates_localize_data' ] );

		add_filter( 'elementor/template-library/get_template', [ $this, 'filter_template_data' ] );

		add_filter( 'elementor/css_file/update', [ $this, 'is_post_css_require_update' ], 10, 2 );

		add_filter( 'elementor/element/get_child_type', [ $this, 'get_element_child_type' ], 10, 2 );

		add_filter( 'elementor/utils/is_post_type_support', [ $this, 'is_post_type_support_elementor' ], 10, 3 );

		add_filter( 'user_has_cap', [ $this, 'remove_user_edit_cap' ], 10, 3 );

		add_filter( 'elementor/template_library/is_template_supports_export', [ $this, 'is_template_supports_export' ], 10, 2 );
	}
}
