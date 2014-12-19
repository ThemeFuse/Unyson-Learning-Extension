<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Student_Pass_Lesson extends FW_Learning_Pass_Lesson {

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
		$this->is_ready = false;
		$this->learning = fw()->extensions->get( 'learning' );
		$this->student  = fw()->extensions->get( 'learning-student' );

		$this->form = new FW_Form( $this->student->get_name() . '-pass-lesson', array(
			'render'   => array( $this, '_form_render' ),
			'validate' => array( $this, '_form_validate' ),
			'save'     => array( $this, '_form_save' ),
		) );
	}

	/**
	 * @param int $lesson_id
	 *
	 * @return string
	 */
	public function get_method( $lesson_id ) {

		if ( $this->student->has_passed( $lesson_id ) ) {
			return '';
		}

		FW_Session::set( $this->student->get_name() . '-pass-lesson-id', $lesson_id );

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

		echo fw_render_view( $this->student->locate_view_path( 'pass-lesson' ) );

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
		if ( ! $this->learning->is_lesson( (int) FW_Session::get( $this->student->get_name() . '-pass-lesson-id', - 1 ) )
		) {
			$errors['corrupt-lesson-id'] = '';

			return $errors;
		}

		$lesson_id = (int) FW_Session::get( $this->student->get_name() . '-pass-lesson-id' );
		$course    = $this->learning->get_lesson_course( $lesson_id );

		if ( $course instanceof WP_Post && ! $this->student->is_subscribed( $course->ID ) ) {
			$errors['unsubscribed-course'] = __( 'You have to subscribe to the course in order to pass the lesson',
				'fw' );

			return $errors;
		}

		if (
			$this->student->get_config( 'lessons-in-order' ) == true
			&& (
				! $this->learning->is_first_lesson( $lesson_id )
				&& ! $this->student->has_passed( $this->learning->get_previous_lesson( $lesson_id )->ID )
			)
		) {
			$errors['unpassed-previous-lesson'] = __( 'You have to pass the previous lesson in order to pass the current one',
				'fw' );

			return $errors;
		}

		return $errors;
	}

	/**
	 * @internal
	 */
	public function _form_save() {
		$lesson_id = (int) FW_Session::get( $this->student->get_name() . '-pass-lesson-id' );

		FW_Session::del( $this->student->get_name() . '-pass-lesson-id' );

		$this->pass_lesson( $lesson_id );
	}
}