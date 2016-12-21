<?php
namespace ElementorPro\Modules\PanelPostsControl\Controls;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Group_Control_Posts extends Group_Control_Base {

	public static function get_type() {
		return 'posts';
	}

	protected function _get_controls( $args ) {
		$controls = [];

		$post_types = self::get_post_types( $args );

		$post_types_options = $post_types;
		$post_types_options['by_id'] = __( 'Manual Selection', 'elementor-pro' );

		$controls['post_type'] = [
			'label' => _x( 'Source', 'Posts Control', 'elementor-pro' ),
			'type' => Controls_Manager::SELECT,
			'default' => key( $post_types ),
			'options' => $post_types_options,
		];

		$controls['posts_ids'] = [
			'label' => _x( 'Search & Select', 'Posts Control', 'elementor-pro' ),
			'type' => Controls_Manager::SELECT2,
			'post_type' => '',
			'options'     => [],
			'label_block' => true,
			'multiple'    => true,
			'filter_type' => 'by_id',
			'object_type' => array_keys( $post_types ),
			'condition' => [
				'post_type' => 'by_id',
			],
		];

		$taxonomy_filter_args = [
			'show_in_nav_menus' => true,
		];

		if ( ! empty( $args['post_type'] ) ) {
			$taxonomy_filter_args['object_type'] = [ $args['post_type'] ];
		}

		$taxonomies = get_taxonomies( $taxonomy_filter_args, 'objects' );

		foreach ( $taxonomies as $taxonomy => $object ) {
			$controls[ $taxonomy . '_ids' ] = [
				'label'       => $object->label,
				'type'        => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'     => [],
				'multiple'    => true,
				'filter_type' => 'taxonomy',
				'object_type' => $taxonomy,
				'condition' => [
					'post_type' => $object->object_type,
				],
			];
		}

		return $controls;
	}

	private static function get_post_types( $args = [] ) {
		$post_type_args = [
			'show_in_nav_menus' => true,
		];

		if ( ! empty( $args['post_type'] ) ) {
			$post_type_args['name'] = $args['post_type'];
		}

		$_post_types = get_post_types( $post_type_args , 'objects' );

		$post_types  = [];

		foreach ( $_post_types as $post_type => $object ) {
			$post_types[ $post_type ] = $object->label;
		}

		return $post_types;
	}
}
