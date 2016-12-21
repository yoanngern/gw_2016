<?php
namespace ElementorPro;

use Elementor\Utils;

if ( ! defined( 'ABSPATH' ) ) {	exit; } // Exit if accessed directly

/**
 * Main class plugin
 */
class Plugin {

	/**
	 * @var Plugin
	 */
	private static $_instance;

	/**
	 * @var Manager
	 */
	private $_modules_manager;

	/**
	 * @var array
	 */
	private $_localize_settings = [];

	/**
	 * @return string
	 */
	public function get_version() {
		return ELEMENTOR_PRO_VERSION;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'elementor-pro' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'elementor-pro' ), '1.0.0' );
	}

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function _includes() {
		require ELEMENTOR_PRO_PATH . 'includes/modules-manager.php';

		if ( is_admin() ) {
			require ELEMENTOR_PRO_PATH . 'includes/admin.php';
		}
	}

	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$filename = strtolower(
			preg_replace(
				[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$class
			)
		);
		$filename = ELEMENTOR_PRO_PATH . $filename . '.php';

		if ( is_readable( $filename ) ) {
			include( $filename );
		}
	}

	public function get_localize_settings() {
		return $this->_localize_settings;
	}

	public function add_localize_settings( $setting_key, $setting_value = null ) {
		if ( is_array( $setting_key ) ) {
			$this->_localize_settings = array_replace_recursive( $this->_localize_settings, $setting_key );

			return;
		}

		if ( ! is_array( $setting_value ) || ! isset( $this->_localize_settings[ $setting_key ] ) || ! is_array( $this->_localize_settings[ $setting_key ] ) ) {
			$this->_localize_settings[ $setting_key ] = $setting_value;

			return;
		}

		$this->_localize_settings[ $setting_key ] = array_replace_recursive( $this->_localize_settings[ $setting_key ], $setting_value );
	}

	public function enqueue_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$direction_suffix = is_rtl() ? '-rtl' : '';

		wp_enqueue_style(
			'elementor-pro',
			ELEMENTOR_PRO_URL . 'assets/css/frontend' . $direction_suffix . $suffix . '.css',
			[],
			Plugin::instance()->get_version()
		);

		if ( is_admin() ) {
			wp_enqueue_style(
				'elementor-pro-admin',
				ELEMENTOR_PRO_URL . 'assets/css/admin' . $direction_suffix . $suffix . '.css',
				[],
				Plugin::instance()->get_version()
			);
		}
	}

	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script(
			'elementor-pro-frontend',
			ELEMENTOR_PRO_URL . 'assets/js/frontend' . $suffix . '.js',
			[
				'jquery',
			],
			Plugin::instance()->get_version(),
			true
		);

		wp_localize_script(
			'elementor-pro-frontend',
			'ElementorProFrontendConfig',
			[
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'elementor-pro-frontend' ),
			]
		);
	}

	public function enqueue_panel_scripts() {
		$suffix = Utils::is_script_debug() ? '' : '.min';

		wp_enqueue_script(
			'elementor-pro',
			ELEMENTOR_PRO_URL . 'assets/js/editor' . $suffix . '.js',
			[
				'backbone-marionette',
			],
			Plugin::instance()->get_version(),
			true
		);

		do_action( 'elementor_pro/enqueue_panel_scripts/before_localize' );

		wp_localize_script(
			'elementor-pro',
			'ElementorProConfig',
			$this->get_localize_settings()
		);
	}

	public function enqueue_panel_styles() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'elementor-pro',
			ELEMENTOR_PRO_URL . 'assets/css/editor' . $suffix . '.css',
			[
				'elementor-editor'
			],
			Plugin::instance()->get_version()
		);
	}

	public function elementor_init() {
		$this->_modules_manager = new Manager();

		// Add element category in panel
		\Elementor\Plugin::instance()->elements_manager->add_category(
			'pro-elements',
			[
				'title' => __( 'Pro Elements', 'elementor-pro' ),
				'icon' => 'font',
			],
			1
		);
	}

	protected function add_actions() {
		add_action( 'elementor/init', [ $this, 'elementor_init' ] );

		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_panel_styles' ] );
		add_action( 'elementor/editor/before_enqueue_scripts', [ $this, 'enqueue_panel_scripts' ] );

		add_action( 'elementor/frontend/enqueue_scripts/before', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Plugin constructor.
	 */
	private function __construct() {
		spl_autoload_register( [ $this, 'autoload' ] );

		$this->_includes();

		$this->add_actions();

		if ( is_admin() ) {
			new Admin();
			new License\Admin();
		}
	}
}

if ( ! defined( 'ELEMENTOR_PRO_TESTS' ) ) {
	// In tests we run the instance manually.
	Plugin::instance();
}
