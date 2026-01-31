<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responses admin page controller.
 */
final class Responses_Page {
    /**
	 * Render responses page.
	 *
	 * @return void
	 */
	public static function render_responses(): void {
		require PPLS_PATH . 'admin/views/responses-page.php';
	}
}
