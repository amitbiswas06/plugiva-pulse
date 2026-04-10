<?php
namespace Plugiva\Pulse\Admin;

use WP_List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Responses list table.
 */
final class Responses_Table extends WP_List_Table {

	/**
	 * DB table name suffix (without prefix).
	 */
	private const TABLE = 'ppls_responses';

	/**
	 * Last seen datetime.
	 *
	 * @var string
	 * @since 1.1.0
	 */
	protected $last_seen;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			[
				'singular' => 'response',
				'plural'   => 'responses',
				'ajax'     => false,
			]
		);

		// update the responses seen option
		// @since 1.1.0
		$this->last_seen = get_option( 'ppls_last_seen_responses', '' );

		if ( empty( $this->last_seen ) ) {
			$this->last_seen = current_time( 'mysql' );
		}
	}

	/**
	 * Render a single row.
	 *
	 * @param array $item
	 * @return void
	 * @since 1.1.0
	 */
	public function single_row( $item ) {

		$created_at = $item['created_at'] ?? '';

		$is_new = ( ! empty( $created_at ) && $created_at > $this->last_seen );

		$class = $is_new ? 'ppls-row-new' : '';

		echo '<tr class="' . esc_attr( $class ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	protected function get_bulk_actions(): array {
		return [
			'delete' => esc_html__( 'Delete', 'plugiva-pulse' ),
		];
	}

	/**
	 * Render checkbox column.
	 *
	 * @param array $item
	 * @return string
	 */
	protected function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="response_ids[]" value="%d" />',
			(int) $item['id']
		);
	}

	/**
	 * Get table columns.
	 *
	 * @return array
	 */
	public function get_columns(): array {
		return [
			'cb'            => '<input type="checkbox" />',
			'type'          => esc_html__( 'Type', 'plugiva-pulse' ),
			'source'        => esc_html__( 'Source', 'plugiva-pulse' ),
			'question'      => esc_html__( 'Question', 'plugiva-pulse' ),
			'answer'        => esc_html__( 'Answer', 'plugiva-pulse' ),
			'submitted_at'  => esc_html__( 'Submitted', 'plugiva-pulse' ),
		];
	}

	/**
	 * Prepare table items.
	 * Custom plugin table (WP_List_Table); direct queries are intentional.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		global $wpdb;

		$table = $wpdb->prefix . self::TABLE;

		$per_page = 20;
		$paged    = $this->get_pagenum();
		$offset   = ( $paged - 1 ) * $per_page;

		// --- Filter ---
		$where = 'WHERE 1=1';

		$type_filter = isset( $_GET['ppls_type'] )
			? sanitize_key( wp_unslash( $_GET['ppls_type'] ) )
			: '';

		if ( $type_filter === 'inline' ) {
			$where .= " AND question_type = 'inline'";
		} elseif ( $type_filter === 'pulse' ) {
			$where .= " AND (question_type IS NULL OR question_type != 'inline')";
		}

		// --- Total count (with filter) ---
		$total_items = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} {$where}"
		);

		// --- Fetch items ---
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				{$where}
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$per_page,
				$offset
			),
			ARRAY_A
		);

		$this->items = $results;

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->_column_headers = [
			$this->get_columns(),
			[],
			[],
		];
	}

	/**
	 * Default column renderer.
	 *
	 * @param array  $item
	 * @param string $column_name
	 * @return string
	 */
	protected function column_default( $item, $column_name ): string {

		switch ( $column_name ) {

			case 'type':
				$type = ( isset( $item['question_type'] ) && $item['question_type'] === 'inline' )
					? esc_html__( 'Inline', 'plugiva-pulse' )
					: esc_html__( 'Pulse', 'plugiva-pulse' );

				return esc_html( $type );

			case 'source':

				// Inline → show post title with link
				if ( isset( $item['question_type'] ) && $item['question_type'] === 'inline' ) {

					$post_id = isset( $item['post_id'] ) ? (int) $item['post_id'] : 0;

					if ( $post_id > 0 ) {
						$title = get_the_title( $post_id );
						$url   = get_permalink( $post_id );

						if ( $title && $url ) {
							return sprintf(
								'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
								esc_url( $url ),
								esc_html( $title )
							);
						}
					}

					return esc_html__( '(Unknown page)', 'plugiva-pulse' );
				}

				// Pulse → show pulse title
				$title = $this->get_pulse_title( $item['pulse_id'] ?? '' );
				return esc_html( $title );

			case 'question':
				return esc_html( $item['question_label'] ?? '' );

			case 'answer':
				return esc_html( $item['answer'] ?? '' );

			case 'submitted_at':
				return esc_html( $item['created_at'] ?? '' );
		}

		return '';
	}

    /**
     * Get pulse title by ID.
     *
     * @param string $pulse_id
     * @return string
     */
    protected function get_pulse_title( string $pulse_id ): string {

        $pulses = get_option( 'ppls_pulses', [] );

        if ( isset( $pulses[ $pulse_id ]['title'] ) ) {
            return $pulses[ $pulse_id ]['title'];
        }

        return esc_html__( '(Deleted pulse)', 'plugiva-pulse' );
    }


	protected function extra_tablenav( $which ) {

		if ( $which !== 'top' ) {
			return;
		}

		$current = isset( $_GET['ppls_type'] )
			? sanitize_key( $_GET['ppls_type'] )
			: '';

		?>
		<div class="alignleft actions">
			<label for="ppls_type" class="screen-reader-text">
				<?php esc_html_e( 'Filter by type', 'plugiva-pulse' ); ?>
			</label>

			<select name="ppls_type" id="ppls_type">
				<option value=""><?php esc_html_e( 'All Types', 'plugiva-pulse' ); ?></option>
				<option value="pulse" <?php selected( $current, 'pulse' ); ?>>
					<?php esc_html_e( 'Pulse', 'plugiva-pulse' ); ?>
				</option>
				<option value="inline" <?php selected( $current, 'inline' ); ?>>
					<?php esc_html_e( 'Inline', 'plugiva-pulse' ); ?>
				</option>
			</select>

			<button type="submit" formmethod="get" class="button">
				<?php esc_html_e( 'Filter', 'plugiva-pulse' ); ?>
			</button>
		</div>
		<?php
	}

}
