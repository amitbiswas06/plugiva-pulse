<?php
namespace Plugiva\Pulse\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responses admin page controller.
 */
final class Responses_Page {

	public static function init(): void {
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
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

	protected static function export_csv(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'ppls_responses';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY created_at DESC", ARRAY_A );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename=pulse-responses.csv' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$output = fopen( 'php://output', 'w' );

		fputcsv( $output, [
			'Pulse Title',
			'Pulse ID',
			'Question',
			'Answer',
			'Submitted At',
		] );

		$pulses = get_option( 'ppls_pulses', [] );

		foreach ( $rows as $row ) {

			$title = $pulses[ $row['pulse_id'] ]['title'] ?? '(Deleted pulse)';

			fputcsv( $output, [
				$title,
				$row['pulse_id'],
				$row['question_label'],
				$row['answer'],
				$row['created_at'],
			] );
		}

		fclose( $output );
	}

	public static function maybe_export_csv(): void {

		if ( ! is_admin() ) {
			return;
		}

		if ( ! isset( $_GET['page'], $_GET['export'] ) ) {
			return;
		}

		if ( $_GET['page'] !== 'ppls-responses' || $_GET['export'] !== 'csv' ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Unauthorized', 'plugiva-pulse' ) );
		}

		self::export_csv();
		exit;
	}

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

		check_admin_referer( 'bulk-responses' );

		$ids = array_map( 'absint', $_POST['response_ids'] ?? [] );
		if ( empty( $ids ) ) {
			return;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'ppls_responses';
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

	public static function enqueue_assets(): void {

		if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'ppls-responses' ) {
			return;
		}

		wp_enqueue_script(
			'ppls-admin-responses',
			PPLS_URL . 'assets/js/ppls-admin-responses.js',
			[],
			microtime(),
			true
		);

		wp_add_inline_script(
			'ppls-admin-responses',
			'window.PPLS = ' . wp_json_encode( \Plugiva\Pulse\Plugin::get_js_config() ) . ';',
			'before'
		);
	}


}
