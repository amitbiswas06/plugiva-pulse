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
	 * Get count of new responses since last seen datetime.
	 *
	 * @param string $last_seen MySQL datetime string.
	 * @return int
	 * @since 1.1.0
	 */
	public static function get_new_responses_count( string $last_seen ): int {
		global $wpdb;

		$table = $wpdb->prefix . 'ppls_responses';

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE created_at > %s",
				$last_seen
			)
		);
	}


	/**
	 * Get all stored pulses.
	 *
	 * @return array<string, array> Pulse ID => pulse data
	 */
	public static function all(): array {

		// Fetch the option that stores all pulses.
		$pulses = get_option( self::OPTION_KEY, [] );

		// Safety: if the option was corrupted or stored incorrectly,
		// always return a predictable array.
		if ( ! is_array( $pulses ) ) {
			return [];
		}

		return $pulses;
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
	 * Save or update a pulse.
	 *
	 * @param array $pulse Pulse data.
	 * @return string|\WP_Error Pulse ID on success, error on failure.
	 */
	public static function save( array $pulse ) {

		// Validate and normalize the pulse structure.
		$pulse = self::validate( $pulse );

		if ( is_wp_error( $pulse ) ) {
			return $pulse;
		}

		// Load all existing pulses.
		$pulses = self::all();

		// Store or replace this pulse by its ID.
		// This ensures multiple pulses coexist safely.
		$pulses[ $pulse['id'] ] = $pulse;

		// Persist the full pulse set back to the database.
		update_option( self::OPTION_KEY, $pulses );

		// Return the pulse ID for redirects / messaging.
		return $pulse['id'];
	}

	/**
	 * Delete a pulse by ID.
	 *
	 * @param string $id Pulse ID.
	 * @return bool True on success, false if pulse not found.
	 */
	public static function delete( string $id ): bool {

		// Load all existing pulses.
		$pulses = self::all();

		// If the pulse does not exist, do nothing.
		if ( ! isset( $pulses[ $id ] ) ) {
			return false;
		}

		// Remove the pulse from the collection.
		unset( $pulses[ $id ] );

		// Persist the updated pulse set.
		update_option( self::OPTION_KEY, $pulses );

		return true;
	}

	/**
	 * Validate and normalize pulse data.
	 *
	 * @param array $data
	 * @return array
	 */
	private static function validate( array $data ) {

		// --- ID (create vs edit) ---
		// Existing ID = internal identifier → keep as-is.
		// Missing ID = new pulse → generate one.
		$id = ( isset( $data['id'] ) && $data['id'] !== '' )
			? sanitize_key( $data['id'] )
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
		$raw_visibility = isset( $data['visibility'] )
			? sanitize_key( $data['visibility'] )
			: 'public';

		$visibility = in_array(
			$raw_visibility,
			[ 'public', 'admin' ],
			true
		)
			? $raw_visibility
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

			$id = ( isset( $question['id'] ) && $question['id'] !== '' )
				? sanitize_key( $question['id'] )
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
