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

		// NEW: route based on type
		// For backward compatibility, default to 'pulse' if type is missing.
		// @since 1.2.0
		if ( isset( $payload['type'] ) && $payload['type'] === 'question' ) {

			self::handle_inline_question( $payload );

		} else {

			$pulse = self::validate_pulse( $payload['pulse_id'] );

			self::validate_answers(
				$pulse,
				$payload['answers']
			);

			self::store_responses(
				$pulse,
				$payload['answers'],
				$payload['meta']['hash'],
				get_the_ID() ?: 0
			);
		}

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

	// Nonce verified once for the entire AJAX payload.
	private static function verify_nonce(): void {
		check_ajax_referer( 'ppls_submit', 'ppls_nonce' );
	}

	private static function get_payload(): array {

		$pulse_id = isset( $_POST['pulse_id'] )
			? sanitize_key( wp_unslash( $_POST['pulse_id'] ) )
			: '';

		$answers = isset( $_POST['answers'] ) && is_array( $_POST['answers'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['answers'] ) )
			: [];

		$meta = isset( $_POST['meta'] ) && is_array( $_POST['meta'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['meta'] ) )
			: [];

		// NEW (inline support)
		// @since 1.2.0
		$type = isset( $_POST['type'] )
			? sanitize_key( wp_unslash( $_POST['type'] ) )
			: 'pulse'; // default to 'pulse' for backward compatibility

		$q_type = isset( $_POST['q_type'] ) && $_POST['q_type'] !== ''
			? sanitize_key( wp_unslash( $_POST['q_type'] ) )
			: 'yesno';

		$qid = isset( $_POST['qid'] )
			? sanitize_text_field( wp_unslash( $_POST['qid'] ) )
			: '';

		$question = isset( $_POST['question'] )
			? sanitize_text_field( wp_unslash( $_POST['question'] ) )
			: '';

		$answer = isset( $_POST['answer'] )
			? sanitize_text_field( wp_unslash( $_POST['answer'] ) )
			: '';

		$post_id = isset( $_POST['post_id'] )
			? absint( wp_unslash( $_POST['post_id'] ) )
			: 0;
		

		if ( empty( $pulse_id ) && empty( $qid ) ) {
			wp_send_json_error( [ 'message' => 'Missing identifier.' ], 400 );
		}

		return [
			'type'     => $type, // 'pulse' or 'question'
			'pulse_id' => $pulse_id,
			'answers'  => $answers,
			'meta'     => $meta,

			// NEW (inline support)
			// @since 1.2.0
			'q_type'   => $q_type, // question type (e.g., 'yesno', 'emoji', 'rating (custom)')
			'qid'      => $qid,
			'question' => $question,
			'answer'   => $answer,
			'post_id'  => $post_id,
		];
	}

	/* -------------------------------------------------------------------------
	 * Spam Protection
	 * ---------------------------------------------------------------------- */

	private static function validate_spam( array $payload ): void {

		$meta = $payload['meta'] ?? [];

		$type = isset( $payload['type'] )
			? sanitize_key( $payload['type'] )
			: 'pulse';

		// Unified identifier
		$id = ! empty( $payload['pulse_id'] )
			? sanitize_key( $payload['pulse_id'] )
			: sanitize_key( $payload['qid'] ?? '' );

		// --- Honeypot ---
		if ( ! empty( $meta['ppls_hp'] ) ) {
			wp_send_json_error( [ 'message' => 'Invalid submission.' ], 400 );
		}

		// --- Time-to-submit (min 3s) ---
		$started_at = isset( $meta['started_at'] ) ? absint( $meta['started_at'] ) : 0;
		if ( $started_at > 0 && ( time() - $started_at ) < 3 ) {
			wp_send_json_error( [ 'message' => 'Submission too fast.' ], 400 );
		}

		// --- Hash validation ---
		$received_hash = isset( $meta['hash'] )
			? sanitize_text_field( $meta['hash'] )
			: '';

		if ( empty( $received_hash ) ) {
			wp_send_json_error( [ 'message' => 'Invalid session.' ], 403 );
		}

		// Determine context
		$context = ( $type === 'question' ) ? 'inline' : 'pulse';

		// Get valid hashes from single source
		$valid_hashes = Hash_Utils::get_request_hashes( $id, $context );

		if ( ! in_array( $received_hash, $valid_hashes, true ) ) {
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
					$value = sanitize_key( $raw );
					if ( ! in_array( $value, [ 'yes', 'no' ], true ) ) {
						wp_send_json_error( [ 'message' => 'Invalid answer.' ], 400 );
					}
					break;

				case 'emoji':
					$value = sanitize_key( $raw );
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
    private static function store_responses(
		array $pulse,
		array $answers,
		string $session_hash,
		int $post_id = 0
	): void {

		global $wpdb;

		$table = $wpdb->prefix . 'ppls_responses';

		$questions = isset( $pulse['questions'] ) && is_array( $pulse['questions'] )
			? array_values( $pulse['questions'] )
			: [];

		$now = current_time( 'mysql' );

		// Normalize identifiers before DB write.
		$pulse_id     = sanitize_key( $pulse['id'] );
		$session_hash = sanitize_key( $session_hash );

		foreach ( $questions as $index => $question ) {

			$key = 'q' . $index;

			if ( ! array_key_exists( $key, $answers ) ) {
				continue;
			}

			$value = trim( (string) $answers[ $key ] );

			if ( $value === '' ) {
				continue;
			}

			$data = [
				'pulse_id'       => $pulse_id,
				'question_index' => (int) $index,
				'question_label' => sanitize_text_field( $question['label'] ),
				'question_type'  => sanitize_key( $question['type'] ),
				'answer'         => $value,
				'session_hash'   => $session_hash,
				'created_at'     => $now,
			];

			$formats = [
				'%s',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			];

			// Only include post_id if valid
			// @since 1.2.0
			if ( $post_id > 0 ) {
				$data['post_id'] = $post_id;
				array_splice( $formats, 1, 0, '%d' );
			}

			$wpdb->insert( $table, $data, $formats );
		}

	}


	/**
	 * Handle inline question submission.
	 *
	 * @param array $payload Request payload.
	 * @return void
	 * @since 1.2.0
	 */
	private static function handle_inline_question( array $payload ): void {

		global $wpdb;

		$qid      = sanitize_key( $payload['qid'] );
		$question = sanitize_text_field( $payload['question'] );

		$answer = isset( $payload['answer'] )
			? (string) sanitize_key( $payload['answer'] )
			: '';

		$post_id  = (int) $payload['post_id'];
		$hash     = sanitize_key( $payload['meta']['hash'] );
		$q_type   = sanitize_key( $payload['q_type'] );

		if ( $qid === '' || $answer === '' ) {
			wp_send_json_error( [ 'message' => 'Invalid request.' ], 400 );
		}

		// Validate answer against allowed options for the question type.
		$allowed = Inline_Utils::get_allowed_answers( $q_type );

		if ( empty( $allowed ) || ! in_array( $answer, $allowed, true ) ) {
			wp_send_json_error( [ 'message' => 'Invalid answer.' ], 400 );
		}

		$table = $wpdb->prefix . 'ppls_responses';

		// Prevent duplicates
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} 
				WHERE pulse_id = %s 
				AND session_hash = %s",
				$qid,
				$hash
			)
		);

		if ( $exists > 0 ) {
			wp_send_json_error(
				[ 'message' => 'Already submitted.' ],
				409
			);
		}

		$wpdb->insert(
			$table,
			[
				'pulse_id'       => $qid,
				'post_id'        => $post_id,
				'question_index' => 0,
				'question_label' => $question,
				'question_type'  => 'inline',
				'answer'         => $answer,
				'session_hash'   => $hash,
				'created_at'     => current_time( 'mysql' ),
			],
			[
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			]
		);
	}

}
