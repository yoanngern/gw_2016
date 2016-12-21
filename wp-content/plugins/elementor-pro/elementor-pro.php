<?php
/**
 * Plugin Name: Elementor Pro
 * Description: Elementor Pro adds new features to the Elementor Page Builder plugin. Control your conversions, your user engagement, your entire website, from one page builder.
 * Plugin URI: https://elementor.com/
 * Author: Elementor.com
 * Version: 1.0.3
 * Author URI: https://elementor.com/
 *
 * Text Domain: elementor-pro
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'ELEMENTOR_PRO_VERSION', '1.0.3' );

define( 'ELEMENTOR_PRO__FILE__', __FILE__ );
define( 'ELEMENTOR_PRO_PLUGIN_BASE', plugin_basename( ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_PATH', plugin_dir_path( ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_MODULES_PATH', ELEMENTOR_PRO_PATH . 'modules/' );
define( 'ELEMENTOR_PRO_URL', plugins_url( '/', ELEMENTOR_PRO__FILE__ ) );
define( 'ELEMENTOR_PRO_ASSETS_URL', ELEMENTOR_PRO_URL . 'assets/' );
define( 'ELEMENTOR_PRO_MODULES_URL', ELEMENTOR_PRO_URL . 'modules/' );

/**
 * Load gettext translate for our text domain.
 *
 * @since 1.0.0
 *
 * @return void
 */
function elementor_pro_load_plugin() {
	load_plugin_textdomain( 'elementor-pro' );

	if ( ! did_action( 'elementor/loaded' ) ) {
		add_action( 'admin_notices', 'elementor_pro_fail_load' );
		return;
	}

	$elementor_version_required = '1.0.3';
	if ( ! version_compare( ELEMENTOR_VERSION, $elementor_version_required, '>=' ) ) {
		add_action( 'admin_notices', 'elementor_pro_fail_load_out_of_date' );
		return;
	}

	require( ELEMENTOR_PRO_PATH . 'plugin.php' );
}
add_action( 'plugins_loaded', 'elementor_pro_load_plugin' );

/**
 * Show in WP Dashboard notice about the plugin is not activated.
 *
 * @since 1.0.0
 *
 * @return void
 */
function elementor_pro_fail_load() {
	$screen = get_current_screen();
	if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
		return;
	}

	$plugin = 'elementor/elementor.php';

	if ( _is_elementor_installed() ) {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

		$message = '<p>' . __( '<strong>Elementor Pro isn\'t available yet.</strong> You need to Activate the Elementor plugin to activate it.', 'elementor-pro' ) . '</p>';
		$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Elementor Now', 'elementor-pro' ) ) . '</p>';
	} else {
		if ( ! current_user_can( 'install_plugins' ) ) {
			return;
		}

		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );

		$message = '<p>' . __( '<strong>Elementor Pro isn\'t available yet.</strong> You need to install the Elementor plugin to activate it.', 'elementor-pro' ) . '</p>';
		$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Elementor Now', 'elementor-pro' ) ) . '</p>';
	}

	echo '<div class="error"><p>' . $message . '</p></div>';
}

function elementor_pro_fail_load_out_of_date() {
	if ( ! current_user_can( 'update_plugins' ) ) {
		return;
	}

	$file_path = 'elementor/elementor.php';

	$upgrade_link = wp_nonce_url( self_admin_url( 'update.php?action=upgrade-plugin&plugin=' ) . $file_path, 'upgrade-plugin_' . $file_path );
	$message = '<p>' . __( '<strong>Elementor Pro isn\'t available yet.</strong> You need to Upgrade the Elementor plugin to activate it.', 'elementor-pro' ) . '</p>';
	$message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $upgrade_link, __( 'Upgrade Elementor Now', 'elementor-pro' ) ) . '</p>';

	echo '<div class="error">' . $message . '</div>';
}

if ( ! function_exists( '_is_elementor_installed' ) ) {

	function _is_elementor_installed() {
		$file_path = 'elementor/elementor.php';
		$installed_plugins = get_plugins();

		return isset( $installed_plugins[ $file_path ] );
	}
}
