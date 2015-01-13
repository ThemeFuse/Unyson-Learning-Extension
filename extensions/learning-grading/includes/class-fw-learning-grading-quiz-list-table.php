<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_Quiz_WP_List_Table extends FW_WP_List_Table {

	private $items_per_page = 20;
	private $total_items = null;

	/**
	 * Prepare the items for the table to process
	 *
	 * @return Void
	 */
	public function prepare_items() {
		$this->items_count();
		$this->items_per_page = ( ( int ) $this->_args['number'] > 0 ) ? (int) $this->_args['number'] : $this->items_per_page;
		$columns              = $this->get_columns();
		$hidden               = $this->get_hidden_columns();
		$sortable             = $this->get_sortable_columns();

		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $this->items_per_page
		) );

		$args = array();

		$args = array_merge( $args, $this->sort_data() );

		$data = $this->table_data( $args );

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}


	private function sort_data() {

		$return = array();

		if ( FW_Request::GET( 'orderby' ) ) {
			$return['orderby'] = FW_Request::GET( 'orderby' );
		}

		if ( FW_Request::GET( 'order' ) ) {
			$return['order'] = FW_Request::GET( 'order' );
		}

		return $return;
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
			'title'      => __( 'Title', 'fw' ),
			'course'     => __( 'Course', 'fw' ),
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

	private function table_data( array $args = array() ) {
		$data = array();

		$default = array(
			'post_type'      => fw_ext( 'learning-quiz' )->get_quiz_post_type(),
			'posts_per_page' => $this->items_per_page,
			'offset'         => $this->get_pagenum() - 1,
			'author'         => get_current_user_id()
		);

		$args = array_merge( $args, $default );

		$quizes = new WP_Query( $args );

		foreach ( $quizes->get_posts() as $quiz ) {
			$title = $quiz->post_title;
			$query = new WP_User_Query( array(
				'meta_query' => array(
					0 => array(
						'key'   => 'learning-grading-quiz-status-' . $quiz->ID,
						'value' => 'pending',
					),
				)
			) );

			$count = $query->get_total();

			if ( $count ) {
				$title = '<strong><a class="row-title" href="' . menu_page_url( 'learning-grading',
						false ) . '&sub-page=users&quiz-id=' . $quiz->ID . '">' . $quiz->post_title . '</a></strong>';
			}

			$course       = get_post( get_post( $quiz->post_parent )->post_parent );
			$course_title = ( $course instanceof WP_Post ) ? $course->post_title : '';
			$course_link  = ( $course instanceof WP_Post ) ? get_permalink( $course->ID ) : '';
			$course_title = ( ! empty( $course_link ) )
				? '<a href="' . $course_link . '">' . $course_title . '</a>'
				: '';

			$data[] = array(
				'title'      => $title,
				'course'     => $course_title,
				'in-pending' => $count,
			);
		}

		return $data;
	}

	private function items_count() {
		if ( is_null( $this->total_items ) ) {
			$count             = wp_count_posts( fw_ext( 'learning-quiz' )->get_quiz_post_type(), 'publish' );
			$this->total_items = $count->publish + $count->private;
		}
	}
}