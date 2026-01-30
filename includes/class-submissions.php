<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle Pulse submissions (AJAX).
 */
final class Submissions {

	/**
	 * Register AJAX hooks.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_ppls_submit_pulse', [ __CLASS__, 'handle' ] );
		add_action( 'wp_ajax_nopriv_ppls_submit_pulse', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle submission request.
	 *
	 * @return void
	 */
	public static function handle(): void {

		// Always return JSON.
		if ( ! wp_doing_ajax() ) {
			wp_send_json_error( [ 'message' => 'Invalid request.' ], 400 );
		}

		// Nonce check (frontend JS will provide this later).
		check_ajax_referer( 'ppls_submit', 'nonce' );

		// Basic payload extraction.
		$pulse_id = isset( $_POST['pulse_id'] )
			? sanitize_text_field( wp_unslash( $_POST['pulse_id'] ) )
			: '';

		$answers = isset( $_POST['answers'] ) && is_array( $_POST['answers'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['answers'] ) )
			: [];

		$meta = isset( $_POST['meta'] ) && is_array( $_POST['meta'] )
			? wp_unslash( $_POST['meta'] )
			: [];

		// Minimal validation (deeper validation comes in 6.3).
		if ( empty( $pulse_id ) ) {
			wp_send_json_error(
				[ 'message' => 'Missing pulse.' ],
				400
			);
		}

		// Pulse existence check.
		$pulse = Pulses::get( $pulse_id );

		if ( is_wp_error( $pulse ) || empty( $pulse ) ) {
			wp_send_json_error(
				[ 'message' => 'Invalid pulse.' ],
				404
			);
		}

		// For now: acknowledge receipt.
		wp_send_json_success( [ 'received' => true ] );
	}
}
