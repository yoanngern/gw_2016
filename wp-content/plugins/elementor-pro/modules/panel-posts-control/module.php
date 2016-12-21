<?php
namespace ElementorPro\Modules\PanelPostsControl;

use Elementor\Plugin;
use ElementorPro\Base\Module_Base;
use ElementorPro\Modules\PanelPostsControl\Controls\Group_Control_Posts;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	public function __construct() {
		parent::__construct();

		$this->add_actions();
	}

	public function get_name() {
		return 'panel-posts-control';
	}

	public function ajax_posts_filter_autocomplete() {
		if ( empty( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'elementor-editing' ) ) {
			wp_send_json_error( new \WP_Error( 'token_expired' ) );
		}

		if ( empty( $_POST['filter_type'] ) || empty( $_POST['q'] ) ) {
			wp_send_json_error( new \WP_Error( 'Bad Request' ) );
		}

		$results = [];

		if ( 'taxonomy' === $_POST['filter_type'] ) {
			$query_params = [
				'taxonomy' => $_POST['object_type'],
				'search'   => $_POST['q'],
			];

			$terms = get_terms( $query_params );

			foreach ( $terms as $term ) {
				$results[] = [
					'id'   => $term->term_id,
					'text' => $term->name,
				];
			}
		} elseif ( 'by_id' === $_POST['filter_type'] ) {
			$query_params = [
				'post_type' => $_POST['object_type'],
				's'         => $_POST['q'],
			];

			$query = new \WP_Query( $query_params );

			foreach ( $query->posts as $post ) {
				$results[] = [
					'id'   => $post->ID,
					'text' => $post->post_title,
				];
			}
		}

		wp_send_json_success(
			[
				'results' => $results,
			]
		);
	}

	public function ajax_posts_filters_values() {
		if ( empty( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], 'elementor-editing' ) ) {
			wp_send_json_error( new \WP_Error( 'token_expired' ) );
		}

		$results = [];

		foreach ( $_POST['views'] as $view_cid => $data ) {

			if ( 'taxonomy' === $data['filter_type'] ) {

				$terms = get_terms(
					[
						'include' => $data['value'],
					]
				);

				foreach ( $terms as $term ) {
					$results[ $view_cid ][ $term->term_id ] = $term->name;
				}
			} elseif ( 'by_id' === $data['filter_type'] ) {
				$query = new \WP_Query(
					[
						'post_type' => 'any',
						'post__in'  => $data['value'],
					]
				);

				foreach ( $query->posts as $post ) {
					$results[ $view_cid ][ $post->ID ] = $post->post_title;
				}
			}
		}

		wp_send_json_success( $results );
	}

	public static function get_query_args( $control_id, $settings ) {
		$defaults = [
			$control_id . '_post_type' => 'post',
			$control_id . '_posts_ids' => [],
			'orderby' => 'date',
			'order' => 'desc',
			'posts_per_page' => 3,
			'offset' => 0,
		];

		$settings = wp_parse_args( $settings, $defaults );

		$post_type = $settings[ $control_id . '_post_type' ];

		$query_args = [
			'orderby' => $settings['orderby'],
			'order' => $settings['order'],
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish', // Hide drafts/private posts for admins
		];

		if ( 'by_id' === $post_type ) {
			$query_args['post_type'] = 'any';
			$query_args['post__in']  = $settings[ $control_id . '_posts_ids' ];

			if ( empty( $query_args['post__in'] ) ) {
				// If no selection - return an empty query
				$query_args['post__in'] = [ -1 ];
			}
		} else {
			$query_args['post_type'] = $post_type;
			$query_args['posts_per_page'] = $settings['posts_per_page'];
			$query_args['tax_query'] = [];
			$query_args['offset'] = $settings['offset'];

			$taxonomies = get_object_taxonomies( $post_type, 'objects' );

			foreach ( $taxonomies as $object ) {
				$setting_key = $control_id . '_' . $object->name . '_ids';

				if ( ! empty( $settings[ $setting_key ] ) ) {
					$query_args['tax_query'][] = [
						'taxonomy' => $object->name,
						'field'    => 'terms_ids',
						'terms'    => $settings[ $setting_key ],
					];
				}
			}
		}

		return $query_args;
	}

	protected function add_actions() {
		add_action( 'wp_ajax_elementor_pro_panel_posts_control_filter_autocomplete', [ $this, 'ajax_posts_filter_autocomplete' ] );
		add_action( 'wp_ajax_elementor_pro_panel_posts_control_filters_values', [ $this, 'ajax_posts_filters_values' ] );
		Plugin::instance()->controls_manager->add_group_control( Group_Control_Posts::get_type(), new Group_Control_Posts() );
	}
}
