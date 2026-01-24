<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pulse configuration storage and validation.
 *
 * Pulses are stored in wp_options as a single array.
 * This class intentionally contains no UI logic.
 */
final class Pulses {

	const OPTION_KEY = 'ppls_pulses';

	/**
	 * Get all pulses.
	 *
	 * @return array
	 */
	public static function all() {
		$pulses = get_option( self::OPTION_KEY, [] );
		return is_array( $pulses ) ? $pulses : [];
	}

	/**
	 * Get a single pulse by ID.
	 *
	 * @param string $pulse_id
	 * @return array|null
	 */
	public static function get( $pulse_id ) {
		$pulses = self::all();
		return $pulses[ $pulse_id ] ?? null;
	}

	/**
	 * Create or update a pulse.
	 *
	 * @param array $data
	 * @return string Pulse ID
	 */
	public static function save( array $data ) {
		$pulses   = self::all();
		$pulse_id = $data['id'] ?? self::generate_id();

		$validated = self::validate( array_merge( $data, [
			'id' => $pulse_id,
		] ) );

		$pulses[ $pulse_id ] = $validated;

		update_option( self::OPTION_KEY, $pulses, false );

		return $pulse_id;
	}

	/**
	 * Delete a pulse.
	 *
	 * @param string $pulse_id
	 * @return void
	 */
	public static function delete( $pulse_id ) {
		$pulses = self::all();

		if ( isset( $pulses[ $pulse_id ] ) ) {
			unset( $pulses[ $pulse_id ] );
			update_option( self::OPTION_KEY, $pulses, false );
		}
	}

	/**
	 * Validate and normalize pulse data.
	 *
	 * @param array $data
	 * @return array
	 */
	private static function validate( array $data ) {
		$now = current_time( 'mysql' );

		$pulse = [
			'id'         => sanitize_text_field( $data['id'] ),
			'title'      => sanitize_text_field( $data['title'] ?? '' ),
			'visibility' => in_array( $data['visibility'] ?? 'public', [ 'public', 'admin' ], true )
				? $data['visibility']
				: 'public',
			'enabled'    => ! empty( $data['enabled'] ),
			'questions'  => self::validate_questions( $data['questions'] ?? [] ),
			'created_at' => $data['created_at'] ?? $now,
			'updated_at' => $now,
		];

		return $pulse;
	}

	/**
	 * Validate questions array.
	 *
	 * @param array $questions
	 * @return array
	 */
	private static function validate_questions( array $questions ) {
		$allowed_types = [ 'emoji', 'yesno', 'text' ];
		$clean         = [];

		foreach ( array_slice( $questions, 0, 3 ) as $question ) {
			if ( empty( $question['type'] ) || ! in_array( $question['type'], $allowed_types, true ) ) {
				continue;
			}

			$clean[] = [
				'id'    => sanitize_text_field( $question['id'] ?? uniqid( 'q_', false ) ),
				'type'  => $question['type'],
				'label' => sanitize_text_field( $question['label'] ?? '' ),
			];
		}

		return $clean;
	}

	/**
	 * Generate a unique pulse ID.
	 *
	 * @return string
	 */
	private static function generate_id() {
		return uniqid( 'pulse_', false );
	}
}
