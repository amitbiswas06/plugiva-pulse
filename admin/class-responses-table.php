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
	 * Get table columns.
	 *
	 * @return array
	 */
	public function get_columns(): array {
		return [
			'pulse_id'      => __( 'Pulse', 'plugiva-pulse' ),
			'question'      => __( 'Question', 'plugiva-pulse' ),
			'answer'        => __( 'Answer', 'plugiva-pulse' ),
			'submitted_at'  => __( 'Submitted', 'plugiva-pulse' ),
		];
	}

	/**
	 * Prepare table items.
	 *
	 * @return void
	 */
	public function prepare_items(): void {
		global $wpdb;

		$table = $wpdb->prefix . 'ppls_responses';

		$per_page = 5;
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
}
