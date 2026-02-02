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
		Assets::register();

		// Register shortcodes at the right time
		add_action( 'init', [ $this, 'register_shortcodes' ] );

		if ( is_admin() ) {
			require_once PPLS_PATH . 'admin/class-admin.php';
			require_once PPLS_PATH . 'admin/class-menu.php';
			require_once PPLS_PATH . 'admin/class-pulses-page.php';
			require_once PPLS_PATH . 'admin/class-responses-page.php';
			require_once PPLS_PATH . 'admin/class-responses-table.php';

			Admin::init();
		}
	}

	public function register_shortcodes(): void {
		Shortcodes::register();
	}

	/**
	 * Build shared JS configuration.
	 *
	 * @return array
	 */
	public static function get_js_config(): array {
		return apply_filters(
			'ppls_js_config',
			[
				'i18n' => [
					'confirmDelete' => wp_strip_all_tags(__( 'Are you sure you want to delete? This action is permanent.', 'plugiva-pulse' ) ),
					'submitting'    => wp_strip_all_tags(__( 'Submitting…', 'plugiva-pulse' ) ),
					'thank_you'    	=> wp_strip_all_tags(__( 'Thank you for your response!', 'plugiva-pulse' ) ),
					'error'         => wp_strip_all_tags(__( 'Something went wrong. Please try again.', 'plugiva-pulse' ) ),
				],
				'flags' => [
					'hardDelete' => true,
				],
			]
		);
	}

}
