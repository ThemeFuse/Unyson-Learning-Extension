<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_Quiz_WP_List_Table extends FW_WP_List_Table {

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
			case 'course':
			case 'in-pending':
				return $item[ $column_name ];

			default:
				return '';
		}
	}

	public function get_columns() {
		$columns = array(
			'title'  => __( 'Title', 'fw' ),
			'course' => __( 'Course', 'fw' ),
			'in-pending' => __( 'Pending', 'fw' ),
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
		$data   = array();
		$offset = 0;
		$quizes = get_posts( array(
			'post_type'      => fw_ext( 'learning-quiz' )->get_quiz_post_type(),
			'posts_per_page' => 10, //FIXME: Add the dynamic posts count
			'offset'         => $offset, //FIXME: Review pagination
		) );

		foreach ( $quizes as $quiz ) {
			$title = $quiz->post_title;
			$query = new WP_User_Query(array(
				'meta_query' => array(
					0 => array(
						'key'     =>  'learning-grading-quiz-status-' . $quiz->ID,
						'value'   => 'pending',
					),
				)
			));

			$count = $query->get_total();

			if ( $count ) {
				$title = '<a href="' . menu_page_url( 'learning-grading', false ) . '&sub-page=users&quiz-id=' . $quiz->ID . '">' . $quiz->post_title . '</a>';
			}

			$course       = get_post( get_post( $quiz->post_parent )->post_parent );
			$course_title = ( $course instanceof WP_Post ) ? $course->post_title : '';
			$course_link  = ( $course instanceof WP_Post ) ? get_permalink( $course->ID ) : '';
			$course_title = ( ! empty( $course_link ) )
				? '<a href="' . $course_link . '">' . $course_title . '</a>'
				: '';

			$data[] = array(
				'title'  => $title,
				'course' => $course_title,
				'in-pending' => $count,
			);
		}


		return $data;
	}
}