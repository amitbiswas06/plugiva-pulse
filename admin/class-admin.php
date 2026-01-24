<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin bootstrap for Plugiva Pulse.
 */
final class Admin {

	public static function init() {
		Menu::register();
	}
}
