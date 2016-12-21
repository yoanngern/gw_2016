<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class DB {

	/**
	 * Current DB version of the editor.
	 */
	const DB_VERSION = '0.4';

	const STATUS_PUBLISH = 'publish';
	const STATUS_DRAFT = 'draft';

	/**
	 * Save builder method.
	 *
	 * @since 1.0.0
	 *
*@param int          $post_id
	 * @param array  $posted
	 * @param string $status
	 *
	 * @return void
	 */
	public function save_editor( $post_id, $posted, $status = self::STATUS_PUBLISH ) {
		// Change the global post to current library post, so widgets can use `get_the_ID` and other post data
		if ( isset( $GLOBALS['post'] ) ) {
			$global_post = $GLOBALS['post'];
		}
		$GLOBALS['post'] = get_post( $post_id );

		$editor_data = $this->_get_editor_data( $posted );

		// We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
		$json_value = wp_slash( wp_json_encode( $editor_data ) );

		if ( self::STATUS_PUBLISH === $status ) {
			$this->remove_draft( $post_id );
			update_post_meta( $post_id, '_elementor_data', $json_value );
			$this->_save_plain_text( $post_id );
		} else {
			update_post_meta( $post_id, '_elementor_draft_data', $json_value );
		}

		update_post_meta( $post_id, '_elementor_version', self::DB_VERSION );

		// Restore global post
		if ( isset( $global_post ) ) {
			$GLOBALS['post'] = $global_post;
		} else {
			unset( $GLOBALS['post'] );
		}

		$css_file = new Post_CSS_File( $post_id );
		$css_file->update();

		do_action( 'elementor/editor/after_save', $post_id, $editor_data );
	}

	/**
	 * Get & Parse the builder from DB.
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @param string $status
	 *
	 * @return array
	 */
	public function get_builder( $post_id, $status = self::STATUS_PUBLISH ) {
		$data = $this->get_plain_editor( $post_id, $status );

		return $this->_get_editor_data( $data, true );
	}

	protected function _get_json_meta( $post_id, $key ) {
		$meta = get_post_meta( $post_id, $key, true );

		if ( is_string( $meta ) && ! empty( $meta ) ) {
			$meta = json_decode( $meta, true );
		}

		return $meta;
	}

	public function get_plain_editor( $post_id, $status = self::STATUS_PUBLISH ) {
		$data = $this->_get_json_meta( $post_id, '_elementor_data' );

		if ( self::STATUS_DRAFT === $status ) {
			$draft_data = $this->_get_json_meta( $post_id, '_elementor_draft_data' );

			if ( ! empty( $draft_data ) ) {
				$data = $draft_data;
			}

			if ( empty( $data ) ) {
				$data = $this->_get_new_editor_from_wp_editor( $post_id );
			}
		}

		return $data;
	}

	protected function _get_new_editor_from_wp_editor( $post_id ) {
		$post = get_post( $post_id );

		if ( empty( $post ) || empty( $post->post_content ) ) {
			return [];
		}

		$text_editor_widget_type = Plugin::instance()->widgets_manager->get_widget_types( 'text-editor' );

		// TODO: Better coding to start template for editor
		return [
			[
				'id' => Utils::generate_random_string(),
				'elType' => 'section',
				'elements' => [
					[
						'id' => Utils::generate_random_string(),
						'elType' => 'column',
						'elements' => [
							[
								'id' => Utils::generate_random_string(),
								'elType' => $text_editor_widget_type::get_type(),
								'widgetType' => $text_editor_widget_type->get_name(),
								'settings' => [
									'editor' => $post->post_content,
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Remove draft data from DB.
	 *
	 * @since 1.0.0
	 * @param $post_id
	 *
	 * @return void
	 */
	public function remove_draft( $post_id ) {
		delete_post_meta( $post_id, '_elementor_draft_data' );
	}

	/**
	 * Get edit mode by Page ID
	 *
	 * @since 1.0.0
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function get_edit_mode( $post_id ) {
		return get_post_meta( $post_id, '_elementor_edit_mode', true );
	}

	/**
	 * Setup the edit mode per Page ID
	 *
	 * @since 1.0.0
	 * @param int $post_id
	 * @param string $mode
	 *
	 * @return void
	 */
	public function set_edit_mode( $post_id, $mode = 'builder' ) {
		if ( 'builder' === $mode ) {
			update_post_meta( $post_id, '_elementor_edit_mode', $mode );
		} else {
			delete_post_meta( $post_id, '_elementor_edit_mode' );
		}
	}

	private function _render_element_plain_content( $element_data ) {
		if ( 'widget' === $element_data['elType'] ) {
			/** @var Widget_Base $widget */
			$widget = Plugin::instance()->elements_manager->create_element_instance( $element_data );

			$widget->render_plain_content();
		}

		if ( ! empty( $element_data['elements'] ) ) {
			foreach ( $element_data['elements'] as $element ) {
				$this->_render_element_plain_content( $element );
			}
		}
	}

	private function _save_plain_text( $post_id ) {
		ob_start();

		$data = $this->get_plain_editor( $post_id );

		if ( $data ) {
			foreach ( $data as $element_data ) {
				$this->_render_element_plain_content( $element_data );
			}
		}

		$plain_text = ob_get_clean();

		// Remove unnecessary tags.
		$plain_text = preg_replace( '/<\/?div[^>]*\>/i', '', $plain_text );
		$plain_text = preg_replace( '/<\/?span[^>]*\>/i', '', $plain_text );
		$plain_text = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $plain_text );
		$plain_text = preg_replace( '/<i [^>]*><\\/i[^>]*>/', '', $plain_text );
		$plain_text = preg_replace( '/ class=".*?"/', '', $plain_text );

		// Remove empty lines.
		$plain_text = preg_replace( '/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', "\n", $plain_text );

		wp_update_post(
			[
				'ID' => $post_id,
				'post_content' => $plain_text,
			]
		);
	}

	/**
	 * Sanitize posted data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data
	 *
	 * @param bool $with_html_content
	 *
	 * @return array
	 */
	private function _get_editor_data( $data, $with_html_content = false ) {
		$editor_data = [];

		foreach ( $data as $element_data ) {
			$element = Plugin::instance()->elements_manager->create_element_instance( $element_data );

			$editor_data[] = $element->get_raw_data( $with_html_content );
		} // End Section

		return $editor_data;
	}

	public function iterate_data( $data_container, $callback ) {
		if ( isset( $data_container['elType'] ) ) {
			if ( ! empty( $data_container['elements'] ) ) {
				$data_container['elements'] = $this->iterate_data( $data_container['elements'], $callback );
			}

			return $callback( $data_container );
		}

		foreach ( $data_container as $element_key => $element_value ) {
			$data_container[ $element_key ] = $this->iterate_data( $data_container[ $element_key ], $callback );
		}

		return $data_container;
	}
}
