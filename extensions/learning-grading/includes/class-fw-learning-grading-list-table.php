<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_WP_List_Table extends FW_WP_List_Table {

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$data = $this->table_data();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $data;
	}

	public function get_columns() {
		$columns = array(
			'title'   => __( 'Title', 'fw' ),
			'course'  => __( 'Course', 'fw' ),
			'pending' => __( 'Pending', 'fw' ),
		);

		return $columns;
	}

	public function get_hidden_columns() {
		return array();
	}

	public function get_sortable_columns() {
		return array( 'title' => array( 'title', false ) );
	}

	public function column_id( $item ) {
		return $item['id'];
	}

	public function column_default( $item, $column_name ) {
	}

	private function table_data() {
		$data = array();

		return $data;
	}
}