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
		$this->student = fw()->extensions->get( 'learning-student' );

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
		if ( ! $this->learning->is_lesson( (int) FW_Session::get( $this->student->get_name() . '-pass-lesson-id', -1 ) ) ) {
			$errors['corrupt-lesson-id'] = '';

			return $errors;
		}

		return array();
	}

	/**
	 * @internal
	 */
	public function _form_save() {
		$lesson_id = (int) $_SESSION[ $this->student->get_name() . '-pass-lesson-id' ];

		unset( $_SESSION[ $this->student->get_name() . '-pass-lesson-id' ] );

		$this->pass_lesson( $lesson_id );
	}
}