<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend and shared assets loader.
 */
final class Assets {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend' ] );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public static function enqueue_frontend(): void {

		// Frontend JS.
		wp_enqueue_script(
			'ppls-frontend',
			PPLS_URL . 'assets/js/ppls-frontend.js',
			[],
			microtime(),
			true
		);

		wp_add_inline_script(
			'ppls-frontend',
			'window.PPLS = ' . wp_json_encode( Plugin::get_js_config() ) . ';',
			'before'
		);

		// Optional frontend CSS.
		wp_enqueue_style(
			'ppls-frontend',
			PPLS_URL . 'assets/css/ppls-frontend.css',
			[],
			microtime()
		);
	}
}
