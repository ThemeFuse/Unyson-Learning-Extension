<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Student_Complete_Course_Method extends FW_Learning_Complete_Course {

	/**
	 * @var FW_Form
	 */
	private $form = null;

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

		$this->form = new FW_Form( $this->student->get_name() . '-complete-course', array(
			'render'   => array( $this, '_form_render' ),
			'validate' => array( $this, '_form_validate' ),
			'save'     => array( $this, '_form_save' ),
		) );
	}

	/**
	 * @param int $course_id
	 *
	 * @return string
	 */
	public function get_method( $course_id ) {

		if (
			empty( $course_id )
			|| ! $this->parent->has_course_take_method( $course_id )
			|| ! $this->student->is_subscribed( $course_id )
			|| $this->student->has_completed( $course_id )
		) {
			return '';
		}

		$this->register_method();

		FW_Session::set( FW_Session::get( $this->student->get_name() . '-complete-course-id' ), $course_id );

		ob_start();

		$this->form->render();

		return ob_get_clean();
	}

	/**
	 * @return bool
	 */
	public function get_priority() {
		return false;
	}

	/**
	 * @internal
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function _form_render( $data ) {

		echo fw_render_view( $this->student->locate_view_path( 'complete-course' ) );

		$data['submit']['html'] = '';

		return $data;
	}

	/**
	 * @internal
	 *
	 * @param array $errors
	 *
	 * @return array
	 */
	public function _form_validate( array $errors ) {

		if ( ! $this->learning->is_course( (int) FW_Session::get( $this->student->get_name() . '-complete-course-id' ), -1 ) ) {
			$errors['corrupt-course-id'] = '';

			return $errors;
		}

		return array();
	}

	/**
	 * @internal
	 */
	public function _form_save() {
		$course_id = (int) FW_Session::get( $this->student->get_name() . '-complete-course-id' );
		FW_Session::del( $this->student->get_name() . '-complete-course-id' );
		$this->complete_course( $course_id );
	}
}