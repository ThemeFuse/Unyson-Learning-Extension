<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Progress extends FW_Extension {
	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;


	/**
	 * @var FW_Extension_Learning_Student
	 */
	private $student = null;

	/**
	 * @internal
	 */
	public function _init() {
		$this->learning = fw()->extensions->get( 'learning' );
		$this->student  = fw()->extensions->get( 'learning-student' );
	}

	/**
	 * @param int $course_id
	 *
	 * @return string
	 */
	public function render_progress( $course_id = null ) {
		if ( ! $this->learning->is_course( $course_id ) || ! $this->student->is_student() ) {
			return '';
		}

		if ( is_null( $course_id ) ) {
			global $post;
			$course_id = $post->ID;
		}

		$course_data = $this->student->get_courses_data( $course_id );
		if ( empty( $course_data ) ) {
			return '';
		}

		$lessons      = $this->learning->get_course_lessons( $course_id );
		$user_lessons = $this->student->get_course_lessons( $course_id );

		return $this->render_view( 'progress',
			array(
				'user' => $this->student->id(),
				'course' => $course_id,
				'status' => $course_data['status'],
				'all_lessons' => $lessons,
				'passed_lessons' => $user_lessons )
		);
	}
}