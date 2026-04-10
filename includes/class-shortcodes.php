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

		// NEW: Inline question shortcode
		// @since 1.2.0
		add_shortcode( 'ppls_question', [ __CLASS__, 'render_question' ] );
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

		$pulse_id = isset( $atts['id'] )
			? sanitize_key( $atts['id'] )
			: '';

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

	/**
	 * Shortcode callback for inline question.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 * @since 1.2.0
	 */
	public static function render_question( array $atts ): string {

		return Pulse_Renderer::render_question_shortcode( $atts );
	}

}
