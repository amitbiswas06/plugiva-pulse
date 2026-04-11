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
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin' ] );
	}

	/**
	 * Enqueue frontend assets.
	 *
	 * @return void
	 */
	public static function enqueue_frontend(): void {

		wp_enqueue_script(
			'ppls-inline',
			PPLS_URL . 'assets/js/ppls-inline.js',
			[],
			PPLS_VERSION,
			true
		);

		// Frontend JS.
		wp_enqueue_script(
			'ppls-frontend',
			PPLS_URL . 'assets/js/ppls-frontend.js',
			[],
			PPLS_VERSION,
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
			PPLS_VERSION
		);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @return void
	 */
	public static function enqueue_admin(): void {
		
		wp_enqueue_script(
			'ppls-admin',
			PPLS_URL . 'assets/js/ppls-admin.js',
			[],
			PPLS_VERSION,
			true
		);

		wp_add_inline_script(
			'ppls-admin',
			'window.PPLS = ' . wp_json_encode( Plugin::get_js_config() ) . ';',
			'before'
		);
	}
}
