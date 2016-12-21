<?php
namespace ElementorPro\Modules\Woocommerce;

use ElementorPro\Base\Module_Base;

class Module extends Module_Base {

	public function get_name() {
		return 'woocommerce';
	}

	public function get_widgets() {
		return [
			'Products',
		];
	}
}
