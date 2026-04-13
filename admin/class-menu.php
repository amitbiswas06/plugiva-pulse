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
			esc_html__( 'Plugiva Pulse', 'plugiva-pulse' ),
			esc_html__( 'Plugiva Pulse', 'plugiva-pulse' ),
			'manage_options',
			'ppls-pulses',
			[ Pulses_Page::class, 'render_list' ],
			'dashicons-heart'
		);

		add_submenu_page(
			'ppls-pulses',
			esc_html__( 'Pulses', 'plugiva-pulse' ),
			esc_html__( 'Pulses', 'plugiva-pulse' ),
			'manage_options',
			'ppls-pulses',
			[ Pulses_Page::class, 'render_list' ]
		);

		add_submenu_page(
			'ppls-pulses',
			esc_html__( 'Inline Feedback', 'plugiva-pulse' ),
			esc_html__( 'Inline', 'plugiva-pulse' ),
			'manage_options',
			'ppls-inline',
			[ Inline_Page::class, 'render' ]
		);

		add_submenu_page(
			'ppls-pulses',
			esc_html__( 'Responses', 'plugiva-pulse' ),
			esc_html__( 'Responses', 'plugiva-pulse' ),
			'manage_options',
			'ppls-responses',
			[ Responses_Page::class, 'render_responses' ]
		);
	}
}
