<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_Students_WP_List_Table extends FW_WP_List_Table {

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
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
		$quiz_id = (int) $this->_args['quiz-id'];

		if ( ! fw_ext( 'learning-quiz' )->is_quiz( $quiz_id ) ) {
			return $data;
		}

		$quiz = get_post( $quiz_id );
		$offset = 0; // TODO: Define number
		$number = 0; // TODO: Define offset
		$users  = new WP_User_Query( array(
			'number'     => $number,
			'offset'     => $offset,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'   => 'learning-grading--quiz-status-' . $quiz->ID,
					'value' => 'pending',
				),
				array(
					'key'   => 'learning-grading--quiz-status-' . $quiz->ID,
					'value' => 'passed',
				),
				array(
					'key'   => 'learning-grading--quiz-status-' . $quiz->ID,
					'value' => 'failed',
				),
			)
		) );

		foreach ( $users->get_results() as $user ) {
			$name = '<a href="' . menu_page_url( 'learning-grading', false )
			        . '&sub-page=review&quiz-id=' . $quiz_id . '&user-id=' . $user->ID . '">'
			        . get_user_meta( $user->ID, 'first_name' )[0]
			        . ' '
			        . get_user_meta( $user->ID, 'last_name' )[0]
			        . '</a>';

			$student = new FW_Learning_Student( $user->ID );
			$meta    = $student->get_lessons_data( $quiz->post_parent );

			$data[] = array(
				'title'  => $name,
				'status' => $meta['quiz']['status'],
			);
		}


		return $data;
	}
}