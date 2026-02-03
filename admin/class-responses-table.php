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
			'cb'           	=> '<input type="checkbox" />',
            'pulse_title'   => esc_html__( 'Pulse Title', 'plugiva-pulse' ),
			'pulse_id'      => esc_html__( 'Pulse ID', 'plugiva-pulse' ),
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

		$total_items = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table}"
		);

		$results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table}
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
            case 'pulse_title':
	            $title = $this->get_pulse_title( $item['pulse_id'] );
	            return esc_html( $title );

			case 'pulse_id':
				return esc_html( $item['pulse_id'] );

			case 'question':
				return esc_html( $item['question_label'] );

			case 'answer':
				return esc_html( $item['answer'] );

			case 'submitted_at':
				return esc_html( $item['created_at'] );
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

}
