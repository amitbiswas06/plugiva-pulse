<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Gutenberg blocks.
 */
final class Blocks {

	/**
	 * Register all blocks.
	 *
	 * @return void
	 */
	public static function register(): void {
		require_once PPLS_PATH . 'blocks/pulse/index.php';
	}
}
