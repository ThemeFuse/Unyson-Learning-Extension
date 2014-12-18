<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Quiz_Pass_Lesson extends FW_Learning_Pass_Lesson {

	/**
	 * @var FW_Extension_Learning_Quiz
	 */
	private $quiz = null;

	/**
	 * {@inheritdoc}
	 */
	public function _init() {
		$this->is_ready = false;
		$this->quiz   = fw()->extensions->get( 'learning-quiz' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_method( $lesson_id ) {
		return fw_render_view( $this->quiz->locate_view_path( 'start-quiz' ), array( 'lesson_id' => $lesson_id ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_priority() {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_method( $lesson_id ) {
		if ( empty( $lesson_id ) || ! $this->quiz->has_quiz( $lesson_id ) ) {
			return false;
		}

		return true;
	}
}