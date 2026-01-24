<?php
namespace Plugiva\Pulse;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles database schema creation for Plugiva Pulse.
 *
 * This class is intentionally limited to schema concerns only.
 * No queries, no data access, no migrations beyond dbDelta().
 */
final class Schema {

	/**
	 * Create or update database tables.
	 *
	 * Runs on plugin activation via dbDelta().
	 *
	 * @return void
	 */
	public static function install() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ppls_responses';
		$charset    = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$sql = "
			CREATE TABLE {$table_name} (
				id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
				pulse_id VARCHAR(64) NOT NULL,
				question_id VARCHAR(64) NOT NULL,
				question_type VARCHAR(20) NOT NULL,
				answer LONGTEXT NOT NULL,
				session_hash CHAR(64) NOT NULL,
				submitted_at DATETIME NOT NULL,
				PRIMARY KEY  (id),
				KEY pulse_id (pulse_id),
				KEY submitted_at (submitted_at)
			) {$charset};
		";

		dbDelta( $sql );
	}
}
