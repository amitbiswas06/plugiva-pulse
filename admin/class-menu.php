<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers admin menus for Plugiva Pulse.
 */
final class Menu {

	public static function register() {
		add_action( 'admin_menu', [ __CLASS__, 'add_menu' ] );
	}

	public static function add_menu() {
		add_menu_page(
			__( 'Plugiva Pulse', 'plugiva-pulse' ),
			__( 'Plugiva Pulse', 'plugiva-pulse' ),
			'manage_options',
			'ppls-pulses',
			[ Pulses_Page::class, 'render_list' ],
			'dashicons-heart'
		);

		add_submenu_page(
			'ppls-pulses',
			__( 'Pulses', 'plugiva-pulse' ),
			__( 'Pulses', 'plugiva-pulse' ),
			'manage_options',
			'ppls-pulses',
			[ Pulses_Page::class, 'render_list' ]
		);

		add_submenu_page(
			'ppls-pulses',
			__( 'Responses', 'plugiva-pulse' ),
			__( 'Responses', 'plugiva-pulse' ),
			'manage_options',
			'ppls-responses',
			[ Pulses_Page::class, 'render_responses_stub' ]
		);
	}
}
