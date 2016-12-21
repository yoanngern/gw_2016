<?php
namespace ElementorPro\License;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Updater {

	public $plugin_version;
	public $plugin_name;
	public $plugin_slug;

	public function __construct() {
		$this->plugin_version = ELEMENTOR_PRO_VERSION;
		$this->plugin_name = ELEMENTOR_PRO_PLUGIN_BASE;
		$this->plugin_slug = basename( ELEMENTOR_PRO__FILE__, '.php' );

		$this->init();
	}

	private function init() {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api_filter' ], 10, 3 );
	}

	public function check_update( $_transient_data ) {
		global $pagenow;

		if ( ! is_object( $_transient_data ) ) {
			$_transient_data = new \stdClass;
		}

		if ( 'plugins.php' === $pagenow && is_multisite() ) {
			return $_transient_data;
		}

		$version_info = API::get_version();
		if ( ! is_wp_error( $version_info ) && ! empty( $version_info['new_version'] ) ) {
			if ( version_compare( $this->plugin_version, $version_info['new_version'], '<' ) ) {
				$_transient_data->response[ $this->plugin_name ] = (object) $version_info;
			}

			$_transient_data->last_checked = current_time( 'timestamp' );
			$_transient_data->checked[ $this->plugin_name ] = $this->plugin_version;
		}

		return $_transient_data;
	}

	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {
		if ( 'plugin_information' !== $_action ) {
			return $_data;
		}

		if ( ! isset( $_args->slug ) || ( $_args->slug !== $this->plugin_slug ) ) {
			return $_data;
		}

		$cache_key = 'elementor_pro_api_request_' . substr( md5( serialize( $this->plugin_slug ) ), 0, 15 );

		$api_request_transient = get_site_transient( $cache_key );

		if ( empty( $api_request_transient ) ) {
			$api_response = API::get_version();

			$_data = new \stdClass();

			$_data->name = 'Elementor Pro';
			$_data->slug = $this->plugin_slug;
			$_data->author = '<a href="https://elementor.com/">Elementor.com</a>';
			$_data->homepage = 'https://elementor.com/';

			$_data->version = $api_response['new_version'];
			$_data->last_updated = $api_response['last_updated'];
			$_data->download_link = $api_response['download_link'];
			$_data->banners = [
				'high' => 'https://ps.w.org/elementor/assets/banner-1544x500.png?rev=1494133',
				'low' => 'https://ps.w.org/elementor/assets/banner-1544x500.png?rev=1494133',
			];

			$_data->sections = unserialize( $api_response['sections'] );

			//Expires in 1 day
			set_site_transient( $cache_key, $_data, DAY_IN_SECONDS );
		}

		$_data = $api_request_transient;

		return $_data;
	}
}
