<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Editor {

	private $_is_edit_mode;

	private $_editor_templates = [
		'editor-templates/global.php',
		'editor-templates/panel.php',
		'editor-templates/panel-elements.php',
		'editor-templates/repeater.php',
		'editor-templates/templates.php',
	];

	public function init() {
		if ( is_admin() || ! $this->is_edit_mode() ) {
			return;
		}

		add_filter( 'show_admin_bar', '__return_false' );

		// Remove all WordPress actions
		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_head_scripts' );
		remove_all_actions( 'wp_footer' );

		// Handle `wp_head`
		add_action( 'wp_head', 'wp_enqueue_scripts', 1 );
		add_action( 'wp_head', 'wp_print_styles', 8 );
		add_action( 'wp_head', 'wp_print_head_scripts', 9 );
		add_action( 'wp_head', [ $this, 'editor_head_trigger' ], 30 );

		// Handle `wp_footer`
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'wp_footer', 'wp_auth_check_html', 30 );
		add_action( 'wp_footer', [ $this, 'wp_footer' ] );

		// Handle `wp_enqueue_scripts`
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 999999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 999999 );

		$post_id = get_the_ID();

		// Change mode to Builder
		Plugin::instance()->db->set_edit_mode( $post_id );

		// Post Lock
		if ( ! $this->get_locked_user( $post_id ) ) {
			$this->lock_post( $post_id );
		}

		// Setup default heartbeat options
		add_filter( 'heartbeat_settings', function( $settings ) {
			$settings['interval'] = 15;
			return $settings;
		} );

		// Tell to WP Cache plugins do not cache this request.
		Utils::do_not_cache();

		// Print the panel
		$this->print_panel_html();
		die;
	}

	public function is_edit_mode() {
		if ( null !== $this->_is_edit_mode ) {
			return $this->_is_edit_mode;
		}

		if ( ! User::is_current_user_can_edit() ) {
			return false;
		}

		if ( isset( $_GET['elementor'] ) ) {
			return true;
		}

		// Ajax request as Editor mode
		$actions = [
			'elementor_render_widget',

			// Templates
			'elementor_get_templates',
			'elementor_save_template',
			'elementor_get_template',
			'elementor_delete_template',
			'elementor_export_template',
			'elementor_import_template',
		];

		if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $actions ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param $post_id
	 */
	public function lock_post( $post_id ) {
		if ( ! function_exists( 'wp_set_post_lock' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/post.php' );
		}

		wp_set_post_lock( $post_id );
	}

	/**
	 * @param $post_id
	 *
	 * @return bool|\WP_User
	 */
	public function get_locked_user( $post_id ) {
		if ( ! function_exists( 'wp_check_post_lock' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/post.php' );
		}

		$locked_user = wp_check_post_lock( $post_id );
		if ( ! $locked_user ) {
			return false;
		}

		return get_user_by( 'id', $locked_user );
	}

	public function print_panel_html() {
		include( 'editor-templates/editor-wrapper.php' );
	}

	public function enqueue_scripts() {
		global $wp_styles, $wp_scripts;

		$post_id = get_the_ID();
		$plugin = Plugin::instance();

		$editor_data = $plugin->db->get_builder( $post_id, DB::STATUS_DRAFT );

		// Reset global variable
		$wp_styles = new \WP_Styles();
		$wp_scripts = new \WP_Scripts();

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		// Hack for waypoint with editor mode.
		wp_register_script(
			'waypoints',
			ELEMENTOR_ASSETS_URL . 'lib/waypoints/waypoints-for-editor.js',
			[
				'jquery',
			],
			'4.0.1',
			true
		);

		// Enqueue frontend scripts too
		$plugin->frontend->enqueue_scripts();

		wp_register_script(
			'backbone-marionette',
			ELEMENTOR_ASSETS_URL . 'lib/backbone/backbone.marionette' . $suffix . '.js',
			[
				'backbone',
			],
			'2.4.5',
			true
		);

		wp_register_script(
			'backbone-radio',
			ELEMENTOR_ASSETS_URL . 'lib/backbone/backbone.radio' . $suffix . '.js',
			[
				'backbone',
			],
			'1.0.4',
			true
		);

		wp_register_script(
			'perfect-scrollbar',
			ELEMENTOR_ASSETS_URL . 'lib/perfect-scrollbar/perfect-scrollbar.jquery' . $suffix . '.js',
			[
				'jquery',
			],
			'0.6.12',
			true
		);

		wp_register_script(
			'jquery-easing',
			ELEMENTOR_ASSETS_URL . 'lib/jquery-easing/jquery-easing' . $suffix . '.js',
			[
				'jquery',
			],
			'1.3.2',
			true
		);

		wp_register_script(
			'nprogress',
			ELEMENTOR_ASSETS_URL . 'lib/nprogress/nprogress' . $suffix . '.js',
			[],
			'0.2.0',
			true
		);

		wp_register_script(
			'tipsy',
			ELEMENTOR_ASSETS_URL . 'lib/tipsy/tipsy' . $suffix . '.js',
			[
				'jquery',
			],
			'1.0.0',
			true
		);

		wp_register_script(
			'imagesloaded',
			ELEMENTOR_ASSETS_URL . 'lib/imagesloaded/imagesloaded' . $suffix . '.js',
			[
				'jquery',
			],
			'4.1.0',
			true
		);

		wp_register_script(
			'elementor-dialog',
			ELEMENTOR_ASSETS_URL . 'lib/dialog/dialog' . $suffix . '.js',
			[
				'jquery-ui-position',
			],
			'3.0.0',
			true
		);

		wp_register_script(
			'jquery-select2',
			ELEMENTOR_ASSETS_URL . 'lib/select2/js/select2' . $suffix . '.js',
			[
				'jquery',
			],
			'4.0.2',
			true
		);

		wp_register_script(
			'jquery-simple-dtpicker',
			ELEMENTOR_ASSETS_URL . 'lib/jquery-simple-dtpicker/jquery.simple-dtpicker' . $suffix . '.js',
			[
				'jquery',
			],
			'1.12.0',
			true
		);

		wp_register_script(
			'ace',
			'https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.5/ace.js',
			[],
			'1.2.5',
			true
		);

		wp_register_script(
			'elementor-editor',
			ELEMENTOR_ASSETS_URL . 'js/editor' . $suffix . '.js',
			[
				'wp-auth-check',
				'jquery-ui-sortable',
				'jquery-ui-resizable',
				'backbone-marionette',
				'backbone-radio',
				'perfect-scrollbar',
				'jquery-easing',
				'nprogress',
				'tipsy',
				'imagesloaded',
				'heartbeat',
				'elementor-dialog',
				'jquery-select2',
				'jquery-simple-dtpicker',
				'ace',
			],
			$plugin->get_version(),
			true
		);

		do_action( 'elementor/editor/before_enqueue_scripts' );

		wp_enqueue_script( 'elementor-editor' );

		// Tweak for WP Admin menu icons
		wp_print_styles( 'editor-buttons' );

		$locked_user = $this->get_locked_user( $post_id );
		if ( $locked_user ) {
			$locked_user = $locked_user->display_name;
		}

		wp_localize_script(
			'elementor-editor',
			'ElementorConfig',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'home_url' => home_url(),
				'nonce' => wp_create_nonce( 'elementor-editing' ),
				'preview_link' => add_query_arg( 'elementor-preview', '', remove_query_arg( 'elementor' ) ),
				'elements_categories' => $plugin->elements_manager->get_categories(),
				'controls' => $plugin->controls_manager->get_controls_data(),
				'elements' => $plugin->elements_manager->get_element_types_config(),
				'widgets' => $plugin->widgets_manager->get_widget_types_config(),
				'schemes' => [
					'items' => $plugin->schemes_manager->get_registered_schemes_data(),
					'enabled_schemes' => Schemes_Manager::get_enabled_schemes(),
				],
				'default_schemes' => $plugin->schemes_manager->get_schemes_defaults(),
				'system_schemes' => $plugin->schemes_manager->get_system_schemes(),
				'wp_editor' => $this->_get_wp_editor_config(),
				'post_id' => $post_id,
				'post_permalink' => get_the_permalink(),
				'edit_post_link' => get_edit_post_link(),
				'settings_page_link' => Settings::get_url(),
				'elementor_site' => 'https://go.elementor.com/about-elementor/',
				'help_the_content_url' => 'https://go.elementor.com/the-content-missing/',
				'assets_url' => ELEMENTOR_ASSETS_URL,
				'data' => $editor_data,
				'locked_user' => $locked_user,
				'is_rtl' => is_rtl(),
				'locale' => get_locale(),
				'introduction' => User::get_introduction(),
				'viewportBreakpoints' => Responsive::get_breakpoints(),
				'rich_editing_enabled' => filter_var( get_user_meta( get_current_user_id(), 'rich_editing', true ), FILTER_VALIDATE_BOOLEAN ),
				'i18n' => [
					'elementor' => __( 'Elementor', 'elementor' ),
					'dialog_confirm_delete' => __( 'Are you sure you want to remove this {0}?', 'elementor' ),
					'dialog_user_taken_over' => __( '{0} has taken over and is currently editing. Do you want to take over this page editing?', 'elementor' ),
					'delete' => __( 'Delete', 'elementor' ),
					'cancel' => __( 'Cancel', 'elementor' ),
					'delete_element' => __( 'Delete {0}', 'elementor' ),
					'take_over' => __( 'Take Over', 'elementor' ),
					'go_back' => __( 'Go Back', 'elementor' ),
					'saved' => __( 'Saved', 'elementor' ),
					'before_unload_alert' => __( 'Please note: All unsaved changes will be lost.', 'elementor' ),
					'edit_element' => __( 'Edit {0}', 'elementor' ),
					'global_colors' => __( 'Global Colors', 'elementor' ),
					'global_fonts' => __( 'Global Fonts', 'elementor' ),
					'elementor_settings' => __( 'Elementor Settings', 'elementor' ),
					'soon' => __( 'Soon', 'elementor' ),
					'revisions_history' => __( 'Revisions History', 'elementor' ),
					'about_elementor' => __( 'About Elementor', 'elementor' ),
					'inner_section' => __( 'Columns', 'elementor' ),
					'dialog_confirm_gallery_delete' => __( 'Are you sure you want to reset this gallery?', 'elementor' ),
					'delete_gallery' => __( 'Reset Gallery', 'elementor' ),
					'gallery_images_selected' => __( '{0} Images Selected', 'elementor' ),
					'insert_media' => __( 'Insert Media', 'elementor' ),
					'preview_el_not_found_header' => __( 'Sorry, the content area was not found in your page.', 'elementor' ),
					'preview_el_not_found_message' => __( 'You must call \'the_content\' function in the current template, in order for Elementor to work on this page.', 'elementor' ),
					'learn_more' => __( 'Learn More', 'elementor' ),
					'an_error_occurred' => __( 'An error occurred', 'elementor' ),
					'templates_request_error' => __( 'The following error occurred when processing the request:', 'elementor' ),
					'save_your_template' => __( 'Save Your {0} to Library', 'elementor' ),
					'save_your_template_description' => __( 'Your designs will be available for export and reuse on any page or website', 'elementor' ),
					'page' => __( 'Page', 'elementor' ),
					'section' => __( 'Section', 'elementor' ),
					'delete_template' => __( 'Delete Template', 'elementor' ),
					'delete_template_confirm' => __( 'Are you sure you want to delete this template?', 'elementor' ),
					'color_picker' => __( 'Color Picker', 'elementor' ),
					'clear_page' => __( 'Delete All Content', 'elementor' ),
					'dialog_confirm_clear_page' => __( 'Attention! We are going to DELETE ALL CONTENT from this page. Are you sure you want to do that?', 'elementor' ),
					'asc' => __( 'Ascending order', 'elementor' ),
					'desc' => __( 'Descending order', 'elementor' ),
				],
			]
		);

		$plugin->controls_manager->enqueue_control_scripts();
	}

	public function enqueue_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$direction_suffix = is_rtl() ? '-rtl' : '';

		wp_register_style(
			'font-awesome',
			ELEMENTOR_ASSETS_URL . 'lib/font-awesome/css/font-awesome' . $suffix . '.css',
			[],
			'4.7.0'
		);

		wp_register_style(
			'select2',
			ELEMENTOR_ASSETS_URL . 'lib/select2/css/select2' . $suffix . '.css',
			[],
			'4.0.2'
		);

		wp_register_style(
			'elementor-icons',
			ELEMENTOR_ASSETS_URL . 'lib/eicons/css/elementor-icons' . $suffix . '.css',
			[],
			Plugin::instance()->get_version()
		);

		wp_register_style(
			'google-font-roboto',
			'https://fonts.googleapis.com/css?family=Roboto:300,400,500,700',
			[],
			Plugin::instance()->get_version()
		);

		wp_register_style(
			'jquery-simple-dtpicker',
			ELEMENTOR_ASSETS_URL . 'lib/jquery-simple-dtpicker/jquery.simple-dtpicker' . $suffix . '.css',
			[],
			'1.12.0'
		);

		wp_register_style(
			'elementor-editor',
			ELEMENTOR_ASSETS_URL . 'css/editor' . $direction_suffix . $suffix . '.css',
			[
				'font-awesome',
				'select2',
				'elementor-icons',
				'wp-auth-check',
				'google-font-roboto',
				'jquery-simple-dtpicker',
			],
			Plugin::instance()->get_version()
		);

		wp_enqueue_style( 'elementor-editor' );
	}

	protected function _get_wp_editor_config() {
		ob_start();
		wp_editor(
			'%%EDITORCONTENT%%',
			'elementorwpeditor',
			[
				'editor_class' => 'elementor-wp-editor',
				'editor_height' => 250,
				'drag_drop_upload' => true,
			]
		);
		return ob_get_clean();
	}

	public function editor_head_trigger() {
		do_action( 'elementor/editor/wp_head' );
	}

	public function add_editor_template( $template_path ) {
		$this->_editor_templates[] = $template_path;
	}

	public function wp_footer() {
		$plugin = Plugin::instance();

		$plugin->controls_manager->render_controls();
		$plugin->widgets_manager->render_widgets_content();
		$plugin->elements_manager->render_elements_content();

		$plugin->schemes_manager->print_schemes_templates();

		foreach ( $this->_editor_templates as $editor_template ) {
			include $editor_template;
		}
	}

	/**
	 * @param bool $edit_mode
	 */
	public function set_edit_mode( $edit_mode ) {
		$this->_is_edit_mode = $edit_mode;
	}

	public function __construct() {
		add_action( 'template_redirect', [ $this, 'init' ] );
	}
}
