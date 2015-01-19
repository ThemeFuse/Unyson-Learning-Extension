<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_Quiz_Review_Form {

	/**
	 * @var FW_FOrm
	 */
	private $form = null;

	/**
	 * @var FW_Learning_Student|null
	 */
	private $user = null;

	/**
	 * @var FW_Learning_Grading_Quiz_Review|null
	 */
	private $quiz = null;

	/**
	 * @param int $user
	 * @param int $quiz
	 */
	public function __construct( $user, $quiz ) {
		$this->user = new FW_Learning_Student( $user );
		$this->quiz = $this->user->get_quiz_data( $quiz );
		$this->form = new FW_Form( 'learning-grading-review-quiz', array(
			'render'   => array( $this, '_render' ),
			'validate' => array( $this, '_validate' ),
			'save'     => array( $this, '_save' ),
		) );
		$this->add_static();
	}

	/**
	 * Render quiz review form
	 *
	 * @return string
	 */
	public function render() {
		if ( ! $this->user->id() || empty( $this->quiz ) ) {
			return '';
		}

		ob_start();
		$this->form->render();

		return ob_get_clean();
	}

	/**
	 * @internal
	 *
	 * @param $data
	 *
	 * @return array
	 */
	public function _render( $data ) {

		echo fw_render_view( fw_ext( 'learning-grading' )->get_declared_path() . '/views/quiz-review-from.php', array(
			'questions' => $this->quiz->get_questions()
		) );

		$data['submit']['html'] = '';

		return $data;
	}

	/**
	 * @internal
	 *
	 * @param $errors
	 *
	 * @return array
	 */
	public function _validate( $errors ) {
		return $errors;
	}

	/**
	 * @internal
	 */
	public function _save() {

	}

	private function add_static() {
		wp_enqueue_style( 'fw' );
	}
}