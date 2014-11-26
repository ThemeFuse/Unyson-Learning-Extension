<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

abstract class FW_Learning_Pass_Lesson {

	/**
	 * @var FW_Extension_Learning_Apply_Course
	 */
	private $parent = null;

	/**
	 * Sometimes the method may not confirm at init that is ready or not, or it will be used or not.
	 * In this case in _init() method set the $is_ready member to false.
	 * If the method will need to have to register latter, you have to use the register_method() method manually;
	 *
	 * @var bool
	 */
	protected $is_ready = true;

	/**
	 * Used in case the class needs to initialize data;
	 *
	 * @return void
	 */
	abstract public function _init();

	/**
	 * Return the method will be used to pass the article, this will be an html form to take a test ot a simple button
	 * to confirm that user passed the article.
	 *
	 * @param int $lesson_id
	 *
	 * @return string
	 */
	abstract public function get_method( $lesson_id );

	/**
	 * Set the priority. Priority is used to understand the importance of the pass method, and if there are other method,
	 * to overwrite it or not
	 *
	 * true - priority is important
	 * false - priority is low
	 *
	 * Note: In case you'll set priority true(high), doesn't mean that this method will be used, this will depend on the
	 * order the method was initialised, the las initialised will be used.
	 *
	 * @return bool
	 */
	abstract public function get_priority();

	final public function __construct() {
		$this->parent = fw()->extensions->get( 'learning-apply-course' );
		$this->_init();

		if ( $this->is_ready === true ) {
			$this->register_method();
		}
	}

	final public function register_method() {
		$this->parent->set_lesson_pass_method( $this );
	}

	/**
	 * Confirm that the course was passed
	 *
	 * @param int $lesson_id
	 */
	public final function pass_lesson( $lesson_id ) {
		do_action( 'fw_ext_learning_lesson_passed', $lesson_id );
	}
}

class FW_Learning_Default_Pass_Lesson extends FW_Learning_Pass_Lesson {

	public function _init() {

	}

	public function get_method( $lesson_id ) {
		$this->pass_lesson( $lesson_id );

		return '';
	}

	public function get_priority() {
		return false;
	}
}

new FW_Learning_Default_Pass_Lesson();