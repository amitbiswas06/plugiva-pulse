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

		Rest::register();
		Blocks::register();
		Submissions::register();

		// Register shortcodes at the right time
		add_action( 'init', [ $this, 'register_shortcodes' ] );

		if ( is_admin() ) {
			require_once PPLS_PATH . 'admin/class-admin.php';
			require_once PPLS_PATH . 'admin/class-menu.php';
			require_once PPLS_PATH . 'admin/class-pulses-page.php';

			Admin::init();
		}
	}

	public function register_shortcodes(): void {
		Shortcodes::register();
	}
}
