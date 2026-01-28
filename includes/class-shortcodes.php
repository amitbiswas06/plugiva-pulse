<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Shortcodes {

	/**
	 * Register shortcodes.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_shortcode( 'ppls_pulse', [ __CLASS__, 'render_pulse' ] );
	}

	/**
	 * Shortcode callback for rendering a pulse.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_pulse( array $atts ): string {

		$atts = shortcode_atts(
			[
				'id' => '',
			],
			$atts,
			'ppls_pulse'
		);

		$pulse_id = sanitize_text_field( $atts['id'] );

		if ( $pulse_id === '' ) {
			return '';
		}

		return Pulse_Renderer::render(
			$pulse_id,
			[
				'admin' => is_admin(),
			]
		);
	}
}
