<?php
namespace Plugiva\Pulse;

use Plugiva\Pulse\Admin\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( is_admin() ) {
			require_once PPLS_PATH . 'admin/class-admin.php';
			require_once PPLS_PATH . 'admin/class-menu.php';
			require_once PPLS_PATH . 'admin/class-pulses-page.php';

			Admin::init();
		}
	}
}
