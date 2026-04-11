<?php
/**
 * Inline utilities for Plugiva Pulse.
 *
 * Handles inline question options, labels, and validation helpers.
 *
 * @package Plugiva\Pulse
 */

namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Inline_Utils {

	/**
	 * Get inline question options.
	 *
	 * Central source for inline options used in both rendering
	 * and validation. Supports simple (string) and extended (array) formats.
	 *
	 * @since 1.2.0
	 *
	 * @return array
	 */
	public static function get_options(): array {

		$options = apply_filters(
			'ppls_inline_options',
			[
				'yesno' => [
					'yes' => '👍',
					'no'  => '👎',
				],
				'emoji' => [
					'happy'   => '😊',
					'neutral' => '😐',
					'sad'     => '😞',
				],
			]
		);

		$sanitized = [];

		foreach ( $options as $type => $items ) {

			$type = sanitize_key( $type );

			if ( ! is_array( $items ) ) {
				continue;
			}

			foreach ( $items as $key => $value ) {

				// Prevent undefined index warning
				if ( ! isset( $sanitized[ $type ] ) ) {
					$sanitized[ $type ] = [];
				}

				$sanitized[ $type ][ sanitize_key( $key ) ] = $value;
			}
		}

		return $sanitized;
	}

	/**
	 * Normalize option label.
	 *
	 * Supports:
	 * - 'yes' => '👍'
	 * - 'yes' => [ 'label' => '👍' ]
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $option Option value.
	 * @return string
	 */
	public static function get_label( $option ): string {

		if ( is_array( $option ) && isset( $option['label'] ) ) {
			return (string) $option['label'];
		}

		return (string) $option;
	}

	/**
	 * Get allowed answers for a given type.
	 *
	 * Derived from inline options to keep validation in sync with UI.
	 *
	 * @since 1.2.0
	 *
	 * @param string $type Question type.
	 * @return array
	 */
	public static function get_allowed_answers( string $type ): array {

		$options = self::get_options();

		if ( isset( $options[ $type ] ) && is_array( $options[ $type ] ) ) {

			// Force all keys to string
			return array_map( 'strval', array_keys( $options[ $type ] ) );
		}

		return [];
	}

}