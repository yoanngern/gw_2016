<?php
namespace ElementorPro\Modules\Forms;

use ElementorPro\Base\Module_Base;
use ElementorPro\Modules\Forms\Classes\AAL_Handler;
use ElementorPro\Modules\Forms\Classes\Ajax_Handler;
use ElementorPro\Modules\Forms\Classes\CF7DB_Handler;
use ElementorPro\Modules\Forms\Classes\Recaptcha_Handler;
use ElementorPro\Modules\Forms\Classes\Webhooks_Handler;
use ElementorPro\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Module_Base {

	public function get_name() {
		return 'forms';
	}

	public function get_widgets() {
		return [
			'Form',
		];
	}

	public function localize_settings() {
		Plugin::instance()->add_localize_settings( 'i18n', [
			'Field' => __( 'Field', 'elementor-pro' ),
		] );
	}

	public function add_actions() {
		add_action( 'elementor_pro/enqueue_panel_scripts/before_localize', [ $this, 'localize_settings' ] );
	}

	public function __construct() {
		parent::__construct();

		$this->add_actions();

		$this->add_component( 'recaptcha', new Recaptcha_Handler() );

		// Activity Log plugin (needs to run also in admin dashboard)
		if ( function_exists( 'aal_insert_log' ) ) {
			$this->add_component( 'aal_handler', new AAL_Handler() );
		}

		// Handlers
		if ( Ajax_Handler::is_form_submitted() ) {
			$this->add_component( 'ajax_handler', new Ajax_Handler() );
			$this->add_component( 'webhooks_handler', new Webhooks_Handler() );
			$this->add_component( 'cf7db_handler', new CF7DB_Handler() );

			do_action( 'elementor_pro/forms/form_submitted', $this );
		}
	}
}
