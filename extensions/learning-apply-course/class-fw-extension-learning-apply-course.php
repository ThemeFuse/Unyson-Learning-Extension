<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Apply_Course extends FW_Extension {

	/**
	 * @var FW_Learning_Pass_Lesson
	 */
	private $lesson_pass_methods = array();

	/**
	 * @var FW_Learning_Take_Course
	 */
	private $take_course_method = null;

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @internal
	 */
	public function _init() {
		$this->learning = fw()->extensions->get( 'learning' );
	}

	/**
	 * @param FW_Learning_Pass_Lesson $method
	 */
	public function set_lesson_pass_method( FW_Learning_Pass_Lesson $method ) {

		//If the current method is not set or has low priority automatically set the new method
		if ( empty( $this->lesson_pass_methods ) || ( $this->lesson_pass_methods->get_priority() == false ) ) {
			$this->lesson_pass_methods = $method;

			return;
		}

		//If current method has high priority, need to check the priority of the new method
		if ( $method->get_priority() == true ) {
			$this->lesson_pass_methods = $method;

			return;
		}
	}

	/**
	 * Return the lesson pass method
	 *
	 * @param int $lesson_id
	 *
	 * @return string
	 */
	public function get_lesson_pass_method( $lesson_id ) {
		if ( ! $this->learning->is_lesson( $lesson_id ) ) {
			return '';
		}

		if ( empty ( $this->lesson_pass_methods ) ) {
			return '';
		}

		return $this->lesson_pass_methods->get_method( $lesson_id );
	}

	/**
	 * @param FW_Learning_Take_Course $method
	 */
	public function set_take_course_method( FW_Learning_Take_Course $method ) {

		//If the current method is not set or has low priority automatically set the new method
		if ( empty( $this->take_course_method ) || ( $this->take_course_method->get_priority() == false ) ) {
			$this->take_course_method = $method;

			return;
		}

		//If current method has high priority, need to check the priority of the new method
		if ( $method->get_priority() == true ) {
			$this->take_course_method = $method;

			return;
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return string
	 */
	public function get_take_course_method( $course_id ) {
		if ( ! $this->learning->is_course( $course_id ) ) {
			return '';
		}

		if ( empty ( $this->take_course_method ) ) {
			return '';
		}

		return $this->take_course_method->get_method( $course_id );
	}

}