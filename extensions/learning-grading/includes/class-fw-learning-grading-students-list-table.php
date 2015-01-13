<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_Students_WP_List_Table extends FW_WP_List_Table {

	private $items_per_page = 20;
	private $total_items = null;
	private $quiz_id = 0;

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$this->quiz_id = (int) $this->_args['quiz-id'];
		$this->items_count();
		$this->items_per_page = ( ( int ) $this->_args['number'] > 0 ) ? (int) $this->_args['number'] : $this->items_per_page;

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $this->items_per_page
		) );

		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();
		usort( $data, array( &$this, 'sort_data' ) );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	/**
	 * Allows you to sort the data by the variables set in the $_GET
	 *
	 * @return Mixed
	 */
	private function sort_data( $a, $b ) {
		// Set defaults
		$orderby = 'title';
		$order   = 'asc';

		// If orderby is set, use this as the sort column
		if ( ! empty( $_GET['orderby'] ) ) {
			$orderby = $_GET['orderby'];
		}

		// If order is set use this as the order
		if ( ! empty( $_GET['order'] ) ) {
			$order = $_GET['order'];
		}

		$result = strnatcmp( $a[ $orderby ], $b[ $orderby ] );

		if ( $order === 'asc' ) {
			return $result;
		}

		return - $result;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @param  Array $item Data
	 * @param  String $column_name - Current column name
	 *
	 * @return Mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'status':
				return $item[ $column_name ];

			default:
				return '';
		}
	}

	public function get_columns() {
		$columns = array(
			'title'  => __( 'Name', 'fw' ),
			'status' => __( 'Status', 'fw' ),
		);

		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	public function get_sortable_columns() {
		return array( 'title' => array( 'title', true ) );
	}

	public function column_id( $item ) {
		return $item['id'];
	}

	private function table_data() {
		$data    = array();

		if ( ! fw_ext( 'learning-quiz' )->is_quiz( $this->quiz_id ) ) {
			return $data;
		}

		$quiz = get_post( $this->quiz_id );

		$users  = new WP_User_Query( array(
			'number'     => $this->items_per_page,
			'offset'     => $this->get_pagenum() - 1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'   => 'learning-grading-quiz-status-' . $quiz->ID,
					'value' => 'pending',
				),
				array(
					'key'   => 'learning-grading-quiz-status-' . $quiz->ID,
					'value' => 'passed',
				),
				array(
					'key'   => 'learning-grading-quiz-status-' . $quiz->ID,
					'value' => 'failed',
				),
			)
		) );

		foreach ( $users->get_results() as $user ) {
			$name = '<strong><a class="row-title" href="' . menu_page_url( 'learning-grading', false )
			        . '&sub-page=review&quiz-id=' . $this->quiz_id . '&user-id=' . $user->ID . '">'
			        . get_user_meta( $user->ID, 'first_name' )[0]
			        . ' '
			        . get_user_meta( $user->ID, 'last_name' )[0]
			        . '</a></strong>';

			$student = new FW_Learning_Student( $user->ID );
			$meta    = $student->get_lessons_data( $quiz->post_parent );

			$data[] = array(
				'title'  => $name,
				'status' => $meta['quiz']['status'],
			);
		}


		return $data;
	}

	private function items_count() {
		if ( is_null( $this->total_items) ) {
			$query  = new WP_User_Query( array(
				'number'     => 0,
				'meta_query' => array(
					'relation' => 'OR',
					array(
						'key'   => 'learning-grading-quiz-status-' . $this->quiz_id,
						'value' => 'pending',
					),
					array(
						'key'   => 'learning-grading-quiz-status-' . $this->quiz_id,
						'value' => 'passed',
					),
					array(
						'key'   => 'learning-grading-quiz-status-' . $this->quiz_id,
						'value' => 'failed',
					),
				)
			) );

			$this->total_items = $query->get_total();
		}
	}
}