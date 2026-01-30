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
            ? array_map( 'sanitize_text_field', wp_unslash( $_POST['meta'] ) )
            : [];

		// Minimal validation (deeper validation comes in 6.3).
		if ( empty( $pulse_id ) ) {
			wp_send_json_error(
				[ 'message' => 'Missing pulse.' ],
				400
			);
		}

        // --- Spam protection ---

        // Honeypot check.
        if ( ! empty( $meta['ppls_hp'] ) ) {
            wp_send_json_error(
                [ 'message' => 'Invalid submission.' ],
                400
            );
        }

        // Time-to-submit check (minimum 3 seconds).
        $started_at = isset( $meta['started_at'] )
            ? absint( $meta['started_at'] )
            : 0;

        if ( $started_at > 0 && ( time() - $started_at ) < 3 ) {
            wp_send_json_error(
                [ 'message' => 'Submission too fast.' ],
                400
            );
        }

        // Session hash verification.
        $expected_hash = hash_hmac(
            'sha256',
            $pulse_id . '|' . ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) . '|' . gmdate( 'Y-m-d-H' ),
            wp_salt()
        );

        if ( empty( $meta['hash'] ) || ! hash_equals( $expected_hash, $meta['hash'] ) ) {
            wp_send_json_error(
                [ 'message' => 'Invalid session.' ],
                403
            );
        }
        // --- End spam protection ---

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
