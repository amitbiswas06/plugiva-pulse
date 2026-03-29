<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin bootstrap for Plugiva Pulse.
 */
final class Admin {

	public static function init() {
		Menu::register();
		Pulses_Page::init();
		Responses_Page::init();

		// @since 1.1.0
		add_action( 'admin_menu', [ __CLASS__, 'maybe_add_responses_bubble' ], 99 );
		add_action( 'admin_head', [ __CLASS__, 'add_admin_styles' ] );
	}

	/**
	 * Maybe add responses bubble to admin menu.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function maybe_add_responses_bubble(): void {

		// Get last seen (or fallback to now)
		$last_seen = get_option( 'ppls_last_seen_responses', '' );

		if ( empty( $last_seen ) ) {
			// $last_seen = current_time( 'mysql' );
			$last_seen = '2000-01-01 00:00:00';
		}

		// Get new responses count
		$count = \Plugiva\Pulse\Pulses::get_new_responses_count( $last_seen );

		if ( $count <= 0 ) {
			return;
		}

		global $menu;

		foreach ( $menu as $key => $item ) {

			if ( isset( $item[2] ) && $item[2] === 'ppls-pulses' ) {

				$menu[$key][0] .= ' <span class="update-plugins count-' . esc_attr( $count ) . '">
					<span class="plugin-count">' . esc_html( $count ) . '</span>
				</span>';

				break;
			}
		}
	}

	/**
	 * Add admin styles.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function add_admin_styles(): void {
		?>
		<style>
			.wp-list-table.responses > tbody > tr.ppls-row-new {
				background-color: #fff8e5;
			}
		</style>
		<?php
	}

}
