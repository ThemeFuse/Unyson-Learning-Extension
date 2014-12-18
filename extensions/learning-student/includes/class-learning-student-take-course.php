<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Student_Take_Course_Method extends FW_Learning_Take_Course {

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

		$this->form = new FW_Form( $this->student->get_name() . '-take-course', array(
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

		$course_status = $this->student->get_courses_data( $course_id );

		if ( is_array( $course_status ) && ( $course_status['status'] == 'open' || $course_status['status'] == 'completed' ) ) {
			return '';
		}

		$this->register_method();

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		$_SESSION[ $this->student->get_name() . '-take-course-id' ] = $course_id;

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

		echo fw_render_view( $this->student->locate_view_path( 'take-course' ) );

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

		if ( ! isset( $_SESSION ) ) {
			session_start();
		}

		if ( ! isset( $_SESSION[ $this->student->get_name() . '-take-course-id' ] ) ) {
			$errors['corrupt-course-id'] = '';

			return $errors;
		}

		$course_id = (int) $_SESSION[ $this->student->get_name() . '-take-course-id' ];

		if ( ! $this->learning->is_course( $course_id ) ) {
			$errors['corrupt-course-id'] = '';

			return $errors;
		}

		return array();
	}

	/**
	 * @internal
	 */
	public function _form_save() {
		$course_id = (int) $_SESSION[ $this->student->get_name() . '-take-course-id' ];
		unset( $_SESSION[ $this->student->get_name() . '-take-course-id' ] );
		$this->take_course( $course_id );
	}
}