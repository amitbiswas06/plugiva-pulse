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
	 * Main AJAX handler.
     * assert → nonce → payload → spam → pulse → answers → store → success
	 *
	 * @return void
	 */
	public static function handle(): void {

		self::assert_ajax();
		self::verify_nonce();

		$payload = self::get_payload();

		self::validate_spam( $payload );
		$pulse = self::validate_pulse( $payload['pulse_id'] );
		self::validate_answers( 
            $pulse, 
            $payload['answers'] 
        );

        self::store_responses( 
            $pulse, 
            $payload['answers'], 
            $payload['meta']['hash'] 
        );

		wp_send_json_success( [ 'received' => true ] );
	}

	/* -------------------------------------------------------------------------
	 * Guards & Payload
	 * ---------------------------------------------------------------------- */

	private static function assert_ajax(): void {
		if ( ! wp_doing_ajax() ) {
			wp_send_json_error( [ 'message' => 'Invalid request.' ], 400 );
		}
	}

	private static function verify_nonce(): void {
		check_ajax_referer( 'ppls_submit', 'nonce' );
	}

	private static function get_payload(): array {

		$pulse_id = isset( $_POST['pulse_id'] )
			? sanitize_text_field( wp_unslash( $_POST['pulse_id'] ) )
			: '';

		$answers = isset( $_POST['answers'] ) && is_array( $_POST['answers'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['answers'] ) )
			: [];

		$meta = isset( $_POST['meta'] ) && is_array( $_POST['meta'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['meta'] ) )
			: [];

		if ( empty( $pulse_id ) ) {
			wp_send_json_error( [ 'message' => 'Missing pulse.' ], 400 );
		}

		return [
			'pulse_id' => $pulse_id,
			'answers'  => $answers,
			'meta'     => $meta,
		];
	}

	/* -------------------------------------------------------------------------
	 * Spam Protection
	 * ---------------------------------------------------------------------- */

	private static function validate_spam( array $payload ): void {

		$meta     = $payload['meta'];
		$pulse_id = $payload['pulse_id'];

		// Honeypot.
		if ( ! empty( $meta['ppls_hp'] ) ) {
			wp_send_json_error( [ 'message' => 'Invalid submission.' ], 400 );
		}

		// Time-to-submit (min 3s).
		$started_at = isset( $meta['started_at'] ) ? absint( $meta['started_at'] ) : 0;
		if ( $started_at > 0 && ( time() - $started_at ) < 3 ) {
			wp_send_json_error( [ 'message' => 'Submission too fast.' ], 400 );
		}

		// Session hash.
		$expected = hash_hmac(
			'sha256',
			$pulse_id . '|' . ( $_SERVER['HTTP_USER_AGENT'] ?? '' ) . '|' . gmdate( 'Y-m-d-H' ),
			wp_salt()
		);

		if ( empty( $meta['hash'] ) || ! hash_equals( $expected, $meta['hash'] ) ) {
			wp_send_json_error( [ 'message' => 'Invalid session.' ], 403 );
		}
	}

	/* -------------------------------------------------------------------------
	 * Pulse Validation
	 * ---------------------------------------------------------------------- */

	private static function validate_pulse( string $pulse_id ): array {

		$pulse = Pulses::get( $pulse_id );

		if ( is_wp_error( $pulse ) || empty( $pulse ) ) {
			wp_send_json_error( [ 'message' => 'Invalid pulse.' ], 404 );
		}

		if ( empty( $pulse['enabled'] ) ) {
			wp_send_json_error( [ 'message' => 'Pulse is not active.' ], 403 );
		}

		return $pulse;
	}

	/* -------------------------------------------------------------------------
	 * Answer Validation
	 * Sanitize and validate submitted answers.
	 * ---------------------------------------------------------------------- */

	private static function validate_answers( array $pulse, array $answers ): void {

		$questions = isset( $pulse['questions'] ) && is_array( $pulse['questions'] )
			? array_values( $pulse['questions'] )
			: [];

		if ( ! is_array( $answers ) ) {
			wp_send_json_error( [ 'message' => 'Invalid answers.' ], 400 );
		}

		if ( count( $answers ) > count( $questions ) ) {
			wp_send_json_error( [ 'message' => 'Invalid answers.' ], 400 );
		}

		foreach ( $questions as $index => $question ) {

			$key = 'q' . $index;

			if ( ! array_key_exists( $key, $answers ) ) {
				continue; // Optional questions (MVP).
			}

			$value = $answers[ $key ];

			if ( ! is_scalar( $value ) ) {
				wp_send_json_error( [ 'message' => 'Invalid answer.' ], 400 );
			}

			$raw = wp_unslash( (string) $value );

			switch ( $question['type'] ) {

				case 'yesno':
					$value = sanitize_text_field( $raw );
					if ( ! in_array( $value, [ 'yes', 'no' ], true ) ) {
						wp_send_json_error( [ 'message' => 'Invalid answer.' ], 400 );
					}
					break;

				case 'emoji':
					$value = sanitize_text_field( $raw );
					if ( ! in_array( $value, [ 'happy', 'neutral', 'sad' ], true ) ) {
						wp_send_json_error( [ 'message' => 'Invalid answer.' ], 400 );
					}
					break;

				case 'text':
					$value = sanitize_textarea_field( $raw );
					if ( strlen( $value ) > 500 ) {
						wp_send_json_error( [ 'message' => 'Answer too long.' ], 400 );
					}
					break;

				default:
					wp_send_json_error( [ 'message' => 'Invalid question type.' ], 400 );
			}
		}
	}

    /**
     * Store validated responses into the database.
     *
     * @param array $pulse   Pulse configuration.
     * @param array $answers Submitted answers.
     * @return void
     */
    private static function store_responses( array $pulse, array $answers, string $session_hash ): void {

        global $wpdb;

        $table = $wpdb->prefix . 'ppls_responses';

        $questions = isset( $pulse['questions'] ) && is_array( $pulse['questions'] )
            ? array_values( $pulse['questions'] )
            : [];

        $now = current_time( 'mysql' );

        foreach ( $questions as $index => $question ) {

            $key = 'q' . $index;

            if ( ! array_key_exists( $key, $answers ) ) {
                continue; // Question not answered (allowed in MVP).
            }

            $value = trim( (string) $answers[ $key ] );

			if ( $value === '' ) {
				continue;
			}

            $wpdb->insert(
                $table,
                [
                    'pulse_id'       => $pulse['id'],
                    'question_index' => $index,
                    'question_label' => $question['label'],
                    'question_type'  => $question['type'],
                    'answer'         => $value,
                    'session_hash'   => $session_hash,
                    'created_at'     => $now,
                ],
                [
                    '%s', // pulse_id
                    '%d', // question_index
                    '%s', // question_label
                    '%s', // question_type
                    '%s', // answer
                    '%s', // session_hash
                    '%s', // created_at
                ]
            );
        }
    }

}
