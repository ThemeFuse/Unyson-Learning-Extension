<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Quiz_Pass_Lesson extends FW_Learning_Pass_Lesson {

	/**
	 * @var FW_Extension_Learning_Quiz
	 */
	private $parent = null;

	public function _init() {
		$this->is_ready = false;
		$this->parent   = fw()->extensions->get( 'learning-quiz' );
	}

	public function get_method( $lesson_id ) {
		return fw_render_view( $this->parent->locate_view_path( 'start-quiz' ), array( 'lesson_id' => $lesson_id ) );
	}

	public function get_priority() {
		return true;
	}
}