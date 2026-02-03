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

		if ( ! is_array( $pulses ) ) {
			return [];
		}

		// Filter out any invalid entries (e.g. WP_Error)
		return array_filter(
			$pulses,
			static function ( $pulse ) {
				return is_array( $pulse );
			}
		);
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

		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

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

		// --- ID (create vs edit) ---
		$id = isset( $data['id'] ) && $data['id'] !== ''
			? sanitize_text_field( $data['id'] )
			: self::generate_id();

		// --- Title (REQUIRED) ---
		$title = sanitize_text_field( $data['title'] ?? '' );

		if ( $title === '' ) {
			return new \WP_Error(
				'ppls_missing_title',
				esc_html__( 'Pulse title is required.', 'plugiva-pulse' )
			);
		}

		// --- Visibility ---
		$visibility = in_array(
			$data['visibility'] ?? 'public',
			[ 'public', 'admin' ],
			true
		)
			? $data['visibility']
			: 'public';

		// --- Enabled flag ---
		$enabled = ! empty( $data['enabled'] );

		// --- Questions (CRITICAL PART) ---
		$questions = self::validate_questions( $data['questions'] ?? [] );

		// HARD STOP: never embed WP_Error into pulse data
		if ( is_wp_error( $questions ) ) {
			return $questions;
		}

		// --- Timestamps ---
		$created_at = isset( $data['created_at'] ) && $data['created_at'] !== ''
			? sanitize_text_field( $data['created_at'] )
			: current_time( 'mysql' );

		$updated_at = current_time( 'mysql' );

		// --- Final validated pulse ---
		return [
			'id'         => $id,
			'title'      => $title,
			'visibility' => $visibility,
			'enabled'    => $enabled,
			'questions'  => $questions, // ALWAYS array now
			'created_at' => $created_at,
			'updated_at' => $updated_at,
		];
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

		foreach ( $questions as $question ) {

			$label = isset( $question['label'] )
				? sanitize_text_field( $question['label'] )
				: '';

			$type = $question['type'] ?? '';

			// Completely empty row → ignore
			if ( $label === '' && $type === '' ) {
				continue;
			}

			// Partial row → error
			if ( $label === '' || ! in_array( $type, $allowed_types, true ) ) {
				return new \WP_Error(
					'ppls_invalid_question',
					esc_html__( 'Each question must have both a label and a valid type.', 'plugiva-pulse' )
				);
			}

			$id = isset( $question['id'] ) && $question['id'] !== ''
				? sanitize_text_field( $question['id'] )
				: uniqid( 'q_', false );

			$clean[] = [
				'id'    => $id,
				'type'  => $type,
				'label' => $label,
			];

			if ( count( $clean ) === 3 ) {
				break;
			}
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
