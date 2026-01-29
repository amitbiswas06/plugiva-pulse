<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API endpoints for Plugiva Pulse.
 */
final class Rest {

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes(): void {

		register_rest_route(
			'plugiva-pulse/v1',
			'/pulses',
			[
				'methods'             => 'GET',
				'callback'            => [ __CLASS__, 'get_pulses' ],
				'permission_callback' => [ __CLASS__, 'permissions_check' ],
			]
		);

	}

	/**
	 * Permission check for pulses endpoint.
	 *
	 * @return bool
	 */
	public static function permissions_check(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get pulses for block editor.
	 *
	 * @return array
	 */
	public static function get_pulses(): array {

		$pulses = Pulses::all();

		$response = [];

		foreach ( $pulses as $pulse ) {

			if ( empty( $pulse['id'] ) || empty( $pulse['title'] ) ) {
				continue;
			}

			$response[] = [
				'id'    => $pulse['id'],
				'title' => $pulse['title'],
			];
		}

		return $response;
	}
}
