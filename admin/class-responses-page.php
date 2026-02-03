<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responses admin page controller.
 */
final class Responses_Page {

	/**
	 * DB table name suffix (without prefix).
	 */
	private const TABLE = 'ppls_responses';

	public static function init(): void {
		add_action( 'admin_init', [ __CLASS__, 'maybe_export_csv' ] );
		add_action( 'admin_init', [ __CLASS__, 'handle_bulk_delete' ] );
		add_action( 'admin_notices', [ __CLASS__, 'maybe_show_notice' ] );
	}

	/**
	 * Render responses page.
	 *
	 * @return void
	 */
	public static function render_responses(): void {

		$table = new Responses_Table();
		$table->prepare_items();

		require PPLS_PATH . 'admin/views/responses-page.php';
	}

	/**
	 * Export responses as CSV.
	 *
	 * @return void
	 */
	protected static function export_csv(): void {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		$rows = $wpdb->get_results(
			"SELECT * FROM {$table} ORDER BY created_at DESC",
			ARRAY_A
		);

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=pulse-responses.csv' );

		// Output buffer instead of fopen/fclose (Plugin Check compliant).
		ob_start();

		// CSV export: raw output intended (non-HTML), values escaped via esc_csv().
		
		echo implode( ',', [
			'Pulse Title',
			'Pulse ID',
			'Question',
			'Answer',
			'Submitted At',
		] ) . "\n";

		$pulses = get_option( 'ppls_pulses', [] );

		foreach ( $rows as $row ) {

			$title = $pulses[ $row['pulse_id'] ]['title'] ?? '(Deleted pulse)';

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo implode( ',', array_map(
				[ __CLASS__, 'esc_csv' ],
				[
					$title,
					$row['pulse_id'],
					$row['question_label'],
					$row['answer'],
					$row['created_at'],
				]
			) ) . "\n";
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		exit;
	}

	/**
	 * Maybe export CSV if requested.
	 *
	 * @return void
	 */
	public static function maybe_export_csv(): void {

		if (
			! is_admin() ||
			empty( $_GET['page'] ) ||
			empty( $_GET['export'] ) ||
			$_GET['page'] !== 'ppls-responses' ||
			$_GET['export'] !== 'csv'
		) {
			return;
		}

		check_admin_referer( 'ppls_export_csv', 'ppls_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'plugiva-pulse' ) );
		}

		self::export_csv();
	}

	/**
	 * Escape value for CSV output.
	 *
	 * @param mixed $value
	 * @return string
	 */
	private static function esc_csv( $value ): string {
		$value = (string) $value;
		$value = str_replace( '"', '""', $value );

		return '"' . $value . '"';
	}

	/**
	 * Handle bulk delete action.
	 *
	 * @return void
	 */
	public static function handle_bulk_delete(): void {

		if (
			! is_admin() ||
			empty( $_POST['action'] ) ||
			$_POST['action'] !== 'delete' ||
			empty( $_GET['page'] ) ||
			$_GET['page'] !== 'ppls-responses'
		) {
			return;
		}

		check_admin_referer( 'bulk-responses', 'ppls_nonce' );

		$ids = array_map( 'absint', $_POST['response_ids'] ?? [] );
		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE id IN ({$placeholders})",
				$ids
			)
		);

		wp_safe_redirect(
			add_query_arg(
				'ppls_deleted',
				'1',
				admin_url( 'admin.php?page=ppls-responses' )
			)
		);
		exit;
	}

	/**
	 * Maybe show deletion notice.
	 *
	 * @return void
	 */
	public static function maybe_show_notice(): void {

		if (
			empty( $_GET['page'] ) ||
			$_GET['page'] !== 'ppls-responses' ||
			empty( $_GET['ppls_deleted'] )
		) {
			return;
		}
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Selected responses deleted.', 'plugiva-pulse' ); ?></p>
		</div>
		<?php
	}

}
